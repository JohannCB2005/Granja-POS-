<?php
// Restricción de seguridad: El acceso a reportes ejecutivos está limitado al rol de Administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    echo "<h1>Acceso denegado</h1>";
    exit;
}

// Cargar la conexión activa a la Base de Datos
require_once dirname(__DIR__) . '/config/conexion.php';
$dbh = Conexion::singleton()->getConexion();

// 1. Obtener la facturación histórica total (Ventas activas)
$stmt = $dbh->query("SELECT COALESCE(SUM(total), 0) as total FROM ventas WHERE estado = 1");
$cumulativeSales = floatval($stmt->fetch()['total']);

// 2. Contar la cantidad de insumos registrados y activos en catálogo
$stmt = $dbh->query("SELECT COUNT(*) as count FROM insumos WHERE estado = 1");
$activeProductsCount = intval($stmt->fetch()['count']);

// 3. Contar la cantidad total de clientes registrados
$stmt = $dbh->query("SELECT COUNT(*) as count FROM clientes");
$activeClientsCount = intval($stmt->fetch()['count']);

// 4. Consultar los 5 insumos con mayor demanda en base a la suma de piezas vendidas
$stmt = $dbh->query("SELECT i.nombre, SUM(dv.piezas) as vendidos, i.precio_unitario, um.abreviatura as unidad
                     FROM detalle_ventas dv
                     INNER JOIN insumos i ON dv.id_insumo = i.id_insumo
                     INNER JOIN unidades_medida um ON i.id_unidad = um.id_unidad
                     INNER JOIN ventas v ON dv.id_venta = v.id_venta
                     WHERE v.estado = 1
                     GROUP BY dv.id_insumo
                     ORDER BY vendidos DESC
                     LIMIT 5");
$topProducts = $stmt->fetchAll();

// 5. Configurar el gráfico histórico de los últimos 7 días de operación
$ventasPorDia = [];
$diasSemanaMap = [
    'Sunday' => 'Dom',
    'Monday' => 'Lun',
    'Tuesday' => 'Mar',
    'Wednesday' => 'Mié',
    'Thursday' => 'Jue',
    'Friday' => 'Vie',
    'Saturday' => 'Sáb'
];

// Rellenar arreglo con 0 ventas por defecto para evitar saltos en la línea de tiempo
for ($i = 6; $i >= 0; $i--) {
    $timestamp = strtotime("-$i days");
    $fechaKey = date('Y-m-d', $timestamp);
    $diaIngles = date('l', $timestamp);
    $diaSemana = isset($diasSemanaMap[$diaIngles]) ? $diasSemanaMap[$diaIngles] : substr($diaIngles, 0, 3);
    
    $ventasPorDia[$fechaKey] = [
        "dia" => $diaSemana,
        "fecha" => $fechaKey,
        "ventas" => 0.0
    ];
}

// Consultar ventas reales de los últimos 7 días
$stmt = $dbh->query("SELECT DATE(fecha) as fecha_dia, SUM(total) as total_dia 
                     FROM ventas 
                     WHERE estado = 1 AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                     GROUP BY DATE(fecha)");
$dbVentas = $stmt->fetchAll();

// Sobrescribir ventas del día recuperadas de la base de datos
foreach ($dbVentas as $row) {
    $f = $row['fecha_dia'];
    if (isset($ventasPorDia[$f])) {
        $ventasPorDia[$f]['ventas'] = floatval($row['total_dia']);
    }
}
$chartData = array_values($ventasPorDia);
?>

<div class="container-fluid px-0">
    <!-- Encabezado de Página -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Reportes de Rendimiento</h4>
            <p class="text-muted mb-0" style="font-size: 14px;">Analiza los resultados de ventas y los insumos más demandados.</p>
        </div>
    </div>

    <!-- Rejilla de Estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="gp-card">
                <span class="text-muted fw-medium d-block mb-1" style="font-size: 13px;">Ventas Acumuladas</span>
                <h3 class="mb-1 fw-bold text-dark">S/ <?php echo number_format($cumulativeSales, 2); ?></h3>
                <small class="text-muted">Total facturado históricamente</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="gp-card">
                <span class="text-muted fw-medium d-block mb-1" style="font-size: 13px;">Catálogo Insumos</span>
                <h3 class="mb-1 fw-bold text-dark"><?php echo $activeProductsCount; ?></h3>
                <small class="text-muted">Insumos activos en inventario</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="gp-card">
                <span class="text-muted fw-medium d-block mb-1" style="font-size: 13px;">Clientes Registrados</span>
                <h3 class="mb-1 fw-bold text-dark"><?php echo $activeClientsCount; ?></h3>
                <small class="text-muted">Compradores fidelizados</small>
            </div>
        </div>
    </div>

    <!-- Fila de Gráficos e Indicadores de Demanda -->
    <div class="row g-4">
        <!-- Tendencia de Ingresos -->
        <div class="col-12 col-lg-7">
            <div class="gp-card">
                <h6 class="mb-4 fw-bold text-dark">Evolución de Ingresos (S/)</h6>
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="reportsTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Insumos Más Vendidos (Barras de progreso relativas) -->
        <div class="col-12 col-lg-5">
            <div class="gp-card h-100 d-flex flex-column">
                <h6 class="mb-4 fw-bold text-dark">Top 5 Insumos Más Vendidos</h6>
                <div class="flex-grow-1">
                    <?php if (empty($topProducts)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam-fill fs-2 mb-2 d-block"></i>
                            <p style="font-size: 13px;">No hay ventas registradas aún.</p>
                        </div>
                    <?php else: ?>
                        <?php 
                        // Obtener el mayor número de ventas para establecer el 100% de la barra relativa
                        $maxSold = floatval($topProducts[0]['vendidos']);
                        foreach ($topProducts as $prod): 
                            $sold = floatval($prod['vendidos']);
                            $percentage = $maxSold > 0 ? ($sold / $maxSold) * 100 : 0;
                        ?>
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-1.5" style="font-size: 13px;">
                                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($prod['nombre']); ?></span>
                                    <span class="text-muted fw-bold"><?php echo number_format($sold, 1); ?> <?php echo htmlspecialchars($prod['unidad']); ?></span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 4px; background-color: #f3f4f6;">
                                    <div class="progress-bar bg-success bg-gradient" 
                                         role="progressbar" 
                                         style="width: <?php echo $percentage; ?>%; border-radius: 4px;" 
                                         aria-valuenow="<?php echo $percentage; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lógica para renderizar Chart.js con degradado -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const rawChartData = <?php echo json_encode($chartData); ?>;
        const labels = rawChartData.map(d => d.dia);
        const dataValues = rawChartData.map(d => d.ventas);
        
        const ctx = document.getElementById('reportsTrendChart').getContext('2d');
        
        // Configurar un degradado verde translúcido debajo de la curva del gráfico
        const gradient = ctx.createLinearGradient(0, 0, 0, 320);
        gradient.addColorStop(0, 'rgba(21, 128, 61, 0.25)');
        gradient.addColorStop(1, 'rgba(21, 128, 61, 0.01)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ventas Diarias (S/)',
                    data: dataValues,
                    borderColor: '#15803d',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#15803d',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1.5,
                    pointRadius: 4.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' S/ ' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Plus Jakarta Sans',
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: '#f3f4f6'
                        },
                        ticks: {
                            font: {
                                family: 'Plus Jakarta Sans',
                                size: 11
                            },
                            callback: function(value) {
                                return 'S/ ' + value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
