<?php
$moduloActual = isset($_GET['modulo']) ? $_GET['modulo'] : ($_SESSION['rol'] === 'Administrador' ? 'dashboard' : 'nueva-venta');
$rol = $_SESSION['rol'];
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon" style="background: transparent; overflow: hidden; display: flex; align-items: center; justify-content: center; width: 36px; height: 36px;">
            <img src="assets/logo_unp.png" alt="UNP" style="max-width: 100%; max-height: 100%; object-fit: contain;">
        </div>
        <div class="brand-text">
            <h6 class="mb-0 fw-bold">Granja POS</h6>
            <small class="text-white-50" style="font-size: 10px;">Inventario & Ventas</small>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <!-- Dashboard group -->
        <?php if ($rol === 'Administrador'): ?>
            <a href="index.php?modulo=dashboard" class="menu-item <?php echo $moduloActual === 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
        <?php endif; ?>

        <!-- Inventario Group -->
        <?php if ($rol === 'Administrador'): ?>
            <div class="menu-header">Inventario</div>
            <a href="index.php?modulo=categorias" class="menu-item <?php echo $moduloActual === 'categorias' ? 'active' : ''; ?>">
                <i class="bi bi-tags-fill"></i>
                <span>Categorías</span>
            </a>
            <a href="index.php?modulo=insumos" class="menu-item <?php echo $moduloActual === 'insumos' ? 'active' : ''; ?>">
                <i class="bi bi-box-seam-fill"></i>
                <span>Insumos</span>
            </a>
            <a href="index.php?modulo=kardex" class="menu-item <?php echo $moduloActual === 'kardex' ? 'active' : ''; ?>">
                <i class="bi bi-journal-bookmark-fill"></i>
                <span>Kardex</span>
            </a>
        <?php endif; ?>

        <!-- Administración Group -->
        <div class="menu-header">Administración</div>
        <?php if ($rol === 'Administrador'): ?>
            <a href="index.php?modulo=usuarios" class="menu-item <?php echo $moduloActual === 'usuarios' ? 'active' : ''; ?>">
                <i class="bi bi-people-fill"></i>
                <span>Usuarios</span>
            </a>
        <?php endif; ?>
        <a href="index.php?modulo=clientes" class="menu-item <?php echo $moduloActual === 'clientes' ? 'active' : ''; ?>">
            <i class="bi bi-person-badge-fill"></i>
            <span>Clientes</span>
        </a>

        <!-- Ventas Group -->
        <div class="menu-header">Ventas & Caja</div>
        <a href="index.php?modulo=caja" class="menu-item <?php echo $moduloActual === 'caja' ? 'active' : ''; ?>">
            <i class="bi bi-cash-coin"></i>
            <span>Mi Caja</span>
        </a>
        <a href="index.php?modulo=nueva-venta" class="menu-item <?php echo $moduloActual === 'nueva-venta' ? 'active' : ''; ?>">
            <i class="bi bi-cart-fill"></i>
            <span>Nueva Venta</span>
        </a>
        <a href="index.php?modulo=historial" class="menu-item <?php echo $moduloActual === 'historial' ? 'active' : ''; ?>">
            <i class="bi bi-receipt-cutoff"></i>
            <span>Historial</span>
        </a>

        <!-- Análisis Group -->
        <?php if ($rol === 'Administrador'): ?>
            <div class="menu-header">Análisis & Control</div>
            <a href="index.php?modulo=control-cajas" class="menu-item <?php echo $moduloActual === 'control-cajas' ? 'active' : ''; ?>">
                <i class="bi bi-safe-fill"></i>
                <span>Control de Cajas</span>
            </a>
            <a href="index.php?modulo=reportes" class="menu-item <?php echo $moduloActual === 'reportes' ? 'active' : ''; ?>">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Reportes</span>
            </a>
        <?php endif; ?>
    </div>
</aside>
