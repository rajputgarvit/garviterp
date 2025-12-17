<?php
require_once __DIR__ . '/Database.php';

class DataExporter {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function generateBackup($companyId) {
        $zipFile = tempnam(sys_get_temp_dir(), 'backup_') . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Cannot create zip file.");
        }

        // Define tables to export
        $tables = [
            'users' => "SELECT username, email, full_name, created_at FROM users WHERE company_id = ?",
            'customers' => "SELECT company_name, contact_person, email, phone, gstin, created_at FROM customers WHERE company_id = ?",
            'products' => "SELECT name, description, sku, standard_cost as purchase_price, selling_price as sale_price FROM products WHERE company_id = ?",
            'invoices' => "SELECT invoice_number, invoice_date, due_date, subtotal, total_amount, status, payment_status FROM invoices WHERE company_id = ?",
            'suppliers' => "SELECT company_name, contact_person, email, phone, gstin, created_at FROM suppliers WHERE company_id = ?",
            'subscriptions' => "SELECT plan_name, plan_price, billing_cycle, status, current_period_start as start_date, current_period_end as end_date FROM subscriptions WHERE company_id = ?"
        ];

        foreach ($tables as $name => $query) {
            try {
                // Check if table exists first (optional, but good for robustness if modules are disabled)
                // We'll trust the query for now, catch exception if table missing?
                // Better: just try to fetch.
                $data = $this->db->fetchAll($query, [$companyId]);
                
                if (!empty($data)) {
                    $csvContent = $this->arrayToCsv($data);
                    $zip->addFromString("{$name}.csv", $csvContent);
                }
            } catch (Exception $e) {
                // Table might not exist or other error, skip it
                error_log("Export failed for table $name: " . $e->getMessage());
            }
        }

        $zip->close();
        return $zipFile;
    }

    private function arrayToCsv($data) {
        if (empty($data)) return '';

        $output = fopen('php://temp', 'r+');
        
        // Header
        fputcsv($output, array_keys($data[0]), ",", "\"", "\\");

        // Rows
        foreach ($data as $row) {
            fputcsv($output, $row, ",", "\"", "\\");
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);
        return $content;
    }
}
