<?php
require_once dirname(__DIR__) . '/models/M_Vale.php';
require_once dirname(__DIR__) . '/entities/Vale.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$valesModel = M_Vale::singleton();

switch ($action) {
    case 'listar':
        $datos = $valesModel->listar();
        echo json_encode(['data' => $datos]);
        break;

    case 'emision_masiva':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $campana = $input['campana'] ?? '';
            $tipo_vale = intval($input['tipo_vale'] ?? 1);
            $monto = floatval($input['monto'] ?? 0);
            $id_insumo_especie = !empty($input['id_insumo_especie']) ? intval($input['id_insumo_especie']) : null;
            $cantidad_especie = !empty($input['cantidad_especie']) ? floatval($input['cantidad_especie']) : null;
            $fecha_vencimiento = $input['fecha_vencimiento'] ?? '';
            $filtro_tipo = $input['id_tipo_trabajador'] ?? null;
            $filtro_dep = $input['id_dependencia'] ?? null;
            
            session_start();
            $id_usuario = $_SESSION['id_usuario'] ?? 1;

            if (empty($campana) || empty($fecha_vencimiento)) {
                echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos para la emisión.']);
                exit;
            }

            if ($tipo_vale == 1 && $monto <= 0) {
                echo json_encode(['success' => false, 'mensaje' => 'Monto inválido para vale monetario.']);
                exit;
            }

            if ($tipo_vale == 2 && (!$id_insumo_especie || $cantidad_especie <= 0)) {
                echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos para vale en especie.']);
                exit;
            }

            $res = $valesModel->emisionMasiva($campana, $tipo_vale, $monto, $id_insumo_especie, $cantidad_especie, $fecha_vencimiento, $filtro_tipo, $filtro_dep, $id_usuario);
            echo json_encode($res);
        }
        break;

    case 'buscar_codigo':
        $codigo = $_GET['codigo'] ?? '';
        $vale = $valesModel->buscarPorCodigo($codigo);
        if ($vale) {
            echo json_encode(['success' => true, 'data' => $vale]);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Vale no encontrado']);
        }
        break;

    case 'eliminar_seleccionados':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $ids = $input['ids'] ?? [];
            if (!empty($ids)) {
                $eliminados = $valesModel->eliminarSeleccionados($ids);
                if ($eliminados !== false) {
                    echo json_encode(['success' => true, 'mensaje' => "$eliminados vale(s) eliminado(s) correctamente."]);
                } else {
                    echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar vales en la base de datos.']);
                }
            } else {
                echo json_encode(['success' => false, 'mensaje' => 'No se enviaron datos.']);
            }
        }
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
        break;
}
?>
