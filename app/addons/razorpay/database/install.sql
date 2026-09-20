DROP TABLE IF EXISTS ?:razorpay_transactions;
CREATE TABLE ?:razorpay_transactions (
    order_id mediumint(8) unsigned NOT NULL,
    razorpay_order_id varchar(64) NOT NULL DEFAULT '',
    razorpay_payment_id varchar(64) NOT NULL DEFAULT '',
    razorpay_signature varchar(255) NOT NULL DEFAULT '',
    amount decimal(12,2) NOT NULL DEFAULT 0.00,
    currency varchar(3) NOT NULL DEFAULT 'INR',
    status varchar(32) NOT NULL DEFAULT '',
    error_code varchar(64) NOT NULL DEFAULT '',
    error_description text,
    created_at int(11) unsigned NOT NULL DEFAULT 0,
    updated_at int(11) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (order_id),
    KEY idx_razorpay_order_id (razorpay_order_id),
    KEY idx_razorpay_payment_id (razorpay_payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS ?:razorpay_webhook_logs;
CREATE TABLE ?:razorpay_webhook_logs (
    log_id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    event_id varchar(64) NOT NULL DEFAULT '',
    event_type varchar(64) NOT NULL DEFAULT '',
    razorpay_order_id varchar(64) NOT NULL DEFAULT '',
    razorpay_payment_id varchar(64) NOT NULL DEFAULT '',
    payload mediumtext,
    status varchar(32) NOT NULL DEFAULT 'processed',
    created_at int(11) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (log_id),
    KEY idx_event_id (event_id),
    KEY idx_order_id (razorpay_order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELETE FROM ?:payment_processors WHERE processor_script = 'razorpay.php';
INSERT INTO ?:payment_processors (processor, processor_script, processor_template, admin_template, callback, type, addon)
VALUES ('Razorpay', 'razorpay.php', 'views/orders/components/payments/cc_outside.tpl', 'razorpay.tpl', 'Y', 'P', 'razorpay');
