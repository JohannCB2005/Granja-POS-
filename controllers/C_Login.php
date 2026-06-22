<?php
require_once '../config/conexion.php';
require_once '../entities/Persona.php'; 
require_once '../entities/Usuario.php'; 
require_once '../models/M_Usuario.php';

// 1. Recibir los datos del fetch (JS)
$json = file_get_contents('php://input');
$datos = json_decode($json);

// 2. Comprobar que los datos vengan en el JSON
if (isset($datos->username) && isset($datos->password)) {
    $username = trim($datos->username);
    $password_ingresada = $datos->password;

    // 3. Instanciar el DAO y buscar el usuario en la BD
    $modeloUsuario = M_Usuario::singleton();
    $usuario = $modeloUsuario->verificarLogin($username);

    // 4. Verificación súper segura (Compara el input con el Hash guardado)
    if ($usuario && password_verify($password_ingresada, $usuario['password'])) {
        
        // Iniciamos la sesión segura en el servidor
        session_start();
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombres'] = $usuario['nombres_razon_social'];
        $_SESSION['rol'] = $usuario['rol'];

        // Enviamos respuesta afirmativa
        echo json_encode([
            "success" => true, 
            "mensaje" => "Bienvenido " . $usuario['nombres_razon_social']
        ]);

    } else {
        // Falló el login (Retornamos mensaje general por seguridad)
        echo json_encode([
            "success" => false, 
            "mensaje" => "Usuario o contraseña incorrectos."
        ]);
    }
} else {
    // Faltaron parámetros
    echo json_encode(["success" => false, "mensaje" => "Faltan datos requeridos."]);
}
?>