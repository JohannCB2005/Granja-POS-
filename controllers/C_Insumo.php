<?php
// Iniciar sesión PHP para el control de autenticación
session_start();

// Definir cabecera de respuesta JSON
header('Content-Type: application/json');

// Restringir el acceso de este controlador únicamente a usuarios con rol de Administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(["success" => false, "mensaje" => "No autorizado."]);
    exit;
}

// Cargar las entidades y modelos necesarios para insumo
require_once dirname(__DIR__) . '/entities/Insumo.php';
require_once dirname(__DIR__) . '/models/M_Insumo.php';

// Obtener la acción a realizar
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Decodificar el cuerpo de la solicitud JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Instanciar el modelo de insumos bajo Singleton
$model = M_Insumo::singleton();

// Enrutar según la acción
switch ($action) {
    
    // Retorna la lista de todos los insumos activos en el inventario
    case 'listar':
        echo json_encode($model->listar());
        break;

    // Registra un nuevo insumo en el inventario
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

        // Validaciones básicas de integridad de datos
        if ($id_categoria <= 0 || $id_unidad <= 0 || empty($nombre) || $precio_unitario < 0 || $stock < 0) {
            echo json_encode(["success" => false, "mensaje" => "Datos inválidos o faltantes."]);
            exit;
        }

        // Crear entidad insumo y guardar en DB
        $insumo = new Insumo($id_categoria, $id_unidad, $nombre, $precio_unitario, $stock);
        if ($model->registrar($insumo)) {
            echo json_encode(["success" => true, "mensaje" => "Insumo registrado con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al registrar el insumo."]);
        }
        break;

    // Actualiza los datos de un insumo existente
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

        // Validaciones de integridad
        if ($id_insumo <= 0 || $id_categoria <= 0 || $id_unidad <= 0 || empty($nombre) || $precio_unitario < 0 || $stock < 0) {
            echo json_encode(["success" => false, "mensaje" => "Datos inválidos o faltantes."]);
            exit;
        }

        // Actualizar datos del insumo
        $insumo = new Insumo($id_categoria, $id_unidad, $nombre, $precio_unitario, $stock);
        $insumo->id_insumo = $id_insumo;

        if ($model->actualizar($insumo)) {
            echo json_encode(["success" => true, "mensaje" => "Insumo actualizado con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al actualizar el insumo."]);
        }
        break;

    // Elimina de forma lógica un insumo del catálogo
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

        // Ejecutar borrado
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
