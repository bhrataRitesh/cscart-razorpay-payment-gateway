<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay;

use Tygh\Core\ApplicationInterface;
use Tygh\Core\BootstrapInterface;
use Tygh\Core\HookHandlerProviderInterface;

class Bootstrap implements BootstrapInterface, HookHandlerProviderInterface
{
    /**
     * @inheritDoc
     */
    public function boot(ApplicationInterface $app)
    {
        $app->register(new ServiceProvider());
    }

    /**
     * @inheritDoc
     */
    public function getHookHandlerMap()
    {
        return [
            'update_payment_post' => [
                'addons.razorpay.hook_handlers.payments',
                'onUpdatePaymentPost',
            ],
            'get_payment_processors_post' => [
                'addons.razorpay.hook_handlers.payments',
                'onGetPaymentProcessorsPost',
            ],
            'finish_payment' => [
                'addons.razorpay.hook_handlers.orders',
                'onFinishPayment',
            ],
        ];
    }
}
