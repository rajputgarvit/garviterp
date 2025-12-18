<?php
require_once __DIR__ . '/Database.php';

class Permission {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // --- Role Management ---

    public function getRoles($companyId) {
        return $this->db->fetchAll(
            "SELECT * FROM roles WHERE company_id = ? ORDER BY name ASC",
            [$companyId]
        );
    }

    public function getRole($roleId, $companyId) {
        return $this->db->fetchOne(
            "SELECT * FROM roles WHERE id = ? AND company_id = ?",
            [$roleId, $companyId]
        );
    }

    public function createRole($companyId, $name, $description) {
        return $this->db->insert('roles', [
            'company_id' => $companyId,
            'name' => $name,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateRole($roleId, $companyId, $name, $description) {
        // Ensure role belongs to company
        $role = $this->getRole($roleId, $companyId);
        if (!$role) return false;

        return $this->db->update('roles', [
            'name' => $name,
            'description' => $description
        ], 'id = ?', [$roleId]);
    }

    public function deleteRole($roleId, $companyId) {
        // Ensure role belongs to company
        $role = $this->getRole($roleId, $companyId);
        if (!$role) return false;

        // Prevent deleting last admin role or similar checks could be added here
        // For now, just delete.
        // Also delete associations
        $this->db->query("DELETE FROM user_roles WHERE role_id = ?", [$roleId]);
        $this->db->query("DELETE FROM role_permissions WHERE role_id = ?", [$roleId]);
        
        return $this->db->query("DELETE FROM roles WHERE id = ?", [$roleId]);
    }

    // --- Permission Management ---

    public function getAllPermissions() {
        // Fetch all permissions available in the system
        return $this->db->fetchAll("SELECT * FROM permissions ORDER BY module, action");
    }

    public function getPermissionsByModule() {
        $perms = $this->getAllPermissions();
        $grouped = [];
        foreach ($perms as $p) {
            $grouped[$p['module']][] = $p;
        }
        return $grouped;
    }

    public function getRolePermissions($roleId) {
        $rows = $this->db->fetchAll(
            "SELECT permission_id FROM role_permissions WHERE role_id = ?",
            [$roleId]
        );
        return array_column($rows, 'permission_id');
    }

    public function assignPermissionsToRole($roleId, $companyId, $permissionIds) {
        // Validation: Verify role belongs to company
        $role = $this->getRole($roleId, $companyId);
        if (!$role) return false;

        try {
            $this->db->beginTransaction();

            // Clear existing
            $this->db->query("DELETE FROM role_permissions WHERE role_id = ?", [$roleId]);

            // Add new
            if (!empty($permissionIds)) {
                foreach ($permissionIds as $permId) {
                    $this->db->insert('role_permissions', [
                        'role_id' => $roleId,
                        'permission_id' => $permId
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
