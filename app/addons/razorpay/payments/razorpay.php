<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

defined('BOOTSTRAP') or die('Access denied');

use Tygh\Addons\Razorpay\PaymentHandler\PaymentHandler;

/** @var array $order_info */
/** @var array $processor_data */
/** @var string $mode */

new PaymentHandler(
    isset($order_info) ? $order_info : [],
    isset($processor_data) ? $processor_data : [],
    isset($mode) ? $mode : ''
);
