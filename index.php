<?php
session_start();

// 1. Auth check
if (!isset($_SESSION['id_usuario'])) {
    require_once 'views/V_login.php';
    exit;
}

$rol = $_SESSION['rol'];

// 2. Default route based on role
$defaultModule = ($rol === 'Administrador') ? 'dashboard' : 'nueva-venta';
$modulo = isset($_GET['modulo']) ? $_GET['modulo'] : $defaultModule;

// 3. Define route permissions
$routes = [
    'dashboard' => ['Administrador'],
    'categorias' => ['Administrador'],
    'insumos' => ['Administrador', 'Vendedor'],
    'kardex' => ['Administrador'],
    'usuarios' => ['Administrador'],
    'clientes' => ['Administrador', 'Vendedor'],
    'nueva-venta' => ['Administrador', 'Vendedor'],
    'historial' => ['Administrador', 'Vendedor'],
    'reportes' => ['Administrador']
];

// 4. Validate route exists and is allowed for the user's role
if (!array_key_exists($modulo, $routes)) {
    $modulo = $defaultModule;
}

if (!in_array($rol, $routes[$modulo])) {
    // If not allowed, redirect to default
    header("Location: index.php?modulo=" . $defaultModule);
    exit;
}

// 5. Title for the header
$titles = [
    'dashboard' => 'Dashboard - Granja POS',
    'categorias' => 'Categorías - Granja POS',
    'insumos' => 'Insumos - Granja POS',
    'kardex' => 'Kardex - Granja POS',
    'usuarios' => 'Usuarios - Granja POS',
    'clientes' => 'Clientes - Granja POS',
    'nueva-venta' => 'Nueva Venta - Granja POS',
    'historial' => 'Historial de Ventas - Granja POS',
    'reportes' => 'Reportes - Granja POS'
];
$title = isset($titles[$modulo]) ? $titles[$modulo] : 'Granja POS';

// 6. Include layout and render module view
require_once 'views/layouts/header.php';
require_once 'views/layouts/sidebar.php';
require_once 'views/layouts/navbar.php';

echo '<main class="main-content">';
switch ($modulo) {
    case 'dashboard':
        require_once 'views/V_dashboard.php';
        break;
    case 'categorias':
        require_once 'views/V_categorias.php';
        break;
    case 'insumos':
        require_once 'views/V_insumos.php';
        break;
    case 'kardex':
        require_once 'views/V_kardex.php';
        break;
    case 'usuarios':
        require_once 'views/V_usuarios.php';
        break;
    case 'clientes':
        require_once 'views/V_clientes.php';
        break;
    case 'nueva-venta':
        require_once 'views/V_nueva_venta.php';
        break;
    case 'historial':
        require_once 'views/V_historial.php';
        break;
    case 'reportes':
        require_once 'views/V_reportes.php';
        break;
}
echo '</main>';

require_once 'views/layouts/footer.php';
?>