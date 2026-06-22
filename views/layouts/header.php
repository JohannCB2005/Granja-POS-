<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Granja POS'; ?></title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Estilos de Diseño Premium Custom (Aesthetics) -->
    <style>
        :root {
            --gp-primary: #15803d; /* oklch(0.58 0.118 162) -> forest green */
            --gp-primary-hover: #166534;
            --gp-primary-light: #e6f4ea;
            --gp-background: #f8faf9;
            --gp-card: #ffffff;
            --gp-sidebar: #0f1712;
            --gp-sidebar-hover: #1b2620;
            --gp-sidebar-active: #15803d;
            --gp-text: #1f2937;
            --gp-text-muted: #6b7280;
            --gp-border: #e5e7eb;
            --font-sans: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--gp-background);
            color: var(--gp-text);
            overflow-x: hidden;
        }

        /* Sidebar styles */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--gp-sidebar);
            color: #ffffff;
            z-index: 1000;
            transition: all 0.3s ease;
            border-right: 1px solid rgba(255,255,255,0.05);
            display: flex;
            flex-col: column;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-b: 1px solid rgba(255,255,255,0.05);
        }

        .sidebar-brand-icon {
            background-color: var(--gp-primary);
            color: #ffffff;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 20px 12px;
        }

        .menu-header {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: rgba(255,255,255,0.3);
            margin-top: 20px;
            margin-bottom: 8px;
            padding-left: 12px;
        }

        .menu-header:first-child {
            margin-top: 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 4px;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .menu-item:hover {
            background-color: var(--gp-sidebar-hover);
            color: #ffffff;
        }

        .menu-item.active {
            background-color: var(--gp-primary);
            color: #ffffff;
            font-weight: 600;
        }

        .menu-item i {
            font-size: 18px;
        }

        /* Top Navbar */
        .top-navbar {
            height: 64px;
            background-color: var(--gp-card);
            border-bottom: 1px solid var(--gp-border);
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            z-index: 999;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
        }

        /* Main Content container */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            flex: 1;
            min-width: 0;
            padding: 80px 16px 16px 16px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Collapsed state */
        .sidebar.collapsed {
            width: 72px;
        }

        .sidebar.collapsed .brand-text, 
        .sidebar.collapsed .menu-item span,
        .sidebar.collapsed .menu-header {
            display: none;
        }

        .sidebar.collapsed .menu-item {
            justify-content: center;
            padding: 10px 0;
        }

        .sidebar.collapsed + .top-navbar {
            left: 72px;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: 72px;
            width: calc(100% - 72px);
        }

        /* Premium Cards */
        .gp-card {
            background-color: var(--gp-card);
            border: 1px solid var(--gp-border);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 1px 2px rgba(0,0,0,0.01);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .gp-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
        }

        .gp-btn-primary {
            background-color: var(--gp-primary);
            border-color: var(--gp-primary);
            color: #ffffff;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .gp-btn-primary:hover, .gp-btn-primary:focus {
            background-color: var(--gp-primary-hover);
            border-color: var(--gp-primary-hover);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(21, 128, 61, 0.15);
        }

        .gp-badge-success {
            background-color: var(--gp-primary-light);
            color: var(--gp-primary);
            border: 1px solid rgba(21, 128, 61, 0.2);
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .gp-badge-danger {
            background-color: #fee2e2;
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Mobile Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                left: -260px;
            }
            .sidebar.show {
                left: 0;
            }
            .top-navbar {
                left: 0;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding-left: 12px;
                padding-right: 12px;
            }
            .sidebar.collapsed {
                left: -260px;
            }
            .sidebar.collapsed.show {
                left: 0;
                width: 260px;
            }
            .sidebar.collapsed.show .brand-text, 
            .sidebar.collapsed.show .menu-item span,
            .sidebar.collapsed.show .menu-header {
                display: inline;
            }
            .sidebar.collapsed.show .menu-item {
                justify-content: start;
                padding: 10px 14px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper d-flex">
