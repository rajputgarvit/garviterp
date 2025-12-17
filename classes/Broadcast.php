<?php
require_once __DIR__ . '/Database.php';

class Broadcast {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($title, $message, $type, $startDate, $endDate, $createdBy, $targetCompanyId = null) {
        return $this->db->insert('system_broadcasts', [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'created_by' => $createdBy,
            'target_company_id' => $targetCompanyId ?: null
        ]);
    }

    public function getAll() {
        // Fetch all broadcasts with company name if targeted
        return $this->db->fetchAll("
            SELECT b.*, c.company_name 
            FROM system_broadcasts b
            LEFT JOIN company_settings c ON b.target_company_id = c.id
            ORDER BY b.created_at DESC
        ");
    }

    public function getActiveBroadcasts($userCompanyId = null) {
        $now = date('Y-m-d H:i:s');
        $params = [$now, $now];
        
        $sql = "SELECT * FROM system_broadcasts 
                WHERE is_active = 1 
                AND start_date <= ? 
                AND end_date >= ?";
        
        if ($userCompanyId) {
            // Show if Global (target_company_id IS NULL) OR matches user's company
            $sql .= " AND (target_company_id IS NULL OR target_company_id = ?)";
            $params[] = $userCompanyId;
        } else {
            // If no company context provided (e.g. public page?), maybe just global?
            // Assuming global only if no company ID.
             $sql .= " AND target_company_id IS NULL";
        }
        
        $sql .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function delete($id) {
        return $this->db->delete('system_broadcasts', 'id = ?', [$id]);
    }
    
    public function toggleStatus($id) {
        // Toggle is_active
        return $this->db->query("UPDATE system_broadcasts SET is_active = NOT is_active WHERE id = ?", [$id]);
    }
}
