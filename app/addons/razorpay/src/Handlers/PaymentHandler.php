<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Handlers;

use Tygh\Addons\Razorpay\DTO\PaymentContext;
use Tygh\Core\ApplicationInterface;
use Tygh\Tygh;

/**
 * Class PaymentHandler
 * Facade coordinating CS-Cart payment requests and dispatching to mode strategies
 */
class PaymentHandler
{
    /**
     * @var PaymentContext
     */
    protected $context;

    /**
     * @var PaymentModeHandlerFactory
     */
    protected $factory;

    /**
     * PaymentHandler constructor.
     *
     * @param array                     $order_info
     * @param array                     $processor_data
     * @param string                    $mode
     * @param PaymentModeHandlerFactory $factory
     */
    public function __construct(
        array $order_info = [],
        array $processor_data = [],
        $mode = '',
        PaymentModeHandlerFactory $factory = null
    ) {
        $this->factory = $factory ?: (isset(Tygh::$app['addons.razorpay.handlers.factory']) ? Tygh::$app['addons.razorpay.handlers.factory'] : new PaymentModeHandlerFactory());

        $resolved_mode = $this->resolveMode($mode);
        $this->context = new PaymentContext($order_info, $processor_data, $resolved_mode);

        $this->dispatch();
    }

    /**
     * Resolve the action mode based on request flags
     *
     * @param string $mode
     * @return string
     */
    protected function resolveMode($mode)
    {
        if (defined('PAYMENT_NOTIFICATION')) {
            if (!empty($_REQUEST['mode'])) {
                return (string) $_REQUEST['mode'];
            }

            return !empty($mode) ? (string) $mode : 'unknown';
        }

        return 'initiate';
    }

    /**
     * Dispatch context to the appropriate mode handler strategy
     *
     * @return void
     */
    public function dispatch()
    {
        $mode = $this->context->getMode();
        $strategy = $this->factory->create($mode);

        if ($strategy) {
            $strategy->handle($this->context);
        } else {
            $this->handleUnknownMode();
        }
    }

    /**
     * Fallback for unrecognized notification modes
     *
     * @return void
     */
    protected function handleUnknownMode()
    {
        fn_order_placement_routines('checkout_redirect');
        exit;
    }

    /**
     * @return PaymentContext
     */
    public function getContext()
    {
        return $this->context;
    }
}
