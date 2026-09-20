<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Handlers\Modes;

use Tygh\Addons\Razorpay\Contracts\PaymentModeHandlerInterface;
use Tygh\Addons\Razorpay\Contracts\TransactionRepositoryInterface;
use Tygh\Addons\Razorpay\DTO\PaymentContext;
use Tygh\Addons\Razorpay\Helper;
use Tygh\Addons\Razorpay\Repositories\TransactionRepository;
use Tygh\Addons\Razorpay\Services\PaymentService;

/**
 * Class ReturnHandler
 * Strategy handling customer return callback and timing-safe cryptographic signature verification
 */
class ReturnHandler implements PaymentModeHandlerInterface
{
    /**
     * @var TransactionRepositoryInterface
     */
    protected $transaction_repository;

    /**
     * ReturnHandler constructor.
     *
     * @param TransactionRepositoryInterface|null $transaction_repo
     */
    public function __construct(TransactionRepositoryInterface $transaction_repo = null)
    {
        $this->transaction_repository = $transaction_repo ?: new TransactionRepository();
    }

    /**
     * @inheritDoc
     */
    public function handle(PaymentContext $context)
    {
        $order_id = $context->getOrderId();
        $razorpay_order_id = !empty($_REQUEST['razorpay_order_id']) ? (string) $_REQUEST['razorpay_order_id'] : '';
        $razorpay_payment_id = !empty($_REQUEST['razorpay_payment_id']) ? (string) $_REQUEST['razorpay_payment_id'] : '';
        $razorpay_signature = !empty($_REQUEST['razorpay_signature']) ? (string) $_REQUEST['razorpay_signature'] : '';

        if (!$order_id || !fn_check_payment_script('razorpay.php', $order_id)) {
            fn_order_placement_routines('checkout_redirect');
            exit;
        }

        $order_info = fn_get_order_info($order_id);
        $processor_data = fn_get_processor_data($order_info['payment_id']);
        $processor_params = $processor_data['processor_params'] ?? [];

        $payment_service = new PaymentService($processor_params);
        $api = $payment_service->getApi();

        $is_valid = $api->verifyPaymentSignature($razorpay_order_id, $razorpay_payment_id, $razorpay_signature);

        if ($is_valid) {
            $success_status = !empty($processor_params['order_status_success'])
                ? $processor_params['order_status_success']
                : 'P';

            $pp_response = [
                'order_status'        => $success_status,
                'transaction_id'      => $razorpay_payment_id,
                'reason_text'         => __('razorpay.payment_successful'),
                'razorpay_payment_id' => $razorpay_payment_id,
                'razorpay_order_id'   => $razorpay_order_id,
                'razorpay_signature'  => $razorpay_signature,
            ];

            // Enrich transaction with payment method details from Razorpay API
            $payment_details = $api->fetchPayment($razorpay_payment_id);
            if ($payment_details['success'] && !empty($payment_details['data'])) {
                $enriched = Helper::extractPaymentDetails($payment_details['data']);
                $pp_response = array_merge($pp_response, $enriched);
            }

            $this->transaction_repository->markAuthorized(
                $order_id,
                $razorpay_order_id,
                $razorpay_payment_id,
                $razorpay_signature,
                !empty($order_info['total']) ? (float) $order_info['total'] : 0.00,
                !empty($order_info['secondary_currency']) ? (string) $order_info['secondary_currency'] : CART_PRIMARY_CURRENCY,
                !empty($order_info['timestamp']) ? (int) $order_info['timestamp'] : TIME
            );

            fn_finish_payment($order_id, $pp_response);
            fn_order_placement_routines('route', $order_id);
            exit;
        } else {
            $failed_status = !empty($processor_params['order_status_failed'])
                ? $processor_params['order_status_failed']
                : 'F';

            $pp_response = [
                'order_status' => $failed_status,
                'reason_text'  => __('razorpay.signature_verification_failed'),
            ];

            $this->transaction_repository->markFailed(
                $order_id,
                'BAD_SIGNATURE',
                'Signature verification failed'
            );

            fn_finish_payment($order_id, $pp_response);
            fn_set_notification('E', __('error'), __('razorpay.signature_verification_failed'));
            fn_order_placement_routines('route', $order_id);
            exit;
        }
    }
}
