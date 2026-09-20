<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Contracts;

/**
 * Interface PaymentApiInterface
 * Defines contract for communicating with the Razorpay REST API
 */
interface PaymentApiInterface
{
    /**
     * Create a Razorpay Order
     *
     * @param array $params Order parameters (amount in subunits, currency, receipt, notes, etc.)
     * @return array [success => bool, data => array, error => string, http_code => int]
     */
    public function createOrder(array $params);

    /**
     * Fetch payment details by Razorpay Payment ID
     *
     * @param string $payment_id Razorpay Payment ID (e.g. 'pay_...')
     * @return array [success => bool, data => array, error => string, http_code => int]
     */
    public function fetchPayment($payment_id);

    /**
     * Timing-safe verification of payment signature
     *
     * @param string $razorpay_order_id
     * @param string $razorpay_payment_id
     * @param string $razorpay_signature
     * @return bool
     */
    public function verifyPaymentSignature($razorpay_order_id, $razorpay_payment_id, $razorpay_signature);

    /**
     * Timing-safe verification of incoming webhook signature
     *
     * @param string $payload Raw request body
     * @param string $signature Signature from HTTP header
     * @param string $secret Webhook secret key
     * @return bool
     */
    public static function verifyWebhookSignature($payload, $signature, $secret);
}
