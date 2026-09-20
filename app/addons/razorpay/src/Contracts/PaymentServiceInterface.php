<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Contracts;

use Tygh\Addons\Razorpay\DTO\RazorpayOrderResult;

/**
 * Interface PaymentServiceInterface
 * Defines business logic contract for payment calculations and checkout payload preparation
 */
interface PaymentServiceInterface
{
    /**
     * Create a Razorpay Order from CS-Cart order information
     *
     * @param array $order_info
     * @return RazorpayOrderResult
     */
    public function createRazorpayOrder(array $order_info);

    /**
     * Build checkout options for Razorpay Checkout JS modal
     *
     * @param array  $order_info
     * @param string $razorpay_order_id
     * @param int    $amount_subunits
     * @param string $currency
     * @return array
     */
    public function buildCheckoutOptions(array $order_info, $razorpay_order_id, $amount_subunits, $currency);

    /**
     * Calculate amount in currency subunits (e.g. paise for INR, cents for USD)
     *
     * @param float  $amount
     * @param string $currency
     * @return int
     */
    public function calculateSubunits($amount, $currency);

    /**
     * Get the configured Payment API client
     *
     * @return PaymentApiInterface
     */
    public function getApi();
}
