<?php
require_once 'Database.php';
require_once 'PaytmChecksum.php';

class Payment {
    private $db;
    private $paytmMerchantId;
    private $paytmMerchantKey;
    private $paytmWebsite;
    private $paytmIndustryType;
    private $paytmChannelId;

    public function __construct() {
        $this->db = Database::getInstance();
        
        // Paytm Credentials (TEST)
        $this->paytmMerchantId = 'EBhZEy48586547679294';
        $this->paytmMerchantKey = 'y8JVmIw9WeSQT7Ma';
        $this->paytmWebsite = 'WEBSTAGING';
        $this->paytmIndustryType = 'Retail';
        $this->paytmChannelId = 'WEB';
    }

    /**
     * Get Paytm Configuration
     */
    public function getPaytmConfig() {
        return [
            'MID' => $this->paytmMerchantId,
            'WEBSITE' => $this->paytmWebsite,
            'INDUSTRY_TYPE_ID' => $this->paytmIndustryType,
            'CHANNEL_ID' => $this->paytmChannelId,
            'CALLBACK_URL' => MODULES_URL . '/subscription/checkout.php'
        ];
    }

    /**
     * Generate Paytm Signature (Checksum)
     */
    public function generatePaytmSignature($params) {
        return PaytmChecksum::generateSignature($params, $this->paytmMerchantKey);
    }

    /**
     * Verify Paytm Signature
     */
    public function verifyPaytmSignature($params, $checksum) {
        return PaytmChecksum::verifySignature($params, $this->paytmMerchantKey, $checksum);
    }

    /**
     * Prepare Paytm Parameters for Checkout
     */
    public function getPaytmParams($orderId, $amount, $custId, $email = '', $mobile = '') {
        $config = $this->getPaytmConfig();
        
        $params = [
            "MID" => $config['MID'],
            "WEBSITE" => $config['WEBSITE'],
            "INDUSTRY_TYPE_ID" => $config['INDUSTRY_TYPE_ID'],
            "CHANNEL_ID" => $config['CHANNEL_ID'],
            "ORDER_ID" => $orderId,
            "CUST_ID" => $custId,
            "TXN_AMOUNT" => number_format((float)$amount, 2, '.', ''), // Amount must be string format 0.00
            "CALLBACK_URL" => $config['CALLBACK_URL'],
        ];

        if (!empty($email)) {
            $params["EMAIL"] = $email;
        }
        if (!empty($mobile)) {
            $params["MOBILE_NO"] = $mobile;
        }

        return $params;
    }

    /**
     * Record payment transaction
     */
    public function recordTransaction($subscriptionId, $paymentData) {
        return $this->db->insert('payment_transactions', [
            'subscription_id' => $subscriptionId,
            'razorpay_payment_id' => $paymentData['txn_id'] ?? null, // Use txn_id as payment_id ref
            'razorpay_order_id' => $paymentData['order_id'] ?? null, // Use order_id ref
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'INR',
            'status' => $paymentData['status'] ?? 'pending',
            'payment_method' => $paymentData['method'] ?? 'Paytm',
            'transaction_date' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get payment history for a subscription
     */
    public function getPaymentHistory($subscriptionId) {
        return $this->db->fetchAll(
            "SELECT * FROM payment_transactions 
             WHERE subscription_id = ? 
             ORDER BY transaction_date DESC",
            [$subscriptionId]
        );
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($transactionId, $status) {
        return $this->db->update('payment_transactions',
            ['status' => $status],
            'id = ?',
            [$transactionId]
        );
    }

    /**
     * Calculate total revenue for a subscription
     */
    public function getTotalRevenue($subscriptionId) {
        $result = $this->db->fetchOne(
            "SELECT SUM(amount) as total 
             FROM payment_transactions 
             WHERE subscription_id = ? AND status = 'success'",
            [$subscriptionId]
        );

        return $result['total'] ?? 0;
    }

    /**
     * Get recent transactions
     */
    public function getRecentTransactions($subscriptionId, $limit = 10) {
        return $this->db->fetchAll(
            "SELECT * FROM payment_transactions 
             WHERE subscription_id = ? 
             ORDER BY transaction_date DESC 
             LIMIT ?",
            [$subscriptionId, $limit]
        );
    }
}

