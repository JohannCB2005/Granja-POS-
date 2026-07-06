<?php
// Restricción de acceso: Solo administradores logueados pueden ver el control global de cajas
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    echo "<h1>Acceso denegado</h1>";
    exit;
}

// Cargar el modelo de Caja para listar reportes
require_once dirname(__DIR__) . '/models/M_Caja.php';
$model = M_Caja::singleton();

// Capturar la fecha de filtro elegida (por defecto la fecha de hoy del servidor)
$fechaFiltro = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$cajas = $model->listarPorFecha($fechaFiltro);
?>
<div class="container-fluid px-0">
    <!-- Encabezado y Formulario de Búsqueda por Fecha -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Control de Cajas</h4>
            <p class="text-muted mb-0" style="font-size: 14px;">Supervisa las aperturas y cierres de todos los usuarios.</p>
        </div>
        <form class="d-flex gap-2" method="GET" action="index.php">
            <input type="hidden" name="modulo" value="control-cajas">
            <input type="date" name="fecha" class="form-control" value="<?php echo htmlspecialchars($fechaFiltro); ?>" max="<?php echo date('Y-m-d'); ?>">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <!-- Panel de Resultados del Turno -->
    <div class="gp-card">
        <div class="table-responsive">
            <table class="table align-middle text-sm" style="font-size: 14px;">
                <thead>
                    <tr class="text-muted border-bottom" style="font-size: 13px;">
                        <th scope="col" class="pb-3">Usuario</th>
                        <th scope="col" class="pb-3">Horario</th>
                        <th scope="col" class="pb-3 text-end">M. Inicial</th>
                        <th scope="col" class="pb-3 text-end">Ventas Totales</th>
                        <th scope="col" class="pb-3 text-end">M. Cierre</th>
                        <th scope="col" class="pb-3 text-end">Diferencia</th>
                        <th scope="col" class="pb-3 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cajas)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-safe-fill fs-2 mb-2 d-block"></i>
                                No hay registros de cajas en esta fecha.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        // Variables acumuladoras para mostrar totales consolidados al pie de la tabla
                        $sum_apertura = 0;
                        $sum_ventas = 0;
                        $sum_cierre = 0;
                        $sum_dif = 0;
                        foreach ($cajas as $caja): 
                            $sum_apertura += $caja['monto_apertura'];
                            
                            // Si la caja sigue activa, calcular en vivo el total de ventas acumuladas
                            $ventas = $caja['total_ventas'] !== null ? $caja['total_ventas'] : $model->calcularVentasAcumuladas($caja['id_usuario'], $caja['fecha_apertura']);
                            $sum_ventas += $ventas;
                            if ($caja['monto_cierre'] !== null) $sum_cierre += $caja['monto_cierre'];
                            if ($caja['diferencia'] !== null) $sum_dif += $caja['diferencia'];
                        ?>
                            <tr class="border-bottom">
                                <td class="py-3">
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($caja['nombre_completo']); ?></span>
                                        <span class="text-muted font-mono" style="font-size: 11px;">@<?php echo htmlspecialchars($caja['username']); ?></span>
                                    </div>
                                </td>
                                <td class="text-muted">
                                    A: <?php echo date('h:i A', strtotime($caja['fecha_apertura'])); ?> <br>
                                    C: <?php echo $caja['fecha_cierre'] ? date('h:i A', strtotime($caja['fecha_cierre'])) : '-'; ?>
                                </td>
                                <td class="text-end fw-medium">S/ <?php echo number_format($caja['monto_apertura'], 2); ?></td>
                                <td class="text-end text-success fw-bold">+ S/ <?php echo number_format($ventas, 2); ?> <br><small class="text-muted">(<?php echo $caja['num_ventas'] !== null ? $caja['num_ventas'] : '-'; ?> op.)</small></td>
                                <td class="text-end fw-bold">
                                    <?php echo $caja['monto_cierre'] !== null ? 'S/ ' . number_format($caja['monto_cierre'], 2) : '-'; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($caja['estado'] == 1): ?>
                                        <span class="text-muted fst-italic">En curso</span>
                                    <?php else: ?>
                                        <!-- Mostrar diferencia con colores indicativos (Verde/Azul para sobrantes, Rojo para faltantes) -->
                                        <?php if ($caja['diferencia'] > 0): ?>
                                            <span class="text-primary fw-bold">+ S/ <?php echo number_format($caja['diferencia'], 2); ?></span>
                                        <?php elseif ($caja['diferencia'] < 0): ?>
                                            <span class="text-danger fw-bold">- S/ <?php echo number_format(abs($caja['diferencia']), 2); ?></span>
                                        <?php else: ?>
                                            <span class="text-success fw-bold">S/ 0.00</span>
                                        <?php endif; ?>
                                        <?php if ($caja['observaciones']): ?>
                                            <i class="bi bi-info-circle ms-1 text-muted" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($caja['observaciones']); ?>"></i>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($caja['estado'] == 1): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill">Abierta</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 rounded-pill">Cerrada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- Fila de Totales del Día Consolidados -->
                        <tr class="bg-light">
                            <td colspan="2" class="text-end fw-bold">TOTALES DEL DÍA:</td>
                            <td class="text-end fw-bold">S/ <?php echo number_format($sum_apertura, 2); ?></td>
                            <td class="text-end fw-bold text-success">S/ <?php echo number_format($sum_ventas, 2); ?></td>
                            <td class="text-end fw-bold">S/ <?php echo number_format($sum_cierre, 2); ?></td>
                            <td class="text-end fw-bold <?php echo $sum_dif > 0 ? 'text-primary' : ($sum_dif < 0 ? 'text-danger' : 'text-success'); ?>">
                                S/ <?php echo number_format($sum_dif, 2); ?>
                            </td>
                            <td></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inicializar tooltips para ver observaciones de descuadres de caja al pasar el mouse
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>
