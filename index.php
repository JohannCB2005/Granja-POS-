<?php
session_start();

// Si NO existe la sesión del usuario, cargamos la vista del Login desde la carpeta views
if (!isset($_SESSION['id_usuario'])) {
    require_once 'views/V_login.php';
    exit;
}

// ==========================================
// ZONA DEL SISTEMA (Usuario Logueado)
// ==========================================
// Aquí más adelante cargarás tu archivo de rutas (config/rutas.php)
// Por ahora, dejamos un Dashboard temporal con Bootstrap para probar el éxito del login
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - POS Granja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="alert alert-success shadow-sm">
            <h4 class="alert-heading fw-bold">¡Bienvenido al sistema, <?php echo htmlspecialchars($_SESSION['nombres']); ?>!</h4>
            <p>Has ingresado exitosamente con el rol de <strong><?php echo htmlspecialchars($_SESSION['rol']); ?></strong>.</p>
            <hr>
            <p class="mb-0">Este archivo <code>index.php</code> pronto servirá como el enrutador principal para los demás módulos.</p>
        </div>
    </div>
</body>
</html>