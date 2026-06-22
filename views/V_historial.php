<?php
if (!isset($_SESSION['id_usuario'])) {
    echo "<h1>Acceso denegado</h1>";
    exit;
}

require_once dirname(__DIR__) . '/models/M_Venta.php';
$modelVenta = M_Venta::singleton();
$ventas = $modelVenta->listar();
?>

<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Historial de Ventas</h4>
            <p class="text-muted mb-0" style="font-size: 14px;">Consulta, anula o visualiza los comprobantes de venta emitidos.</p>
        </div>
    </div>

    <!-- History Card -->
    <div class="gp-card">
        <!-- Search and Filter bar -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted" id="search-addon">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0 text-sm" id="searchVentas" placeholder="Buscar por código o cliente..." aria-label="Buscar" aria-describedby="search-addon" style="box-shadow: none; font-size: 14px;">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select class="form-select text-sm" id="statusFilter" style="font-size: 14px;">
                    <option value="all">Todos los estados</option>
                    <option value="Completada">Completadas</option>
                    <option value="Anulada">Anuladas</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table align-middle text-sm" id="tableVentas" style="font-size: 14px;">
                <thead>
                    <tr class="text-muted border-bottom" style="font-size: 13px;">
                        <th scope="col" class="pb-3">Código</th>
                        <th scope="col" class="pb-3">Fecha / Hora</th>
                        <th scope="col" class="pb-3">Cliente</th>
                        <th scope="col" class="pb-3">Vendedor</th>
                        <th scope="col" class="pb-3 text-end">Total</th>
                        <th scope="col" class="pb-3">Tipo</th>
                        <th scope="col" class="pb-3">Estado</th>
                        <th scope="col" class="pb-3 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ventas)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt-cutoff fs-2 mb-2 d-block"></i>
                                No se encontraron ventas registradas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ventas as $v): ?>
                            <tr class="border-bottom venta-row" 
                                data-cliente="<?php echo htmlspecialchars(strtolower($v['cliente'])); ?>"
                                data-codigo="v-<?php echo str_pad($v['id_venta'], 6, '0', STR_PAD_LEFT); ?>"
                                data-estado="<?php echo $v['estado'] == 1 ? 'Completada' : 'Anulada'; ?>">
                                <td class="py-3 font-mono fw-bold text-dark">
                                    V-<?php echo str_pad($v['id_venta'], 6, '0', STR_PAD_LEFT); ?>
                                </td>
                                <td class="text-muted">
                                    <?php echo date('d/m/Y H:i', strtotime($v['fecha'])); ?>
                                </td>
                                <td class="fw-semibold text-dark">
                                    <?php echo htmlspecialchars($v['cliente']); ?>
                                </td>
                                <td class="text-muted">
                                    <?php echo htmlspecialchars($v['vendedor']); ?>
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    S/ <?php echo number_format($v['total'], 2); ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1.5 fw-semibold" style="font-size: 11px;">
                                        <?php 
                                            if ($v['tipo_comprobante'] == 1) echo 'Boleta';
                                            elseif ($v['tipo_comprobante'] == 2) echo 'Factura';
                                            else echo 'Nota de Venta';
                                         ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $v['estado'] == 1 ? 'gp-badge-success' : 'gp-badge-danger'; ?>">
                                        <?php echo $v['estado'] == 1 ? 'Completada' : 'Anulada'; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-link text-muted p-1 hover-text-primary view-details-btn" 
                                                data-id="<?php echo $v['id_venta']; ?>"
                                                data-codigo="V-<?php echo str_pad($v['id_venta'], 6, '0', STR_PAD_LEFT); ?>"
                                                data-cliente="<?php echo htmlspecialchars($v['cliente']); ?>"
                                                data-fecha="<?php echo date('d/m/Y H:i', strtotime($v['fecha'])); ?>"
                                                data-total="<?php echo number_format($v['total'], 2); ?>"
                                                data-tipo="<?php echo $v['tipo_comprobante'] == 1 ? 'Boleta' : ($v['tipo_comprobante'] == 2 ? 'Factura' : 'Nota de Venta'); ?>"
                                                data-estado="<?php echo $v['estado'] == 1 ? 'Completada' : 'Anulada'; ?>"
                                                title="Ver Detalle">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        <?php if ($v['estado'] == 1): ?>
                                            <button class="btn btn-link text-muted p-1 hover-text-danger cancel-sale-btn" 
                                                    data-id="<?php echo $v['id_venta']; ?>"
                                                    title="Anular Venta">
                                                <i class="bi bi-x-circle-fill"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Detalle de Venta (Comprobante) -->
<div class="modal fade" id="detalleVentaModal" tabindex="-1" aria-labelledby="detalleVentaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-success text-white border-0 py-3" style="border-radius: 15px 15px 0 0;">
                <h6 class="modal-title fw-bold" id="detalleVentaModalLabel">Comprobante de Pago</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="box-shadow: none;"></button>
            </div>
            <div class="modal-body p-4" id="ticketContent">
                <!-- Meta Details -->
                <div class="text-center mb-4 border-bottom pb-3">
                    <h5 class="fw-bold text-dark mb-1">Granja UNP</h5>
                    <p class="text-muted mb-2" style="font-size: 12px;">Gestión de Insumos y Ventas</p>
                    <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-1.5 fw-bold" id="ticketCodigo" style="font-size: 13px;">
                        V-000000
                    </div>
                </div>

                <div class="row g-2 mb-4" style="font-size: 12.5px;">
                    <div class="col-6">
                        <span class="text-muted d-block">Fecha / Hora</span>
                        <strong class="text-dark" id="ticketFecha">--/--/---- --:--</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Tipo Comprobante</span>
                        <strong class="text-dark" id="ticketTipo">Boleta</strong>
                    </div>
                    <div class="col-12 mt-2">
                        <span class="text-muted d-block">Cliente</span>
                        <strong class="text-dark" id="ticketCliente">Público General</strong>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="border-top pt-3">
                    <h6 class="fw-bold text-dark mb-3" style="font-size: 13px;">Detalle de Insumos</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0" style="font-size: 12.5px;">
                            <thead>
                                <tr class="text-muted border-bottom" style="font-size: 11px;">
                                    <th scope="col" class="ps-0">Descripción</th>
                                    <th scope="col" class="text-center">Cant.</th>
                                    <th scope="col" class="text-end">P. Unit</th>
                                    <th scope="col" class="text-end pe-0">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="ticketItems">
                                <!-- Dynamic rows loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Totals Section -->
                <div class="border-top mt-3 pt-3 bg-light p-3 rounded-3" style="font-size: 13px;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold text-dark" id="ticketSubtotal">S/ 0.00</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted">IGV (18%)</span>
                        <span class="fw-semibold text-dark" id="ticketIgv">S/ 0.00</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                        <strong class="text-dark" style="font-size: 14px;">Total General</strong>
                        <strong class="text-success" style="font-size: 15px;" id="ticketTotal">S/ 0.00</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light fw-semibold w-100" data-bs-dismiss="modal" style="border-radius: 8px;">Cerrar Comprobante</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for History Filtering and AJAX Details -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('searchVentas');
        const statusFilter = document.getElementById('statusFilter');
        const rows = document.querySelectorAll('.venta-row');

        // Real-time Filters
        function filterVentas() {
            const query = searchInput.value.toLowerCase().trim();
            const status = statusFilter.value;

            rows.forEach(row => {
                const textMatch = row.innerText.toLowerCase().includes(query);
                const statusMatch = (status === 'all' || row.dataset.estado === status);

                if (textMatch && statusMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        if (searchInput) searchInput.addEventListener('input', filterVentas);
        if (statusFilter) statusFilter.addEventListener('change', filterVentas);

        // View Details Modal
        const detailsModal = new bootstrap.Modal(document.getElementById('detalleVentaModal'));
        document.querySelectorAll('.view-details-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;
                document.getElementById('ticketCodigo').innerText = btn.dataset.codigo;
                document.getElementById('ticketCliente').innerText = btn.dataset.cliente;
                document.getElementById('ticketFecha').innerText = btn.dataset.fecha;
                document.getElementById('ticketTipo').innerText = btn.dataset.tipo;
                
                const totalFloat = parseFloat(btn.dataset.total.replace(/,/g, ''));
                const subtotalFloat = totalFloat / 1.18;
                const igvFloat = totalFloat - subtotalFloat;

                document.getElementById('ticketSubtotal').innerText = 'S/ ' + subtotalFloat.toFixed(2);
                document.getElementById('ticketIgv').innerText = 'S/ ' + igvFloat.toFixed(2);
                document.getElementById('ticketTotal').innerText = 'S/ ' + totalFloat.toFixed(2);

                const itemsBody = document.getElementById('ticketItems');
                itemsBody.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm text-success" role="status"></div> Cargando detalle...</td></tr>';

                detailsModal.show();

                try {
                    const response = await fetch(`./controllers/C_Venta.php?action=detalles&id_venta=${id}`);
                    const details = await response.json();
                    
                    if (details && details.length > 0) {
                        let rowsHtml = '';
                        details.forEach(item => {
                            const cant = parseFloat(item.cantidad);
                            const prec = parseFloat(item.precio_venta);
                            const subt = parseFloat(item.subtotal);
                            rowsHtml += `
                                <tr>
                                    <td class="ps-0 text-dark fw-medium">${item.insumo_nombre}</td>
                                    <td class="text-center text-muted">${cant} ${item.abreviatura}</td>
                                    <td class="text-end text-muted">S/ ${prec.toFixed(2)}</td>
                                    <td class="text-end pe-0 fw-semibold text-dark">S/ ${subt.toFixed(2)}</td>
                                </tr>
                            `;
                        });
                        itemsBody.innerHTML = rowsHtml;
                    } else {
                        itemsBody.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-danger">No se pudieron cargar los detalles.</td></tr>';
                    }
                } catch (error) {
                    itemsBody.innerHTML = '<tr><td colspan="4" class="text-center py-3 text-danger">Error de conexión.</td></tr>';
                }
            });
        });

        // Cancel / Anular Sale
        document.querySelectorAll('.cancel-sale-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                Swal.fire({
                    title: '¿Anular esta venta?',
                    text: 'Esta acción devolverá los insumos vendidos al stock del inventario.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, anular venta',
                    cancelButtonText: 'Cancelar'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const response = await fetch('./controllers/C_Venta.php?action=anular', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id_venta: id })
                            });
                            const data = await response.json();

                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Venta Anulada!',
                                    text: data.mensaje,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => window.location.reload());
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.mensaje,
                                    confirmButtonColor: '#15803d'
                                });
                            }
                        } catch (err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de red',
                                text: 'No se pudo contactar al servidor.'
                            });
                        }
                    }
                });
            });
        });
    });
</script>
