<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Repositories;

use Tygh\Addons\Razorpay\Contracts\WebhookLogRepositoryInterface;
use Tygh\Addons\Razorpay\Model\WebhookLogModel;

/**
 * Class WebhookLogRepository
 * Implements Repository Pattern for Razorpay webhook audit logging
 */
class WebhookLogRepository implements WebhookLogRepositoryInterface
{
    /**
     * @var WebhookLogModel
     */
    protected $model;

    /**
     * WebhookLogRepository constructor.
     *
     * @param WebhookLogModel|null $model
     */
    public function __construct(WebhookLogModel $model = null)
    {
        $this->model = $model ?: new WebhookLogModel();
    }

    /**
     * @inheritDoc
     */
    public function logEvent($event_id, $event_type, $order_id, $payment_id, $raw_payload, $status = 'processed')
    {
        return (bool) $this->model->logEvent(
            (string) $event_id,
            (string) $event_type,
            (string) $order_id,
            (string) $payment_id,
            (string) $raw_payload,
            (string) $status
        );
    }

    /**
     * @inheritDoc
     */
    public function findByEventId($event_id)
    {
        $res = $this->model->getByEventId((string) $event_id);
        return is_array($res) ? $res : [];
    }
}
