<?php
// Capturamos el módulo solicitado, si no hay ninguno, forzamos el login
$modulo = isset($_GET['modulo']) ? $_GET['modulo'] : 'login';

switch ($modulo) {
    case 'login':
        require_once "controllers/C_Login.php";
        break;

    case 'inventario':
        // require_once "controllers/C_Inventario.php";
        break;

    case 'ventas':
        // require_once "controllers/C_Venta.php";
        break;

    default:
        echo "<h1>Error 404 - Ruta no encontrada</h1>";
        break;
}
?>