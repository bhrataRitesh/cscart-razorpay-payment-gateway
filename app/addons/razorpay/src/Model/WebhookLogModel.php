<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Model;

/**
 * Model for ?:razorpay_webhook_logs table
 */
class WebhookLogModel extends BaseModel
{
    /**
     * @var string Table name
     */
    protected $table = '?:razorpay_webhook_logs';

    /**
     * @var string Primary key
     */
    protected $primary_key = 'log_id';

    /**
     * Log an incoming webhook event
     *
     * @param string $event_id
     * @param string $event_type
     * @param string $order_id
     * @param string $payment_id
     * @param string $raw_payload
     * @param string $status
     * @return int|bool
     */
    public function logEvent($event_id, $event_type, $order_id, $payment_id, $raw_payload, $status = 'processed')
    {
        return $this->insert([
            'event_id'            => (string) $event_id,
            'event_type'          => (string) $event_type,
            'razorpay_order_id'   => (string) $order_id,
            'razorpay_payment_id' => (string) $payment_id,
            'payload'             => (string) $raw_payload,
            'status'              => (string) $status,
            'created_at'          => TIME,
        ]);
    }

    /**
     * Find webhook log by Razorpay Event ID
     *
     * @param string $event_id
     * @return array|false
     */
    public function getByEventId($event_id)
    {
        return $this->find(['event_id' => (string) $event_id]);
    }
}
