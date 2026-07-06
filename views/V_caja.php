<?php
// Validar que exista una sesión activa
if (!isset($_SESSION['id_usuario'])) {
    echo "<h1>Acceso denegado</h1>";
    exit;
}

// Cargar el modelo de Caja para las consultas en el servidor
require_once dirname(__DIR__) . '/models/M_Caja.php';
$modelCaja = M_Caja::singleton();
$id_usuario = $_SESSION['id_usuario'];

// Consultar si el usuario tiene una caja abierta actualmente
$cajaAbierta = $modelCaja->obtenerCajaAbierta($id_usuario);
$ventasAcumuladas = 0;
if ($cajaAbierta) {
    // Si la caja está abierta, calcular las ventas registradas desde la fecha de apertura
    $ventasAcumuladas = $modelCaja->calcularVentasAcumuladas($id_usuario, $cajaAbierta['fecha_apertura']);
}
?>
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Mi Caja</h4>
            <p class="text-muted mb-0" style="font-size: 14px;">Apertura y cierre de sesión de ventas.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-8 col-lg-6 mx-auto">
            <?php if (!$cajaAbierta): ?>
                <!-- Vista para Aperturar Caja (Si no hay sesión de caja abierta) -->
                <div class="gp-card p-4 text-center shadow-sm">
                    <div class="mb-4">
                        <i class="bi bi-lock-fill text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 fw-bold">Caja Cerrada</h5>
                        <p class="text-muted text-sm">Debes abrir caja para poder registrar nuevas ventas en el sistema.</p>
                    </div>
                    
                    <form id="formAbrirCaja">
                        <div class="mb-4 text-start">
                            <label class="form-label fw-semibold text-muted">Monto inicial de apertura (S/)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0">S/</span>
                                <input type="number" class="form-control border-start-0 ps-1" id="monto_apertura" step="0.01" min="0" required placeholder="0.00" value="0.00" style="box-shadow: none;">
                            </div>
                        </div>
                        <button type="submit" class="gp-btn-primary w-100 py-3 rounded-3 fw-bold border-0" style="font-size: 1.05rem;">
                            <i class="bi bi-unlock-fill me-2"></i> Aperturar Caja
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Vista para Cerrar Caja (Si el usuario ya tiene sesión activa) -->
                <div class="gp-card p-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-unlock-fill fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">Caja Abierta</h5>
                                <small class="text-muted">Aperturada el <?php echo date('d/m/Y h:i A', strtotime($cajaAbierta['fecha_apertura'])); ?></small>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-semibold">Activa</span>
                    </div>

                    <!-- Resumen del Estado de la Caja en el Turno -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="bg-light p-3 rounded-3 text-center h-100 d-flex flex-column justify-content-center border" style="border-color: #f3f4f6 !important;">
                                <small class="d-block text-muted mb-1 fw-medium" style="font-size: 13px;">Monto Inicial</small>
                                <span class="fs-5 fw-bold text-dark">S/ <?php echo number_format($cajaAbierta['monto_apertura'], 2); ?></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-success bg-opacity-10 p-3 rounded-3 text-center h-100 d-flex flex-column justify-content-center border border-success border-opacity-25">
                                <small class="d-block text-success fw-semibold mb-1" style="font-size: 13px;">Ventas del Turno</small>
                                <span class="fs-5 fw-bold text-success">+ S/ <?php echo number_format($ventasAcumuladas, 2); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Monto Total que debería haber físicamente en caja -->
                    <div class="bg-dark p-3 rounded-3 text-center mb-4">
                        <small class="d-block text-white-50 fw-medium mb-1" style="font-size: 13px;">Total Esperado en Caja</small>
                        <h3 class="mb-0 text-white fw-bold">S/ <?php echo number_format($cajaAbierta['monto_apertura'] + $ventasAcumuladas, 2); ?></h3>
                        <input type="hidden" id="total_esperado" value="<?php echo ($cajaAbierta['monto_apertura'] + $ventasAcumuladas); ?>">
                    </div>

                    <form id="formCerrarCaja">
                        <input type="hidden" id="id_caja" value="<?php echo $cajaAbierta['id_caja']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark" style="font-size: 14px;">Monto real de cierre (S/) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0 text-muted">S/</span>
                                <input type="number" class="form-control border-start-0 ps-1 fw-bold" id="monto_cierre" step="0.01" min="0" required placeholder="0.00" style="color: #1f2937; box-shadow: none;">
                            </div>
                            <!-- Mensaje dinámico de cuadre/descuadre en base a la entrada de usuario -->
                            <div id="diferencia_hint" class="form-text mt-2 fw-medium" style="font-size: 13px;">Ingrese el dinero físico actual para calcular diferencia.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-muted" style="font-size: 14px;">Observaciones (Opcional)</label>
                            <textarea class="form-control text-sm" id="observaciones_cierre" rows="2" placeholder="Motivo de descuadre o nota adicional..." style="box-shadow: none; resize: none;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 py-3 rounded-3 fw-bold text-white d-flex align-items-center justify-content-center gap-2 border-0" style="font-size: 1.05rem;">
                            <i class="bi bi-lock-fill"></i> Efectuar Cierre de Caja
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Manejador de Apertura de Caja
    const formAbrir = document.getElementById('formAbrirCaja');
    if (formAbrir) {
        formAbrir.addEventListener('submit', async (e) => {
            e.preventDefault();
            const monto = document.getElementById('monto_apertura').value;
            
            // Confirmación visual con SweetAlert2
            Swal.fire({
                title: '¿Abrir caja?',
                text: `Se aperturará la caja con un fondo de S/ ${parseFloat(monto).toFixed(2)}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#15803d',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, abrir caja',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const res = await fetch('controllers/C_Caja.php?action=abrir', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ monto_apertura: monto })
                        });
                        const data = await res.json();
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Caja Abierta!',
                                text: data.mensaje,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire('Error', data.mensaje, 'error');
                        }
                    } catch (err) {
                        Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
                    }
                }
            });
        });
    }

    // 2. Manejador de Cierre de Caja
    const formCerrar = document.getElementById('formCerrarCaja');
    if (formCerrar) {
        const inputMontoCierre = document.getElementById('monto_cierre');
        const hint = document.getElementById('diferencia_hint');
        const totalEsperado = parseFloat(document.getElementById('total_esperado').value);

        // Evaluar dinámicamente y en tiempo real el cuadre de caja (sobrante o faltante)
        inputMontoCierre.addEventListener('input', () => {
            const cierre = parseFloat(inputMontoCierre.value);
            if (isNaN(cierre)) {
                hint.innerHTML = `Ingrese el dinero físico actual para calcular diferencia.`;
                return;
            }
            
            const dif = cierre - totalEsperado;
            if (dif > 0) {
                hint.innerHTML = `<span class="text-primary fw-bold"><i class="bi bi-arrow-up-circle-fill"></i> Sobrante en caja de S/ ${dif.toFixed(2)}</span>`;
            } else if (dif < 0) {
                hint.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-arrow-down-circle-fill"></i> Faltante en caja de S/ ${Math.abs(dif).toFixed(2)}</span>`;
            } else {
                hint.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> La caja cuadra perfectamente.</span>`;
            }
        });

        formCerrar.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('id_caja').value;
            const monto = document.getElementById('monto_cierre').value;
            const obs = document.getElementById('observaciones_cierre').value;

            Swal.fire({
                title: '¿Confirmar cierre de caja?',
                text: `Vas a declarar S/ ${parseFloat(monto).toFixed(2)}. Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, cerrar caja',
                cancelButtonText: 'Revisar',
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const res = await fetch('controllers/C_Caja.php?action=cerrar', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id_caja: id, monto_cierre: monto, observaciones: obs })
                        });
                        const data = await res.json();
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Caja Cerrada!',
                                text: data.mensaje,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire('Error', data.mensaje, 'error');
                        }
                    } catch (err) {
                        Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
                    }
                }
            });
        });
    }
});
</script>
