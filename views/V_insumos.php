<?php
// Restricción de acceso: Solo usuarios Administradores pueden gestionar el catálogo de insumos
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    echo "<h1>Acceso denegado</h1>";
    exit;
}

// Cargar modelos requeridos para las relaciones de categoría y unidades en los formularios
require_once dirname(__DIR__) . '/models/M_Insumo.php';
require_once dirname(__DIR__) . '/models/M_Categoria.php';
require_once dirname(__DIR__) . '/models/M_Unidad.php';

$modelInsumo = M_Insumo::singleton();
$insumos = $modelInsumo->listar();

$modelCat = M_Categoria::singleton();
$categorias = $modelCat->listar();

$modelUni = M_Unidad::singleton();
$unidades = $modelUni->listar();

$isAdmin = ($_SESSION['rol'] === 'Administrador');
?>

<div class="container-fluid px-0">
    <!-- Encabezado de Página -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Insumos</h4>
            <p class="text-muted mb-0" style="font-size: 14px;">Administra los productos e insumos disponibles en la granja.</p>
        </div>
        <?php if ($isAdmin): ?>
            <button class="gp-btn-primary d-flex align-items-center gap-2 border-0" data-bs-toggle="modal" data-bs-target="#nuevoInsumoModal">
                <i class="bi bi-plus-lg"></i>
                <span>Nuevo Insumo</span>
            </button>
        <?php endif; ?>
    </div>

    <!-- Panel de Insumos -->
    <div class="gp-card">
        <!-- Barra de Búsqueda -->
        <div class="row mb-3">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted" id="search-addon">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0 text-sm" id="searchInsumos" placeholder="Buscar insumo..." aria-label="Buscar" aria-describedby="search-addon" style="box-shadow: none; font-size: 14px;">
                </div>
            </div>
        </div>

        <!-- Tabla del Inventario de Insumos -->
        <div class="table-responsive">
            <table class="table align-middle text-sm" id="tableInsumos" style="font-size: 14px;">
                <thead>
                    <tr class="text-muted border-bottom" style="font-size: 13px;">
                        <th scope="col" class="pb-3">Insumo</th>
                        <th scope="col" class="pb-3">Categoría</th>
                        <th scope="col" class="pb-3 text-end">Precio</th>
                        <th scope="col" class="pb-3 text-end">Stock</th>
                        <th scope="col" class="pb-3">Unidad</th>
                        <th scope="col" class="pb-3">Estado</th>
                        <?php if ($isAdmin): ?>
                            <th scope="col" class="pb-3 text-end">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($insumos)): ?>
                        <tr>
                            <td colspan="<?php echo $isAdmin ? '7' : '6'; ?>" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam-fill fs-2 mb-2 d-block"></i>
                                No se encontraron insumos.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($insumos as $ins): ?>
                            <!-- Resaltar visualmente si el insumo está bajo la cuota mínima de 20 unidades -->
                            <?php $lowStock = ($ins['stock_piezas'] <= 20); ?>
                            <tr class="border-bottom insumo-row">
                                <td class="py-3">
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($ins['nombre']); ?></span>
                                        <span class="text-muted font-mono" style="font-size: 11px;">INS-<?php echo str_pad($ins['id_insumo'], 3, '0', STR_PAD_LEFT); ?></span>
                                    </div>
                                </td>
                                <td class="text-muted">
                                    <?php echo htmlspecialchars($ins['categoria']); ?>
                                </td>
                                <td class="text-end fw-medium">
                                    S/ <?php echo number_format($ins['precio_unitario'], 2); ?>
                                </td>
                                <td class="text-end">
                                    <span class="<?php echo $lowStock ? 'text-danger fw-bold' : 'text-dark fw-medium'; ?>">
                                        <?php echo number_format($ins['stock_piezas'], 2); ?>
                                    </span>
                                </td>
                                <td class="text-muted">
                                    <?php echo htmlspecialchars($ins['unidad']); ?> (<?php echo htmlspecialchars($ins['abreviatura']); ?>)
                                </td>
                                <td>
                                    <span class="<?php echo $ins['estado'] == 1 ? 'gp-badge-success' : 'gp-badge-danger'; ?>">
                                        <?php echo $ins['estado'] == 1 ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <?php if ($isAdmin): ?>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <!-- Mapear datos para edición rápida -->
                                            <button class="btn btn-link text-muted p-1 hover-text-primary edit-insumo-btn" 
                                                    data-id="<?php echo $ins['id_insumo']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($ins['nombre']); ?>"
                                                    data-categoria="<?php echo $ins['categoria']; ?>"
                                                    data-unidad="<?php echo $ins['unidad']; ?>"
                                                    data-precio="<?php echo $ins['precio_unitario']; ?>"
                                                    data-costo="<?php echo $ins['costo_produccion']; ?>"
                                                    data-stock="<?php echo $ins['stock_piezas']; ?>"
                                                    data-contenido="<?php echo htmlspecialchars($ins['contenido_estandar'] ?? ''); ?>"
                                                    data-imagen="<?php echo htmlspecialchars($ins['imagen'] ?? ''); ?>"
                                                    title="Editar">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <button class="btn btn-link text-muted p-1 hover-text-danger delete-insumo-btn" 
                                                    data-id="<?php echo $ins['id_insumo']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($ins['nombre']); ?>"
                                                    title="Eliminar">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($isAdmin): ?>
<!-- Modal: Nuevo Insumo -->
<div class="modal fade" id="nuevoInsumoModal" tabindex="-1" aria-labelledby="nuevoInsumoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-success text-white border-0 py-3" style="border-radius: 15px 15px 0 0;">
                <h6 class="modal-title fw-bold" id="nuevoInsumoModalLabel">Nuevo Insumo</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="box-shadow: none;"></button>
            </div>
            <form id="formNuevoInsumo">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="new_nombre" class="form-label fw-semibold" style="font-size: 13px;">Nombre del insumo</label>
                        <input type="text" class="form-control" id="new_nombre" placeholder="Ej. Maíz a granel" required autocomplete="off">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="new_categoria" class="form-label fw-semibold" style="font-size: 13px;">Categoría</label>
                            <select class="form-select" id="new_categoria" required>
                                <option value="" disabled selected>Seleccionar</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id_categoria']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="new_unidad" class="form-label fw-semibold" style="font-size: 13px;">Unidad de medida</label>
                            <select class="form-select" id="new_unidad" required>
                                <option value="" disabled selected>Seleccionar</option>
                                <?php foreach ($unidades as $uni): ?>
                                    <option value="<?php echo $uni['id_unidad']; ?>"><?php echo htmlspecialchars($uni['nombre']); ?> (<?php echo htmlspecialchars($uni['abreviatura']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <label for="new_costo" class="form-label fw-semibold" style="font-size: 13px;">Costo base (S/)</label>
                            <input type="number" class="form-control" id="new_costo" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-4">
                            <label for="new_precio" class="form-label fw-semibold" style="font-size: 13px;">Precio Venta (S/)</label>
                            <input type="number" class="form-control" id="new_precio" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-4">
                            <label for="new_stock" class="form-label fw-semibold" style="font-size: 13px;">Stock inicial</label>
                            <input type="number" class="form-control" id="new_stock" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="new_imagen" class="form-label fw-semibold" style="font-size: 13px;"><i class="bi bi-image text-success me-1"></i>Imagen del producto (Opcional)</label>
                        <input type="file" class="form-control" id="new_imagen" accept="image/*">
                    </div>
                    <!-- Checkbox: Activa el modal de pesaje en balanza al momento de realizar la venta -->
                    <div class="mt-3 p-3 rounded-3" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="new_requiere_pesaje" style="width: 2.5em; height: 1.3em; cursor: pointer;">
                            <label class="form-check-label fw-semibold ms-2" for="new_requiere_pesaje" style="font-size: 13px; cursor: pointer;">
                                <i class="bi bi-moisture text-success me-1"></i>Requiere pesaje en balanza
                            </label>
                            <div class="text-muted mt-1" style="font-size: 11px; padding-left: 3.5em;">Al activar esto, en cada venta se pedirá el peso del producto (Ej: pavos vivos).</div>
                        </div>
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

<!-- Modal: Editar Insumo -->
<div class="modal fade" id="editarInsumoModal" tabindex="-1" aria-labelledby="editarInsumoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-success text-white border-0 py-3" style="border-radius: 15px 15px 0 0;">
                <h6 class="modal-title fw-bold" id="editarInsumoModalLabel">Editar Registro</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="box-shadow: none;"></button>
            </div>
            <form id="formEditarInsumo">
                <input type="hidden" id="edit_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_nombre" class="form-label fw-semibold" style="font-size: 13px;">Nombre del insumo</label>
                        <input type="text" class="form-control" id="edit_nombre" required autocomplete="off">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="edit_categoria" class="form-label fw-semibold" style="font-size: 13px;">Categoría</label>
                            <select class="form-select" id="edit_categoria" required>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id_categoria']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="edit_unidad" class="form-label fw-semibold" style="font-size: 13px;">Unidad de medida</label>
                            <select class="form-select" id="edit_unidad" required>
                                <?php foreach ($unidades as $uni): ?>
                                    <option value="<?php echo $uni['id_unidad']; ?>"><?php echo htmlspecialchars($uni['nombre']); ?> (<?php echo htmlspecialchars($uni['abreviatura']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <label for="edit_costo" class="form-label fw-semibold" style="font-size: 13px;">Costo base (S/)</label>
                            <input type="number" class="form-control" id="edit_costo" step="0.01" min="0" required>
                        </div>
                        <div class="col-4">
                            <label for="edit_precio" class="form-label fw-semibold" style="font-size: 13px;">Precio Venta (S/)</label>
                            <input type="number" class="form-control" id="edit_precio" step="0.01" min="0" required>
                        </div>
                        <div class="col-4">
                            <label for="edit_stock" class="form-label fw-semibold" style="font-size: 13px;">Stock</label>
                            <input type="number" class="form-control" id="edit_stock" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="edit_imagen" class="form-label fw-semibold" style="font-size: 13px;"><i class="bi bi-image text-success me-1"></i>Nueva Imagen (Opcional)</label>
                        <input type="file" class="form-control" id="edit_imagen" accept="image/*">
                        <div id="edit_imagen_preview" class="mt-2 d-none">
                            <span class="text-muted" style="font-size: 11px;">Imagen actual:</span>
                            <img src="" id="edit_imagen_img" class="d-block mt-1 border rounded" style="max-height: 85px; max-width: 100%; object-fit: contain;">
                        </div>
                    </div>
                    <!-- Checkbox: Activa el modal de pesaje en balanza al momento de realizar la venta -->
                    <div class="mt-3 p-3 rounded-3" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="edit_requiere_pesaje" style="width: 2.5em; height: 1.3em; cursor: pointer;">
                            <label class="form-check-label fw-semibold ms-2" for="edit_requiere_pesaje" style="font-size: 13px; cursor: pointer;">
                                <i class="bi bi-moisture text-success me-1"></i>Requiere pesaje en balanza
                            </label>
                            <div class="text-muted mt-1" style="font-size: 11px; padding-left: 3.5em;">Al activar esto, en cada venta se pedirá el peso del producto (Ej: pavos vivos).</div>
                        </div>
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
<?php endif; ?>

<!-- JavaScript para CRUD de Insumos -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Filtrado dinámico de insumos (búsqueda)
        const searchInput = document.getElementById('searchInsumos');
        const rows = document.querySelectorAll('.insumo-row');

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

        <?php if ($isAdmin): ?>
         // 2. Registro de Insumo por AJAX
        const formNuevo = document.getElementById('formNuevoInsumo');
        if (formNuevo) {
            formNuevo.addEventListener('submit', async (e) => {
                e.preventDefault();
                const nombre = document.getElementById('new_nombre').value.trim();
                const id_categoria = document.getElementById('new_categoria').value;
                const id_unidad = document.getElementById('new_unidad').value;
                const precio_unitario = document.getElementById('new_precio').value;
                const costo_produccion = document.getElementById('new_costo').value;
                const stock = document.getElementById('new_stock').value;
                const requiere_pesaje = document.getElementById('new_requiere_pesaje').checked;

                const formData = new FormData();
                formData.append('nombre', nombre);
                formData.append('id_categoria', id_categoria);
                formData.append('id_unidad', id_unidad);
                formData.append('precio_unitario', precio_unitario);
                formData.append('costo_produccion', costo_produccion);
                formData.append('stock', stock);
                if (!requiere_pesaje) {
                    formData.append('contenido_estandar', '0');
                }

                const fileInput = document.getElementById('new_imagen');
                if (fileInput && fileInput.files.length > 0) {
                    formData.append('imagen', fileInput.files[0]);
                }

                try {
                    const response = await fetch('./controllers/C_Insumo.php?action=crear', {
                        method: 'POST',
                        body: formData
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

        // 3. Rellenar campos en el modal de Edición
        const editModal = new bootstrap.Modal(document.getElementById('editarInsumoModal'));
        const selectCat = document.getElementById('edit_categoria');
        const selectUni = document.getElementById('edit_unidad');

        document.querySelectorAll('.edit-insumo-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('edit_id').value = btn.dataset.id;
                document.getElementById('edit_nombre').value = btn.dataset.nombre;
                document.getElementById('edit_precio').value = btn.dataset.precio;
                document.getElementById('edit_costo').value = btn.dataset.costo;
                document.getElementById('edit_stock').value = btn.dataset.stock;

                // Previsualizar la imagen actual si existe
                const imagen = btn.dataset.imagen;
                const previewDiv = document.getElementById('edit_imagen_preview');
                const previewImg = document.getElementById('edit_imagen_img');
                // Limpiar input file viejo
                document.getElementById('edit_imagen').value = '';
                if (imagen && imagen !== '' && imagen !== 'null') {
                    previewImg.src = `./assets/productos/${imagen}`;
                    previewDiv.classList.remove('d-none');
                } else {
                    previewDiv.classList.add('d-none');
                    previewImg.src = '';
                }

                // Poblar el checkbox: si contenido_estandar está vacío o es null → el insumo requiere pesaje
                const contenido = btn.dataset.contenido;
                document.getElementById('edit_requiere_pesaje').checked = (!contenido || contenido === '' || contenido === 'null');
                
                // Mapear los dropdown de categoría y unidades dinámicamente
                Array.from(selectCat.options).forEach(opt => {
                    if (opt.text === btn.dataset.categoria) opt.selected = true;
                });
                Array.from(selectUni.options).forEach(opt => {
                    if (opt.text.startsWith(btn.dataset.unidad)) opt.selected = true;
                });

                editModal.show();
            });
        });

        // 4. Guardar cambios del Insumo editado
        const formEditar = document.getElementById('formEditarInsumo');
        if (formEditar) {
            formEditar.addEventListener('submit', async (e) => {
                e.preventDefault();
                const id_insumo = document.getElementById('edit_id').value;
                const nombre = document.getElementById('edit_nombre').value.trim();
                const id_categoria = document.getElementById('edit_categoria').value;
                const id_unidad = document.getElementById('edit_unidad').value;
                const precio_unitario = document.getElementById('edit_precio').value;
                const costo_produccion = document.getElementById('edit_costo').value;
                const stock = document.getElementById('edit_stock').value;
                const requiere_pesaje = document.getElementById('edit_requiere_pesaje').checked;

                const formData = new FormData();
                formData.append('id_insumo', id_insumo);
                formData.append('nombre', nombre);
                formData.append('id_categoria', id_categoria);
                formData.append('id_unidad', id_unidad);
                formData.append('precio_unitario', precio_unitario);
                formData.append('costo_produccion', costo_produccion);
                formData.append('stock', stock);
                if (!requiere_pesaje) {
                    formData.append('contenido_estandar', '0');
                }

                const fileInput = document.getElementById('edit_imagen');
                if (fileInput && fileInput.files.length > 0) {
                    formData.append('imagen', fileInput.files[0]);
                }

                try {
                    const response = await fetch('./controllers/C_Insumo.php?action=actualizar', {
                        method: 'POST',
                        body: formData
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

        // 5. Eliminar lógicamente un insumo del catálogo activo
        document.querySelectorAll('.delete-insumo-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id_insumo = btn.dataset.id;
                const nombre = btn.dataset.nombre;

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Deseas eliminar el insumo "${nombre}"`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const response = await fetch('./controllers/C_Insumo.php?action=eliminar', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id_insumo })
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
        <?php endif; ?>
    });
</script>
