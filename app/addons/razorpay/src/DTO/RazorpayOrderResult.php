<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\DTO;

/**
 * Class RazorpayOrderResult
 * Value Object encapsulating the result of creating a Razorpay order via API
 */
class RazorpayOrderResult
{
    /**
     * @var bool
     */
    protected $success;

    /**
     * @var string Razorpay order ID (e.g. 'order_...')
     */
    protected $razorpay_order_id;

    /**
     * @var int Amount in currency subunits (paise)
     */
    protected $amount_subunits;

    /**
     * @var string Currency code (e.g. 'INR')
     */
    protected $currency;

    /**
     * @var string Error message if failed
     */
    protected $error;

    /**
     * @var array Raw response data
     */
    protected $raw_data;

    /**
     * RazorpayOrderResult constructor.
     *
     * @param bool   $success
     * @param string $razorpay_order_id
     * @param int    $amount_subunits
     * @param string $currency
     * @param string $error
     * @param array  $raw_data
     */
    public function __construct(
        $success,
        $razorpay_order_id = '',
        $amount_subunits = 0,
        $currency = '',
        $error = '',
        array $raw_data = []
    ) {
        $this->success = (bool) $success;
        $this->razorpay_order_id = (string) $razorpay_order_id;
        $this->amount_subunits = (int) $amount_subunits;
        $this->currency = (string) $currency;
        $this->error = (string) $error;
        $this->raw_data = $raw_data;
    }

    /**
     * Factory method for successful result
     *
     * @param string $razorpay_order_id
     * @param int    $amount_subunits
     * @param string $currency
     * @param array  $raw_data
     * @return static
     */
    public static function success($razorpay_order_id, $amount_subunits, $currency, array $raw_data = [])
    {
        return new static(true, $razorpay_order_id, $amount_subunits, $currency, '', $raw_data);
    }

    /**
     * Factory method for failed result
     *
     * @param string $error
     * @param array  $raw_data
     * @return static
     */
    public static function failure($error, array $raw_data = [])
    {
        return new static(false, '', 0, '', $error, $raw_data);
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getRazorpayOrderId()
    {
        return $this->razorpay_order_id;
    }

    /**
     * @return int
     */
    public function getAmountSubunits()
    {
        return $this->amount_subunits;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return array
     */
    public function getRawData()
    {
        return $this->raw_data;
    }

    /**
     * Convert to array for backward compatibility
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'success'           => $this->success,
            'razorpay_order_id' => $this->razorpay_order_id,
            'amount_subunits'   => $this->amount_subunits,
            'currency'          => $this->currency,
            'error'             => $this->error,
            'data'              => $this->raw_data,
        ];
    }
}
