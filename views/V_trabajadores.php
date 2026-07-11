<?php
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    echo "<h1>Acceso denegado</h1>";
    exit;
}
?>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Gestor de Trabajadores</h4>
            <p class="text-muted mb-0" style="font-size: 14px;">Directorio del personal UNP (Docentes y CAS) | <span class="badge bg-dark" id="contadorTrabajadores">0 registros</span></p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-danger fw-bold px-3 d-none" id="btnEliminar">
                <i class="bi bi-trash-fill me-2"></i> Eliminar
            </button>
            <button class="btn btn-primary fw-bold px-4" data-bs-toggle="modal" data-bs-target="#modalImportar">
                <i class="bi bi-cloud-arrow-up-fill me-2"></i> Importar XLSX
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 d-flex gap-3">
            <div style="width: 250px;">
                <label class="form-label text-muted" style="font-size: 12px;">Tipo de Trabajador</label>
                <select class="form-select form-select-sm" id="filtroTipo">
                    <option value="">Todos</option>
                    <option value="Docente/Nombrado">Docentes / Nombrados</option>
                    <option value="Personal CAS">Personal CAS</option>
                </select>
            </div>
            <div class="flex-grow-1">
                <label class="form-label text-muted" style="font-size: 12px;">Buscar</label>
                <input type="text" id="filtroBuscar" class="form-control form-control-sm" placeholder="Buscar por DNI, Nombres o Dependencia...">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaTrabajadores" style="font-size: 14px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;"><input class="form-check-input" type="checkbox" id="chkTodos"></th>
                            <th>DNI</th>
                            <th>Nombres y Apellidos</th>
                            <th>Tipo</th>
                            <th>Dependencia</th>
                        </tr>
                    </thead>
                    <tbody id="listaTrabajadores">
                        <tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Importar -->
<div class="modal fade" id="modalImportar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Importar Trabajadores</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formImportar">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de Trabajador</label>
                            <select class="form-select" id="tipoImportacion" required>
                                <option value="">Seleccione el tipo...</option>
                                <option value="docente">Docente / Nombrado</option>
                                <option value="cas">Personal CAS</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Archivo Excel (.xlsx)</label>
                            <input class="form-control" type="file" id="archivoExcel" accept=".xlsx" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold mb-3" id="btnSubir">
                        <i class="bi bi-eye-fill me-2"></i> Subir y Previsualizar
                    </button>
                </form>

                <div id="previewCard" class="d-none">
                    <h6 class="fw-bold mb-3 d-flex justify-content-between border-top pt-3">
                        Vista Previa (Primeros 10 registros)
                        <span class="badge bg-info text-dark" id="previewCount">0 Total</span>
                    </h6>
                    <div class="table-responsive bg-light rounded p-2 mb-3">
                        <table class="table table-sm table-striped mb-0" style="font-size: 13px;">
                            <thead>
                                <tr>
                                    <th>DNI</th>
                                    <th>Apellidos</th>
                                    <th>Nombres</th>
                                    <th>Dependencia</th>
                                </tr>
                            </thead>
                            <tbody id="previewBody"></tbody>
                        </table>
                    </div>
                    <button class="btn btn-success w-100 fw-bold py-2" id="btnConfirmar">
                        <i class="bi bi-check-circle me-2"></i> Confirmar e Importar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let allTrabajadores = [];

document.addEventListener('DOMContentLoaded', () => {
    cargarTrabajadores();

    // Filtros
    document.getElementById('filtroTipo').addEventListener('change', renderTabla);
    document.getElementById('filtroBuscar').addEventListener('input', renderTabla);

    // Lógica del modal de importación
    const form = document.getElementById('formImportar');
    const previewCard = document.getElementById('previewCard');
    const btnSubir = document.getElementById('btnSubir');
    const btnConfirmar = document.getElementById('btnConfirmar');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const fileInput = document.getElementById('archivoExcel');
        const tipoInput = document.getElementById('tipoImportacion');
        
        if (!fileInput.files[0] || !tipoInput.value) return;

        const formData = new FormData();
        formData.append('archivo_excel', fileInput.files[0]);
        formData.append('tipo_archivo', tipoInput.value);

        btnSubir.disabled = true;
        btnSubir.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Leyendo...';

        try {
            const res = await fetch('./controllers/C_Importar.php?action=previsualizar', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (data.success) {
                let html = '';
                data.preview.forEach(r => {
                    html += `<tr>
                        <td>${r.dni}</td>
                        <td>${r.paterno} ${r.materno}</td>
                        <td>${r.nombres}</td>
                        <td>${r.dependencia || 'General'}</td>
                    </tr>`;
                });
                document.getElementById('previewBody').innerHTML = html;
                document.getElementById('previewCount').innerText = `${data.total} Registros a importar`;
                previewCard.classList.remove('d-none');
            } else {
                Swal.fire({icon: 'error', text: data.mensaje});
                previewCard.classList.add('d-none');
            }
        } catch (e) {
            Swal.fire({icon: 'error', text: 'Error procesando el archivo.'});
        } finally {
            btnSubir.disabled = false;
            btnSubir.innerHTML = '<i class="bi bi-eye-fill me-2"></i> Subir y Previsualizar';
        }
    });

    btnConfirmar.addEventListener('click', async () => {
        btnConfirmar.disabled = true;
        btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Importando...';

        const fileInput = document.getElementById('archivoExcel');
        const tipoInput = document.getElementById('tipoImportacion');
        
        const formData = new FormData();
        formData.append('archivo_excel', fileInput.files[0]);
        formData.append('tipo_archivo', tipoInput.value);

        try {
            const res = await fetch('./controllers/C_Importar.php?action=importar', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (data.success) {
                Swal.fire({icon: 'success', title: 'Éxito', text: data.mensaje}).then(() => {
                    form.reset();
                    previewCard.classList.add('d-none');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalImportar'));
                    modal.hide();
                    cargarTrabajadores();
                });
            } else {
                Swal.fire({icon: 'error', text: data.mensaje});
            }
        } catch (e) {
            Swal.fire({icon: 'error', text: 'Error en la conexión.'});
        } finally {
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = '<i class="bi bi-check-circle me-2"></i> Confirmar e Importar';
        }
    });
});

async function cargarTrabajadores() {
    try {
        const res = await fetch('./controllers/C_Trabajador.php?action=listar');
        const data = await res.json();
        if (data.success) {
            allTrabajadores = data.data;
            renderTabla();
        }
    } catch (e) {
        console.error('Error loading workers');
    }
}

function renderTabla() {
    const filterTipo = document.getElementById('filtroTipo').value;
    const filterTxt = document.getElementById('filtroBuscar').value.toLowerCase();
    
    let html = '';
    
    let list = allTrabajadores.filter(w => {
        let matchTipo = true;
        if (filterTipo && w.tipo_trabajador !== filterTipo) matchTipo = false;
        
        let matchTxt = true;
        if (filterTxt) {
            const fullStr = `${w.numero_documento} ${w.nombres_razon_social} ${w.apellidos} ${w.dependencia}`.toLowerCase();
            if (!fullStr.includes(filterTxt)) matchTxt = false;
        }
        
        return matchTipo && matchTxt;
    });

    if (list.length === 0) {
        html = `<tr><td colspan="5" class="text-center text-muted py-4">No se encontraron trabajadores</td></tr>`;
    } else {
        list.forEach(w => {
            html += `<tr>
                <td><input class="form-check-input chk-trabajador" type="checkbox" value="${w.id_trabajador}"></td>
                <td class="fw-bold">${w.numero_documento}</td>
                <td>
                    <div class="text-dark fw-semibold">${w.apellidos || ''}, ${w.nombres_razon_social}</div>
                </td>
                <td><span class="badge bg-secondary">${w.tipo_trabajador || 'N/A'}</span></td>
                <td><span class="badge bg-light text-dark border">${w.dependencia || 'General'}</span></td>
            </tr>`;
        });
    }

    document.getElementById('listaTrabajadores').innerHTML = html;
    document.getElementById('contadorTrabajadores').innerText = `${list.length} registro${list.length !== 1 ? 's' : ''}`;
    actualizarBotonEliminar();
    document.getElementById('chkTodos').checked = false;
}

function actualizarBotonEliminar() {
    const seleccionados = document.querySelectorAll('.chk-trabajador:checked').length;
    const btn = document.getElementById('btnEliminar');
    if (seleccionados > 0) {
        btn.classList.remove('d-none');
        btn.innerHTML = `<i class="bi bi-trash-fill me-2"></i> Eliminar (${seleccionados})`;
    } else {
        btn.classList.add('d-none');
    }
}

document.getElementById('chkTodos').addEventListener('change', function() {
    const checks = document.querySelectorAll('.chk-trabajador');
    checks.forEach(c => c.checked = this.checked);
    actualizarBotonEliminar();
});

document.getElementById('listaTrabajadores').addEventListener('change', function(e) {
    if (e.target.classList.contains('chk-trabajador')) {
        actualizarBotonEliminar();
        const total = document.querySelectorAll('.chk-trabajador').length;
        const seleccionados = document.querySelectorAll('.chk-trabajador:checked').length;
        document.getElementById('chkTodos').checked = (total > 0 && total === seleccionados);
    }
});

document.getElementById('btnEliminar').addEventListener('click', async () => {
    const seleccionados = Array.from(document.querySelectorAll('.chk-trabajador:checked')).map(c => c.value);
    
    if (seleccionados.length === 0) return;

    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: `Se eliminarán ${seleccionados.length} trabajador(es) de la lista.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            const res = await fetch('./controllers/C_Trabajador.php?action=eliminar_seleccionados', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: seleccionados })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Eliminado', data.mensaje, 'success');
                cargarTrabajadores();
            } else {
                Swal.fire('Error', data.mensaje, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Error en la conexión', 'error');
        }
    }
});
</script>
