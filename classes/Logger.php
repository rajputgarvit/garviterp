<?php
require_once __DIR__ . '/Database.php';

class Logger {
    private $db;
    private $auth;

    public function __construct() {
        $this->db = Database::getInstance();
        // Lazy load auth to avoid circular dependencies if auth uses logger
    }

    public function log($action, $resourceType = null, $resourceId = null, $details = null) {
        $userId = $_SESSION['user_id'] ?? null;
        $companyId = $_SESSION['company_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'CLI';

        // Context details
        $context = [
            'details' => $details,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];

        return $this->db->insert('audit_logs', [
            'company_id' => $companyId,
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $resourceType, // Mapping resource_type to table_name
            'record_id' => $resourceId,
            'new_values' => json_encode($context), // Storing details in new_values
            'ip_address' => $ipAddress,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getLogs($companyId, $limit = 50) {
        return $this->db->fetchAll(
            "SELECT l.*, u.full_name, u.email 
             FROM audit_logs l 
             LEFT JOIN users u ON l.user_id = u.id 
             WHERE l.company_id = ? 
             ORDER BY l.created_at DESC 
             LIMIT ?",
            [$companyId, $limit]
        );
    }
}
