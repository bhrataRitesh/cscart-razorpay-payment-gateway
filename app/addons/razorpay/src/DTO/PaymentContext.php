<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\DTO;

/**
 * Class PaymentContext
 * Immutable Data Transfer Object encapsulating execution context for payment processing
 */
class PaymentContext
{
    /**
     * @var array CS-Cart order information
     */
    protected $order_info;

    /**
     * @var array Payment processor configuration data
     */
    protected $processor_data;

    /**
     * @var array Unwrapped processor params
     */
    protected $processor_params;

    /**
     * @var string Payment action mode (e.g. 'initiate', 'return', 'cancel', 'notify')
     */
    protected $mode;

    /**
     * @var int Resolved order ID
     */
    protected $order_id;

    /**
     * PaymentContext constructor.
     *
     * @param array  $order_info
     * @param array  $processor_data
     * @param string $mode
     * @param int    $order_id
     */
    public function __construct(array $order_info = [], array $processor_data = [], $mode = '', $order_id = 0)
    {
        $this->order_info = $order_info;
        $this->processor_data = $processor_data;
        $this->processor_params = !empty($processor_data['processor_params']) ? $processor_data['processor_params'] : [];
        $this->mode = (string) $mode;

        if ($order_id > 0) {
            $this->order_id = (int) $order_id;
        } elseif (!empty($_REQUEST['order_id'])) {
            $this->order_id = (int) $_REQUEST['order_id'];
        } elseif (!empty($order_info['order_id'])) {
            $this->order_id = (int) $order_info['order_id'];
        } else {
            $this->order_id = 0;
        }
    }

    /**
     * @return array
     */
    public function getOrderInfo()
    {
        return $this->order_info;
    }

    /**
     * @return array
     */
    public function getProcessorData()
    {
        return $this->processor_data;
    }

    /**
     * @return array
     */
    public function getProcessorParams()
    {
        return $this->processor_params;
    }

    /**
     * Get single processor configuration parameter
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        return isset($this->processor_params[$key]) ? $this->processor_params[$key] : $default;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * Get order total amount
     *
     * @return float
     */
    public function getTotal()
    {
        return !empty($this->order_info['total']) ? (float) $this->order_info['total'] : 0.00;
    }

    /**
     * Get order currency code
     *
     * @return string
     */
    public function getCurrency()
    {
        if (!empty($this->order_info['secondary_currency'])) {
            return (string) $this->order_info['secondary_currency'];
        }

        return defined('CART_PRIMARY_CURRENCY') ? CART_PRIMARY_CURRENCY : 'INR';
    }

    /**
     * Check if current request is a payment notification callback
     *
     * @return bool
     */
    public function isNotification()
    {
        return defined('PAYMENT_NOTIFICATION');
    }
}
