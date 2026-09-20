<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Contracts;

/**
 * Interface WebhookProcessorInterface
 * Defines contract for verifying and processing Razorpay webhooks
 */
interface WebhookProcessorInterface
{
    /**
     * Process an incoming webhook
     *
     * @param string $raw_payload Raw request payload
     * @param string $signature   Header signature
     * @param array  $processor_params Configuration parameters
     * @return bool
     */
    public function process($raw_payload, $signature, array $processor_params);
}
