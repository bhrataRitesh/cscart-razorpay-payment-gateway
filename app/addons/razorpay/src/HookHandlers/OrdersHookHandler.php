<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\HookHandlers;

use Tygh\Core\ApplicationInterface;

class OrdersHookHandler
{
    /**
     * @var \Tygh\Core\ApplicationInterface
     */
    protected $application;

    public function __construct(ApplicationInterface $application)
    {
        $this->application = $application;
    }

    /**
     * Hook handler for "finish_payment"
     *
     * @param int   $order_id
     * @param array $pp_response
     * @param array $force_notification
     * @return void
     */
    public function onFinishPayment($order_id, array $pp_response, array $force_notification)
    {
        if (empty($pp_response['razorpay_payment_id'])) {
            return;
        }

        // Record audit log entry in order data or activity log
        fn_log_event('orders', 'updated', [
            'order_id'       => $order_id,
            'payment_method' => 'Razorpay',
            'transaction_id' => $pp_response['transaction_id'] ?? '',
            'order_status'   => $pp_response['order_status'] ?? '',
        ]);
    }
}
