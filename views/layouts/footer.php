    </div> <!-- Cierre del contenedor principal (wrapper) -->

    <!-- Bootstrap 5 Bundle JS con dependencias de Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script de control interactivo de la barra lateral (Sidebar) -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('toggle-sidebar');
            const mobileToggleBtn = document.getElementById('mobile-toggle-sidebar');
            const sidebar = document.getElementById('sidebar');
            
            // Alternar estado colapsado en pantallas de escritorio
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('collapsed');
                });
            }
            
            // Alternar visibilidad del sidebar en pantallas móviles (pantalla superpuesta)
            if (mobileToggleBtn && sidebar) {
                mobileToggleBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    sidebar.classList.toggle('show');
                });
            }
            
            // Cerrar el sidebar móvil de manera intuitiva haciendo clic fuera de su área
            document.addEventListener('click', (e) => {
                if (sidebar && sidebar.classList.contains('show') && !sidebar.contains(e.target) && e.target !== mobileToggleBtn) {
                    sidebar.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
