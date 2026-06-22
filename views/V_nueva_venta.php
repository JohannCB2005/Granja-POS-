<?php
if (!isset($_SESSION['id_usuario'])) {
    echo "<h1>Acceso denegado</h1>";
    exit;
}

require_once dirname(__DIR__) . '/models/M_Insumo.php';
require_once dirname(__DIR__) . '/models/M_Cliente.php';
require_once dirname(__DIR__) . '/models/M_Categoria.php';

$modelInsumo = M_Insumo::singleton();
$insumos = $modelInsumo->listar(); // Array of active insumos with category, unit, stock, etc.

$modelCliente = M_Cliente::singleton();
$clientes = $modelCliente->listarClientes();

$modelCat = M_Categoria::singleton();
$categorias = $modelCat->listar();
?>

<style>
    /* ── Autocomplete dropdown ── */
    #clientAutocompleteDropdown {
        display: none;  /* hidden by default, JS sets display:block */
        position: absolute;
        left: 0;
        right: 0;
        top: 100%;
        z-index: 1055;
        margin-top: 4px;
        background: #ffffff;
        border: 1px solid rgba(0,0,0,0.10);
        border-radius: 10px;
        box-shadow: 0 12px 28px -6px rgba(0,0,0,0.12), 0 6px 10px -4px rgba(0,0,0,0.08);
        max-height: 260px;
        overflow-y: auto;
        padding: 4px 0;
    }

    #clientAutocompleteDropdown.open {
        display: block;
    }

    .client-dropdown-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        font-size: 13.5px;
        color: #374151;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.12s ease, color 0.12s ease;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
    }

    .client-dropdown-item:hover, .client-dropdown-item:focus {
        background-color: #f0fdf4;
        color: #15803d;
        outline: none;
    }

    .client-dropdown-item strong {
        color: #111827;
    }

    .client-dropdown-item:hover strong {
        color: #15803d;
    }

    .client-dropdown-divider {
        height: 1px;
        background: #e5e7eb;
        margin: 4px 0;
    }

    .client-dropdown-empty {
        padding: 12px 14px;
        font-size: 13px;
        color: #9ca3af;
        text-align: center;
    }

    .client-dropdown-create {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        font-size: 13.5px;
        color: #15803d;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.12s ease;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
    }

    .client-dropdown-create:hover {
        background-color: #f0fdf4;
    }

    /* Modal tab headers */
    #clientTabs .nav-link {
        color: #6b7280;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
    }
    
    #clientTabs .nav-link.active {
        color: #15803d !important;
        border-bottom: 3px solid #15803d !important;
        font-weight: 600;
    }

    /* Clear selection button */
    #clearClientSelectionBtn:hover {
        color: #dc3545 !important;
        background-color: transparent !important;
    }

    /* Ensure autocomplete parent has position relative */
    .client-autocomplete-wrapper {
        position: relative;
    }

    /* Style for disabled state of primary buttons to match professional grey theme instead of inline light green */
    .gp-btn-primary:disabled {
        background-color: #e5e7eb !important;
        border-color: #e5e7eb !important;
        color: #9ca3af !important;
        cursor: not-allowed;
    }
</style>

<div class="container-fluid px-0">
    <!-- Top Row: Client Data -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="gp-card p-3" style="border-radius: 12px;">
                <h6 class="mb-3 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-cart3 text-success"></i>
                    Datos del Cliente
                </h6>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold d-block text-muted mb-1" style="font-size: 12px;">Tipo de comprobante</label>
                        <select class="form-select form-select-sm text-sm fw-semibold" id="docTypeSelect" style="height: 38px; border-color: #ced4da; box-shadow: none;">
                            <option value="3">Nota de Venta</option>
                            <option value="1" selected>Boleta</option>
                            <option value="2">Factura</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-8">
                        <input type="hidden" id="cartClientId" value="1">
                        <label class="form-label fw-semibold text-muted mb-1" style="font-size: 12px;">Cliente</label>
                        <div class="client-autocomplete-wrapper">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0 text-muted" style="border-color: #ced4da;">
                                    <i class="bi bi-person-fill"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-1" id="clientAutocompleteInput" placeholder="Escriba DNI, RUC o Nombre..." autocomplete="off" style="font-size: 13.5px; box-shadow: none; border-color: #ced4da; height: 38px;">
                                <button type="button" class="btn btn-outline-secondary border-start-0 text-muted d-none" id="clearClientSelectionBtn" style="border-color: #ced4da; background: transparent;">
                                    <i class="bi bi-x-lg" style="font-size: 11px;"></i>
                                </button>
                            </div>
                            
                            <!-- Custom Autocomplete Dropdown List -->
                            <div id="clientAutocompleteDropdown">
                                <!-- Dynamic options loaded here -->
                            </div>
                        </div>
                        
                        <!-- Selected Client Badge -->
                        <div id="selectedClientBadge" class="mt-2 d-none" style="font-size: 13px;">
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1">
                                <i class="bi bi-person-check-fill"></i> 
                                <span id="selectedClientText">Público General</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Catalog column -->
        <div class="col-12 col-lg-7 col-xl-8">
            <div class="gp-card h-100 d-flex flex-column" style="min-height: 600px;">
                <!-- Header / Search -->
                <div class="mb-4">
                    <h4 class="fw-bold text-dark mb-1">Nueva Venta</h4>
                    <p class="text-muted mb-4" style="font-size: 13.5px;">Selecciona los insumos para agregarlos al carrito.</p>
                    
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <div class="input-group" style="width: 250px; max-width: 100%;">
                            <span class="input-group-text bg-transparent border-end-0 text-muted" id="search-pos-addon">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-0 text-sm" id="searchCatalog" placeholder="Buscar insumos..." aria-label="Buscar" aria-describedby="search-pos-addon" style="box-shadow: none;">
                        </div>
                        
                        <!-- Category Filter Pills -->
                        <div class="d-flex gap-2 overflow-auto pb-2 pb-md-0" style="flex: 1; white-space: nowrap; scrollbar-width: none;" id="categoryFilterPills">
                            <style>#categoryFilterPills::-webkit-scrollbar { display: none; }</style>
                            <button class="btn btn-success btn-sm rounded-pill px-3 fw-semibold cat-filter-btn active" data-cat="all">Todos</button>
                            <?php foreach ($categorias as $cat): ?>
                                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold cat-filter-btn" style="border-color: #e5e7eb; color: #4b5563;" data-cat="<?php echo htmlspecialchars($cat['nombre']); ?>">
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="flex-grow-1 overflow-auto pe-1" style="max-height: 480px;" id="catalogGrid">
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                        <?php foreach ($insumos as $ins): ?>
                            <?php if ($ins['estado'] == 1): ?>
                                <div class="col product-card" 
                                     data-nombre="<?php echo htmlspecialchars(strtolower($ins['nombre'])); ?>"
                                     data-categoria="<?php echo htmlspecialchars($ins['categoria']); ?>">
                                    <div class="card h-100 border border-light shadow-sm hover-shadow-md transition-all position-relative" style="border-radius: 12px; overflow: hidden;">
                                        <!-- Stock indicator badge -->
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <?php if ($ins['stock'] <= 0): ?>
                                                <span class="badge bg-danger rounded-pill px-2.5 py-1 fw-bold" style="font-size: 10px;">Agotado</span>
                                            <?php elseif ($ins['stock'] <= 20): ?>
                                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fw-bold" style="font-size: 10px;">Bajo Stock (<?php echo number_format($ins['stock'], 1); ?>)</span>
                                            <?php else: ?>
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 fw-bold" style="font-size: 10px;">Stock: <?php echo number_format($ins['stock'], 1); ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                                            <div class="mb-3 pt-2">
                                                <span class="text-muted text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.5px;"><?php echo htmlspecialchars($ins['categoria']); ?></span>
                                                <h6 class="card-title fw-bold text-dark mb-1 text-truncate-2" style="font-size: 14px; min-height: 38px;"><?php echo htmlspecialchars($ins['nombre']); ?></h6>
                                                <small class="text-muted" style="font-size: 11px;">U.M: <?php echo htmlspecialchars($ins['unidad']); ?></small>
                                            </div>

                                            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                                <div class="d-flex flex-column">
                                                    <span class="text-muted" style="font-size: 10px;">Precio Unit.</span>
                                                    <span class="fw-bold text-success" style="font-size: 15px;">S/ <?php echo number_format($ins['precio_unitario'], 2); ?></span>
                                                </div>
                                                <button class="btn btn-success bg-gradient border-0 add-to-cart-btn" 
                                                        data-id="<?php echo $ins['id_insumo']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($ins['nombre']); ?>"
                                                        data-precio="<?php echo $ins['precio_unitario']; ?>"
                                                        data-stock="<?php echo $ins['stock']; ?>"
                                                        data-unidad="<?php echo htmlspecialchars($ins['abreviatura']); ?>"
                                                        style="width: 32px; height: 32px; border-radius: 8px; padding: 0;"
                                                        <?php echo $ins['stock'] <= 0 ? 'disabled' : ''; ?>>
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart column -->
        <div class="col-12 col-lg-5 col-xl-4">
            <div class="gp-card h-100 d-flex flex-column justify-content-between" style="min-height: 600px;">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-cart3 text-success"></i>
                            Carrito de Venta
                        </h6>
                        <span class="badge bg-success rounded-pill px-2" id="cartItemCountBadge">0 items</span>
                    </div>

                    <!-- Cart List -->
                    <div class="overflow-auto mb-3 pe-1" style="max-height: 250px; min-height: 180px;" id="cartList">
                        <!-- Dynamic items loaded here -->
                        <div class="text-center py-5 text-muted" id="emptyCartMessage">
                            <i class="bi bi-cart fs-2 mb-2 d-block"></i>
                            <p style="font-size: 13px;" class="mb-1">El carrito está vacío</p>
                            <small class="text-muted" style="font-size: 11px;">Agrega insumos desde el catálogo.</small>
                        </div>
                    </div>
                </div>

                <!-- Footer Summary -->
                <div>
                    <!-- Totals -->
                    <div class="bg-light p-3 rounded-3 mb-3" style="font-size: 13px;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-semibold text-dark" id="summarySubtotal">S/ 0.00</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted">IGV (18%)</span>
                            <span class="fw-semibold text-dark" id="summaryIgv">S/ 0.00</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <span class="fw-bold text-dark" style="font-size: 14px;">Total</span>
                            <span class="fw-bold text-success" style="font-size: 16px;" id="summaryTotal">S/ 0.00</span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <button class="gp-btn-primary w-100 border-0 py-2.5 d-flex align-items-center justify-content-center gap-2" id="submitSaleBtn" disabled>
                        <span>Registrar Venta</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal: Nuevo Cliente -->
<div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark" id="nuevoClienteModalLabel" style="font-size: 16px;">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="box-shadow: none;"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs border-bottom mb-4" id="clientTabs" role="tablist" style="font-size: 13.5px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold text-success border-0 border-bottom border-3 border-success px-3 pb-2" id="datos-tab" data-bs-toggle="tab" data-bs-target="#tab-datos" type="button" role="tab" aria-controls="tab-datos" aria-selected="true" style="background: transparent;">Datos de Cliente</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold text-muted border-0 px-3 pb-2" id="direccion-tab" data-bs-toggle="tab" data-bs-target="#tab-direccion" type="button" role="tab" aria-controls="tab-direccion" aria-selected="false" style="background: transparent;">Dirección</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold text-muted border-0 px-3 pb-2" id="otros-tab" data-bs-toggle="tab" data-bs-target="#tab-otros" type="button" role="tab" aria-controls="tab-otros" aria-selected="false" style="background: transparent;">Otros Datos</button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="clientTabsContent" style="font-size: 13px;">
                    <!-- TAB 1: DATOS DE CLIENTE -->
                    <div class="tab-pane fade show active" id="tab-datos" role="tabpanel" aria-labelledby="datos-tab">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted fw-semibold mb-1">Tipo Doc. Identidad <span class="text-danger">*</span></label>
                                <select class="form-select" id="modalTipoDoc" style="box-shadow: none; height: 38px; font-size: 13.5px;">
                                    <option value="1">DNI</option>
                                    <option value="2">RUC</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted fw-semibold mb-1">Número <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="modalNumDoc" placeholder="Ej. 78945612" style="box-shadow: none; height: 38px; font-size: 13.5px;">
                                    <button type="button" class="btn btn-success fw-semibold d-flex align-items-center gap-1 px-3" id="modalSearchApiBtn" style="height: 38px; border: none; background-color: #198754;">
                                        <i class="bi bi-search"></i> <span id="modalSearchApiBtnText">RENIEC</span>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted fw-semibold mb-1">Nombre / Razón Social <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modalNombre" placeholder="Nombres o Razón Social" style="box-shadow: none; height: 38px; font-size: 13.5px;">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted fw-semibold mb-1">Nombre comercial</label>
                                <input type="text" class="form-control" id="modalNombreComercial" placeholder="Nombre comercial" style="box-shadow: none; height: 38px; font-size: 13.5px;">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label text-muted fw-semibold mb-1">Días de crédito</label>
                                <input type="number" class="form-control" id="modalDiasCredito" value="0" style="box-shadow: none; height: 38px; font-size: 13.5px;">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label text-muted fw-semibold mb-1">Código interno</label>
                                <input type="text" class="form-control" id="modalCodInterno" placeholder="Código interno" style="box-shadow: none; height: 38px; font-size: 13.5px;">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label text-muted fw-semibold mb-1">Nacionalidad</label>
                                <select class="form-select" id="modalNacionalidad" style="box-shadow: none; height: 38px; font-size: 13.5px;">
                                    <option value="PE" selected>PERU</option>
                                    <option value="OTHER">OTRO</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted fw-semibold mb-1">Tipo de cliente</label>
                                <select class="form-select" id="modalTipoCliente" style="box-shadow: none; height: 38px; font-size: 13.5px;">
                                    <option value="1" selected>Natural</option>
                                    <option value="2">Jurídica</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted fw-semibold mb-1">Código de barra</label>
                                <input type="text" class="form-control" id="modalCodBarra" placeholder="Código de barra" style="box-shadow: none; height: 38px; font-size: 13.5px;">
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="modalAgenteRetencion" style="box-shadow: none;">
                                    <label class="form-check-label text-muted fw-semibold" for="modalAgenteRetencion">
                                        ¿Es agente de retención?
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: DIRECCIÓN & TELÉFONO -->
                    <div class="tab-pane fade" id="tab-direccion" role="tabpanel" aria-labelledby="direccion-tab">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label text-muted fw-semibold mb-1">Dirección</label>
                                <input type="text" class="form-control" id="modalDireccion" placeholder="Dirección del cliente" style="box-shadow: none; height: 38px; font-size: 13.5px;">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted fw-semibold mb-1">Teléfono</label>
                                <input type="text" class="form-control" id="modalTelefono" placeholder="Teléfono / Celular" style="box-shadow: none; height: 38px; font-size: 13.5px;">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: OTROS DATOS -->
                    <div class="tab-pane fade" id="tab-otros" role="tabpanel" aria-labelledby="otros-tab">
                        <p class="text-muted mb-0">No se requieren campos adicionales obligatorios.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-3 d-flex justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
                <button type="button" class="btn btn-light fw-semibold px-4" data-bs-dismiss="modal" style="font-size: 13.5px; height: 38px;">Cancelar</button>
                <button type="button" class="btn btn-success fw-semibold px-4" id="modalSaveClientBtn" style="font-size: 13.5px; height: 38px; background-color: #198754; border: none;">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // POS Shopping Cart State
        let cart = [];

        // Dynamic Client List (Loaded from database)
        const clientsList = <?php echo json_encode($clientes); ?>;

        // DOM elements
        const searchInput = document.getElementById('searchCatalog');
        const catFilterBtns = document.querySelectorAll('.cat-filter-btn');
        const productCards = document.querySelectorAll('.product-card');
        const cartList = document.getElementById('cartList');
        const emptyCartMessage = document.getElementById('emptyCartMessage');
        const clearCartBtn = document.getElementById('clearCart');
        const submitSaleBtn = document.getElementById('submitSaleBtn');
        
        // Client Input & Search elements (Autocomplete + Modal)
        const clientAutocompleteInput = document.getElementById('clientAutocompleteInput');
        const clearClientSelectionBtn = document.getElementById('clearClientSelectionBtn');
        const clientAutocompleteDropdown = document.getElementById('clientAutocompleteDropdown');
        const cartClientId = document.getElementById('cartClientId');
        const selectedClientBadge = document.getElementById('selectedClientBadge');
        const selectedClientText = document.getElementById('selectedClientText');

        // Document type select element
        const docTypeSelect = document.getElementById('docTypeSelect');

        // Summary elements
        const summarySubtotal = document.getElementById('summarySubtotal');
        const summaryIgv = document.getElementById('summaryIgv');
        const summaryTotal = document.getElementById('summaryTotal');

        // Modal Elements
        const modalTipoDoc = document.getElementById('modalTipoDoc');
        const modalNumDoc = document.getElementById('modalNumDoc');
        const modalSearchApiBtn = document.getElementById('modalSearchApiBtn');
        const modalSearchApiBtnText = document.getElementById('modalSearchApiBtnText');
        const modalNombre = document.getElementById('modalNombre');
        const modalNombreComercial = document.getElementById('modalNombreComercial');
        const modalDiasCredito = document.getElementById('modalDiasCredito');
        const modalCodInterno = document.getElementById('modalCodInterno');
        const modalNacionalidad = document.getElementById('modalNacionalidad');
        const modalTipoCliente = document.getElementById('modalTipoCliente');
        const modalCodBarra = document.getElementById('modalCodBarra');
        const modalAgenteRetencion = document.getElementById('modalAgenteRetencion');
        const modalDireccion = document.getElementById('modalDireccion');
        const modalTelefono = document.getElementById('modalTelefono');
        const modalSaveClientBtn = document.getElementById('modalSaveClientBtn');

        // Modal Tab Navigation visual highlights
        const tabButtons = document.querySelectorAll('#clientTabs button');
        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                tabButtons.forEach(b => {
                    b.classList.remove('active', 'text-success', 'border-bottom', 'border-3', 'border-success');
                    b.classList.add('text-muted');
                });
                btn.classList.add('active', 'text-success', 'border-bottom', 'border-3', 'border-success');
                btn.classList.remove('text-muted');
            });
        });

        // Update button text in modal based on Doc Type select
        modalTipoDoc.addEventListener('change', () => {
            if (modalTipoDoc.value === '1') {
                modalSearchApiBtnText.innerText = 'RENIEC';
                modalTipoCliente.value = '1'; // Natural
            } else {
                modalSearchApiBtnText.innerText = 'SUNAT';
                modalTipoCliente.value = '2'; // Jurídica
            }
        });

        // Toggle Receipt Document Mode
        function updateDocumentMode() {
            const docType = docTypeSelect.value;
            
            // Clear current selection and restore default
            if (docType === '1' || docType === '3') {
                cartClientId.value = '1';
                selectedClientText.innerText = 'Público General';
                clientAutocompleteInput.value = '';
                clearClientSelectionBtn.classList.add('d-none');
            } else {
                cartClientId.value = '';
                selectedClientText.innerText = 'Se requiere RUC para Factura';
                clientAutocompleteInput.value = '';
                clearClientSelectionBtn.classList.add('d-none');
            }
            validateSubmitBtn();
        }

        if (docTypeSelect) {
            docTypeSelect.addEventListener('change', updateDocumentMode);
        }

        // Autocomplete Filter & Render function
        function showAutocompleteDropdown() {
            const rawVal = clientAutocompleteInput.value;
            const val = rawVal.toLowerCase().trim();
            
            // If empty, show first 8 clients as suggestion list
            let matches = [];
            if (val === '') {
                matches = clientsList.slice(0, 8);
            } else {
                matches = clientsList.filter(c => 
                    c.numero_documento.toLowerCase().includes(val) || 
                    c.nombres_razon_social.toLowerCase().includes(val) || 
                    (c.apellidos && c.apellidos.toLowerCase().includes(val))
                );
            }

            let dropdownHtml = '';
            if (matches.length > 0) {
                matches.forEach(c => {
                    const label = `${c.nombres_razon_social}${c.apellidos ? ', ' + c.apellidos : ''}`;
                    dropdownHtml += `
                        <button type="button" class="client-dropdown-item client-opt"
                            data-id="${c.id_cliente}"
                            data-doc="${c.numero_documento}"
                            data-name="${label.trim()}">
                            <strong>${c.numero_documento}</strong>&nbsp;–&nbsp;${label.trim()}
                        </button>
                    `;
                });
                // Always offer option to create new one too
                if (val !== '') {
                    dropdownHtml += `<div class="client-dropdown-divider"></div>`;
                    dropdownHtml += `
                        <button type="button" class="client-dropdown-create" id="createClientDropdownBtn" data-value="${rawVal}">
                            <i class="bi bi-person-plus-fill"></i> Crear cliente "${rawVal}"
                        </button>
                    `;
                }
            } else {
                dropdownHtml += `<div class="client-dropdown-empty">No se encontraron resultados</div>`;
                dropdownHtml += `<div class="client-dropdown-divider"></div>`;
                dropdownHtml += `
                    <button type="button" class="client-dropdown-create" id="createClientDropdownBtn" data-value="${rawVal}">
                        <i class="bi bi-person-plus-fill"></i> Crear cliente "${rawVal}"
                    </button>
                `;
            }

            clientAutocompleteDropdown.innerHTML = dropdownHtml;
            // Show using our custom CSS class instead of Bootstrap's dropdown
            clientAutocompleteDropdown.classList.add('open');

            // Attach click listeners to matching client options
            clientAutocompleteDropdown.querySelectorAll('.client-opt').forEach(opt => {
                opt.addEventListener('mousedown', (e) => {
                    e.preventDefault(); // prevent blur before click fires
                    const id = opt.dataset.id;
                    const doc = opt.dataset.doc;
                    const name = opt.dataset.name;
                    selectClient(id, doc, name);
                });
            });

            // Attach click listener to "Crear cliente" button
            const createBtn = document.getElementById('createClientDropdownBtn');
            if (createBtn) {
                createBtn.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    openQuickCreateModal(createBtn.dataset.value);
                });
            }
        }

        function hideAutocompleteDropdown() {
            clientAutocompleteDropdown.classList.remove('open');
        }

        // Set selected client state
        function selectClient(id, doc, name) {
            cartClientId.value = id;
            clientAutocompleteInput.value = `${doc} - ${name}`;
            selectedClientText.innerText = name;
            clearClientSelectionBtn.classList.remove('d-none');
            hideAutocompleteDropdown();
            validateSubmitBtn();
        }

        // Clear Selection
        clearClientSelectionBtn.addEventListener('click', () => {
            const docType = docTypeSelect.value;
            if (docType === '1' || docType === '3') {
                cartClientId.value = '1';
                selectedClientText.innerText = 'Público General';
            } else {
                cartClientId.value = '';
                selectedClientText.innerText = 'Se requiere RUC para Factura';
            }
            clientAutocompleteInput.value = '';
            clearClientSelectionBtn.classList.add('d-none');
            validateSubmitBtn();
        });

        // Trigger autocomplete on input or focus
        clientAutocompleteInput.addEventListener('input', showAutocompleteDropdown);
        clientAutocompleteInput.addEventListener('focus', showAutocompleteDropdown);

        // Close dropdown on blur (with small delay so mousedown clicks register first)
        clientAutocompleteInput.addEventListener('blur', () => {
            setTimeout(hideAutocompleteDropdown, 150);
        });

        // Open quick create modal pre-populating fields
        function openQuickCreateModal(typedVal) {
            clientAutocompleteDropdown.classList.add('d-none');
            
            // Clean modal fields
            modalNombre.value = '';
            modalNombreComercial.value = '';
            modalDiasCredito.value = '0';
            modalCodInterno.value = '';
            modalCodBarra.value = '';
            modalAgenteRetencion.checked = false;
            modalDireccion.value = '';
            modalTelefono.value = '';

            const num = typedVal.replace(/\D/g, ''); // Numbers only
            modalNumDoc.value = num;

            // Auto-detect Document Type and Client Type
            if (num.length === 11) {
                modalTipoDoc.value = '2'; // RUC
                modalSearchApiBtnText.innerText = 'SUNAT';
                modalTipoCliente.value = '2'; // Jurídica by default
            } else if (num.length === 8) {
                modalTipoDoc.value = '1'; // DNI
                modalSearchApiBtnText.innerText = 'RENIEC';
                modalTipoCliente.value = '1'; // Natural
            } else {
                modalTipoDoc.value = '1'; // Default DNI
                modalSearchApiBtnText.innerText = 'RENIEC';
                modalTipoCliente.value = '1';
            }

            // Reset tab highlights
            tabButtons.forEach((b, idx) => {
                if (idx === 0) {
                    b.classList.add('active', 'text-success', 'border-bottom', 'border-3', 'border-success');
                    b.classList.remove('text-muted');
                } else {
                    b.classList.remove('active', 'text-success', 'border-bottom', 'border-3', 'border-success');
                    b.classList.add('text-muted');
                }
            });
            
            // Activate first tab pane
            const triggerEl = document.querySelector('#clientTabs button[data-bs-target="#tab-datos"]');
            if (triggerEl) {
                const tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }

            // Open Modal
            const createModal = new bootstrap.Modal(document.getElementById('nuevoClienteModal'));
            createModal.show();
        }

        // Modal API Search Button event handler
        modalSearchApiBtn.addEventListener('click', async () => {
            const docNum = modalNumDoc.value.trim();
            const docType = modalTipoDoc.value;

            if (docNum === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Número requerido',
                    text: 'Debe ingresar el número de documento para realizar la consulta.',
                    confirmButtonColor: '#15803d'
                });
                return;
            }

            if (docType === '1' && docNum.length !== 8) {
                Swal.fire({
                    icon: 'warning',
                    title: 'DNI Inválido',
                    text: 'El DNI debe tener exactamente 8 dígitos.',
                    confirmButtonColor: '#15803d'
                });
                return;
            }

            if (docType === '2' && docNum.length !== 11) {
                Swal.fire({
                    icon: 'warning',
                    title: 'RUC Inválido',
                    text: 'El RUC debe tener exactamente 11 dígitos.',
                    confirmButtonColor: '#15803d'
                });
                return;
            }

            modalSearchApiBtn.disabled = true;
            const originalText = modalSearchApiBtnText.innerText;
            modalSearchApiBtnText.innerText = 'Buscando...';

            try {
                const response = await fetch('./controllers/C_Cliente.php?action=buscar_api_only', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ numero_documento: docNum })
                });
                const res = await response.json();

                if (res.success) {
                    modalNombre.value = res.data.nombre;
                    modalDireccion.value = res.data.direccion;
                    modalTipoCliente.value = res.data.tipo_cliente;

                    Swal.fire({
                        icon: 'success',
                        title: '¡Datos Obtenidos!',
                        text: 'Los datos del cliente se cargaron exitosamente.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de consulta',
                        text: res.mensaje,
                        confirmButtonColor: '#15803d'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de red',
                    text: 'No se pudo conectar con el servidor para la consulta de API.',
                    confirmButtonColor: '#15803d'
                });
            } finally {
                modalSearchApiBtn.disabled = false;
                modalSearchApiBtnText.innerText = originalText;
            }
        });

        // Modal Save Button event handler
        modalSaveClientBtn.addEventListener('click', async () => {
            const tipo_documento = parseInt(modalTipoDoc.value);
            const numero_documento = modalNumDoc.value.trim();
            const nombres_razon_social = modalNombre.value.trim();
            const direccion = modalDireccion.value.trim();
            const telefono = modalTelefono.value.trim();
            const tipo_cliente = parseInt(modalTipoCliente.value);

            if (numero_documento === '' || nombres_razon_social === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos obligatorios',
                    text: 'Debe ingresar el Número de documento y el Nombre / Razón Social.',
                    confirmButtonColor: '#15803d'
                });
                return;
            }

            modalSaveClientBtn.disabled = true;

            try {
                const response = await fetch('./controllers/C_Cliente.php?action=crear', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        tipo_documento,
                        numero_documento,
                        nombres_razon_social,
                        apellidos: '', // Concatenated by RENIEC endpoint anyway
                        direccion,
                        telefono,
                        tipo_cliente
                    })
                });
                const res = await response.json();

                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Cliente Guardado!',
                        text: res.mensaje,
                        showConfirmButton: false,
                        timer: 1500
                    });

                    // Add new client to the local array dynamically
                    const newCli = res.cliente;
                    clientsList.push(newCli);

                    // Auto select the newly created client
                    selectClient(newCli.id_cliente, newCli.numero_documento, `${newCli.nombres_razon_social} ${newCli.apellidos || ''}`);

                    // Hide modal
                    const modalEl = document.getElementById('nuevoClienteModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al registrar',
                        text: res.mensaje,
                        confirmButtonColor: '#15803d'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de red',
                    text: 'No se pudo contactar al servidor para registrar el cliente.',
                    confirmButtonColor: '#15803d'
                });
            } finally {
                modalSaveClientBtn.disabled = false;
            }
        });

        // Local Filter for Catalog
        let currentCategory = 'all';
        function filterCatalog() {
            const query = searchInput.value.toLowerCase().trim();

            productCards.forEach(card => {
                const nameMatch = card.dataset.nombre.includes(query);
                const categoryMatch = (currentCategory === 'all' || card.dataset.categoria === currentCategory);

                if (nameMatch && categoryMatch) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        if (searchInput) searchInput.addEventListener('input', filterCatalog);
        
        catFilterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                catFilterBtns.forEach(b => {
                    b.classList.remove('btn-success', 'active');
                    b.classList.add('btn-outline-secondary');
                    b.style.borderColor = '#e5e7eb';
                    b.style.color = '#4b5563';
                });
                btn.classList.add('btn-success', 'active');
                btn.classList.remove('btn-outline-secondary');
                btn.style.borderColor = '';
                btn.style.color = '';
                
                currentCategory = btn.dataset.cat;
                filterCatalog();
            });
        });

        // Add to Cart
        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.dataset.id);
                const nombre = btn.dataset.nombre;
                const precio = parseFloat(btn.dataset.precio);
                const stock = parseFloat(btn.dataset.stock);
                const unidad = btn.dataset.unidad;

                // Check if already in cart
                const existing = cart.find(item => item.id_insumo === id);
                if (existing) {
                    if (existing.cantidad + 1 > stock) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stock Insuficiente',
                            text: `Solo hay ${stock} unidades disponibles de este insumo.`,
                            confirmButtonColor: '#15803d'
                        });
                        return;
                    }
                    existing.cantidad += 1;
                    existing.subtotal = existing.cantidad * existing.precio;
                } else {
                    cart.push({
                        id_insumo: id,
                        nombre: nombre,
                        precio: precio,
                        stock: stock,
                        unidad: unidad,
                        cantidad: 1,
                        subtotal: precio
                    });
                }

                renderCart();
            });
        });

        // Clear Cart
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', () => {
                cart = [];
                renderCart();
            });
        }

        // Increment/Decrement/Change quantity
        function updateQuantity(id, newQty) {
            const item = cart.find(i => i.id_insumo === id);
            if (!item) return;

            if (newQty <= 0) {
                cart = cart.filter(i => i.id_insumo !== id);
            } else if (newQty > item.stock) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock Insuficiente',
                    text: `El stock disponible es de ${item.stock} ${item.unidad}.`,
                    confirmButtonColor: '#15803d'
                });
                item.cantidad = item.stock;
                item.subtotal = item.cantidad * item.precio;
            } else {
                item.cantidad = newQty;
                item.subtotal = item.cantidad * item.precio;
            }
            renderCart();
        }

        // Validate complete checkout state
        function validateSubmitBtn() {
            const hasItems = cart.length > 0;
            const hasClient = cartClientId.value && cartClientId.value !== '';
            submitSaleBtn.disabled = !(hasItems && hasClient);
        }

        // Render Cart UI
        function renderCart() {
            const cartItemCountBadge = document.getElementById('cartItemCountBadge');
            if (cartItemCountBadge) {
                cartItemCountBadge.innerText = `${cart.length} items`;
            }

            if (cart.length === 0) {
                cartList.innerHTML = '';
                cartList.appendChild(emptyCartMessage);
                
                summarySubtotal.innerText = 'S/ 0.00';
                summaryIgv.innerText = 'S/ 0.00';
                summaryTotal.innerText = 'S/ 0.00';
                validateSubmitBtn();
                return;
            }

            // Remove empty message if present
            if (document.getElementById('emptyCartMessage')) {
                cartList.innerHTML = '';
            }

            let cartHtml = '<div class="list-group list-group-flush">';
            let totalGeneral = 0;

            cart.forEach(item => {
                totalGeneral += item.subtotal;
                cartHtml += `
                    <div class="list-group-item px-0 py-2.5 border-bottom bg-transparent d-flex flex-column gap-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-semibold text-dark text-truncate" style="font-size: 13px; max-width: 180px;">${item.nombre}</span>
                            <span class="fw-bold text-dark" style="font-size: 13.5px;">S/ ${item.subtotal.toFixed(2)}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="input-group input-group-sm" style="max-width: 120px;">
                                <button class="btn btn-outline-secondary px-2 border" type="button" onclick="window.posDecrease(${item.id_insumo})">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control text-center py-0" value="${item.cantidad}" step="0.01" min="0.01" style="font-size: 12px;" onchange="window.posChange(${item.id_insumo}, this.value)">
                                <button class="btn btn-outline-secondary px-2 border" type="button" onclick="window.posIncrease(${item.id_insumo})">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted" style="font-size: 11px;">S/ ${item.precio.toFixed(2)} / ${item.unidad}</span>
                                <button class="btn btn-link text-danger p-0 border-0" onclick="window.posRemove(${item.id_insumo})">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            cartHtml += '</div>';
            cartList.innerHTML = cartHtml;

            // Calculations: Total is final, subtotal is total / 1.18, igv is total - subtotal
            const subtotalVal = totalGeneral / 1.18;
            const igvVal = totalGeneral - subtotalVal;

            summarySubtotal.innerText = `S/ ${subtotalVal.toFixed(2)}`;
            summaryIgv.innerText = `S/ ${igvVal.toFixed(2)}`;
            summaryTotal.innerText = `S/ ${totalGeneral.toFixed(2)}`;
            validateSubmitBtn();
        }

        // Global functions for inline events
        window.posDecrease = (id) => {
            const item = cart.find(i => i.id_insumo === id);
            if (item) updateQuantity(id, item.cantidad - 1);
        };
        window.posIncrease = (id) => {
            const item = cart.find(i => i.id_insumo === id);
            if (item) updateQuantity(id, item.cantidad + 1);
        };
        window.posChange = (id, val) => {
            const num = parseFloat(val);
            if (isNaN(num) || num <= 0) {
                updateQuantity(id, 0);
            } else {
                updateQuantity(id, num);
            }
        };
        window.posRemove = (id) => {
            cart = cart.filter(i => i.id_insumo !== id);
            renderCart();
        };

        // Submit Sale
        if (submitSaleBtn) {
            submitSaleBtn.addEventListener('click', async () => {
                const id_cliente = parseInt(cartClientId.value);
                const tipo_comprobante = parseInt(docTypeSelect.value);
                
                // Read calculated total
                let total = 0;
                cart.forEach(item => total += item.subtotal);

                const dataToSend = {
                    id_cliente,
                    tipo_comprobante,
                    total,
                    cart: cart.map(item => ({
                        id_insumo: item.id_insumo,
                        cantidad: item.cantidad,
                        precio: item.precio,
                        subtotal: item.subtotal
                    }))
                };

                // Confirm Alert
                let docLabel = 'Boleta';
                if (tipo_comprobante === 2) docLabel = 'Factura';
                else if (tipo_comprobante === 3) docLabel = 'Nota de Venta';
                
                Swal.fire({
                    title: '¿Confirmar venta?',
                    text: `Se registrará una ${docLabel} por un total de S/ ${total.toFixed(2)}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#15803d',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Registrar',
                    cancelButtonText: 'Cancelar'
                }).then(async (res) => {
                    if (res.isConfirmed) {
                        submitSaleBtn.disabled = true;
                        try {
                            const response = await fetch('./controllers/C_Venta.php?action=crear', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(dataToSend)
                            });
                            const result = await response.json();

                            if (result.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Venta Registrada!',
                                    text: result.mensaje,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    cart = [];
                                    renderCart();
                                    // Refresh the page to reload the catalog with updated stocks
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.mensaje,
                                    confirmButtonColor: '#15803d'
                                });
                                submitSaleBtn.disabled = false;
                            }
                        } catch (err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de red',
                                text: 'No se pudo contactar al servidor.'
                            });
                            submitSaleBtn.disabled = false;
                        }
                    }
                });
            });
        }
    });
</script>
