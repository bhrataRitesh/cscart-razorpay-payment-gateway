<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Services;

use Tygh\Addons\Razorpay\Contracts\PaymentApiInterface;
use Tygh\Addons\Razorpay\Contracts\TransactionRepositoryInterface;
use Tygh\Addons\Razorpay\Contracts\WebhookLogRepositoryInterface;
use Tygh\Addons\Razorpay\Contracts\WebhookProcessorInterface;
use Tygh\Addons\Razorpay\Repositories\TransactionRepository;
use Tygh\Addons\Razorpay\Repositories\WebhookLogRepository;

/**
 * Class WebhookService
 * Implements WebhookProcessorInterface for Razorpay asynchronous webhook ingestion and settlement
 */
class WebhookService implements WebhookProcessorInterface
{
    /**
     * @var TransactionRepositoryInterface
     */
    protected $transaction_repository;

    /**
     * @var WebhookLogRepositoryInterface
     */
    protected $webhook_log_repository;

    /**
     * WebhookService constructor.
     *
     * @param TransactionRepositoryInterface|null $transaction_repo
     * @param WebhookLogRepositoryInterface|null  $log_repo
     */
    public function __construct(
        TransactionRepositoryInterface $transaction_repo = null,
        WebhookLogRepositoryInterface $log_repo = null
    ) {
        $this->transaction_repository = $transaction_repo ?: new TransactionRepository();
        $this->webhook_log_repository = $log_repo ?: new WebhookLogRepository();
    }

    /**
     * @inheritDoc
     */
    public function process($raw_payload, $signature, array $processor_params)
    {
        $webhook_secret = !empty($processor_params['webhook_secret']) ? $processor_params['webhook_secret'] : '';

        if (!empty($webhook_secret)) {
            if (!RazorpayApi::verifyWebhookSignature($raw_payload, $signature, $webhook_secret)) {
                return false;
            }
        }

        $event = json_decode($raw_payload, true);
        if (empty($event['event'])) {
            return false;
        }

        $event_name = $event['event'];
        $payload = $event['payload'] ?? [];

        $order_id = 0;
        $payment_entity = null;

        if (!empty($payload['payment']['entity'])) {
            $payment_entity = $payload['payment']['entity'];
            if (!empty($payment_entity['notes']['cscart_order_id'])) {
                $order_id = (int) $payment_entity['notes']['cscart_order_id'];
            }
        }

        if (!$order_id && !empty($payload['order']['entity']['receipt'])) {
            $receipt = $payload['order']['entity']['receipt'];
            if (preg_match('/^order_(\d+)$/', $receipt, $matches)) {
                $order_id = (int) $matches[1];
            }
        }

        $this->webhook_log_repository->logEvent(
            !empty($event['id']) ? (string) $event['id'] : '',
            $event_name,
            !empty($payment_entity['order_id']) ? (string) $payment_entity['order_id'] : '',
            !empty($payment_entity['id']) ? (string) $payment_entity['id'] : '',
            $raw_payload,
            $order_id ? 'processed' : 'ignored'
        );

        if (!$order_id) {
            return false;
        }

        $order_info = fn_get_order_info($order_id);
        if (empty($order_info)) {
            return false;
        }

        $settled_statuses = fn_get_settled_order_statuses();

        if ($event_name === 'payment.captured' || $event_name === 'order.paid') {
            $this->transaction_repository->markCaptured(
                $order_id,
                !empty($payment_entity['id']) ? (string) $payment_entity['id'] : ''
            );

            if (!in_array($order_info['status'], $settled_statuses, true)) {
                $success_status = !empty($processor_params['order_status_success'])
                    ? $processor_params['order_status_success']
                    : 'P';

                $pp_response = [
                    'order_status'        => $success_status,
                    'transaction_id'      => !empty($payment_entity['id']) ? $payment_entity['id'] : '',
                    'reason_text'         => __('razorpay.payment_confirmed_via_webhook'),
                    'razorpay_payment_id' => !empty($payment_entity['id']) ? $payment_entity['id'] : '',
                    'razorpay_order_id'   => !empty($payment_entity['order_id']) ? $payment_entity['order_id'] : '',
                ];

                fn_finish_payment($order_id, $pp_response);
            }
        } elseif ($event_name === 'payment.failed') {
            $this->transaction_repository->markFailed(
                $order_id,
                !empty($payment_entity['error_code']) ? (string) $payment_entity['error_code'] : 'PAYMENT_FAILED',
                !empty($payment_entity['error_description']) ? (string) $payment_entity['error_description'] : 'Payment failed via webhook'
            );

            if ($order_info['status'] === 'N' || $order_info['status'] === 'O') {
                $failed_status = !empty($processor_params['order_status_failed'])
                    ? $processor_params['order_status_failed']
                    : 'F';

                $err_desc = !empty($payment_entity['error_description'])
                    ? $payment_entity['error_description']
                    : __('razorpay.payment_failed_via_webhook');

                $pp_response = [
                    'order_status' => $failed_status,
                    'reason_text'  => $err_desc,
                ];

                fn_finish_payment($order_id, $pp_response);
            }
        }

        return true;
    }
}
