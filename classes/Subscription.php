<?php
require_once __DIR__ . '/Database.php';

class Subscription {
    private $db;
    private $companyId;
    private $features = null;

    public function __construct($companyId = null) {
        $this->db = Database::getInstance();
        $this->companyId = $companyId;
    }

    /**
     * Check if company has an active subscription
     * @param int|null $companyId
     * @return bool
     */
    public function hasActiveSubscription($companyId = null) {
        $cid = $companyId ?? $this->companyId;
        if (!$cid) return false;

        $sub = $this->db->fetchOne(
            "SELECT id FROM subscriptions WHERE company_id = ? AND status IN ('active', 'trial') AND (current_period_end IS NULL OR current_period_end >= NOW())", 
            [$cid]
        );
        
        return (bool)$sub;
    }

    /**
     * Get detailed subscription stats for header/dashboard
     * @param int|null $companyId
     * @return array|false
     */
    public function getSubscriptionStats($companyId = null) {
        $cid = $companyId ?? $this->companyId;
        if (!$cid) return false;

        $sub = $this->db->fetchOne(
            "SELECT * FROM subscriptions WHERE company_id = ? AND status IN ('active', 'trial') ORDER BY created_at DESC LIMIT 1", 
            [$cid]
        );
        
        if (!$sub) return false;

        $isTrial = $sub['status'] === 'trial';
        $endDate = $isTrial ? $sub['trial_ends_at'] : $sub['current_period_end'];
        
        $daysRemaining = 0;
        if ($endDate) {
            $now = new DateTime();
            $end = new DateTime($endDate);
            $daysRemaining = (int)$now->diff($end)->format('%r%a');
        }

        return [
            'is_trial' => $isTrial,
            'days_remaining' => max(0, $daysRemaining),
            'plan_name' => $sub['plan_name'],
            'status' => $sub['status']
        ];
    }

    /**
     * Check if a company has ever used a trial subscription
     * @param int|null $companyId
     * @return bool
     */
    public function hasUsedTrial($companyId = null) {
        $cid = $companyId ?? $this->companyId;
        if (!$cid) return false;

        $trial = $this->db->fetchOne(
            "SELECT id FROM subscriptions WHERE company_id = ? AND (status = 'trial' OR trial_ends_at IS NOT NULL)", 
            [$cid]
        );
        
        return (bool)$trial;
    }

    /**
     * Load all features and limits for the company into memory
     */
    private function loadFeatures() {
        if ($this->features !== null) {
            return;
        }

        $rows = $this->db->fetchAll("SELECT * FROM v_company_plan_limits WHERE company_id = ?", [$this->companyId]);
        $this->features = [];
        foreach ($rows as $row) {
            $this->features[$row['feature_code']] = [
                'is_enabled' => (bool)$row['is_enabled'],
                'limit_value' => $row['limit_value'] === null ? INF : (int)$row['limit_value'],
                'feature_name' => $row['feature_name']
            ];
        }
    }

    /**
     * Check if a feature is enabled
     * @param string $featureCode
     * @return bool
     */
    public function canAccess($featureCode) {
        $this->loadFeatures();
        return isset($this->features[$featureCode]) && $this->features[$featureCode]['is_enabled'];
    }

    /**
     * Get the limit for a specific feature
     * @param string $featureCode
     * @return int|float (INF for unlimited)
     */
    public function getLimit($featureCode) {
        $this->loadFeatures();
        if (!isset($this->features[$featureCode])) {
            return 0;
        }
        return $this->features[$featureCode]['limit_value'];
    }

    /**
     * Check usage against limits
     * @param string $featureCode
     * @return array ['current' => int, 'limit' => int|INF, 'remaining' => int|INF, 'status' => string]
     */
    public function getUsageStatus($featureCode) {
        $status = $this->db->fetchOne("SELECT * FROM v_company_usage_status WHERE company_id = ? AND feature_code = ?", [$this->companyId, $featureCode]);
        
        if (!$status) {
            // If checking a non-measurable feature or one without usage yet
            $limit = $this->getLimit($featureCode);
            return [
                'current' => 0,
                'limit' => $limit,
                'remaining' => $limit,
                'status' => 'normal'
            ];
        }

        $limit = $status['limit_value'] === null ? INF : (int)$status['limit_value'];
        $current = (int)$status['current_usage'];
        
        return [
            'current' => $current,
            'limit' => $limit,
            'remaining' => $limit === INF ? INF : max(0, $limit - $current),
            'status' => $status['usage_status']
        ];
    }

    /**
     * Increment usage for a feature
     * @param string $featureCode
     * @param int $amount
     */
    public function incrementUsage($featureCode, $amount = 1) {
        // Use the Stored Procedure for atomic increment
        // Get subscription ID first
        $sub = $this->db->fetchOne("SELECT id FROM subscriptions WHERE company_id = ? AND status IN ('active', 'trial')", [$this->companyId]);
        
        if ($sub) {
            $this->db->query("CALL sp_increment_usage(?, ?, ?)", [$sub['id'], $featureCode, $amount]);
        }
    }

    /**
     * Create a new subscription for a company
     * @param int $companyId
     * @param string $planName
     * @param string $billingCycle 'monthly' or 'annual'
     * @param string $status 'trial', 'active', 'cancelled', 'expired'
     * @param int|null $userId The user who created this subscription
     * @return int The ID of the new subscription
     */
    public function createSubscription($companyId, $planName, $billingCycle, $status = 'active', $userId = null) {
        // 1. Get Plan Details
        $plan = $this->db->fetchOne("SELECT * FROM subscription_plans WHERE plan_name = ?", [$planName]);
        if (!$plan) {
            throw new Exception("Subscription plan '$planName' not found.");
        }

        $price = ($billingCycle === 'annual') ? $plan['annual_price'] : $plan['monthly_price'];
        
        // 2. Determine Period
        $start = date('Y-m-d H:i:s');
        $end = null;
        $trialEnd = null;

        if ($status === 'trial') {
            $trialEnd = date('Y-m-d H:i:s', strtotime('+14 days'));
            $end = $trialEnd; // Trial period ends
        } else {
            $period = ($billingCycle === 'annual') ? '+1 year' : '+1 month';
            $end = date('Y-m-d H:i:s', strtotime($period));
        }

        // 3. Cancel any existing active/trial subscriptions
        $this->db->update(
            "subscriptions", 
            ['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s')], 
            "company_id = ? AND status IN ('active', 'trial')", 
            [$companyId]
        );

        // 4. If no userId provided, find the owner
        if (!$userId) {
            $owner = $this->db->fetchOne("SELECT id FROM users WHERE company_id = ? ORDER BY created_at ASC LIMIT 1", [$companyId]);
            $userId = $owner ? $owner['id'] : null;
        }

        // 5. Create new Subscription
        $subscriptionData = [
            'user_id' => $userId,
            'company_id' => $companyId,
            'plan_name' => $planName,
            'plan_price' => $price,
            'billing_cycle' => $billingCycle,
            'status' => $status,
            'trial_ends_at' => $trialEnd,
            'current_period_start' => $start,
            'current_period_end' => $end,
            'created_at' => $start
        ];
        
        $subId = $this->db->insert("subscriptions", $subscriptionData);
        
        // 6. Reset internal cache if for same company
        if ($this->companyId == $companyId) {
            $this->features = null;
        }

        return $subId;
    }

    /**
     * Get all plans with their features for display
     */
    public function getPlans() {
        // 1. Fetch Plans
        $plans = $this->db->fetchAll("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY monthly_price ASC");
        
        // 2. Fetch Features for each plan
        foreach ($plans as &$plan) {
            $features = $this->db->fetchAll("
                SELECT f.feature_name, pf.limit_value
                FROM plan_features pf
                JOIN feature_definitions f ON pf.feature_code = f.feature_code
                WHERE pf.plan_id = ? AND pf.is_enabled = 1
                ORDER BY f.display_order ASC
            ", [$plan['id']]);
            
            // Format features for display
            $plan['features_list'] = [];
            foreach ($features as $f) {
                if ($f['limit_value'] !== null) {
                     // For limits like Users: "5 Users" instead of "Maximum Users (5)" ideally, but using generic format for now
                     // Let's formatting be smart: if name starts with "Maximum ", remove it?
                     // e.g. "Maximum Users" -> "5 Users"
                     $name = $f['feature_name'];
                     if (strpos($name, 'Maximum ') === 0) {
                         $cleanName = substr($name, 8);
                         $plan['features_list'][] = $f['limit_value'] . ' ' . $cleanName;
                     } else {
                         $plan['features_list'][] = $name . ': ' . $f['limit_value'];
                     }
                } else {
                    $plan['features_list'][] = $f['feature_name'];
                }
            }
        }
        
        return $plans;
    }
    /**
     * Main method to assign a subscription manually (e.g. from Admin Panel)
     * @param int $companyId
     * @param string $planName
     * @param string $startDate (Y-m-d H:i:s)
     * @param string $endDate (Y-m-d H:i:s)
     */
    public function assignManualSubscription($companyId, $planName, $startDate, $endDate) {
        // 1. Find a User ID (Owner) for this company
        // We need a user_id for the subscriptions table constraint
        $owner = $this->db->fetchOne("SELECT id FROM users WHERE company_id = ? ORDER BY created_at ASC LIMIT 1", [$companyId]);
        
        if (!$owner) {
            throw new Exception("Cannot assign subscription: Company has no users.");
        }
        
        $userId = $owner['id'];
        
        // 2. Get Plan Details
        $plan = $this->db->fetchOne("SELECT monthly_price FROM subscription_plans WHERE plan_name = ?", [$planName]);
        $price = $plan ? $plan['monthly_price'] : 0.00;
        
        // 3. Cancel any existing active subscriptions
        $this->db->update(
            "subscriptions", 
            ['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s')], 
            "company_id = ? AND status IN ('active', 'trial')", 
            [$companyId]
        );
        
        // 4. Create new Subscription
        $subscriptionData = [
            'user_id' => $userId,
            'company_id' => $companyId,
            'plan_name' => $planName,
            'plan_price' => $price, // Assuming monthly for manual
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'current_period_start' => $startDate,
            'current_period_end' => $endDate,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert("subscriptions", $subscriptionData);
        
        // 5. Reset internal cache if for same company
        if ($this->companyId == $companyId) {
            $this->features = null;
        }
    }

    /**
     * Cancel a company's subscription immediately
     * @param int $companyId
     */
    public function cancelSubscription($companyId) {
        $this->db->update(
            "subscriptions", 
            [
                'status' => 'cancelled', 
                'cancelled_at' => date('Y-m-d H:i:s'),
                'current_period_end' => date('Y-m-d H:i:s') // End immediately
            ], 
            "company_id = ? AND status IN ('active', 'trial')", 
            [$companyId]
        );
        
        if ($this->companyId == $companyId) {
            $this->features = null;
        }
    }

    // ==========================================
    // OFFLINE SUBSCRIPTION REQUESTS
    // ==========================================

    public function requestSubscription($companyId, $userId, $planName, $billingCycle) {
        // Check for existing pending request
        $existing = $this->db->fetchOne(
            "SELECT id FROM subscription_requests WHERE company_id = ? AND status = 'pending'", 
            [$companyId]
        );
        
        if ($existing) {
            // Update existing request
            $this->db->update('subscription_requests', [
                'plan_name' => $planName,
                'billing_cycle' => $billingCycle,
                'request_date' => date('Y-m-d H:i:s')
            ], 'id = ?', [$existing['id']]);
        } else {
            // Create new request
            $this->db->insert('subscription_requests', [
                'company_id' => $companyId,
                'user_id' => $userId,
                'plan_name' => $planName,
                'billing_cycle' => $billingCycle,
                'status' => 'pending'
            ]);
        }
    }

    public function getPendingRequests() {
        return $this->db->fetchAll("
            SELECT sr.*, c.company_name, u.full_name as user_name, u.email as user_email
            FROM subscription_requests sr
            JOIN company_settings c ON sr.company_id = c.id
            JOIN users u ON sr.user_id = u.id
            WHERE sr.status = 'pending'
            ORDER BY sr.request_date DESC
        ");
    }

    public function approveRequest($requestId, $adminId, $startDate, $endDate) {
        $request = $this->db->fetchOne("SELECT * FROM subscription_requests WHERE id = ?", [$requestId]);
        if (!$request) return false;

        // Use the existing manual assignment logic
        $this->assignManualSubscription($request['company_id'], $request['plan_name'], $startDate, $endDate);

        // Mark request as approved
        $this->db->update('subscription_requests', [
            'status' => 'approved',
            'processed_by' => $adminId,
            'processed_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$requestId]);
        
        return true;
    }

    public function rejectRequest($requestId, $adminId) {
        $this->db->update('subscription_requests', [
            'status' => 'rejected',
            'processed_by' => $adminId,
            'processed_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$requestId]);
    }
}
