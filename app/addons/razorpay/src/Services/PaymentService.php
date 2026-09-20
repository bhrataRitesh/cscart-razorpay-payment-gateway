<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Services;

use Tygh\Addons\Razorpay\Contracts\PaymentApiInterface;
use Tygh\Addons\Razorpay\Contracts\PaymentServiceInterface;
use Tygh\Addons\Razorpay\DTO\RazorpayOrderResult;
use Tygh\Registry;

class PaymentService implements PaymentServiceInterface
{
    /**
     * @var array
     */
    protected $processor_params;

    /**
     * @var PaymentApiInterface
     */
    protected $api;

    public function __construct(array $processor_params, PaymentApiInterface $api = null)
    {
        $this->processor_params = $processor_params;
        if ($api) {
            $this->api = $api;
        } else {
            $key_id = !empty($processor_params['key_id']) ? $processor_params['key_id'] : '';
            $key_secret = !empty($processor_params['key_secret']) ? $processor_params['key_secret'] : '';
            $this->api = new RazorpayApi($key_id, $key_secret);
        }
    }

    /**
     * @return PaymentApiInterface
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * Resolves the currency to charge in Razorpay
     *
     * @param array $order_info
     * @return string Currency code (e.g. 'INR', 'USD')
     */
    public function getCurrency(array $order_info)
    {
        if (!empty($this->processor_params['currency']) && $this->processor_params['currency'] !== 'store') {
            return strtoupper($this->processor_params['currency']);
        }

        if (!empty($order_info['secondary_currency'])) {
            return strtoupper($order_info['secondary_currency']);
        }

        if (defined('CART_SECONDARY_CURRENCY')) {
            return strtoupper(CART_SECONDARY_CURRENCY);
        }

        return 'INR';
    }

    /**
     * Calculates the order amount converted to target currency
     *
     * @param array $order_info
     * @param string $currency
     * @return float
     */
    public function getAmount(array $order_info, $currency)
    {
        $order_total = isset($order_info['total']) ? (float) $order_info['total'] : 0.0;

        // If order was already placed in this secondary currency, total is already in that currency
        if (!empty($order_info['secondary_currency']) && $order_info['secondary_currency'] === $currency) {
            return $order_total;
        }

        if (defined('CART_PRIMARY_CURRENCY') && CART_PRIMARY_CURRENCY === $currency) {
            return $order_total;
        }

        // Convert from primary to target currency
        return (float) fn_format_price_by_currency($order_total, CART_PRIMARY_CURRENCY, $currency);
    }

    /**
     * Converts standard amount to subunits (paise, cents, etc.)
     * Razorpay uses subunits for all currencies (amount * 100).
     *
     * @param float $amount
     * @param string $currency
     * @return int
     */
    public function getAmountInSubunits($amount, $currency = 'INR')
    {
        // Zero-decimal currencies can be handled here if needed, but standard currencies use 100
        return (int) round($amount * 100);
    }

    /**
     * Creates a Razorpay Order via REST API
     *
     * @param array $order_info
     * @return array [success => bool, razorpay_order_id => string, error => string]
     */
    public function createRazorpayOrder(array $order_info)
    {
        $order_id = $order_info['order_id'];
        $currency = $this->getCurrency($order_info);
        $amount = $this->getAmount($order_info, $currency);
        $amount_subunits = $this->getAmountInSubunits($amount, $currency);

        $auto_capture = empty($this->processor_params['payment_action']) || $this->processor_params['payment_action'] === 'capture';

        $params = [
            'amount'          => $amount_subunits,
            'currency'        => $currency,
            'receipt'         => 'order_' . $order_id,
            'payment_capture' => $auto_capture ? 1 : 0,
            'notes'           => [
                'cscart_order_id' => (string) $order_id,
                'customer_email'  => !empty($order_info['email']) ? $order_info['email'] : '',
                'customer_name'   => trim(($order_info['firstname'] ?? '') . ' ' . ($order_info['lastname'] ?? '')),
            ],
        ];

        $res = $this->api->createOrder($params);

        if (!$res['success'] || empty($res['data']['id'])) {
            return RazorpayOrderResult::failure(
                !empty($res['error']) ? $res['error'] : __('razorpay.order_creation_failed'),
                $res
            );
        }

        return RazorpayOrderResult::success(
            $res['data']['id'],
            $amount_subunits,
            $currency,
            $res['data']
        );
    }

    /**
     * @inheritDoc
     */
    public function calculateSubunits($amount, $currency)
    {
        return $this->getAmountInSubunits($amount, $currency);
    }

    /**
     * Builds the options configuration array for Razorpay Standard Checkout (checkout.js)
     *
     * @param array $order_info
     * @param string $razorpay_order_id
     * @param int $amount_subunits
     * @param string $currency
     * @return array
     */
    public function buildCheckoutOptions(array $order_info, $razorpay_order_id, $amount_subunits, $currency)
    {
        $order_id = $order_info['order_id'];

        $customer_name = trim(($order_info['firstname'] ?? '') . ' ' . ($order_info['lastname'] ?? ''));
        if (empty($customer_name) && !empty($order_info['b_firstname'])) {
            $customer_name = trim($order_info['b_firstname'] . ' ' . ($order_info['b_lastname'] ?? ''));
        }

        $customer_phone = !empty($order_info['phone'])
            ? $order_info['phone']
            : (!empty($order_info['b_phone']) ? $order_info['b_phone'] : ($order_info['s_phone'] ?? ''));

        $merchant_name = !empty($this->processor_params['merchant_name'])
            ? $this->processor_params['merchant_name']
            : Registry::get('settings.Company.company_name');

        $theme_color = !empty($this->processor_params['theme_color'])
            ? $this->processor_params['theme_color']
            : '#3399cc';

        return [
            'key'         => !empty($this->processor_params['key_id']) ? $this->processor_params['key_id'] : '',
            'amount'      => $amount_subunits,
            'currency'    => $currency,
            'name'        => $merchant_name,
            'description' => __('order') . ' #' . $order_id,
            'order_id'    => $razorpay_order_id,
            'prefill'     => [
                'name'    => $customer_name,
                'email'   => !empty($order_info['email']) ? $order_info['email'] : '',
                'contact' => $customer_phone,
            ],
            'notes'       => [
                'cscart_order_id' => (string) $order_id,
            ],
            'theme'       => [
                'color' => $theme_color,
            ],
        ];
    }
}
