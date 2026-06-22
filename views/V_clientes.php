<?php
if (!isset($_SESSION['id_usuario'])) {
    echo "<h1>Acceso denegado</h1>";
    exit;
}

require_once dirname(__DIR__) . '/models/M_Cliente.php';
$modelCliente = M_Cliente::singleton();
$clientes = $modelCliente->listarClientes();
?>

<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Clientes</h4>
            <p class="text-muted mb-0" style="font-size: 14px;">Administra la información de los compradores y clientes.</p>
        </div>
        <button class="gp-btn-primary d-flex align-items-center gap-2 border-0" data-bs-toggle="modal" data-bs-target="#nuevoClienteModal">
            <i class="bi bi-plus-lg"></i>
            <span>Nuevo Cliente</span>
        </button>
    </div>

    <!-- Clientes Card -->
    <div class="gp-card">
        <!-- Search bar -->
        <div class="row mb-3">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted" id="search-addon">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0 text-sm" id="searchClientes" placeholder="Buscar cliente..." aria-label="Buscar" aria-describedby="search-addon" style="box-shadow: none; font-size: 14px;">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table align-middle text-sm" id="tableClientes" style="font-size: 14px;">
                <thead>
                    <tr class="text-muted border-bottom" style="font-size: 13px;">
                        <th scope="col" class="pb-3">Nombres / Razón Social</th>
                        <th scope="col" class="pb-3">N° Documento</th>
                        <th scope="col" class="pb-3">Teléfono</th>
                        <th scope="col" class="pb-3">Dirección</th>
                        <th scope="col" class="pb-3">Tipo Cliente</th>
                        <th scope="col" class="pb-3">Estado</th>
                        <th scope="col" class="pb-3 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-person-badge-fill fs-2 mb-2 d-block"></i>
                                No se encontraron clientes.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $cli): ?>
                            <tr class="border-bottom cliente-row">
                                <td class="py-3">
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($cli['nombres_razon_social'] . ' ' . $cli['apellidos']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-dark fw-medium"><?php echo htmlspecialchars($cli['numero_documento']); ?></span>
                                        <small class="text-muted" style="font-size: 11px;"><?php echo $cli['tipo_documento'] == 1 ? 'DNI' : 'RUC'; ?></small>
                                    </div>
                                </td>
                                <td class="text-muted">
                                    <?php echo htmlspecialchars($cli['telefono'] ? $cli['telefono'] : '-'); ?>
                                </td>
                                <td class="text-muted" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars($cli['direccion'] ? $cli['direccion'] : '-'); ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1.5 fw-semibold" style="font-size: 11px;">
                                        <?php echo $cli['tipo_cliente'] == 1 ? 'Persona Natural' : 'Persona Jurídica'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?php echo $cli['estado'] == 1 ? 'gp-badge-success' : 'gp-badge-danger'; ?>">
                                        <?php echo $cli['estado'] == 1 ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-link text-muted p-1 hover-text-primary edit-cliente-btn" 
                                                data-id="<?php echo $cli['id_cliente']; ?>"
                                                data-tipodoc="<?php echo $cli['tipo_documento']; ?>"
                                                data-numdoc="<?php echo htmlspecialchars($cli['numero_documento']); ?>"
                                                data-nombres="<?php echo htmlspecialchars($cli['nombres_razon_social']); ?>"
                                                data-apellidos="<?php echo htmlspecialchars($cli['apellidos']); ?>"
                                                data-direccion="<?php echo htmlspecialchars($cli['direccion']); ?>"
                                                data-telefono="<?php echo htmlspecialchars($cli['telefono']); ?>"
                                                data-tipocli="<?php echo $cli['tipo_cliente']; ?>"
                                                title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <?php if ($cli['id_cliente'] != 1): // Do not delete Público General (id_cliente = 1) ?>
                                            <button class="btn btn-link text-muted p-1 hover-text-danger delete-cliente-btn" 
                                                    data-id="<?php echo $cli['id_cliente']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($cli['nombres_razon_social'] . ' ' . $cli['apellidos']); ?>"
                                                    title="Eliminar">
                                                <i class="bi bi-trash-fill"></i>
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

<!-- Modal: Nuevo Cliente -->
<div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-success text-white border-0 py-3" style="border-radius: 15px 15px 0 0;">
                <h6 class="modal-title fw-bold" id="nuevoClienteModalLabel">Nuevo Cliente</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="box-shadow: none;"></button>
            </div>
            <form id="formNuevoCliente">
                <div class="modal-body p-4">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="new_tipo_doc" class="form-label fw-semibold" style="font-size: 13px;">Tipo Documento</label>
                            <select class="form-select" id="new_tipo_doc" required>
                                <option value="1" selected>DNI</option>
                                <option value="2">RUC</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="new_num_doc" class="form-label fw-semibold" style="font-size: 13px;">Número Documento</label>
                            <input type="text" class="form-control" id="new_num_doc" placeholder="N° Documento" required autocomplete="off">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="new_nombres" class="form-label fw-semibold" style="font-size: 13px;">Nombres / Razón Social</label>
                            <input type="text" class="form-control" id="new_nombres" placeholder="Nombres o Razón Social" required autocomplete="off">
                        </div>
                        <div class="col-6">
                            <label for="new_apellidos" class="form-label fw-semibold" style="font-size: 13px;">Apellidos (Opcional)</label>
                            <input type="text" class="form-control" id="new_apellidos" placeholder="Apellidos" autocomplete="off">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="new_telefono" class="form-label fw-semibold" style="font-size: 13px;">Teléfono</label>
                            <input type="text" class="form-control" id="new_telefono" placeholder="Ej. 987654321" autocomplete="off">
                        </div>
                        <div class="col-6">
                            <label for="new_tipo_cli" class="form-label fw-semibold" style="font-size: 13px;">Tipo Cliente</label>
                            <select class="form-select" id="new_tipo_cli" required>
                                <option value="1" selected>Persona Natural</option>
                                <option value="2">Persona Jurídica</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="new_direccion" class="form-label fw-semibold" style="font-size: 13px;">Dirección</label>
                        <input type="text" class="form-control" id="new_direccion" placeholder="Dirección del cliente" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal" style="border-radius: 8px;">Cancelar</button>
                    <button type="submit" class="gp-btn-primary border-0">Crear registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Cliente -->
<div class="modal fade" id="editarClienteModal" tabindex="-1" aria-labelledby="editarClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-success text-white border-0 py-3" style="border-radius: 15px 15px 0 0;">
                <h6 class="modal-title fw-bold" id="editarClienteModalLabel">Editar Registro</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="box-shadow: none;"></button>
            </div>
            <form id="formEditarCliente">
                <input type="hidden" id="edit_id">
                <div class="modal-body p-4">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="edit_tipo_doc" class="form-label fw-semibold" style="font-size: 13px;">Tipo Documento</label>
                            <select class="form-select" id="edit_tipo_doc" required>
                                <option value="1">DNI</option>
                                <option value="2">RUC</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="edit_num_doc" class="form-label fw-semibold" style="font-size: 13px;">Número Documento</label>
                            <input type="text" class="form-control" id="edit_num_doc" required autocomplete="off">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="edit_nombres" class="form-label fw-semibold" style="font-size: 13px;">Nombres / Razón Social</label>
                            <input type="text" class="form-control" id="edit_nombres" required autocomplete="off">
                        </div>
                        <div class="col-6">
                            <label for="edit_apellidos" class="form-label fw-semibold" style="font-size: 13px;">Apellidos (Opcional)</label>
                            <input type="text" class="form-control" id="edit_apellidos" autocomplete="off">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="edit_telefono" class="form-label fw-semibold" style="font-size: 13px;">Teléfono</label>
                            <input type="text" class="form-control" id="edit_telefono" autocomplete="off">
                        </div>
                        <div class="col-6">
                            <label for="edit_tipo_cli" class="form-label fw-semibold" style="font-size: 13px;">Tipo Cliente</label>
                            <select class="form-select" id="edit_tipo_cli" required>
                                <option value="1">Persona Natural</option>
                                <option value="2">Persona Jurídica</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="edit_direccion" class="form-label fw-semibold" style="font-size: 13px;">Dirección</label>
                        <input type="text" class="form-control" id="edit_direccion" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal" style="border-radius: 8px;">Cancelar</button>
                    <button type="submit" class="gp-btn-primary border-0">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for CRUD Operations -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Search functionality
        const searchInput = document.getElementById('searchClientes');
        const rows = document.querySelectorAll('.cliente-row');

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const query = searchInput.value.toLowerCase().trim();
                rows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    if (text.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Add Cliente Submit
        const formNuevo = document.getElementById('formNuevoCliente');
        if (formNuevo) {
            formNuevo.addEventListener('submit', async (e) => {
                e.preventDefault();
                const tipo_documento = document.getElementById('new_tipo_doc').value;
                const numero_documento = document.getElementById('new_num_doc').value.trim();
                const nombres_razon_social = document.getElementById('new_nombres').value.trim();
                const apellidos = document.getElementById('new_apellidos').value.trim();
                const telefono = document.getElementById('new_telefono').value.trim();
                const tipo_cliente = document.getElementById('new_tipo_cli').value;
                const direccion = document.getElementById('new_direccion').value.trim();

                try {
                    const response = await fetch('./controllers/C_Cliente.php?action=crear', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ tipo_documento, numero_documento, nombres_razon_social, apellidos, telefono, tipo_cliente, direccion })
                    });
                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Creado!',
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
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo conectar al servidor.' });
                }
            });
        }

        // Edit button click handler
        const editModal = new bootstrap.Modal(document.getElementById('editarClienteModal'));
        document.querySelectorAll('.edit-cliente-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('edit_id').value = btn.dataset.id;
                document.getElementById('edit_tipo_doc').value = btn.dataset.tipodoc;
                document.getElementById('edit_num_doc').value = btn.dataset.numdoc;
                document.getElementById('edit_nombres').value = btn.dataset.nombres;
                document.getElementById('edit_apellidos').value = btn.dataset.apellidos;
                document.getElementById('edit_direccion').value = btn.dataset.direccion;
                document.getElementById('edit_telefono').value = btn.dataset.telefono;
                document.getElementById('edit_tipo_cli').value = btn.dataset.tipocli;
                
                editModal.show();
            });
        });

        // Edit Form Submit
        const formEditar = document.getElementById('formEditarCliente');
        if (formEditar) {
            formEditar.addEventListener('submit', async (e) => {
                e.preventDefault();
                const id_cliente = document.getElementById('edit_id').value;
                const tipo_documento = document.getElementById('edit_tipo_doc').value;
                const numero_documento = document.getElementById('edit_num_doc').value.trim();
                const nombres_razon_social = document.getElementById('edit_nombres').value.trim();
                const apellidos = document.getElementById('edit_apellidos').value.trim();
                const telefono = document.getElementById('edit_telefono').value.trim();
                const tipo_cliente = document.getElementById('edit_tipo_cli').value;
                const direccion = document.getElementById('edit_direccion').value.trim();

                try {
                    const response = await fetch('./controllers/C_Cliente.php?action=actualizar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_cliente, tipo_documento, numero_documento, nombres_razon_social, apellidos, telefono, tipo_cliente, direccion })
                    });
                    const data = await response.json();

                    if (data.success) {
                        editModal.hide();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
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
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo conectar al servidor.' });
                }
            });
        }

        // Delete Button handler
        document.querySelectorAll('.delete-cliente-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id_cliente = btn.dataset.id;
                const nombre = btn.dataset.nombre;

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Deseas eliminar al cliente "${nombre}"`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const response = await fetch('./controllers/C_Cliente.php?action=eliminar', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id_cliente })
                            });
                            const data = await response.json();

                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
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
                        } catch (error) {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo conectar al servidor.' });
                        }
                    }
                });
            });
        });
    });
</script>
