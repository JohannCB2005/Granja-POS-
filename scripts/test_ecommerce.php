<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/models/M_Ecommerce.php';

try {
    $db = Conexion::singleton()->getConexion();
    $ecommerce = M_Ecommerce::singleton();

    echo "=== TEST: E-Commerce (Fase 3) ===\n";
    
    // 1. Agregar un insumo de prueba con stock
    $db->exec("INSERT INTO insumos (id_categoria, id_unidad, nombre, precio_unitario, costo_produccion, stock_piezas, estado) VALUES (1, 1, 'Pavo Test Online', 15.00, 10.00, 50, 1)");
    $id_insumo_test = $db->lastInsertId();
    echo "Insumo creado con ID: $id_insumo_test (Stock inicial: 50)\n";

    // 2. Simular carrito
    $carrito = [
        [
            'id_insumo' => $id_insumo_test,
            'cantidad' => 2,
            'peso_neto' => 20,
            'precio' => 15.00,
            'subtotal' => 300.00
        ]
    ];
    $cliente = [
        'dni' => '12345678',
        'nombres' => 'Juan',
        'apellidos' => 'Perez',
        'telefono' => '999888777',
        'direccion' => ''
    ];

    // 3. Crear pedido
    $resPedido = $ecommerce->crearPedido($cliente, 'OPE-9999', 300.00, $carrito);
    if ($resPedido['ok']) {
        $id_pedido = $resPedido['id_pedido'];
        echo "Pedido creado exitosamente con ID: $id_pedido\n";
    } else {
        echo "Error al crear pedido: " . $resPedido['mensaje'] . "\n";
        exit;
    }

    // 4. Verificar stock deducido
    $stmtStock = $db->query("SELECT stock_piezas FROM insumos WHERE id_insumo = $id_insumo_test");
    $stock_actual = $stmtStock->fetchColumn();
    echo "Stock actual tras reserva: $stock_actual (Debería ser 48)\n";

    // 5. Listar pedidos
    $pedidos = $ecommerce->listarPedidos();
    echo "Hay " . count($pedidos) . " pedido(s) en la base de datos.\n";

    // 6. Aprobar pedido (asumimos id_usuario = 1)
    $resAprobar = $ecommerce->gestionarPedido($id_pedido, 'aprobar', 1);
    if ($resAprobar['ok']) {
        echo "Pedido aprobado exitosamente. Venta física creada con ID: " . $resAprobar['id_venta'] . "\n";
    } else {
        echo "Error al aprobar pedido: " . $resAprobar['mensaje'] . "\n";
        exit;
    }

    // 7. Limpiar
    $db->exec("DELETE FROM detalle_ventas WHERE id_venta = " . $resAprobar['id_venta']);
    $db->exec("DELETE FROM ventas WHERE id_venta = " . $resAprobar['id_venta']);
    $db->exec("DELETE FROM detalle_pedidos_online WHERE id_pedido = $id_pedido");
    $db->exec("DELETE FROM pedidos_online WHERE id_pedido = $id_pedido");
    $db->exec("DELETE FROM insumos WHERE id_insumo = $id_insumo_test");
    
    echo "Test completado y datos limpiados correctamente.\n";

} catch (Exception $e) {
    echo "Excepción capturada: " . $e->getMessage() . "\n";
}
?>
