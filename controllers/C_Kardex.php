<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado.']);
    exit;
}

require_once dirname(__DIR__) . '/models/M_Kardex.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$model  = M_Kardex::singleton();

switch ($action) {
    case 'listar_insumos':
        $insumos = $model->listarInsumos();
        echo json_encode(['success' => true, 'data' => $insumos]);
        break;

    case 'movimientos':
        $id_insumo = isset($_GET['id_insumo']) ? intval($_GET['id_insumo']) : 0;
        $desde     = isset($_GET['desde'])     ? trim($_GET['desde'])       : null;
        $hasta     = isset($_GET['hasta'])     ? trim($_GET['hasta'])       : null;
        $tipo      = isset($_GET['tipo'])      ? trim($_GET['tipo'])        : 'todos';
        $busqueda  = isset($_GET['busqueda'])  ? trim($_GET['busqueda'])    : '';

        if ($id_insumo <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'Insumo inválido.']);
            exit;
        }

        $result = $model->obtenerMovimientos($id_insumo, $desde ?: null, $hasta ?: null, $tipo, $busqueda);

        if (isset($result['error'])) {
            echo json_encode(['success' => false, 'mensaje' => $result['error']]);
        } else {
            echo json_encode(['success' => true, 'data' => $result]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida.']);
        break;
}
?>
