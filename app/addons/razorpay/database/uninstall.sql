DROP TABLE IF EXISTS ?:razorpay_transactions;
DROP TABLE IF EXISTS ?:razorpay_webhook_logs;

DELETE FROM ?:payment_processors WHERE processor_script = 'razorpay.php';
