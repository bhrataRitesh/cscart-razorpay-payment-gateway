<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Handlers\Modes;

use Tygh\Addons\Razorpay\Contracts\PaymentModeHandlerInterface;
use Tygh\Addons\Razorpay\Contracts\WebhookProcessorInterface;
use Tygh\Addons\Razorpay\DTO\PaymentContext;
use Tygh\Addons\Razorpay\Helper;
use Tygh\Addons\Razorpay\Services\WebhookService;

/**
 * Class WebhookHandler
 * Strategy handling incoming asynchronous webhook notifications from Razorpay
 */
class WebhookHandler implements PaymentModeHandlerInterface
{
    /**
     * @var WebhookProcessorInterface
     */
    protected $webhook_processor;

    /**
     * WebhookHandler constructor.
     *
     * @param WebhookProcessorInterface|null $processor
     */
    public function __construct(WebhookProcessorInterface $processor = null)
    {
        $this->webhook_processor = $processor ?: new WebhookService();
    }

    /**
     * @inheritDoc
     */
    public function handle(PaymentContext $context)
    {
        $raw_payload = @file_get_contents('php://input');
        $signature = isset($_SERVER['HTTP_X_RAZORPAY_SIGNATURE']) ? $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] : '';

        $processor_params = Helper::getActiveProcessorParams();
        $result = $this->webhook_processor->process($raw_payload, $signature, $processor_params);

        if ($result) {
            http_response_code(200);
            exit('OK');
        } else {
            http_response_code(400);
            exit('Webhook verification failed');
        }
    }
}
