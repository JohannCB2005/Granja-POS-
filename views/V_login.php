<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Granja POS</title>
    <link rel="icon" type="image/png" href="assets/logo_unp.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f4f0;
        }

        /* ── Card wrapper ── */
        .login-wrapper {
            display: flex;
            width: 900px;
            max-width: 96vw;
            min-height: 540px;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 24px 64px -12px rgba(0,0,0,0.22), 0 8px 24px -6px rgba(0,0,0,0.10);
            background: #fff;
        }

        /* ── Left panel ── */
        .login-hero {
            flex: 0 0 42%;
            position: relative;
            background: url('assets/login_hero.jpg') center center / cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 36px 32px;
        }

        .login-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                180deg,
                rgba(0,0,0,0.08) 0%,
                rgba(10,50,20,0.72) 100%
            );
        }

        .hero-text {
            position: relative;
            z-index: 1;
            color: #fff;
        }

        .hero-text h2 {
            font-size: 26px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .hero-text h2 span {
            color: #4ade80;
        }

        .hero-text p {
            font-size: 13.5px;
            color: rgba(255,255,255,0.80);
            line-height: 1.6;
        }

        /* ── Right panel ── */
        .login-form-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 52px 48px;
        }

        .brand-name {
            font-size: 32px;
            font-weight: 800;
            color: #15803d;
            letter-spacing: -1px;
            margin-bottom: 28px;
            text-transform: uppercase;
        }

        .brand-name span {
            color: #1e3a2f;
        }

        .login-title {
            font-size: 26px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }

        .login-subtitle {
            font-size: 13.5px;
            color: #6b7280;
            margin-bottom: 32px;
            line-height: 1.55;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control {
            height: 46px;
            border-radius: 10px;
            border: 1.5px solid #d1d5db;
            font-size: 14px;
            color: #111827;
            transition: border-color 0.15s, box-shadow 0.15s;
            box-shadow: none !important;
        }

        .form-control:focus {
            border-color: #15803d;
            box-shadow: 0 0 0 3px rgba(21,128,61,0.12) !important;
        }

        .input-group .form-control {
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group .btn-toggle-pass {
            border: 1.5px solid #d1d5db;
            border-left: none;
            border-radius: 0 10px 10px 0;
            background: #fff;
            color: #9ca3af;
            padding: 0 14px;
            transition: color 0.15s;
        }

        .input-group .btn-toggle-pass:hover { color: #15803d; }

        .input-group:focus-within .form-control,
        .input-group:focus-within .btn-toggle-pass {
            border-color: #15803d;
        }

        .input-group:focus-within .btn-toggle-pass {
            box-shadow: 0 0 0 3px rgba(21,128,61,0.12);
        }

        .btn-login {
            height: 48px;
            border-radius: 10px;
            background: #15803d;
            border: none;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: background 0.18s, transform 0.1s;
            width: 100%;
            margin-top: 24px;
        }

        .btn-login:hover { background: #166534; }
        .btn-login:active { transform: scale(0.985); }

        .btn-login:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }

        .login-footer {
            margin-top: 18px;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
        }

        .login-footer a {
            color: #15803d;
            font-weight: 600;
            text-decoration: none;
        }

        .login-footer a:hover { text-decoration: underline; }

        /* Responsive */
        @media (max-width: 650px) {
            .login-hero { display: none; }
            .login-form-panel { padding: 36px 28px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- LEFT: Hero Image Panel -->
    <div class="login-hero">
        <div class="hero-text">
            <h2>La forma moderna<br>de <span>gestionar tu granja</span></h2>
            <p>Controla ventas, inventario y clientes desde un solo lugar. Simple, rápido y confiable.</p>
        </div>
    </div>

    <!-- RIGHT: Form Panel -->
    <div class="login-form-panel">
        <div class="brand-name">Granja <span>POS</span></div>

        <h1 class="login-title">Iniciar Sesión 👋</h1>
        <p class="login-subtitle">
            Bienvenido al sistema de gestión de insumos y ventas.<br>
            Ingresa tus credenciales para continuar.
        </p>

        <form id="loginForm" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Usuario</label>
                <input
                    type="text"
                    class="form-control"
                    id="username"
                    placeholder="Ingresa tu usuario"
                    autocomplete="username"
                    required>
            </div>

            <div class="mb-1">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-group">
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required>
                    <button type="button" class="btn-toggle-pass" id="togglePass" tabindex="-1">
                        <i class="bi bi-eye" id="togglePassIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                Iniciar Sesión
            </button>
        </form>

        <div class="login-footer">
            <a href="#" onclick="return false;">¿Olvidaste tu contraseña?</a>
        </div>
    </div>
</div>

<script>
    // Toggle password visibility
    document.getElementById('togglePass').addEventListener('click', function () {
        const pass = document.getElementById('password');
        const icon = document.getElementById('togglePassIcon');
        if (pass.type === 'password') {
            pass.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            pass.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });

    // Login submit
    document.getElementById('loginForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const btn      = document.getElementById('loginBtn');

        if (!username || !password) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Por favor ingresa tu usuario y contraseña.',
                confirmButtonColor: '#15803d'
            });
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Verificando...';

        try {
            const response = await fetch('controllers/C_Login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Bienvenido!',
                    text: data.mensaje,
                    showConfirmButton: false,
                    timer: 1200,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = 'index.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Acceso denegado',
                    text: data.mensaje,
                    confirmButtonColor: '#15803d'
                });
                btn.disabled = false;
                btn.innerHTML = 'Iniciar Sesión';
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo contactar al servidor.',
                confirmButtonColor: '#15803d'
            });
            btn.disabled = false;
            btn.innerHTML = 'Iniciar Sesión';
        }
    });
</script>
</body>
</html>