    </div> <!-- Close wrapper -->

    <!-- Bootstrap 5 Bundle JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('toggle-sidebar');
            const mobileToggleBtn = document.getElementById('mobile-toggle-sidebar');
            const sidebar = document.getElementById('sidebar');
            
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('collapsed');
                });
            }
            
            if (mobileToggleBtn && sidebar) {
                mobileToggleBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    sidebar.classList.toggle('show');
                });
            }
            
            // Close mobile sidebar when clicking outside
            document.addEventListener('click', (e) => {
                if (sidebar && sidebar.classList.contains('show') && !sidebar.contains(e.target) && e.target !== mobileToggleBtn) {
                    sidebar.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
