<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Model;

/**
 * Model for ?:razorpay_transactions table
 */
class TransactionModel extends BaseModel
{
    /**
     * @var string Table name
     */
    protected $table = '?:razorpay_transactions';

    /**
     * @var string Primary key
     */
    protected $primary_key = 'order_id';

    /**
     * Save initial order transaction
     *
     * @param int $order_id
     * @param string $razorpay_order_id
     * @param float $amount
     * @param string $currency
     * @param string $status
     * @return int|bool
     */
    public function saveTransaction($order_id, $razorpay_order_id, $amount, $currency, $status = 'created')
    {
        return $this->replace([
            'order_id'          => (int) $order_id,
            'razorpay_order_id' => (string) $razorpay_order_id,
            'amount'            => (float) $amount,
            'currency'          => (string) $currency,
            'status'            => (string) $status,
            'created_at'        => TIME,
            'updated_at'        => TIME,
        ]);
    }

    /**
     * Mark transaction as authorized on successful payment callback
     *
     * @param int $order_id
     * @param string $razorpay_order_id
     * @param string $razorpay_payment_id
     * @param string $signature
     * @param float $amount
     * @param string $currency
     * @param int $created_at
     * @return int|bool
     */
    public function markAuthorized($order_id, $razorpay_order_id, $razorpay_payment_id, $signature, $amount, $currency, $created_at = 0)
    {
        return $this->replace([
            'order_id'            => (int) $order_id,
            'razorpay_order_id'   => (string) $razorpay_order_id,
            'razorpay_payment_id' => (string) $razorpay_payment_id,
            'razorpay_signature'  => (string) $signature,
            'amount'              => (float) $amount,
            'currency'            => (string) $currency,
            'status'              => 'authorized',
            'created_at'          => $created_at ? (int) $created_at : TIME,
            'updated_at'          => TIME,
        ]);
    }

    /**
     * Mark transaction as captured (via webhook)
     *
     * @param int $order_id
     * @param string $razorpay_payment_id
     * @return int|bool
     */
    public function markCaptured($order_id, $razorpay_payment_id = '')
    {
        $data = [
            'status'     => 'captured',
            'updated_at' => TIME,
        ];

        if (!empty($razorpay_payment_id)) {
            $data['razorpay_payment_id'] = (string) $razorpay_payment_id;
        }

        return $this->updateById($order_id, $data);
    }

    /**
     * Mark transaction as failed
     *
     * @param int $order_id
     * @param string $error_code
     * @param string $error_description
     * @return int|bool
     */
    public function markFailed($order_id, $error_code = '', $error_description = '')
    {
        return $this->updateById($order_id, [
            'status'            => 'failed',
            'error_code'        => (string) $error_code,
            'error_description' => (string) $error_description,
            'updated_at'        => TIME,
        ]);
    }

    /**
     * Mark transaction as cancelled by customer
     *
     * @param int $order_id
     * @return int|bool
     */
    public function markCancelled($order_id)
    {
        return $this->updateById($order_id, [
            'status'            => 'cancelled',
            'error_code'        => 'CUSTOMER_CANCELLED',
            'error_description' => 'Customer cancelled payment',
            'updated_at'        => TIME,
        ]);
    }

    /**
     * Find transaction by Razorpay Order ID
     *
     * @param string $razorpay_order_id
     * @return array|false
     */
    public function getByRazorpayOrderId($razorpay_order_id)
    {
        return $this->find(['razorpay_order_id' => (string) $razorpay_order_id]);
    }

    /**
     * Find transaction by Razorpay Payment ID
     *
     * @param string $razorpay_payment_id
     * @return array|false
     */
    public function getByRazorpayPaymentId($razorpay_payment_id)
    {
        return $this->find(['razorpay_payment_id' => (string) $razorpay_payment_id]);
    }

    /**
     * Get transaction joined with CS-Cart orders table
     * Demonstrates complex fluent query with JOIN
     *
     * @param int $order_id
     * @return array
     */
    public function getTransactionWithOrder($order_id)
    {
        return $this->query('t')
            ->select([
                't.*',
                'o.status as cs_order_status',
                'o.email',
                'o.user_id',
                'o.total as cs_order_total',
            ])
            ->leftJoin('?:orders AS o', 't.order_id', '=', 'o.order_id')
            ->where('t.order_id', (int) $order_id)
            ->first();
    }

    /**
     * Get aggregated transaction summary grouped by currency
     * Demonstrates GROUP BY, HAVING, and aggregation functions
     *
     * @param float $min_total Minimum total volume threshold
     * @return array
     */
    public function getTransactionsSummaryByCurrency($min_total = 0.0)
    {
        $qb = $this->query('t')
            ->select([
                't.currency',
                'COUNT(t.order_id) as total_count',
                'SUM(t.amount) as total_volume',
            ])
            ->whereIn('t.status', ['authorized', 'captured'])
            ->groupBy('t.currency');

        if ($min_total > 0.0) {
            $qb->having('total_volume', '>=', (float) $min_total);
        }

        return $qb->get();
    }
}
