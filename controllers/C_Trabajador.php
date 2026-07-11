<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
    exit;
}

require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/models/M_Trabajador.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$modelo = M_Trabajador::singleton();

switch ($action) {
    case 'listar':
        try {
            $data = $modelo->listar();
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
        }
        break;

    case 'eliminar_seleccionados':
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $ids = $input['ids'] ?? [];
            
            if (empty($ids)) {
                echo json_encode(['success' => false, 'mensaje' => 'No se seleccionaron trabajadores.']);
                exit;
            }
            
            $eliminados = $modelo->eliminar($ids);
            echo json_encode(['success' => true, 'mensaje' => "$eliminados trabajador(es) eliminado(s) correctamente."]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
        break;
}
?>
