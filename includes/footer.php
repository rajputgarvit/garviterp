            </div><!-- End Content Area -->
        </main>
    </div><!-- End Dashboard Wrapper -->
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/script.js?v=<?php echo time(); ?>"></script>
    
    <script>
        // Toggle Sidebar on Mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const body = document.body;
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    body.classList.toggle('sidebar-is-collapsed');
                    // Save preference
                    const isCollapsed = body.classList.contains('sidebar-is-collapsed');
                    localStorage.setItem('sidebarCollapsed', isCollapsed);
                });
            }
        });
    </script>
</body>
</html>
