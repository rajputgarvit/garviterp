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

        // Cancel any existing active/trial subscriptions to ensure only one is active
        $this->db->update('subscriptions', 
            [
                'status' => 'cancelled', 
                'cancelled_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], 
            "company_id = ? AND status IN ('active', 'trial')", 
            [$companyId]
        );

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

        return $this->db->insert('subscriptions', [
            'company_id' => $subscription['company_id'],
            'user_id' => $subscription['user_id'],
            'plan_name' => $newPlanName,
            'plan_price' => $price,
            'billing_cycle' => $cycle,
            'status' => 'active', // Assuming immediate activation on change
            'trial_ends_at' => null,
            'current_period_start' => date('Y-m-d H:i:s'),
            'current_period_end' => ($cycle === 'annual') 
                ? date('Y-m-d H:i:s', strtotime('+1 year'))
                : date('Y-m-d H:i:s', strtotime('+1 month')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
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

    /**
     * Assign a manual subscription (Admin Override)
     */
    public function assignManualSubscription($companyId, $planName, $startDate, $endDate) {
        $plan = $this->db->fetchOne(
            "SELECT * FROM subscription_plans WHERE plan_name = ?",
            [$planName]
        );

        if (!$plan) {
            throw new Exception("Invalid subscription plan");
        }

        // Fetch company owner
        $owner = $this->db->fetchOne(
            "SELECT id FROM users WHERE company_id = ? ORDER BY created_at ASC LIMIT 1",
            [$companyId]
        );

        // Cancel any existing active/trial subscriptions to ensure only one is active
        $this->db->update('subscriptions', 
            [
                'status' => 'cancelled', 
                'cancelled_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], 
            "company_id = ? AND status IN ('active', 'trial')", 
            [$companyId]
        );

        // Always insert a new record to preserve history
        return $this->db->insert('subscriptions', [
            'company_id' => $companyId,
            'user_id' => $owner['id'] ?? null,
            'plan_name' => $planName,
            'plan_price' => 0.00,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'current_period_start' => $startDate,
            'current_period_end' => $endDate,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Cancel a subscription (Admin Override)
     */
    public function cancelSubscription($companyId) {
        $existing = $this->db->fetchOne(
            "SELECT id FROM subscriptions WHERE company_id = ? ORDER BY id DESC LIMIT 1",
            [$companyId]
        );

        if (!$existing) {
            throw new Exception("No subscription found for this company");
        }

        return $this->db->update('subscriptions',
            [
                'status' => 'cancelled',
                'current_period_end' => date('Y-m-d H:i:s'), // Immediate cancellation
                'updated_at' => date('Y-m-d H:i:s')
            ],
            'id = ?',
            [$existing['id']]
        );
    }
}

