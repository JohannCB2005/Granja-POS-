<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "mensaje" => "No autorizado."]);
    exit;
}

require_once dirname(__DIR__) . '/entities/Venta.php';
require_once dirname(__DIR__) . '/entities/DetalleVenta.php';
require_once dirname(__DIR__) . '/models/M_Venta.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$model = M_Venta::singleton();

switch ($action) {
    case 'listar':
        echo json_encode($model->listar());
        break;

    case 'crear':
        $id_usuario = $_SESSION['id_usuario'];
        $id_cliente = isset($input['id_cliente']) ? intval($input['id_cliente']) : 1; // Default to Cliente Varios (id_cliente = 1 in seed)
        $tipo_comprobante = isset($input['tipo_comprobante']) ? intval($input['tipo_comprobante']) : 1; // 1 = Boleta, 2 = Factura
        $total = isset($input['total']) ? floatval($input['total']) : 0.0;
        $cart = isset($input['cart']) ? $input['cart'] : []; // Array of items: {id_insumo, cantidad, precio, subtotal}

        if (empty($cart) || $total <= 0) {
            echo json_encode(["success" => false, "mensaje" => "El carrito está vacío o el total es cero."]);
            exit;
        }

        $venta = new Venta($id_usuario, $id_cliente, $tipo_comprobante, $total);
        
        foreach ($cart as $item) {
            $id_insumo = intval($item['id_insumo']);
            $cantidad = floatval($item['cantidad']);
            $precio = floatval($item['precio']);
            $subtotal = floatval($item['subtotal']);

            $detalle = new DetalleVenta(null, $id_insumo, $cantidad, $precio, $subtotal);
            $venta->agregarDetalle($detalle);
        }

        if ($model->registrar($venta)) {
            echo json_encode(["success" => true, "mensaje" => "Venta registrada con éxito."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al registrar la venta. Verifique el stock disponible de los insumos."]);
        }
        break;

    case 'anular':
        $id_venta = isset($input['id_venta']) ? intval($input['id_venta']) : 0;

        if ($id_venta <= 0) {
            echo json_encode(["success" => false, "mensaje" => "ID de venta inválido."]);
            exit;
        }

        if ($model->anular($id_venta)) {
            echo json_encode(["success" => true, "mensaje" => "Venta anulada con éxito. El stock ha sido retornado."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al anular la venta."]);
        }
        break;

    case 'detalles':
        $id_venta = isset($_GET['id_venta']) ? intval($_GET['id_venta']) : (isset($input['id_venta']) ? intval($input['id_venta']) : 0);

        if ($id_venta <= 0) {
            echo json_encode(["success" => false, "mensaje" => "ID de venta inválido."]);
            exit;
        }

        echo json_encode($model->obtenerDetallesPorVenta($id_venta));
        break;

    default:
        echo json_encode(["success" => false, "mensaje" => "Acción no válida."]);
        break;
}
?>
