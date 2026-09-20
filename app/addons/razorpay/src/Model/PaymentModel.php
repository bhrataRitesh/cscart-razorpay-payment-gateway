<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Model;

/**
 * Model for ?:payments table
 */
class PaymentModel extends BaseModel
{
    /**
     * @var string Table name
     */
    protected $table = '?:payments';

    /**
     * @var string Primary key
     */
    protected $primary_key = 'payment_id';

    /**
     * Get active payment method by processor ID
     *
     * @param int $processor_id
     * @return array|false
     */
    public function getActiveByProcessorId($processor_id)
    {
        return $this->find([
            'processor_id' => (int) $processor_id,
            'status'       => 'A',
        ]);
    }

    /**
     * Disable and unassign processor for payments
     *
     * @param int $processor_id
     * @return int|bool
     */
    public function disableByProcessorId($processor_id)
    {
        return $this->update(
            ['processor_id' => (int) $processor_id],
            [
                'processor_id'     => 0,
                'processor_params' => '',
                'status'           => 'D',
            ]
        );
    }
}
