<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Repositories;

use Tygh\Addons\Razorpay\Contracts\TransactionRepositoryInterface;
use Tygh\Addons\Razorpay\Model\TransactionModel;

/**
 * Class TransactionRepository
 * Implements Repository Pattern for Razorpay transaction domain operations
 */
class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var TransactionModel
     */
    protected $model;

    /**
     * TransactionRepository constructor.
     *
     * @param TransactionModel|null $model
     */
    public function __construct(TransactionModel $model = null)
    {
        $this->model = $model ?: new TransactionModel();
    }

    /**
     * @inheritDoc
     */
    public function saveOrderTransaction($order_id, $razorpay_order_id, $amount, $currency, $status = 'created')
    {
        return (bool) $this->model->saveTransaction(
            (int) $order_id,
            (string) $razorpay_order_id,
            (float) $amount,
            (string) $currency,
            (string) $status
        );
    }

    /**
     * @inheritDoc
     */
    public function markAuthorized($order_id, $razorpay_order_id, $razorpay_payment_id, $signature, $amount, $currency, $created_at = 0)
    {
        return (bool) $this->model->markAuthorized(
            (int) $order_id,
            (string) $razorpay_order_id,
            (string) $razorpay_payment_id,
            (string) $signature,
            (float) $amount,
            (string) $currency,
            (int) $created_at
        );
    }

    /**
     * @inheritDoc
     */
    public function markCaptured($order_id, $razorpay_payment_id = '')
    {
        return (bool) $this->model->markCaptured((int) $order_id, (string) $razorpay_payment_id);
    }

    /**
     * @inheritDoc
     */
    public function markFailed($order_id, $error_code = '', $error_description = '')
    {
        return (bool) $this->model->markFailed((int) $order_id, (string) $error_code, (string) $error_description);
    }

    /**
     * @inheritDoc
     */
    public function markCancelled($order_id)
    {
        return (bool) $this->model->markCancelled((int) $order_id);
    }

    /**
     * @inheritDoc
     */
    public function findByOrderId($order_id)
    {
        $res = $this->model->findById((int) $order_id);
        return is_array($res) ? $res : [];
    }

    /**
     * @inheritDoc
     */
    public function findByRazorpayOrderId($razorpay_order_id)
    {
        $res = $this->model->getByRazorpayOrderId((string) $razorpay_order_id);
        return is_array($res) ? $res : [];
    }

    /**
     * @inheritDoc
     */
    public function findByRazorpayPaymentId($razorpay_payment_id)
    {
        $res = $this->model->getByRazorpayPaymentId((string) $razorpay_payment_id);
        return is_array($res) ? $res : [];
    }
}
