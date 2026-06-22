document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault(); // Evita que la pantalla parpadee o se recargue

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            try {
                // Al estar la vista cargada desde index.php en la raíz, 
                // buscamos el controlador desde ahí.
                const response = await fetch('./controllers/C_Login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();

                if (data.success) {
                    // Alerta elegante de éxito con SweetAlert2
                    Swal.fire({
                        icon: 'success',
                        title: '¡Acceso Concedido!',
                        text: data.mensaje,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Recargamos la página. Como ahora SÍ existe una sesión, 
                        // el index.php entrará a la zona del "Dashboard".
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Acceso Denegado',
                        text: data.mensaje,
                        confirmButtonColor: '#198754'
                    });
                }
            } catch (error) {
                console.error("Error en la petición:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo conectar con el servidor backend.',
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    }
});