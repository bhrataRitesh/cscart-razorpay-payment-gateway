<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Handlers\Modes;

use Tygh\Addons\Razorpay\Contracts\PaymentModeHandlerInterface;
use Tygh\Addons\Razorpay\Contracts\PaymentServiceInterface;
use Tygh\Addons\Razorpay\Contracts\TransactionRepositoryInterface;
use Tygh\Addons\Razorpay\DTO\PaymentContext;
use Tygh\Addons\Razorpay\Repositories\TransactionRepository;
use Tygh\Addons\Razorpay\Services\PaymentService;
use Tygh\Tygh;

/**
 * Class InitiateHandler
 * Strategy handling payment checkout initiation and standard checkout modal presentation
 */
class InitiateHandler implements PaymentModeHandlerInterface
{
    /**
     * @var TransactionRepositoryInterface
     */
    protected $transaction_repository;

    /**
     * InitiateHandler constructor.
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
        $order_info = $context->getOrderInfo();
        $processor_params = $context->getProcessorParams();

        if (empty($order_info) || empty($order_info['order_id'])) {
            fn_set_notification('E', __('error'), __('razorpay.invalid_order'));
            fn_order_placement_routines('checkout_redirect');
            exit;
        }

        if (empty($processor_params['key_id']) || empty($processor_params['key_secret'])) {
            fn_set_notification('E', __('error'), __('razorpay.missing_api_credentials'));
            fn_order_placement_routines('checkout_redirect');
            exit;
        }

        $payment_service = new PaymentService($processor_params);
        $order_result = $payment_service->createRazorpayOrder($order_info);

        if (!$order_result->isSuccess() || empty($order_result->getRazorpayOrderId())) {
            fn_set_notification('E', __('error'), $order_result->getError());
            fn_order_placement_routines('checkout_redirect');
            exit;
        }

        $razorpay_order_id = $order_result->getRazorpayOrderId();

        fn_update_order_payment_info($order_info['order_id'], [
            'razorpay_order_id' => $razorpay_order_id,
        ]);

        $this->transaction_repository->saveOrderTransaction(
            $order_info['order_id'],
            $razorpay_order_id,
            $context->getTotal(),
            $order_result->getCurrency()
        );

        $checkout_options = $payment_service->buildCheckoutOptions(
            $order_info,
            $razorpay_order_id,
            $order_result->getAmountSubunits(),
            $order_result->getCurrency()
        );

        $return_url = fn_url(
            'payment_notification.return?payment=razorpay&order_id=' . $order_info['order_id'],
            'C',
            'current'
        );
        $cancel_url = fn_url(
            'payment_notification.cancel?payment=razorpay&order_id=' . $order_info['order_id'],
            'C',
            'current'
        );

        Tygh::$app['view']->assign([
            'checkout_options' => $checkout_options,
            'json_options'     => json_encode($checkout_options),
            'order_id'         => $order_info['order_id'],
            'order_info'       => $order_info,
            'return_url'       => $return_url,
            'cancel_url'       => $cancel_url,
        ])->display('addons/razorpay/views/orders/components/payments/razorpay_checkout.tpl');

        exit;
    }
}
