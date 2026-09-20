<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Contracts;

/**
 * Interface TransactionRepositoryInterface
 * Repository contract for managing Razorpay transaction records
 */
interface TransactionRepositoryInterface
{
    /**
     * Save initial order transaction
     *
     * @param int    $order_id
     * @param string $razorpay_order_id
     * @param float  $amount
     * @param string $currency
     * @param string $status
     * @return bool
     */
    public function saveOrderTransaction($order_id, $razorpay_order_id, $amount, $currency, $status = 'created');

    /**
     * Mark transaction as authorized on successful return callback
     *
     * @param int    $order_id
     * @param string $razorpay_order_id
     * @param string $razorpay_payment_id
     * @param string $signature
     * @param float  $amount
     * @param string $currency
     * @param int    $created_at
     * @return bool
     */
    public function markAuthorized($order_id, $razorpay_order_id, $razorpay_payment_id, $signature, $amount, $currency, $created_at = 0);

    /**
     * Mark transaction as captured (via webhook)
     *
     * @param int    $order_id
     * @param string $razorpay_payment_id
     * @return bool
     */
    public function markCaptured($order_id, $razorpay_payment_id = '');

    /**
     * Mark transaction as failed
     *
     * @param int    $order_id
     * @param string $error_code
     * @param string $error_description
     * @return bool
     */
    public function markFailed($order_id, $error_code = '', $error_description = '');

    /**
     * Mark transaction as cancelled by customer
     *
     * @param int $order_id
     * @return bool
     */
    public function markCancelled($order_id);

    /**
     * Find transaction by CS-Cart Order ID
     *
     * @param int $order_id
     * @return array
     */
    public function findByOrderId($order_id);

    /**
     * Find transaction by Razorpay Order ID
     *
     * @param string $razorpay_order_id
     * @return array
     */
    public function findByRazorpayOrderId($razorpay_order_id);

    /**
     * Find transaction by Razorpay Payment ID
     *
     * @param string $razorpay_payment_id
     * @return array
     */
    public function findByRazorpayPaymentId($razorpay_payment_id);
}
