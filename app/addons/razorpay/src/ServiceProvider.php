<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Addons\Razorpay\Handlers\Modes\CancelHandler;
use Tygh\Addons\Razorpay\Handlers\Modes\InitiateHandler;
use Tygh\Addons\Razorpay\Handlers\Modes\ReturnHandler;
use Tygh\Addons\Razorpay\Handlers\Modes\WebhookHandler;
use Tygh\Addons\Razorpay\Handlers\PaymentModeHandlerFactory;
use Tygh\Addons\Razorpay\HookHandlers\OrdersHookHandler;
use Tygh\Addons\Razorpay\HookHandlers\PaymentsHookHandler;
use Tygh\Addons\Razorpay\Model\PaymentModel;
use Tygh\Addons\Razorpay\Model\PaymentProcessorModel;
use Tygh\Addons\Razorpay\Model\TransactionModel;
use Tygh\Addons\Razorpay\Model\WebhookLogModel;
use Tygh\Addons\Razorpay\Repositories\TransactionRepository;
use Tygh\Addons\Razorpay\Repositories\WebhookLogRepository;
use Tygh\Addons\Razorpay\Services\PaymentService;
use Tygh\Addons\Razorpay\Services\RazorpayApi;
use Tygh\Addons\Razorpay\Services\WebhookService;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        // 1. Models
        $app['addons.razorpay.model.transaction'] = static function (Container $app) {
            return new TransactionModel($app['db']);
        };

        $app['addons.razorpay.model.webhook_log'] = static function (Container $app) {
            return new WebhookLogModel($app['db']);
        };

        $app['addons.razorpay.model.payment_processor'] = static function (Container $app) {
            return new PaymentProcessorModel($app['db']);
        };

        $app['addons.razorpay.model.payment'] = static function (Container $app) {
            return new PaymentModel($app['db']);
        };

        // 2. Repositories (Repository Pattern)
        $app['addons.razorpay.repositories.transaction'] = static function (Container $app) {
            return new TransactionRepository($app['addons.razorpay.model.transaction']);
        };

        $app['addons.razorpay.repositories.webhook_log'] = static function (Container $app) {
            return new WebhookLogRepository($app['addons.razorpay.model.webhook_log']);
        };

        // 3. Domain & Infrastructure Services
        $app['addons.razorpay.api_factory'] = static function () {
            return static function ($key_id, $key_secret) {
                return new RazorpayApi($key_id, $key_secret);
            };
        };

        $app['addons.razorpay.payment_service_factory'] = static function () {
            return static function (array $processor_params) {
                return new PaymentService($processor_params);
            };
        };

        $app['addons.razorpay.services.webhook'] = static function (Container $app) {
            return new WebhookService(
                $app['addons.razorpay.repositories.transaction'],
                $app['addons.razorpay.repositories.webhook_log']
            );
        };

        // 4. Mode Strategies & Strategy Factory (Strategy & Factory Patterns)
        $app['addons.razorpay.handlers.modes.initiate'] = static function (Container $app) {
            return new InitiateHandler($app['addons.razorpay.repositories.transaction']);
        };

        $app['addons.razorpay.handlers.modes.return'] = static function (Container $app) {
            return new ReturnHandler($app['addons.razorpay.repositories.transaction']);
        };

        $app['addons.razorpay.handlers.modes.cancel'] = static function (Container $app) {
            return new CancelHandler($app['addons.razorpay.repositories.transaction']);
        };

        $app['addons.razorpay.handlers.modes.notify'] = static function (Container $app) {
            return new WebhookHandler($app['addons.razorpay.services.webhook']);
        };

        $app['addons.razorpay.handlers.factory'] = static function (Container $app) {
            return new PaymentModeHandlerFactory($app);
        };

        $app['addons.razorpay.payment_handler_factory'] = static function (Container $app) {
            return static function (array $order_info = [], array $processor_data = [], $mode = '') use ($app) {
                return new \Tygh\Addons\Razorpay\Handlers\PaymentHandler(
                    $order_info,
                    $processor_data,
                    $mode,
                    $app['addons.razorpay.handlers.factory']
                );
            };
        };

        // 5. Hooks & Helpers
        $app['addons.razorpay.hook_handlers.payments'] = static function (Container $app) {
            return new PaymentsHookHandler($app);
        };

        $app['addons.razorpay.hook_handlers.orders'] = static function (Container $app) {
            return new OrdersHookHandler($app);
        };

        $app['addons.razorpay.helper'] = static function (Container $app) {
            return new Helper($app);
        };
    }
}
