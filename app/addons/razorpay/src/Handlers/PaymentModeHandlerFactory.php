<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Handlers;

use Tygh\Addons\Razorpay\Contracts\PaymentModeHandlerInterface;
use Tygh\Addons\Razorpay\Handlers\Modes\CancelHandler;
use Tygh\Addons\Razorpay\Handlers\Modes\InitiateHandler;
use Tygh\Addons\Razorpay\Handlers\Modes\ReturnHandler;
use Tygh\Addons\Razorpay\Handlers\Modes\WebhookHandler;
use Tygh\Core\ApplicationInterface;
use Tygh\Tygh;

/**
 * Class PaymentModeHandlerFactory
 * Factory Pattern resolving the appropriate payment mode strategy
 */
class PaymentModeHandlerFactory
{
    /**
     * @var ApplicationInterface
     */
    protected $app;

    /**
     * @var array Map of mode names to handler service IDs or class names
     */
    protected $strategies = [
        'initiate' => InitiateHandler::class,
        'return'   => ReturnHandler::class,
        'cancel'   => CancelHandler::class,
        'notify'   => WebhookHandler::class,
    ];

    /**
     * PaymentModeHandlerFactory constructor.
     *
     * @param ApplicationInterface|null $app
     */
    public function __construct(ApplicationInterface $app = null)
    {
        $this->app = $app ?: Tygh::$app;
    }

    /**
     * Resolve and instantiate mode handler strategy
     *
     * @param string $mode Mode identifier ('initiate', 'return', 'cancel', 'notify')
     * @return PaymentModeHandlerInterface|null
     */
    public function create($mode)
    {
        $mode = strtolower(trim((string) $mode));

        if (!isset($this->strategies[$mode])) {
            return null;
        }

        $service_id = 'addons.razorpay.handlers.modes.' . $mode;
        if (isset($this->app[$service_id])) {
            return $this->app[$service_id];
        }

        $class_name = $this->strategies[$mode];
        return new $class_name();
    }

    /**
     * Register or override a mode strategy (Open/Closed Principle)
     *
     * @param string $mode
     * @param string $handler_class FQCN implementing PaymentModeHandlerInterface
     * @return void
     */
    public function registerStrategy($mode, $handler_class)
    {
        $this->strategies[strtolower(trim($mode))] = $handler_class;
    }
}
