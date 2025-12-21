<?php
/**
 * Central Permission Routing Map
 * 
 * Maps URL patterns to required permissions.
 * Keys are regex patterns matching the script path relative to BASE_URL.
 * Values are ['module', 'action'].
 */

return [
    // Admin / Super Admin
    '#^modules/admin/users\.php#' => ['super_admin', 'view'],
    '#^modules/admin/roles\.php#' => ['super_admin', 'view'],
    '#^modules/admin/permissions\.php#' => ['super_admin', 'view'],
    '#^modules/admin/.*\.php#' => ['super_admin', 'view'], // Catch-all for other admin files

    // Sales
    '#^modules/sales/invoices/create\.php#' => ['sales', 'create'],
    '#^modules/sales/invoices/edit\.php#' => ['sales', 'edit'],
    '#^modules/sales/invoices/delete\.php#' => ['sales', 'delete'],
    '#^modules/sales/invoices/.*\.php#' => ['sales', 'view'], // Default view for index/view
    
    '#^modules/sales/quotations/create\.php#' => ['sales', 'create'],
    '#^modules/sales/quotations/edit\.php#' => ['sales', 'edit'],
    '#^modules/sales/quotations/.*\.php#' => ['sales', 'view'],
    
    '#^modules/sales/orders/create\.php#' => ['sales', 'create'],
    '#^modules/sales/orders/edit\.php#' => ['sales', 'edit'],
    '#^modules/sales/orders/.*\.php#' => ['sales', 'view'],

    // Inventory
    '#^modules/inventory/products/create\.php#' => ['inventory', 'create'],
    '#^modules/inventory/products/edit\.php#' => ['inventory', 'edit'],
    '#^modules/inventory/products/delete\.php#' => ['inventory', 'delete'],
    '#^modules/inventory/warehouses/create\.php#' => ['inventory', 'create'],
    '#^modules/inventory/warehouses/edit\.php#' => ['inventory', 'edit'],
    '#^modules/inventory/.*\.php#' => ['inventory', 'view'],

    // HRM
    '#^modules/hr/employees/create\.php#' => ['hrm', 'create'],
    '#^modules/hr/employees/edit\.php#' => ['hrm', 'edit'],
    '#^modules/hr/attendance/mark\.php#' => ['hrm', 'create'],
    '#^modules/hr/leaves/apply\.php#' => ['hrm', 'create'],
    '#^modules/hr/payroll/process\.php#' => ['hrm', 'create'],
    '#^modules/hr/.*\.php#' => ['hrm', 'view'],

    // Accounting
    '#^modules/accounting/.*\.php#' => ['accounting', 'view'], // Basic view for now, refine if needed

    // Reports
    '#^modules/reports/.*\.php#' => ['reports', 'view'],
    
    // Settings
    '#^modules/settings/company\.php#' => ['settings', 'edit'],
    '#^modules/settings/permissions\.php#' => ['settings', 'edit'],
    '#^modules/settings/delete-data\.php#' => ['settings', 'delete'],
    '#^modules/settings/roles\.php#' => ['settings', 'view'], 
    '#^modules/settings/(?!profile\.php).*\.php#' => ['settings', 'view'],
];
