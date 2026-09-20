<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Contracts;

use Tygh\Addons\Razorpay\DTO\PaymentContext;

/**
 * Interface PaymentModeHandlerInterface
 * Strategy contract for handling payment modes (initiate, return, cancel, notify)
 */
interface PaymentModeHandlerInterface
{
    /**
     * Execute handling for the payment mode
     *
     * @param PaymentContext $context
     * @return void
     */
    public function handle(PaymentContext $context);
}
