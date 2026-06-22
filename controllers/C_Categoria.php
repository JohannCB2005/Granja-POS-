<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "mensaje" => "No autorizado."]);
    exit;
}

require_once dirname(__DIR__) . '/entities/Categoria.php';
require_once dirname(__DIR__) . '/models/M_Categoria.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$model = M_Categoria::singleton();

switch ($action) {
    case 'listar':
        echo json_encode($model->listar());
        break;

    case 'crear':
        if ($_SESSION['rol'] !== 'Administrador') {
            echo json_encode(["success" => false, "mensaje" => "No tienes permisos para esta acción."]);
            exit;
        }
        $nombre = isset($input['nombre']) ? trim($input['nombre']) : '';
        $descripcion = isset($input['descripcion']) ? trim($input['descripcion']) : '';
        
        if (empty($nombre)) {
            echo json_encode(["success" => false, "mensaje" => "El nombre es obligatorio."]);
            exit;
        }
        
        $cat = new Categoria($nombre, $descripcion);
        if ($model->registrar($cat)) {
            echo json_encode(["success" => true, "mensaje" => "Categoría registrada con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al registrar la categoría."]);
        }
        break;

    case 'actualizar':
        if ($_SESSION['rol'] !== 'Administrador') {
            echo json_encode(["success" => false, "mensaje" => "No tienes permisos para esta acción."]);
            exit;
        }
        $id = isset($input['id_categoria']) ? intval($input['id_categoria']) : 0;
        $nombre = isset($input['nombre']) ? trim($input['nombre']) : '';
        $descripcion = isset($input['descripcion']) ? trim($input['descripcion']) : '';
        
        if ($id <= 0 || empty($nombre)) {
            echo json_encode(["success" => false, "mensaje" => "Datos inválidos."]);
            exit;
        }
        
        $cat = new Categoria($nombre, $descripcion);
        $cat->id_categoria = $id;
        
        if ($model->actualizar($cat)) {
            echo json_encode(["success" => true, "mensaje" => "Categoría actualizada con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al actualizar la categoría."]);
        }
        break;

    case 'eliminar':
        if ($_SESSION['rol'] !== 'Administrador') {
            echo json_encode(["success" => false, "mensaje" => "No tienes permisos para esta acción."]);
            exit;
        }
        $id = isset($input['id_categoria']) ? intval($input['id_categoria']) : 0;
        
        if ($id <= 0) {
            echo json_encode(["success" => false, "mensaje" => "ID de categoría inválido."]);
            exit;
        }
        
        if ($model->eliminar($id)) {
            echo json_encode(["success" => true, "mensaje" => "Categoría eliminada con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al eliminar la categoría."]);
        }
        break;

    default:
        echo json_encode(["success" => false, "mensaje" => "Acción no válida."]);
        break;
}
?>
