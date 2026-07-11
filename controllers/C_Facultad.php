<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "mensaje" => "No autorizado."]);
    exit;
}

require_once dirname(__DIR__) . '/models/M_Facultad.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$model = M_Facultad::singleton();

switch ($action) {
    case 'listar':
        echo json_encode($model->listar());
        break;
    default:
        echo json_encode(["success" => false, "mensaje" => "Acción no válida."]);
        break;
}
?>
