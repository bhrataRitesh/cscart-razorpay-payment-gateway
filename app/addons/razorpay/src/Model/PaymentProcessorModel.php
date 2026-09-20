<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Model;

/**
 * Model for ?:payment_processors table
 */
class PaymentProcessorModel extends BaseModel
{
    /**
     * @var string Table name
     */
    protected $table = '?:payment_processors';

    /**
     * @var string Primary key
     */
    protected $primary_key = 'processor_id';

    /**
     * Get payment processor by script name
     *
     * @param string $script E.g. 'razorpay.php'
     * @return array|false
     */
    public function getByScript($script = 'razorpay.php')
    {
        return $this->find(['processor_script' => (string) $script]);
    }

    /**
     * Delete payment processor by script name
     *
     * @param string $script E.g. 'razorpay.php'
     * @return int|bool
     */
    public function deleteByScript($script = 'razorpay.php')
    {
        return $this->delete(['processor_script' => (string) $script]);
    }
}
