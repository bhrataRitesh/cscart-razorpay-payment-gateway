<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay;

use Tygh\Addons\InstallerInterface;
use Tygh\Addons\Razorpay\Model\PaymentModel;
use Tygh\Addons\Razorpay\Model\PaymentProcessorModel;
use Tygh\Core\ApplicationInterface;

class Installer implements InstallerInterface
{
    /**
     * @var \Tygh\Core\ApplicationInterface
     */
    protected $application;

    /**
     * @var \Tygh\Database\Connection
     */
    protected $db;

    public function __construct(ApplicationInterface $app)
    {
        $this->application = $app;
        $this->db = $app['db'];
    }

    /**
     * @inheritDoc
     */
    public static function factory(ApplicationInterface $app)
    {
        return new self($app);
    }

    /**
     * @inheritDoc
     */
    public function onBeforeInstall()
    {
        return;
    }

    /**
     * @inheritDoc
     */
    public function onInstall()
    {
        $processor_model = new PaymentProcessorModel($this->db);
        $processor = $processor_model->getByScript('razorpay.php');

        if (!$processor) {
            $processor_model->insert([
                'processor'          => 'Razorpay',
                'processor_script'   => 'razorpay.php',
                'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
                'admin_template'     => 'razorpay.tpl',
                'callback'           => 'Y',
                'type'               => 'P',
                'addon'              => 'razorpay',
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function onUninstall()
    {
        $processor_model = new PaymentProcessorModel($this->db);
        $processor = $processor_model->getByScript('razorpay.php');

        if (!$processor) {
            return;
        }

        $processor_model->deleteById($processor['processor_id']);

        $payment_model = new PaymentModel($this->db);
        $payment_model->disableByProcessorId((int) $processor['processor_id']);
    }
}
