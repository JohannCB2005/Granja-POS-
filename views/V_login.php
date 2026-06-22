<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POS Granja UNP</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/logo_unp.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .login-card { border-radius: 15px; border: none; }
        .card-header { border-radius: 15px 15px 0 0 !important; }
    </style>
</head>
<body class="bg-light d-flex align-items-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg login-card">
                    <div class="card-header text-center bg-success text-white py-3">
                        <h4 class="mb-0 fw-bold">POS Granja Zootecnia</h4>
                    </div>
                    <div class="card-body p-4">
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">Usuario</label>
                                <input type="text" class="form-control form-control-lg" id="username" placeholder="Ingresa tu usuario" required autocomplete="off">
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Contraseña</label>
                                <input type="password" class="form-control form-control-lg" id="password" placeholder="••••••••" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg fw-bold">Ingresar al Sistema</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3 text-muted">
                    <small>Universidad Nacional de Piura &copy; 2026</small>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>