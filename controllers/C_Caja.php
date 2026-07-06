<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "mensaje" => "No autorizado."]);
    exit;
}

require_once dirname(__DIR__) . '/models/M_Caja.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$model = M_Caja::singleton();
$id_usuario = $_SESSION['id_usuario'];

switch ($action) {
    case 'estado':
        $caja = $model->obtenerCajaAbierta($id_usuario);
        if ($caja) {
            $ventas_acumuladas = $model->calcularVentasAcumuladas($id_usuario, $caja['fecha_apertura']);
            echo json_encode(["success" => true, "caja_abierta" => true, "caja" => $caja, "ventas_acumuladas" => $ventas_acumuladas]);
        } else {
            echo json_encode(["success" => true, "caja_abierta" => false]);
        }
        break;

    case 'abrir':
        $monto_apertura = isset($input['monto_apertura']) ? floatval($input['monto_apertura']) : 0.00;
        
        if ($monto_apertura < 0) {
            echo json_encode(["success" => false, "mensaje" => "Monto inválido."]);
            exit;
        }

        if ($model->abrirCaja($id_usuario, $monto_apertura)) {
            echo json_encode(["success" => true, "mensaje" => "Caja aperturada con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al abrir la caja. Es posible que ya tengas una caja abierta."]);
        }
        break;

    case 'cerrar':
        $id_caja = isset($input['id_caja']) ? intval($input['id_caja']) : 0;
        $monto_cierre = isset($input['monto_cierre']) ? floatval($input['monto_cierre']) : 0.00;
        $observaciones = isset($input['observaciones']) ? trim($input['observaciones']) : '';

        if ($id_caja <= 0 || $monto_cierre < 0) {
            echo json_encode(["success" => false, "mensaje" => "Datos inválidos."]);
            exit;
        }

        if ($model->cerrarCaja($id_caja, $id_usuario, $monto_cierre, $observaciones)) {
            echo json_encode(["success" => true, "mensaje" => "Caja cerrada con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al cerrar la caja."]);
        }
        break;

    case 'listar':
        if ($_SESSION['rol'] !== 'Administrador') {
            echo json_encode(["success" => false, "mensaje" => "No tienes permisos."]);
            exit;
        }
        $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
        echo json_encode($model->listarPorFecha($fecha));
        break;

    default:
        echo json_encode(["success" => false, "mensaje" => "Acción no válida."]);
        break;
}
?>
