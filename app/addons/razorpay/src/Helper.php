<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay;

use Tygh\Addons\Razorpay\Model\PaymentModel;
use Tygh\Addons\Razorpay\Model\PaymentProcessorModel;
use Tygh\Addons\Razorpay\Model\TransactionModel;
use Tygh\Addons\Razorpay\Model\WebhookLogModel;
use Tygh\Addons\Razorpay\Services\RazorpayApi;
use Tygh\Core\ApplicationInterface;
use Tygh\Tygh;

class Helper
{
    /**
     * @var \Tygh\Core\ApplicationInterface
     */
    protected $application;

    public function __construct(ApplicationInterface $app = null)
    {
        $this->application = $app ?: Tygh::$app;
    }

    /**
     * Handles incoming Razorpay Webhook notification (delegates to WebhookService)
     *
     * @param string $raw_payload Raw HTTP request payload
     * @param string $signature Signature from HTTP_X_RAZORPAY_SIGNATURE header
     * @param array $processor_params Configuration parameters for Razorpay
     * @return bool
     */
    public static function processWebhook($raw_payload, $signature, array $processor_params)
    {
        $service = isset(Tygh::$app['addons.razorpay.services.webhook'])
            ? Tygh::$app['addons.razorpay.services.webhook']
            : new \Tygh\Addons\Razorpay\Services\WebhookService();

        return $service->process($raw_payload, $signature, $processor_params);
    }

    /**
     * Extracts and normalizes payment details from Razorpay API payload
     *
     * @param array $pdata
     * @return array
     */
    public static function extractPaymentDetails(array $pdata)
    {
        $details = [];

        if (!empty($pdata['method'])) {
            $details['payment_method'] = $pdata['method'];
        }

        if (!empty($pdata['card']['network'])) {
            $details['card_network'] = $pdata['card']['network'];
            $details['card_last4'] = $pdata['card']['last4'] ?? '';
            $details['card_type'] = $pdata['card']['type'] ?? '';
        } elseif (!empty($pdata['vpa'])) {
            $details['vpa'] = $pdata['vpa'];
        } elseif (!empty($pdata['bank'])) {
            $details['bank'] = $pdata['bank'];
        } elseif (!empty($pdata['wallet'])) {
            $details['wallet'] = $pdata['wallet'];
        }

        return $details;
    }

    /**
     * Retrieves the processor params for the active Razorpay payment method
     *
     * @return array
     */
    public static function getActiveProcessorParams()
    {
        $processor_model = isset(Tygh::$app['addons.razorpay.model.payment_processor'])
            ? Tygh::$app['addons.razorpay.model.payment_processor']
            : new PaymentProcessorModel();

        $processor = $processor_model->getByScript('razorpay.php');
        if (empty($processor['processor_id'])) {
            return [];
        }

        $payment_model = isset(Tygh::$app['addons.razorpay.model.payment'])
            ? Tygh::$app['addons.razorpay.model.payment']
            : new PaymentModel();

        $payment = $payment_model->getActiveByProcessorId((int) $processor['processor_id']);
        if (empty($payment['payment_id'])) {
            return [];
        }

        $processor_data = fn_get_processor_data($payment['payment_id']);
        return $processor_data['processor_params'] ?? [];
    }
}
