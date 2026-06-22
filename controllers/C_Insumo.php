<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "mensaje" => "No autorizado."]);
    exit;
}

require_once dirname(__DIR__) . '/entities/Insumo.php';
require_once dirname(__DIR__) . '/models/M_Insumo.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$model = M_Insumo::singleton();

switch ($action) {
    case 'listar':
        echo json_encode($model->listar());
        break;

    case 'crear':
        if ($_SESSION['rol'] !== 'Administrador') {
            echo json_encode(["success" => false, "mensaje" => "No tienes permisos para esta acción."]);
            exit;
        }
        $id_categoria = isset($input['id_categoria']) ? intval($input['id_categoria']) : 0;
        $id_unidad = isset($input['id_unidad']) ? intval($input['id_unidad']) : 0;
        $nombre = isset($input['nombre']) ? trim($input['nombre']) : '';
        $precio_unitario = isset($input['precio_unitario']) ? floatval($input['precio_unitario']) : 0.0;
        $stock = isset($input['stock']) ? floatval($input['stock']) : 0.0;

        if ($id_categoria <= 0 || $id_unidad <= 0 || empty($nombre) || $precio_unitario < 0 || $stock < 0) {
            echo json_encode(["success" => false, "mensaje" => "Datos inválidos o faltantes."]);
            exit;
        }

        $insumo = new Insumo($id_categoria, $id_unidad, $nombre, $precio_unitario, $stock);
        if ($model->registrar($insumo)) {
            echo json_encode(["success" => true, "mensaje" => "Insumo registrado con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al registrar el insumo."]);
        }
        break;

    case 'actualizar':
        if ($_SESSION['rol'] !== 'Administrador') {
            echo json_encode(["success" => false, "mensaje" => "No tienes permisos para esta acción."]);
            exit;
        }
        $id_insumo = isset($input['id_insumo']) ? intval($input['id_insumo']) : 0;
        $id_categoria = isset($input['id_categoria']) ? intval($input['id_categoria']) : 0;
        $id_unidad = isset($input['id_unidad']) ? intval($input['id_unidad']) : 0;
        $nombre = isset($input['nombre']) ? trim($input['nombre']) : '';
        $precio_unitario = isset($input['precio_unitario']) ? floatval($input['precio_unitario']) : 0.0;
        $stock = isset($input['stock']) ? floatval($input['stock']) : 0.0;

        if ($id_insumo <= 0 || $id_categoria <= 0 || $id_unidad <= 0 || empty($nombre) || $precio_unitario < 0 || $stock < 0) {
            echo json_encode(["success" => false, "mensaje" => "Datos inválidos o faltantes."]);
            exit;
        }

        $insumo = new Insumo($id_categoria, $id_unidad, $nombre, $precio_unitario, $stock);
        $insumo->id_insumo = $id_insumo;

        if ($model->actualizar($insumo)) {
            echo json_encode(["success" => true, "mensaje" => "Insumo actualizado con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al actualizar el insumo."]);
        }
        break;

    case 'eliminar':
        if ($_SESSION['rol'] !== 'Administrador') {
            echo json_encode(["success" => false, "mensaje" => "No tienes permisos para esta acción."]);
            exit;
        }
        $id_insumo = isset($input['id_insumo']) ? intval($input['id_insumo']) : 0;

        if ($id_insumo <= 0) {
            echo json_encode(["success" => false, "mensaje" => "ID de insumo inválido."]);
            exit;
        }

        if ($model->eliminar($id_insumo)) {
            echo json_encode(["success" => true, "mensaje" => "Insumo eliminado con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al eliminar el insumo."]);
        }
        break;

    default:
        echo json_encode(["success" => false, "mensaje" => "Acción no válida."]);
        break;
}
?>
