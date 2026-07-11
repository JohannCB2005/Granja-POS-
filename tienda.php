<?php
// Tienda en Línea Pública
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Online - Granja UNP</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary: #15803d;
            --primary-hover: #166534;
            --secondary: #f3f4f6;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: var(--text-dark);
            padding-top: 70px;
        }
        /* Navbar */
        .navbar {
            background-color: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--primary) !important;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, #064e3b 100%);
            padding: 60px 0;
            color: white;
            border-radius: 20px;
            margin: 20px;
            box-shadow: 0 10px 25px rgba(21, 128, 61, 0.2);
            text-align: center;
        }
        .hero h1 { font-weight: 800; letter-spacing: -1px; margin-bottom: 15px; }
        .hero p { font-size: 1.1rem; opacity: 0.9; }

        /* Product Cards */
        .product-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
            border-color: #d1d5db;
        }
        .product-img-wrap {
            height: 160px;
            background: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #9ca3af;
        }
        .product-info { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .product-title { font-weight: 700; font-size: 1.1rem; margin-bottom: 5px; color: var(--text-dark); }
        .product-category { font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 15px; }
        .product-price { font-size: 1.3rem; font-weight: 800; color: var(--primary); }
        .product-stock { font-size: 0.85rem; color: #059669; background: #d1fae5; padding: 3px 8px; border-radius: 20px; font-weight: 600; }
        
        .btn-add {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px;
            font-weight: 600;
            width: 100%;
            margin-top: auto;
            transition: all 0.2s;
        }
        .btn-add:hover { background: var(--primary-hover); transform: scale(1.02); }
        .btn-add:active { transform: scale(0.98); }

        /* Cart Offcanvas */
        .offcanvas-header { border-bottom: 1px solid #e5e7eb; }
        .cart-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px dashed #e5e7eb;
        }
        .cart-item-info h6 { font-size: 0.95rem; font-weight: 700; margin: 0; }
        .cart-item-info small { color: var(--text-muted); font-size: 0.8rem; }
        .qty-controls {
            display: flex;
            align-items: center;
            background: var(--secondary);
            border-radius: 8px;
            padding: 2px;
        }
        .qty-controls button {
            border: none;
            background: white;
            border-radius: 6px;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-dark);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .qty-controls input {
            width: 40px;
            border: none;
            background: transparent;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        /* Modal Yape */
        .yape-qr {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 15px;
            border: 2px dashed #00e0a1;
            padding: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <img src="assets/logo_unp.png" alt="Logo" height="30" onerror="this.src='https://via.placeholder.com/30?text=UNP'">
                <span>Granja UNP <small class="text-muted fw-normal fs-6">Click & Collect</small></span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="btn btn-light btn-sm fw-semibold d-none d-md-block">Acceso Personal</a>
                <button class="btn btn-dark position-relative rounded-pill px-3 fw-semibold shadow-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas">
                    <i class="bi bi-bag-fill me-1"></i> Mi Cesta
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" id="cartBadge">
                        0
                    </span>
                </button>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Hero -->
        <div class="hero">
            <h1>Productos Frescos Directo a ti</h1>
            <p>Reserva online, paga por Yape y recoge en la Granja UNP.</p>
        </div>

        <!-- Catálogo -->
        <div class="row g-4 my-4" id="catalogoContainer">
            <!-- Cargando -->
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-success" role="status"></div>
                <p class="mt-2 text-muted fw-semibold">Cargando catálogo...</p>
            </div>
        </div>
    </div>

    <!-- Offcanvas Carrito -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas">
        <div class="offcanvas-header bg-light">
            <h5 class="offcanvas-title fw-bold"><i class="bi bi-bag-check-fill text-success me-2"></i>Tu Cesta</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            <div class="flex-grow-1 overflow-auto px-4 py-2" id="cartItemsContainer">
                <div class="text-center text-muted py-5 mt-5">
                    <i class="bi bi-basket2 fs-1 mb-3 d-block"></i>
                    <p>Tu cesta está vacía</p>
                </div>
            </div>
            <div class="p-4 bg-light border-top mt-auto">
                <div class="d-flex justify-content-between mb-3 fw-bold fs-5 text-dark">
                    <span>Total:</span>
                    <span id="cartTotal">S/ 0.00</span>
                </div>
                <button class="btn btn-success w-100 py-3 fw-bold rounded-3 shadow-sm fs-6" id="btnCheckout" disabled>
                    Proceder al Pago <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Checkout & Yape -->
    <div class="modal fade" id="modalCheckout" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                    <h4 class="modal-title fw-bold">Finalizar Compra</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4">
                    
                    <!-- Paso 1: Datos -->
                    <div id="stepDatos">
                        <p class="text-muted small mb-4">Ingresa tus datos para registrar el pedido y poder recogerlo en nuestras instalaciones.</p>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">DNI</label>
                            <input type="text" class="form-control" id="coDni" maxlength="8">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Nombres</label>
                                <input type="text" class="form-control" id="coNombres">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Apellidos</label>
                                <input type="text" class="form-control" id="coApellidos">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Teléfono / Celular</label>
                            <input type="text" class="form-control" id="coTelefono" maxlength="15">
                        </div>
                        <button class="btn btn-dark w-100 py-2.5 fw-bold rounded-3" id="btnContinuarPago">Continuar a Pago</button>
                    </div>

                    <!-- Paso 2: Pago Yape -->
                    <div id="stepPago" class="d-none text-center py-2">
                        <div class="badge bg-dark rounded-pill px-3 py-2 mb-3 fs-6">Total a Pagar: <span id="yapeTotal"></span></div>
                        <p class="fw-bold" style="color: #00e0a1;">Escanea el QR de Yape</p>
                        <!-- QR Falso para diseño -->
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=YAPE_GRANJA_UNP" class="yape-qr" alt="QR Yape">
                        <p class="fw-semibold text-muted small mb-1">Titular: Universidad Nacional de Piura</p>
                        
                        <hr class="my-4 dashed">

                        <div class="text-start">
                            <label class="form-label fw-bold text-dark">N° de Operación Yape</label>
                            <input type="text" class="form-control form-control-lg text-center fw-bold text-success font-monospace" id="coOperacion" placeholder="Ej: 12345678" maxlength="12">
                            <div class="form-text mt-2"><i class="bi bi-info-circle"></i> Ingresa los dígitos de la operación confirmada.</div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button class="btn btn-light w-50 fw-semibold" id="btnVolverDatos">Volver</button>
                            <button class="btn btn-success w-50 fw-bold shadow" id="btnConfirmarPedido">Confirmar Pedido</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let catalogo = [];
        let cart = [];

        document.addEventListener('DOMContentLoaded', () => {
            cargarCatalogo();
        });

        async function cargarCatalogo() {
            try {
                const res = await fetch('./controllers/C_Ecommerce.php?action=catalogo');
                const json = await res.json();
                if (json.success) {
                    catalogo = json.data;
                    renderCatalogo();
                }
            } catch (e) {
                console.error(e);
            }
        }

        function renderCatalogo() {
            const container = document.getElementById('catalogoContainer');
            if (catalogo.length === 0) {
                container.innerHTML = '<div class="col-12 text-center py-5 text-muted">No hay productos disponibles por el momento.</div>';
                return;
            }

            let html = '';
            catalogo.forEach(item => {
                // Icono según categoría
                let icon = 'bi-box-seam';
                let cat = item.categoria.toLowerCase();
                if (cat.includes('ave') || cat.includes('pavo')) icon = 'bi-twitter';
                else if (cat.includes('huevo')) icon = 'bi-egg-fill';
                else if (cat.includes('cerdo')) icon = 'bi-piggy-bank-fill';

                html += `
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="product-card">
                            <div class="product-img-wrap">
                                <i class="bi ${icon}"></i>
                            </div>
                            <div class="product-info">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="product-category">${item.categoria}</div>
                                    <div class="product-stock">${item.stock_piezas} disp.</div>
                                </div>
                                <h3 class="product-title">${item.nombre}</h3>
                                <div class="mt-auto pt-3">
                                    <div class="product-price mb-3">S/ ${parseFloat(item.precio_unitario).toFixed(2)} <span class="fs-6 text-muted fw-normal">/ ${item.unidad}</span></div>
                                    <button class="btn-add" onclick="agregarAlCarrito(${item.id_insumo})">
                                        <i class="bi bi-cart-plus-fill me-1"></i> Añadir a Cesta
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        window.agregarAlCarrito = (id) => {
            const producto = catalogo.find(p => p.id_insumo == id);
            if (!producto) return;

            const existing = cart.find(i => i.id_insumo == id);
            if (existing) {
                if (existing.cantidad + 1 > producto.stock_piezas) {
                    Swal.fire({ icon: 'warning', text: 'Stock máximo alcanzado' });
                    return;
                }
                existing.cantidad += 1;
                // Si es pesado, asumimos que se vende por unidad estándar en web y luego en POS se regulariza?
                // Mejor, para pavos (contenido_estandar = NULL), en e-commerce solo podemos vender la pieza.
                // Asumimos un peso estándar de 10kg para pre-cobro.
                let peso_aprox = producto.contenido_estandar ? parseFloat(producto.contenido_estandar) : 10.0;
                existing.peso_neto = existing.cantidad * peso_aprox;
                existing.subtotal = (producto.contenido_estandar ? existing.cantidad : existing.peso_neto) * parseFloat(producto.precio_unitario);
            } else {
                let peso_aprox = producto.contenido_estandar ? parseFloat(producto.contenido_estandar) : 10.0;
                let precioBase = parseFloat(producto.precio_unitario);
                let subtotal = (producto.contenido_estandar ? 1 : peso_aprox) * precioBase;

                cart.push({
                    id_insumo: producto.id_insumo,
                    nombre: producto.nombre,
                    precio: precioBase,
                    cantidad: 1,
                    peso_neto: peso_aprox,
                    stock_max: producto.stock_piezas,
                    subtotal: subtotal,
                    unidad: producto.unidad,
                    es_pesado: !producto.contenido_estandar
                });
            }
            actualizarCarrito();
            
            // Feedback visual
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('cartOffcanvas'));
            offcanvas.show();
        };

        window.modificarCart = (id, change) => {
            const item = cart.find(i => i.id_insumo == id);
            if (!item) return;

            let newQty = item.cantidad + change;
            if (newQty <= 0) {
                cart = cart.filter(i => i.id_insumo != id);
            } else {
                if (newQty > item.stock_max) {
                    Swal.fire({ icon: 'warning', text: 'Stock máximo alcanzado' });
                    return;
                }
                item.cantidad = newQty;
                let unitPeso = item.peso_neto / (item.cantidad - change);
                item.peso_neto = newQty * unitPeso;
                item.subtotal = (item.es_pesado ? item.peso_neto : item.cantidad) * item.precio;
            }
            actualizarCarrito();
        };

        function actualizarCarrito() {
            const container = document.getElementById('cartItemsContainer');
            const badge = document.getElementById('cartBadge');
            const totalEl = document.getElementById('cartTotal');
            const btnCheckout = document.getElementById('btnCheckout');

            let qtyTotal = 0;
            let sumTotal = 0;
            let html = '';

            cart.forEach(item => {
                qtyTotal += item.cantidad;
                sumTotal += item.subtotal;

                let extraInfo = item.es_pesado ? `<br><small class="text-primary">Peso aprox: ${item.peso_neto} Kg</small>` : '';

                html += `
                    <div class="cart-item">
                        <div class="cart-item-info pe-2">
                            <h6 class="text-truncate" style="max-width: 150px;">${item.nombre}</h6>
                            <small>S/ ${item.precio.toFixed(2)} ${item.es_pesado ? 'x Kg' : '/ ' + item.unidad}</small>
                            ${extraInfo}
                            <div class="fw-bold mt-1 text-dark">S/ ${item.subtotal.toFixed(2)}</div>
                        </div>
                        <div class="qty-controls">
                            <button onclick="modificarCart(${item.id_insumo}, -1)"><i class="bi bi-dash"></i></button>
                            <input type="text" value="${item.cantidad}" readonly>
                            <button onclick="modificarCart(${item.id_insumo}, 1)"><i class="bi bi-plus"></i></button>
                        </div>
                    </div>
                `;
            });

            if (cart.length === 0) {
                html = `
                    <div class="text-center text-muted py-5 mt-5">
                        <i class="bi bi-basket2 fs-1 mb-3 d-block"></i>
                        <p>Tu cesta está vacía</p>
                    </div>
                `;
                btnCheckout.disabled = true;
            } else {
                btnCheckout.disabled = false;
            }

            container.innerHTML = html;
            badge.innerText = qtyTotal;
            totalEl.innerText = `S/ ${sumTotal.toFixed(2)}`;
            document.getElementById('yapeTotal').innerText = `S/ ${sumTotal.toFixed(2)}`;
        }

        // Checkout Logic
        const modalCheckoutObj = new bootstrap.Modal(document.getElementById('modalCheckout'));
        
        document.getElementById('btnCheckout').addEventListener('click', () => {
            document.getElementById('stepDatos').classList.remove('d-none');
            document.getElementById('stepPago').classList.add('d-none');
            modalCheckoutObj.show();
        });

        document.getElementById('btnContinuarPago').addEventListener('click', () => {
            const dni = document.getElementById('coDni').value.trim();
            const nom = document.getElementById('coNombres').value.trim();
            const tel = document.getElementById('coTelefono').value.trim();

            if (!dni || !nom || !tel) {
                Swal.fire({ icon: 'warning', text: 'Por favor completa DNI, Nombres y Teléfono.' });
                return;
            }
            if (dni.length !== 8) {
                Swal.fire({ icon: 'warning', text: 'El DNI debe tener 8 dígitos.' });
                return;
            }

            document.getElementById('stepDatos').classList.add('d-none');
            document.getElementById('stepPago').classList.remove('d-none');
        });

        document.getElementById('btnVolverDatos').addEventListener('click', () => {
            document.getElementById('stepPago').classList.add('d-none');
            document.getElementById('stepDatos').classList.remove('d-none');
        });

        document.getElementById('btnConfirmarPedido').addEventListener('click', async () => {
            const nro_op = document.getElementById('coOperacion').value.trim();
            if (!nro_op || nro_op.length < 6) {
                Swal.fire({ icon: 'warning', text: 'Ingresa un Número de Operación válido de Yape.' });
                return;
            }

            const btn = document.getElementById('btnConfirmarPedido');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

            let total = cart.reduce((acc, el) => acc + el.subtotal, 0);

            const payload = {
                cliente: {
                    dni: document.getElementById('coDni').value.trim(),
                    nombres: document.getElementById('coNombres').value.trim(),
                    apellidos: document.getElementById('coApellidos').value.trim(),
                    telefono: document.getElementById('coTelefono').value.trim(),
                    direccion: ''
                },
                nro_operacion: nro_op,
                total: total,
                carrito: cart
            };

            try {
                const res = await fetch('./controllers/C_Ecommerce.php?action=crear_pedido', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                
                if (result.success) {
                    modalCheckoutObj.hide();
                    Swal.fire({
                        icon: 'success',
                        title: '¡Pedido Recibido!',
                        text: `Tu pedido #${result.id_pedido} ha sido registrado. Acércate a la Granja UNP para recoger tus productos.`,
                        confirmButtonColor: '#15803d'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: result.mensaje });
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.' });
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Confirmar Pedido';
            }
        });
    </script>
</body>
</html>
