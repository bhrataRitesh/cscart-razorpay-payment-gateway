<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Services;

use Tygh\Addons\Razorpay\Contracts\PaymentApiInterface;
use Tygh\Http;

class RazorpayApi implements PaymentApiInterface
{
    const API_URL = 'https://api.razorpay.com/v1/';

    /**
     * @var string
     */
    protected $key_id;

    /**
     * @var string
     */
    protected $key_secret;

    public function __construct($key_id, $key_secret)
    {
        $this->key_id = trim((string) $key_id);
        $this->key_secret = trim((string) $key_secret);
    }

    /**
     * Creates an Order on Razorpay
     *
     * @param array $params Order parameters (amount, currency, receipt, notes, etc.)
     * @return array [success => bool, data => array, error => string]
     */
    public function createOrder(array $params)
    {
        $url = self::API_URL . 'orders';

        $response = Http::post(
            $url,
            json_encode($params),
            [
                'headers'    => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                'basic_auth' => [$this->key_id, $this->key_secret],
            ]
        );

        return $this->parseResponse($response);
    }

    /**
     * Fetches details of a specific payment from Razorpay
     *
     * @param string $payment_id
     * @return array [success => bool, data => array, error => string]
     */
    public function fetchPayment($payment_id)
    {
        $url = self::API_URL . 'payments/' . rawurlencode($payment_id);

        $response = Http::get(
            $url,
            [],
            [
                'headers'    => [
                    'Accept: application/json',
                ],
                'basic_auth' => [$this->key_id, $this->key_secret],
            ]
        );

        return $this->parseResponse($response);
    }

    /**
     * Captures an authorized payment
     *
     * @param string $payment_id
     * @param int $amount Amount in subunits
     * @param string $currency
     * @return array [success => bool, data => array, error => string]
     */
    public function capturePayment($payment_id, $amount, $currency = 'INR')
    {
        $url = self::API_URL . 'payments/' . rawurlencode($payment_id) . '/capture';

        $response = Http::post(
            $url,
            json_encode([
                'amount'   => (int) $amount,
                'currency' => $currency,
            ]),
            [
                'headers'    => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                'basic_auth' => [$this->key_id, $this->key_secret],
            ]
        );

        return $this->parseResponse($response);
    }

    /**
     * Verifies the Razorpay payment signature from client checkout modal
     *
     * @param string $razorpay_order_id
     * @param string $razorpay_payment_id
     * @param string $razorpay_signature
     * @return bool
     */
    public function verifyPaymentSignature($razorpay_order_id, $razorpay_payment_id, $razorpay_signature)
    {
        if (empty($razorpay_order_id) || empty($razorpay_payment_id) || empty($razorpay_signature)) {
            return false;
        }

        $expected_signature = hash_hmac(
            'sha256',
            $razorpay_order_id . '|' . $razorpay_payment_id,
            $this->key_secret
        );

        return hash_equals($expected_signature, $razorpay_signature);
    }

    /**
     * Verifies the Razorpay Webhook signature
     *
     * @param string $raw_payload
     * @param string $signature
     * @param string $webhook_secret
     * @return bool
     */
    public static function verifyWebhookSignature($raw_payload, $signature, $webhook_secret)
    {
        if (empty($raw_payload) || empty($signature) || empty($webhook_secret)) {
            return false;
        }

        $expected_signature = hash_hmac('sha256', $raw_payload, $webhook_secret);

        return hash_equals($expected_signature, $signature);
    }

    /**
     * Parses HTTP response JSON
     *
     * @param string|false $response
     * @return array
     */
    protected function parseResponse($response)
    {
        if ($response === false) {
            return [
                'success' => false,
                'data'    => [],
                'error'   => 'Could not connect to Razorpay API.',
            ];
        }

        $data = json_decode($response, true);

        if (!is_array($data)) {
            return [
                'success' => false,
                'data'    => [],
                'error'   => 'Invalid response from Razorpay server.',
            ];
        }

        if (!empty($data['error'])) {
            $err_desc = !empty($data['error']['description'])
                ? $data['error']['description']
                : (!empty($data['error']['code']) ? $data['error']['code'] : 'Unknown error from Razorpay');

            return [
                'success' => false,
                'data'    => $data,
                'error'   => $err_desc,
            ];
        }

        return [
            'success' => true,
            'data'    => $data,
            'error'   => '',
        ];
    }
}
