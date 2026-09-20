<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\HookHandlers;

use Tygh\Core\ApplicationInterface;

class PaymentsHookHandler
{
    /**
     * @var \Tygh\Core\ApplicationInterface
     */
    protected $application;

    public function __construct(ApplicationInterface $application)
    {
        $this->application = $application;
    }

    /**
     * Hook handler for "update_payment_post"
     *
     * @param array  $payment_data
     * @param int    $payment_id
     * @param string $lang_code
     * @param array  $certificate_file
     * @param string $certificates_dir
     * @param array  $processor_params
     * @return void
     */
    public function onUpdatePaymentPost(array $payment_data, $payment_id, $lang_code, array $certificate_file, $certificates_dir, array $processor_params)
    {
        if (empty($payment_data['processor_id'])) {
            return;
        }

        $processor_script = $this->application['db']->getField(
            'SELECT processor_script FROM ?:payment_processors WHERE processor_id = ?i',
            $payment_data['processor_id']
        );

        if ($processor_script !== 'razorpay.php') {
            return;
        }

        // Basic verification notice if keys look incomplete
        if (empty($processor_params['key_id']) || empty($processor_params['key_secret'])) {
            fn_set_notification('W', __('warning'), __('razorpay.keys_missing_notice'));
        }
    }

    /**
     * Hook handler for "get_payment_processors_post"
     *
     * @param string $lang_code
     * @param array  $processors
     * @return void
     */
    public function onGetPaymentProcessorsPost($lang_code, array &$processors)
    {
        foreach ($processors as &$processor) {
            if ((!empty($processor['addon']) && $processor['addon'] === 'razorpay') || (!empty($processor['processor']) && $processor['processor'] === 'Razorpay')) {
                if (empty($processor['description'])) {
                    $processor['description'] = __('processor_description_razorpay');
                }
            }
        }
    }
}
