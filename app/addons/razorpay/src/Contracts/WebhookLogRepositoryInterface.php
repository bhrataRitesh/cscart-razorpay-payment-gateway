<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Contracts;

/**
 * Interface WebhookLogRepositoryInterface
 * Repository contract for managing Razorpay webhook audit logs
 */
interface WebhookLogRepositoryInterface
{
    /**
     * Log an incoming webhook event
     *
     * @param string $event_id
     * @param string $event_type
     * @param string $order_id
     * @param string $payment_id
     * @param string $raw_payload
     * @param string $status
     * @return bool
     */
    public function logEvent($event_id, $event_type, $order_id, $payment_id, $raw_payload, $status = 'processed');

    /**
     * Find webhook log by Razorpay Event ID
     *
     * @param string $event_id
     * @return array
     */
    public function findByEventId($event_id);
}
