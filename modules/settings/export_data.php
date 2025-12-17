<?php
$currentPage = 'export_data';
require_once '../../config/config.php';
require_once '../../includes/admin_layout.php';
require_once '../../classes/DataExporter.php';

$user = $currentUser;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    try {
        $exporter = new DataExporter();
        $zipPath = $exporter->generateBackup($user['company_id']);
        
        if (file_exists($zipPath)) {
            // Serve file
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="company_data_backup_' . date('Y-m-d') . '.zip"');
            header('Content-Length: ' . filesize($zipPath));
            readfile($zipPath);
            
            // Cleanup
            unlink($zipPath);
            exit;
        } else {
            $error = "Failed to generate backup file.";
        }
    } catch (Exception $e) {
        $error = "Export error: " . $e->getMessage();
    }
}
?>

<div>



<div class="container-fluid" style="padding: 20px;">
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div class="card-header">
            <div class="card-title">Data Export & Backup</div>
        </div>
        <div class="card-body text-center" style="padding: 40px;">
            <i class="fas fa-cloud-download-alt" style="font-size: 48px; color: #4a90e2; margin-bottom: 20px;"></i>
            <h4>Download Your Data</h4>
            <p class="text-muted mb-4">
                Export all your company data (Customers, Invoices, Products, etc.) into a single ZIP file containing CSVs.
                Use this for backups or migrating to other tools.
            </p>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="export" value="1">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-file-export"></i> Generator Backup Archive
                </button>
            </form>
            
            <div style="margin-top: 20px;">
                <a href="../admin/settings.php" class="text-muted small">Back to Settings</a>
            </div>
        </div>
    </div>
</div>

</div>
</div><!-- End content-area -->
</main><!-- End main-content -->
</div><!-- End dashboard-wrapper -->

<?php require_once '../../includes/footer.php'; ?>
