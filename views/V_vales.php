<?php
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    echo "<h1>Acceso denegado</h1>";
    exit;
}

require_once 'models/M_TipoTrabajador.php';
require_once 'models/M_Dependencia.php';

$tiposTrabajador = M_TipoTrabajador::singleton()->listar();
$dependencias = M_Dependencia::singleton()->listar();
?>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Vales Navideños</h4>
            <p class="text-muted mb-0" style="font-size: 14px;">Emisión masiva y listado general | <span class="badge bg-dark" id="contadorVales">0 vales</span></p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-danger fw-bold px-3 d-none" id="btnEliminar">
                <i class="bi bi-trash-fill me-2"></i> Eliminar
            </button>
            <button class="btn btn-primary fw-semibold px-4 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalEmision">
                <i class="bi bi-magic me-1"></i> Emisión Masiva
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 d-flex flex-wrap gap-3">
            <div style="width: 200px;">
                <label class="form-label text-muted" style="font-size: 12px;">Campaña</label>
                <select class="form-select form-select-sm" id="filtroCampana">
                    <option value="">Todas</option>
                </select>
            </div>
            <div style="width: 150px;">
                <label class="form-label text-muted" style="font-size: 12px;">Estado</label>
                <select class="form-select form-select-sm" id="filtroEstado">
                    <option value="">Todos</option>
                    <option value="1">Pendiente</option>
                    <option value="0">Canjeado</option>
                </select>
            </div>
            <div class="flex-grow-1">
                <label class="form-label text-muted" style="font-size: 12px;">Buscar</label>
                <input type="text" id="filtroBuscar" class="form-control form-control-sm" placeholder="Buscar por DNI, Nombres, Dependencia o Código...">
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaVales">
                    <thead class="table-light text-muted" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <tr>
                            <th style="width: 40px;"><input class="form-check-input" type="checkbox" id="chkTodos"></th>
                            <th>Código</th>
                            <th>Trabajador</th>
                            <th>DNI</th>
                            <th>Dependencia</th>
                            <th>Monto (S/)</th>
                            <th>Emisión</th>
                            <th>Vencimiento</th>
                            <th>Campaña</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="listaVales" style="font-size: 14px;"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Emisión Masiva -->
<div class="modal fade" id="modalEmision" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">Nueva Emisión Masiva de Vales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <form id="formEmision">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre de Campaña</label>
                            <input type="text" class="form-control" id="emCampana" value="Navidad 2026" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Monto (S/)</label>
                            <input type="number" class="form-control" id="emMonto" value="100.00" step="0.10" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Vencimiento</label>
                            <input type="date" class="form-control" id="emVencimiento" value="2026-12-31" required>
                        </div>
                    </div>
                    
                    <h6 class="fw-bold mt-4 mb-3 text-secondary">Filtros de Trabajadores (Opcional)</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo de Trabajador</label>
                            <select class="form-select" id="emTipo">
                                <option value="">Todos los tipos</option>
                                <?php foreach($tiposTrabajador as $t): ?>
                                    <option value="<?= $t['id_tipo'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Dependencia</label>
                            <select class="form-select" id="emDependencia">
                                <option value="">Todas las dependencias</option>
                                <?php foreach($dependencias as $d): ?>
                                    <option value="<?= $d['id_dependencia'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-3" style="background-color: #f0f9ff; color: #0369a1;">
                        <i class="bi bi-info-circle-fill me-2"></i> Esta acción generará vales únicos para todos los trabajadores que cumplan los filtros seleccionados.
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4" id="btnProcesarEmision">
                            <i class="bi bi-magic me-1"></i> Generar Vales
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let allVales = [];

document.addEventListener('DOMContentLoaded', () => {
    cargarVales();

    document.getElementById('filtroCampana').addEventListener('change', renderTabla);
    document.getElementById('filtroEstado').addEventListener('change', renderTabla);
    document.getElementById('filtroBuscar').addEventListener('input', renderTabla);

    document.getElementById('formEmision').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const btn = document.getElementById('btnProcesarEmision');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generando...';

        const payload = {
            campana: document.getElementById('emCampana').value,
            monto: document.getElementById('emMonto').value,
            fecha_vencimiento: document.getElementById('emVencimiento').value,
            id_tipo_trabajador: document.getElementById('emTipo').value,
            id_dependencia: document.getElementById('emDependencia').value
        };

        try {
            const res = await fetch('./controllers/C_Vale.php?action=emision_masiva', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.ok) {
                Swal.fire({icon: 'success', title: '¡Éxito!', text: `Se generaron ${data.creados} vales correctamente.`});
                bootstrap.Modal.getInstance(document.getElementById('modalEmision')).hide();
                cargarVales();
            } else {
                Swal.fire({icon: 'error', text: data.mensaje});
            }
        } catch (error) {
            Swal.fire({icon: 'error', text: 'Error de conexión.'});
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-magic me-1"></i> Generar Vales';
        }
    });
});

async function cargarVales() {
    const tbody = document.getElementById('listaVales');
    tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Cargando...</td></tr>';
    
    try {
        const res = await fetch('./controllers/C_Vale.php?action=listar');
        const json = await res.json();
        
        if (json.data) {
            allVales = json.data;
            actualizarFiltros();
            renderTabla();
        } else {
            allVales = [];
            renderTabla();
        }
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger py-4">Error al cargar los datos</td></tr>';
    }
}

function actualizarFiltros() {
    const selectCampana = document.getElementById('filtroCampana');
    const actualVal = selectCampana.value;
    const campanas = [...new Set(allVales.map(v => v.campana))].filter(Boolean);
    
    let html = '<option value="">Todas</option>';
    campanas.forEach(c => {
        html += `<option value="${c}" ${actualVal === c ? 'selected' : ''}>${c}</option>`;
    });
    selectCampana.innerHTML = html;
}

function renderTabla() {
    const filterCampana = document.getElementById('filtroCampana').value;
    const filterEstado = document.getElementById('filtroEstado').value;
    const filterTxt = document.getElementById('filtroBuscar').value.toLowerCase();
    
    let list = allVales.filter(v => {
        if (filterCampana && v.campana !== filterCampana) return false;
        if (filterEstado !== '' && v.estado != filterEstado) return false;
        
        if (filterTxt) {
            const fullStr = `${v.codigo} ${v.numero_documento} ${v.nombres_razon_social} ${v.apellidos || ''} ${v.dependencia || ''}`.toLowerCase();
            if (!fullStr.includes(filterTxt)) return false;
        }
        return true;
    });

    const tbody = document.getElementById('listaVales');
    if (list.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">No se encontraron vales con estos filtros</td></tr>';
    } else {
        let html = '';
        list.forEach(v => {
            const nombre = `${v.apellidos || ''}, ${v.nombres_razon_social}`.replace(/^, /, '');
            const estadoHtml = v.estado == 1 
                ? `<span class="badge bg-success bg-opacity-10 text-success border border-success">Pendiente</span>` 
                : `<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">Canjeado</span>`;
            
            // Solo permitir eliminar vales Pendientes (estado = 1)
            const disableChk = v.estado == 0 ? 'disabled' : '';
                
            html += `<tr>
                <td><input class="form-check-input chk-vale" type="checkbox" value="${v.id_vale}" ${disableChk}></td>
                <td><span class="badge bg-dark fw-normal font-monospace">${v.codigo}</span></td>
                <td>${nombre}</td>
                <td>${v.numero_documento}</td>
                <td>${v.dependencia || ''}</td>
                <td>S/ ${parseFloat(v.monto).toFixed(2)}</td>
                <td>${v.fecha_emision}</td>
                <td>${v.fecha_vencimiento}</td>
                <td>${v.campana}</td>
                <td>${estadoHtml}</td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }
    
    document.getElementById('contadorVales').innerText = `${list.length} vale${list.length !== 1 ? 's' : ''}`;
    document.getElementById('chkTodos').checked = false;
    actualizarBotonEliminar();
}

function actualizarBotonEliminar() {
    const seleccionados = document.querySelectorAll('.chk-vale:checked').length;
    const btn = document.getElementById('btnEliminar');
    if (seleccionados > 0) {
        btn.classList.remove('d-none');
        btn.innerHTML = `<i class="bi bi-trash-fill me-2"></i> Eliminar (${seleccionados})`;
    } else {
        btn.classList.add('d-none');
    }
}

document.getElementById('chkTodos').addEventListener('change', function() {
    const checks = document.querySelectorAll('.chk-vale:not([disabled])');
    checks.forEach(c => c.checked = this.checked);
    actualizarBotonEliminar();
});

document.getElementById('listaVales').addEventListener('change', function(e) {
    if (e.target.classList.contains('chk-vale')) {
        actualizarBotonEliminar();
        const activables = document.querySelectorAll('.chk-vale:not([disabled])').length;
        const seleccionados = document.querySelectorAll('.chk-vale:checked').length;
        document.getElementById('chkTodos').checked = (activables > 0 && activables === seleccionados);
    }
});

document.getElementById('btnEliminar').addEventListener('click', async () => {
    const seleccionados = Array.from(document.querySelectorAll('.chk-vale:checked')).map(c => c.value);
    
    if (seleccionados.length === 0) return;

    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: `Se eliminarán permanentemente ${seleccionados.length} vale(s) de la base de datos. Los vales canjeados no pueden ser eliminados.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        const btn = document.getElementById('btnEliminar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Eliminando...';

        try {
            const res = await fetch('./controllers/C_Vale.php?action=eliminar_seleccionados', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: seleccionados })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({icon: 'success', title: '¡Eliminado!', text: data.mensaje, showConfirmButton: false, timer: 1500});
                cargarVales();
            } else {
                Swal.fire({icon: 'error', title: 'Error', text: data.mensaje});
            }
        } catch (e) {
            Swal.fire({icon: 'error', title: 'Error', text: 'Error de red.'});
        } finally {
            btn.disabled = false;
        }
    }
});
</script>
