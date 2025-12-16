<?php
require_once __DIR__ . '/Database.php';

class Subscription {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new subscription for a user
     */
    /**
     * Create a new subscription for a company
     */
    public function createSubscription($companyId, $planName, $billingCycle = 'monthly', $status = 'trial', $userId = null) {
        // Get plan details
        $plan = $this->db->fetchOne(
            "SELECT * FROM subscription_plans WHERE plan_name = ? AND is_active = 1",
            [$planName]
        );

        if (!$plan) {
            throw new Exception("Invalid subscription plan");
        }

        // Calculate price based on billing cycle
        $price = ($billingCycle === 'annual') ? $plan['annual_price'] : $plan['monthly_price'];

        // Determine dates based on status
        if ($status === 'trial') {
            $trialEndsAt = date('Y-m-d H:i:s', strtotime('+14 days'));
            $currentPeriodStart = date('Y-m-d H:i:s');
        } else {
            $trialEndsAt = null;
            $currentPeriodStart = date('Y-m-d H:i:s');
        }
        
        $currentPeriodEnd = ($billingCycle === 'annual') 
            ? date('Y-m-d H:i:s', strtotime('+1 year'))
            : date('Y-m-d H:i:s', strtotime('+1 month'));

        // Create subscription
        $subscriptionId = $this->db->insert('subscriptions', [
            'company_id' => $companyId,
            'user_id' => $userId, // Optional: Purchaser ID
            'plan_name' => $planName,
            'plan_price' => $price,
            'billing_cycle' => $billingCycle,
            'status' => $status,
            'trial_ends_at' => $trialEndsAt,
            'current_period_start' => $currentPeriodStart,
            'current_period_end' => $currentPeriodEnd
        ]);

        return $subscriptionId;
    }

    /**
     * Get subscription for a company
     */
    public function getSubscription($companyId) {
        return $this->db->fetchOne(
            "SELECT s.*, sp.features, sp.max_users, sp.storage_gb 
             FROM subscriptions s
             LEFT JOIN subscription_plans sp ON s.plan_name = sp.plan_name
             WHERE s.company_id = ? 
             ORDER BY s.created_at DESC 
             LIMIT 1",
            [$companyId]
        );
    }

    // ... updateSubscriptionStatus and cancelSubscription remain ID based ...

    /**
     * Check if company has active trial
     */
    public function isTrialActive($companyId) {
        $subscription = $this->getSubscription($companyId);
        
        if (!$subscription) {
            return false;
        }

        if ($subscription['status'] !== 'trial') {
            return false;
        }

        $now = time();
        $trialEnds = strtotime($subscription['trial_ends_at']);

        return $now < $trialEnds;
    }

    /**
     * Check if company has used their trial (any previous subscription)
     */
    public function hasUsedTrial($companyId) {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM subscriptions WHERE company_id = ?",
            [$companyId]
        );
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Check if company has active subscription
     */
    public function hasActiveSubscription($companyId) {
        $subscription = $this->getSubscription($companyId);
        
        if (!$subscription) {
            return false;
        }

        // Trial is considered active
        if ($this->isTrialActive($companyId)) {
            return true;
        }

        if ($subscription['status'] === 'trial') {
            $now = time();
            $trialEnds = strtotime($subscription['trial_ends_at']);
            return $now < $trialEnds;
        }

        // Check if subscription is active and not expired
        if ($subscription['status'] === 'active') {
            $now = time();
            $periodEnds = strtotime($subscription['current_period_end']);
            return $now < $periodEnds;
        }

        return false;
    }

    /**
     * Get all subscription plans
     */
    public function getPlans() {
        return $this->db->fetchAll(
            "SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY display_order"
        );
    }

    /**
     * Upgrade/Downgrade subscription
     */
    public function changePlan($subscriptionId, $newPlanName, $billingCycle = null) {
        $subscription = $this->db->fetchOne(
            "SELECT * FROM subscriptions WHERE id = ?",
            [$subscriptionId]
        );

        if (!$subscription) {
            throw new Exception("Subscription not found");
        }

        $plan = $this->db->fetchOne(
            "SELECT * FROM subscription_plans WHERE plan_name = ? AND is_active = 1",
            [$newPlanName]
        );

        if (!$plan) {
            throw new Exception("Invalid subscription plan");
        }

        $cycle = $billingCycle ?? $subscription['billing_cycle'];
        $price = ($cycle === 'annual') ? $plan['annual_price'] : $plan['monthly_price'];

        return $this->db->update('subscriptions',
            [
                'plan_name' => $newPlanName,
                'plan_price' => $price,
                'billing_cycle' => $cycle,
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = ?',
            [$subscriptionId]
        );
    }

    /**
     * Activate subscription after payment
     */
    public function activateSubscription($subscriptionId, $razorpaySubscriptionId = null, $razorpayCustomerId = null) {
        $updateData = [
            'status' => 'active',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($razorpaySubscriptionId) {
            $updateData['razorpay_subscription_id'] = $razorpaySubscriptionId;
        }

        if ($razorpayCustomerId) {
            $updateData['razorpay_customer_id'] = $razorpayCustomerId;
        }

        return $this->db->update('subscriptions',
            $updateData,
            'id = ?',
            [$subscriptionId]
        );
    }

    /**
     * Get subscription statistics
     */
    public function getSubscriptionStats($companyId) {
        $subscription = $this->getSubscription($companyId);
        
        if (!$subscription) {
            return null;
        }

        $stats = [
            'plan_name' => $subscription['plan_name'],
            'status' => $subscription['status'],
            'billing_cycle' => $subscription['billing_cycle'],
            'price' => $subscription['plan_price'],
            'max_users' => $subscription['max_users'],
            'storage_gb' => $subscription['storage_gb'],
            'trial_ends_at' => $subscription['trial_ends_at'],
            'current_period_end' => $subscription['current_period_end'],
            'is_trial' => $this->isTrialActive($companyId),
            'is_active' => $this->hasActiveSubscription($companyId)
        ];

        // Calculate days remaining
        if ($stats['is_trial']) {
            $now = time();
            $trialEnds = strtotime($subscription['trial_ends_at']);
            $stats['days_remaining'] = ceil(($trialEnds - $now) / 86400);
        } else {
            $now = time();
            $periodEnds = strtotime($subscription['current_period_end']);
            $stats['days_remaining'] = ceil(($periodEnds - $now) / 86400);
        }

        return $stats;
    }
}
