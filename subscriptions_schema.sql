-- =====================================================
-- ENHANCED PRICING MODEL SCHEMA
-- =====================================================
-- This schema extends your existing subscription system
-- with granular feature controls and usage tracking
-- =====================================================

-- 1. PLAN FEATURES TABLE
-- Defines what features are available in each plan
CREATE TABLE `plan_features` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `plan_id` INT(11) NOT NULL,
  `feature_code` VARCHAR(50) NOT NULL COMMENT 'e.g., advanced_reporting, api_access, multi_warehouse',
  `feature_name` VARCHAR(100) NOT NULL,
  `feature_category` ENUM('module', 'feature', 'integration', 'support', 'limit') DEFAULT 'feature',
  `is_enabled` TINYINT(1) DEFAULT 1,
  `limit_value` INT(11) DEFAULT NULL COMMENT 'NULL = unlimited, number = specific limit',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_plan_feature` (`plan_id`, `feature_code`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_feature_code` (`feature_code`),
  CONSTRAINT `fk_plan_features_plan` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. FEATURE DEFINITIONS TABLE
-- Master list of all features that can be controlled
CREATE TABLE `feature_definitions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `feature_code` VARCHAR(50) NOT NULL UNIQUE,
  `feature_name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `category` ENUM('module', 'feature', 'integration', 'support', 'limit') DEFAULT 'feature',
  `is_measurable` TINYINT(1) DEFAULT 0 COMMENT '1 if this feature has a numeric limit (users, storage, etc)',
  `default_limit` INT(11) DEFAULT NULL COMMENT 'Default limit if measurable',
  `is_active` TINYINT(1) DEFAULT 1,
  `display_order` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_code` (`feature_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. SUBSCRIPTION USAGE TRACKING
-- Track actual usage against limits
CREATE TABLE `subscription_usage` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `subscription_id` INT(11) NOT NULL,
  `feature_code` VARCHAR(50) NOT NULL,
  `usage_count` INT(11) DEFAULT 0,
  `usage_date` DATE NOT NULL,
  `reset_at` DATETIME DEFAULT NULL COMMENT 'When this counter resets (monthly/yearly)',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_subscription_feature_date` (`subscription_id`, `feature_code`, `usage_date`),
  KEY `idx_subscription` (`subscription_id`),
  KEY `idx_feature` (`feature_code`),
  KEY `idx_usage_date` (`usage_date`),
  CONSTRAINT `fk_usage_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. MODULE ACCESS CONTROL
-- Control which modules are available in each plan
CREATE TABLE `plan_modules` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `plan_id` INT(11) NOT NULL,
  `module_code` VARCHAR(50) NOT NULL COMMENT 'hrm, inventory, sales, purchases, accounting, crm, production, reports',
  `is_enabled` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_plan_module` (`plan_id`, `module_code`),
  KEY `idx_plan_id` (`plan_id`),
  CONSTRAINT `fk_plan_modules_plan` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. COMPANY FEATURE OVERRIDES
-- Allow custom feature access per company (enterprise customization)
CREATE TABLE `company_feature_overrides` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `company_id` INT(11) NOT NULL,
  `feature_code` VARCHAR(50) NOT NULL,
  `is_enabled` TINYINT(1) DEFAULT 1,
  `limit_value` INT(11) DEFAULT NULL COMMENT 'Override the plan limit',
  `notes` TEXT DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME DEFAULT NULL COMMENT 'For temporary access grants',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_company_feature` (`company_id`, `feature_code`),
  KEY `idx_company_id` (`company_id`),
  KEY `idx_feature_code` (`feature_code`),
  CONSTRAINT `fk_company_override_company` FOREIGN KEY (`company_id`) REFERENCES `company_settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. USAGE ALERTS
-- Track when companies approach or exceed limits
CREATE TABLE `usage_alerts` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `company_id` INT(11) NOT NULL,
  `subscription_id` INT(11) NOT NULL,
  `feature_code` VARCHAR(50) NOT NULL,
  `alert_type` ENUM('warning', 'limit_reached', 'limit_exceeded') DEFAULT 'warning',
  `current_usage` INT(11) NOT NULL,
  `limit_value` INT(11) NOT NULL,
  `alert_message` TEXT DEFAULT NULL,
  `is_notified` TINYINT(1) DEFAULT 0,
  `notified_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_company` (`company_id`),
  KEY `idx_subscription` (`subscription_id`),
  KEY `idx_alert_type` (`alert_type`),
  KEY `idx_notified` (`is_notified`),
  CONSTRAINT `fk_usage_alerts_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INITIAL DATA - FEATURE DEFINITIONS
-- =====================================================

INSERT INTO `feature_definitions` (`feature_code`, `feature_name`, `description`, `category`, `is_measurable`, `default_limit`, `display_order`) VALUES
-- User Limits
('max_users', 'Maximum Users', 'Maximum number of users allowed', 'limit', 1, 5, 1),
('max_products', 'Maximum Products', 'Maximum number of products in inventory', 'limit', 1, 1000, 2),
('max_customers', 'Maximum Customers', 'Maximum number of customers', 'limit', 1, 500, 3),
('max_invoices_per_month', 'Monthly Invoice Limit', 'Maximum invoices per month', 'limit', 1, 100, 4),
('storage_gb', 'Storage (GB)', 'File storage limit in GB', 'limit', 1, 5, 5),

-- Module Access
('module_hrm', 'HRM Module', 'Human Resource Management', 'module', 0, NULL, 10),
('module_inventory', 'Inventory Module', 'Inventory Management', 'module', 0, NULL, 11),
('module_sales', 'Sales Module', 'Sales Management', 'module', 0, NULL, 12),
('module_purchases', 'Purchases Module', 'Purchase Management', 'module', 0, NULL, 13),
('module_accounting', 'Accounting Module', 'Accounting & Finance', 'module', 0, NULL, 14),
('module_crm', 'CRM Module', 'Customer Relationship Management', 'module', 0, NULL, 15),
('module_production', 'Production Module', 'Manufacturing & Production', 'module', 0, NULL, 16),
('module_reports', 'Reports Module', 'Advanced Reporting', 'module', 0, NULL, 17),

-- Features
('advanced_reports', 'Advanced Reports', 'Custom reports and analytics', 'feature', 0, NULL, 20),
('api_access', 'API Access', 'REST API access', 'feature', 0, NULL, 21),
('multi_warehouse', 'Multiple Warehouses', 'Multiple warehouse support', 'feature', 0, NULL, 22),
('multi_currency', 'Multi-Currency', 'Multiple currency support', 'feature', 0, NULL, 23),
('barcode_scanning', 'Barcode Scanning', 'Barcode generation and scanning', 'feature', 0, NULL, 24),
('email_notifications', 'Email Notifications', 'Automated email notifications', 'feature', 0, NULL, 25),
('sms_notifications', 'SMS Notifications', 'SMS notification support', 'feature', 0, NULL, 26),
('custom_branding', 'Custom Branding', 'Custom logos and branding', 'feature', 0, NULL, 27),
('data_export', 'Data Export', 'Export data to Excel/CSV', 'feature', 0, NULL, 28),
('data_import', 'Data Import', 'Bulk data import', 'feature', 0, NULL, 29),
('audit_logs', 'Audit Logs', 'Detailed audit trail', 'feature', 0, NULL, 30),
('two_factor_auth', 'Two-Factor Authentication', '2FA security', 'feature', 0, NULL, 31),

-- Integrations
('payment_gateway', 'Payment Gateway', 'Online payment integration', 'integration', 0, NULL, 40),
('shipping_integration', 'Shipping Integration', 'Shipping provider integration', 'integration', 0, NULL, 41),
('accounting_software', 'Accounting Software Sync', 'QuickBooks, Tally integration', 'integration', 0, NULL, 42),
('ecommerce_integration', 'E-commerce Integration', 'Shopify, WooCommerce sync', 'integration', 0, NULL, 43),

-- Support
('email_support', 'Email Support', 'Email support access', 'support', 0, NULL, 50),
('priority_support', 'Priority Support', 'Priority support queue', 'support', 0, NULL, 51),
('phone_support', 'Phone Support', '24/7 phone support', 'support', 0, NULL, 52),
('dedicated_manager', 'Dedicated Account Manager', 'Personal account manager', 'support', 0, NULL, 53);

-- =====================================================
-- PLAN FEATURES - MAP FEATURES TO EXISTING PLANS
-- =====================================================

-- STARTER PLAN (ID: 1)
INSERT INTO `plan_features` (`plan_id`, `feature_code`, `feature_name`, `feature_category`, `is_enabled`, `limit_value`) VALUES
-- Limits
(1, 'max_users', 'Maximum Users', 'limit', 1, 5),
(1, 'max_products', 'Maximum Products', 'limit', 1, 1000),
(1, 'max_customers', 'Maximum Customers', 'limit', 1, 500),
(1, 'max_invoices_per_month', 'Monthly Invoice Limit', 'limit', 1, 100),
(1, 'storage_gb', 'Storage (GB)', 'limit', 1, 5),
-- Modules (Basic)
(1, 'module_inventory', 'Inventory Module', 'module', 1, NULL),
(1, 'module_sales', 'Sales Module', 'module', 1, NULL),
(1, 'module_purchases', 'Purchases Module', 'module', 1, NULL),
(1, 'module_reports', 'Reports Module', 'module', 1, NULL),
-- Features (Basic)
(1, 'email_notifications', 'Email Notifications', 'feature', 1, NULL),
(1, 'custom_branding', 'Custom Branding', 'feature', 1, NULL),
(1, 'data_export', 'Data Export', 'feature', 1, NULL),
-- Support
(1, 'email_support', 'Email Support', 'support', 1, NULL);

-- PROFESSIONAL PLAN (ID: 2)
INSERT INTO `plan_features` (`plan_id`, `feature_code`, `feature_name`, `feature_category`, `is_enabled`, `limit_value`) VALUES
-- Limits
(2, 'max_users', 'Maximum Users', 'limit', 1, 20),
(2, 'max_products', 'Maximum Products', 'limit', 1, 10000),
(2, 'max_customers', 'Maximum Customers', 'limit', 1, 5000),
(2, 'max_invoices_per_month', 'Monthly Invoice Limit', 'limit', 1, 1000),
(2, 'storage_gb', 'Storage (GB)', 'limit', 1, 50),
-- All Modules
(2, 'module_hrm', 'HRM Module', 'module', 1, NULL),
(2, 'module_inventory', 'Inventory Module', 'module', 1, NULL),
(2, 'module_sales', 'Sales Module', 'module', 1, NULL),
(2, 'module_purchases', 'Purchases Module', 'module', 1, NULL),
(2, 'module_accounting', 'Accounting Module', 'module', 1, NULL),
(2, 'module_crm', 'CRM Module', 'module', 1, NULL),
(2, 'module_reports', 'Reports Module', 'module', 1, NULL),
-- Advanced Features
(2, 'advanced_reports', 'Advanced Reports', 'feature', 1, NULL),
(2, 'api_access', 'API Access', 'feature', 1, NULL),
(2, 'multi_warehouse', 'Multiple Warehouses', 'feature', 1, NULL),
(2, 'multi_currency', 'Multi-Currency', 'feature', 1, NULL),
(2, 'barcode_scanning', 'Barcode Scanning', 'feature', 1, NULL),
(2, 'email_notifications', 'Email Notifications', 'feature', 1, NULL),
(2, 'custom_branding', 'Custom Branding', 'feature', 1, NULL),
(2, 'data_export', 'Data Export', 'feature', 1, NULL),
(2, 'data_import', 'Data Import', 'feature', 1, NULL),
(2, 'audit_logs', 'Audit Logs', 'feature', 1, NULL),
-- Integrations
(2, 'payment_gateway', 'Payment Gateway', 'integration', 1, NULL),
(2, 'shipping_integration', 'Shipping Integration', 'integration', 1, NULL),
-- Support
(2, 'email_support', 'Email Support', 'support', 1, NULL),
(2, 'priority_support', 'Priority Support', 'support', 1, NULL);

-- ENTERPRISE PLAN (ID: 3)
INSERT INTO `plan_features` (`plan_id`, `feature_code`, `feature_name`, `feature_category`, `is_enabled`, `limit_value`) VALUES
-- Limits (Unlimited or very high)
(3, 'max_users', 'Maximum Users', 'limit', 1, 999),
(3, 'max_products', 'Maximum Products', 'limit', 1, NULL), -- NULL = unlimited
(3, 'max_customers', 'Maximum Customers', 'limit', 1, NULL),
(3, 'max_invoices_per_month', 'Monthly Invoice Limit', 'limit', 1, NULL),
(3, 'storage_gb', 'Storage (GB)', 'limit', 1, 999),
-- All Modules
(3, 'module_hrm', 'HRM Module', 'module', 1, NULL),
(3, 'module_inventory', 'Inventory Module', 'module', 1, NULL),
(3, 'module_sales', 'Sales Module', 'module', 1, NULL),
(3, 'module_purchases', 'Purchases Module', 'module', 1, NULL),
(3, 'module_accounting', 'Accounting Module', 'module', 1, NULL),
(3, 'module_crm', 'CRM Module', 'module', 1, NULL),
(3, 'module_production', 'Production Module', 'module', 1, NULL),
(3, 'module_reports', 'Reports Module', 'module', 1, NULL),
-- All Features
(3, 'advanced_reports', 'Advanced Reports', 'feature', 1, NULL),
(3, 'api_access', 'API Access', 'feature', 1, NULL),
(3, 'multi_warehouse', 'Multiple Warehouses', 'feature', 1, NULL),
(3, 'multi_currency', 'Multi-Currency', 'feature', 1, NULL),
(3, 'barcode_scanning', 'Barcode Scanning', 'feature', 1, NULL),
(3, 'email_notifications', 'Email Notifications', 'feature', 1, NULL),
(3, 'sms_notifications', 'SMS Notifications', 'feature', 1, NULL),
(3, 'custom_branding', 'Custom Branding', 'feature', 1, NULL),
(3, 'data_export', 'Data Export', 'feature', 1, NULL),
(3, 'data_import', 'Data Import', 'feature', 1, NULL),
(3, 'audit_logs', 'Audit Logs', 'feature', 1, NULL),
(3, 'two_factor_auth', 'Two-Factor Authentication', 'feature', 1, NULL),
-- All Integrations
(3, 'payment_gateway', 'Payment Gateway', 'integration', 1, NULL),
(3, 'shipping_integration', 'Shipping Integration', 'integration', 1, NULL),
(3, 'accounting_software', 'Accounting Software Sync', 'integration', 1, NULL),
(3, 'ecommerce_integration', 'E-commerce Integration', 'integration', 1, NULL),
-- Premium Support
(3, 'email_support', 'Email Support', 'support', 1, NULL),
(3, 'priority_support', 'Priority Support', 'support', 1, NULL),
(3, 'phone_support', 'Phone Support', 'support', 1, NULL),
(3, 'dedicated_manager', 'Dedicated Account Manager', 'support', 1, NULL);

-- =====================================================
-- PLAN MODULES - MAP MODULES TO PLANS
-- =====================================================

-- Starter Plan Modules
INSERT INTO `plan_modules` (`plan_id`, `module_code`, `is_enabled`) VALUES
(1, 'inventory', 1),
(1, 'sales', 1),
(1, 'purchases', 1),
(1, 'reports', 1);

-- Professional Plan Modules (All except production)
INSERT INTO `plan_modules` (`plan_id`, `module_code`, `is_enabled`) VALUES
(2, 'hrm', 1),
(2, 'inventory', 1),
(2, 'sales', 1),
(2, 'purchases', 1),
(2, 'accounting', 1),
(2, 'crm', 1),
(2, 'reports', 1);

-- Enterprise Plan Modules (All)
INSERT INTO `plan_modules` (`plan_id`, `module_code`, `is_enabled`) VALUES
(3, 'hrm', 1),
(3, 'inventory', 1),
(3, 'sales', 1),
(3, 'purchases', 1),
(3, 'accounting', 1),
(3, 'crm', 1),
(3, 'production', 1),
(3, 'reports', 1);

-- =====================================================
-- VIEWS FOR EASY ACCESS
-- =====================================================

-- View to check company's current plan and limits
CREATE OR REPLACE VIEW v_company_plan_limits AS
SELECT 
    cs.id as company_id,
    cs.company_name,
    s.id as subscription_id,
    s.plan_name,
    s.status as subscription_status,
    pf.feature_code,
    pf.feature_name,
    pf.feature_category,
    COALESCE(cfo.limit_value, pf.limit_value) as limit_value,
    COALESCE(cfo.is_enabled, pf.is_enabled) as is_enabled,
    cfo.id as has_override
FROM company_settings cs
INNER JOIN subscriptions s ON cs.id = s.company_id AND s.status IN ('active', 'trial')
INNER JOIN subscription_plans sp ON s.plan_name = sp.plan_name
INNER JOIN plan_features pf ON sp.id = pf.plan_id
LEFT JOIN company_feature_overrides cfo ON cs.id = cfo.company_id 
    AND pf.feature_code = cfo.feature_code 
    AND (cfo.expires_at IS NULL OR cfo.expires_at > NOW());

-- View to check company's current usage vs limits
CREATE OR REPLACE VIEW v_company_usage_status AS
SELECT 
    vcpl.*,
    COALESCE(su.usage_count, 0) as current_usage,
    CASE 
        WHEN vcpl.limit_value IS NULL THEN 'unlimited'
        WHEN COALESCE(su.usage_count, 0) >= vcpl.limit_value THEN 'exceeded'
        WHEN COALESCE(su.usage_count, 0) >= (vcpl.limit_value * 0.8) THEN 'warning'
        ELSE 'normal'
    END as usage_status,
    CASE 
        WHEN vcpl.limit_value IS NULL THEN NULL
        ELSE ROUND((COALESCE(su.usage_count, 0) / vcpl.limit_value) * 100, 2)
    END as usage_percentage
FROM v_company_plan_limits vcpl
LEFT JOIN subscription_usage su ON vcpl.subscription_id = su.subscription_id 
    AND vcpl.feature_code = su.feature_code 
    AND su.usage_date = CURDATE()
WHERE vcpl.feature_category = 'limit';

-- =====================================================
-- HELPER STORED PROCEDURES
-- =====================================================

DELIMITER //

-- Check if a company has access to a feature
CREATE PROCEDURE sp_check_feature_access(
    IN p_company_id INT,
    IN p_feature_code VARCHAR(50),
    OUT p_has_access BOOLEAN,
    OUT p_limit_value INT
)
BEGIN
    SELECT 
        COALESCE(cfo.is_enabled, pf.is_enabled, 0),
        COALESCE(cfo.limit_value, pf.limit_value)
    INTO p_has_access, p_limit_value
    FROM company_settings cs
    INNER JOIN subscriptions s ON cs.id = s.company_id 
        AND s.status IN ('active', 'trial')
    INNER JOIN subscription_plans sp ON s.plan_name = sp.plan_name
    LEFT JOIN plan_features pf ON sp.id = pf.plan_id 
        AND pf.feature_code = p_feature_code
    LEFT JOIN company_feature_overrides cfo ON cs.id = cfo.company_id 
        AND cfo.feature_code = p_feature_code
        AND (cfo.expires_at IS NULL OR cfo.expires_at > NOW())
    WHERE cs.id = p_company_id
    LIMIT 1;
END//

-- Increment usage counter
CREATE PROCEDURE sp_increment_usage(
    IN p_subscription_id INT,
    IN p_feature_code VARCHAR(50),
    IN p_increment INT
)
BEGIN
    INSERT INTO subscription_usage 
        (subscription_id, feature_code, usage_count, usage_date)
    VALUES 
        (p_subscription_id, p_feature_code, p_increment, CURDATE())
    ON DUPLICATE KEY UPDATE 
        usage_count = usage_count + p_increment;
END//

-- Check if usage limit is exceeded
CREATE PROCEDURE sp_check_usage_limit(
    IN p_company_id INT,
    IN p_feature_code VARCHAR(50),
    OUT p_is_exceeded BOOLEAN,
    OUT p_current_usage INT,
    OUT p_limit_value INT
)
BEGIN
    DECLARE v_subscription_id INT;
    
    SELECT s.id INTO v_subscription_id
    FROM subscriptions s
    WHERE s.company_id = p_company_id 
        AND s.status IN ('active', 'trial')
    LIMIT 1;
    
    SELECT 
        CASE 
            WHEN vcpl.limit_value IS NULL THEN FALSE
            WHEN COALESCE(su.usage_count, 0) >= vcpl.limit_value THEN TRUE
            ELSE FALSE
        END,
        COALESCE(su.usage_count, 0),
        vcpl.limit_value
    INTO p_is_exceeded, p_current_usage, p_limit_value
    FROM v_company_plan_limits vcpl
    LEFT JOIN subscription_usage su ON vcpl.subscription_id = su.subscription_id 
        AND vcpl.feature_code = su.feature_code 
        AND su.usage_date = CURDATE()
    WHERE vcpl.company_id = p_company_id 
        AND vcpl.feature_code = p_feature_code
    LIMIT 1;
END//

DELIMITER ;

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================

-- Already included in table definitions above

-- =====================================================
-- NOTES FOR IMPLEMENTATION
-- =====================================================
/*
WHERE TO EDIT:

1. DEFINE NEW FEATURES:
   - Edit: feature_definitions table
   - Add new feature codes, names, and limits

2. ASSIGN FEATURES TO PLANS:
   - Edit: plan_features table
   - Map which features are available in each plan

3. CONTROL MODULE ACCESS:
   - Edit: plan_modules table
   - Enable/disable modules per plan

4. GRANT CUSTOM ACCESS:
   - Edit: company_feature_overrides table
   - Give specific companies special access

5. MONITOR USAGE:
   - View: subscription_usage table
   - Track how much each company uses

6. SET UP ALERTS:
   - Edit: usage_alerts table
   - Configure when to notify about limits

USAGE EXAMPLES:

-- Check if company has access to a feature
CALL sp_check_feature_access(1, 'api_access', @has_access, @limit);
SELECT @has_access, @limit;

-- Check current usage vs limit
CALL sp_check_usage_limit(1, 'max_users', @exceeded, @current, @limit);
SELECT @exceeded, @current, @limit;

-- Increment usage (e.g., when creating a new user)
CALL sp_increment_usage(1, 'max_users', 1);

-- View company's plan and limits
SELECT * FROM v_company_plan_limits WHERE company_id = 1;

-- View usage status
SELECT * FROM v_company_usage_status WHERE company_id = 1;
*/