<?php
// Start session for CSRF and bruteforce tracking
session_start();

// Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin');
header('Content-Type: application/json');

require_once '../config/conexion.php';
require_once '../entities/Persona.php'; 
require_once '../entities/Usuario.php'; 
require_once '../models/M_Usuario.php';

// Brute force protection setup
if (!isset($_SESSION['login_intentos'])) {
    $_SESSION['login_intentos'] = 0;
    $_SESSION['ultimo_intento'] = time();
}

// Check if blocked
if ($_SESSION['login_intentos'] >= 5) {
    if (time() - $_SESSION['ultimo_intento'] < 600) { // 10 minutes
        $minutos_restantes = ceil((600 - (time() - $_SESSION['ultimo_intento'])) / 60);
        echo json_encode(["success" => false, "mensaje" => "Cuenta bloqueada temporalmente por demasiados intentos fallidos. Intenta en $minutos_restantes minuto(s)."]);
        exit;
    } else {
        // Reset after 10 min
        $_SESSION['login_intentos'] = 0;
    }
}

// 1. Recibir los datos del fetch (JS)
$json = file_get_contents('php://input');
$datos = json_decode($json);

// 2. Comprobar que los datos vengan en el JSON
if (isset($datos->username) && isset($datos->password) && isset($datos->csrf_token)) {
    // CSRF verification
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $datos->csrf_token)) {
        echo json_encode(["success" => false, "mensaje" => "Token de seguridad inválido. Recarga la página."]);
        exit;
    }

    $username = trim($datos->username);
    $password_ingresada = $datos->password;

    // 3. Instanciar el DAO y buscar el usuario en la BD
    $modeloUsuario = M_Usuario::singleton();
    $usuario = $modeloUsuario->verificarLogin($username);

    // 4. Verificación súper segura
    if ($usuario && $usuario['estado'] == 1 && password_verify($password_ingresada, $usuario['password'])) {
        
        // Iniciamos la sesión segura en el servidor
        session_regenerate_id(true); // Prevenir Session Fixation
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombres'] = $usuario['nombres_razon_social'];
        $_SESSION['rol'] = $usuario['rol'];
        
        // Reset brute force counters
        $_SESSION['login_intentos'] = 0;
        unset($_SESSION['csrf_token']); // Consume CSRF token

        // Enviamos respuesta afirmativa
        echo json_encode([
            "success" => true, 
            "mensaje" => "Bienvenido " . $usuario['nombres_razon_social']
        ]);

    } else {
        // Falló el login
        $_SESSION['login_intentos']++;
        $_SESSION['ultimo_intento'] = time();
        echo json_encode([
            "success" => false, 
            "mensaje" => "Usuario o contraseña incorrectos, o cuenta inactiva."
        ]);
    }
} else {
    // Faltaron parámetros
    echo json_encode(["success" => false, "mensaje" => "Faltan datos requeridos."]);
}
?>