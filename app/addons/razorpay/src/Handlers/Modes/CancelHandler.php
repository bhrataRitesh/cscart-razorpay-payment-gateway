<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Handlers\Modes;

use Tygh\Addons\Razorpay\Contracts\PaymentModeHandlerInterface;
use Tygh\Addons\Razorpay\Contracts\TransactionRepositoryInterface;
use Tygh\Addons\Razorpay\DTO\PaymentContext;
use Tygh\Addons\Razorpay\Repositories\TransactionRepository;

/**
 * Class CancelHandler
 * Strategy handling customer cancellation and checkout recovery
 */
class CancelHandler implements PaymentModeHandlerInterface
{
    /**
     * @var TransactionRepositoryInterface
     */
    protected $transaction_repository;

    /**
     * CancelHandler constructor.
     *
     * @param TransactionRepositoryInterface|null $transaction_repo
     */
    public function __construct(TransactionRepositoryInterface $transaction_repo = null)
    {
        $this->transaction_repository = $transaction_repo ?: new TransactionRepository();
    }

    /**
     * @inheritDoc
     */
    public function handle(PaymentContext $context)
    {
        $order_id = $context->getOrderId();

        if ($order_id && fn_check_payment_script('razorpay.php', $order_id)) {
            $pp_response = [
                'order_status' => 'N',
                'reason_text'  => __('razorpay.payment_cancelled_by_user'),
            ];

            $this->transaction_repository->markCancelled($order_id);

            fn_finish_payment($order_id, $pp_response);
        }

        fn_set_notification('W', __('warning'), __('razorpay.payment_cancelled_by_user'));
        fn_order_placement_routines('checkout_redirect');
        exit;
    }
}
