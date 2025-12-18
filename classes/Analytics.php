<?php
require_once __DIR__ . '/Database.php';

class Analytics {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getTotalUsers() {
        return $this->db->fetchOne("SELECT COUNT(*) as count FROM users")['count'];
    }

    public function getActiveSubscriptions() {
        return $this->db->fetchOne("SELECT COUNT(*) as count FROM subscriptions WHERE status IN ('active', 'trial')")['count'];
    }

    public function getTotalRevenue() {
        return $this->db->fetchOne("SELECT SUM(amount) as total FROM payment_transactions WHERE status = 'success'")['total'] ?? 0;
    }

    public function getMRR() {
        $mrr = 0;
        $activeSubscriptions = $this->db->fetchAll("SELECT plan_price, billing_cycle FROM subscriptions WHERE status = 'active'");
        foreach ($activeSubscriptions as $sub) {
            if ($sub['billing_cycle'] === 'monthly') {
                $mrr += $sub['plan_price'];
            } else {
                $mrr += $sub['plan_price'] / 12;
            }
        }
        return $mrr;
    }

    public function getChurnRate() {
        // Simple Churn: (Cancelled Subscriptions / Total Ever Subscribed) * 100
        // Or better: Cancelled in last 30 days / Active at start of 30 days.
        // Let's stick to simple "Logo Churn" for now: Active vs Cancelled status in subscriptions table.
        // Note: A company might have multiple subscription rows due to history. 
        // We need unique companies who currently have NO active subscription but HAD one.
        
        // 1. Get total unique companies with any subscription history
        $totalCompaniesWithSubs = $this->db->fetchOne("SELECT COUNT(DISTINCT company_id) as count FROM subscriptions")['count'];
        
        if ($totalCompaniesWithSubs == 0) return 0;

        // 2. Get companies that currently have an active/trial subscription
        $activeCompanies = $this->db->fetchOne("
            SELECT COUNT(DISTINCT company_id) as count 
            FROM subscriptions 
            WHERE status IN ('active', 'trial')
        ")['count'];

        // 3. Churned = Total - Active
        $churnedCompanies = $totalCompaniesWithSubs - $activeCompanies;

        return ($churnedCompanies / $totalCompaniesWithSubs) * 100;
    }

    public function getUserGrowth($months = 6) {
        return $this->db->fetchAll("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM users
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ", [$months]);
    }

    public function getSubscriptionDistribution() {
        return $this->db->fetchAll("
            SELECT plan_name, COUNT(*) as count 
            FROM subscriptions 
            WHERE status = 'active' 
            GROUP BY plan_name
        ");
    }

    public function getRecentUsers($limit = 5) {
        return $this->db->fetchAll("
            SELECT u.*, s.status as sub_status 
            FROM users u
            LEFT JOIN subscriptions s ON u.company_id = s.company_id AND s.status = 'active'
            ORDER BY u.created_at DESC 
            LIMIT ?
        ", [$limit]);
    }
}
