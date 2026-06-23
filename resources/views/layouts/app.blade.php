{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HYPER PSARO') - Système Décisionnel Power BI</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <style>
        :root {
            --primary: #1a56db;
            --primary-dark: #0e3a8a;
            --primary-light: #3f83f8;
            --secondary: #7e3af2;
            --success: #0e9f6e;
            --danger: #e02424;
            --warning: #f5a623;
            --info: #3abff8;
            --dark: #111928;
            --darker: #0a0f1c;
            --light: #f9fafb;
            --gray: #6b7280;
            --gray-light: #e5e7eb;
            --sidebar-width: 280px;
            --sidebar-collapsed: 72px;
            
            --gradient-primary: linear-gradient(135deg, #1a56db 0%, #7e3af2 100%);
            --gradient-success: linear-gradient(135deg, #0e9f6e 0%, #3abff8 100%);
            --gradient-danger: linear-gradient(135deg, #e02424 0%, #f5a623 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: var(--light);
            overflow-x: hidden;
        }
        
        /* ============================================
           SIDEBAR STYLE POWER BI - RESPONSIVE
        ============================================ */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--dark);
            color: white;
            z-index: 1050;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }
        
        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }
        
        .sidebar.collapsed .logo-text,
        .sidebar.collapsed .logo-sub,
        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .nav-label {
            display: none;
        }
        
        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 12px;
        }
        
        .sidebar.collapsed .nav-link i {
            margin: 0;
            font-size: 20px;
        }
        
        /* ============================================
           SIDEBAR HEADER
        ============================================ */
        .sidebar-header {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            min-width: 0;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
            flex-shrink: 0;
        }
        
        .logo-text {
            font-size: 18px;
            font-weight: 700;
            color: white;
            white-space: nowrap;
        }
        
        .logo-text span {
            color: #3abff8;
        }
        
        .logo-sub {
            font-size: 10px;
            color: var(--gray);
            margin-top: 2px;
            white-space: nowrap;
        }
        
        .sidebar-close-btn {
            display: none;
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 22px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .sidebar-close-btn:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }
        
        /* ============================================
           SIDEBAR OVERLAY POUR MOBILE
        ============================================ */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
            backdrop-filter: blur(4px);
            transition: all 0.3s;
        }
        .sidebar-overlay.show {
            display: block;
        }
        
        /* ============================================
           NAVIGATION
        ============================================ */
        .nav-menu {
            padding: 16px 12px;
            list-style: none;
            margin: 0;
        }
        
        .nav-item {
            margin-bottom: 2px;
            list-style: none;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.2s;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }
        
        .nav-link i {
            width: 22px;
            font-size: 18px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .nav-link .nav-label {
            white-space: nowrap;
            transition: opacity 0.2s;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.06);
            color: white;
        }
        
        .nav-link.active {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(26,86,219,0.3);
        }
        
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 24px;
            background: #3abff8;
            border-radius: 0 4px 4px 0;
        }
        
        .nav-divider {
            height: 1px;
            background: rgba(255,255,255,0.06);
            margin: 12px 16px;
        }
        
        .nav-logout {
            color: #f87171 !important;
        }
        .nav-logout:hover {
            background: rgba(248,113,113,0.1) !important;
            color: #fca5a5 !important;
        }
        
        /* ============================================
           MAIN CONTENT
        ============================================ */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--light);
        }
        
        .main-content.expanded {
            margin-left: var(--sidebar-collapsed);
        }
        
        /* ============================================
           TOP NAVBAR
        ============================================ */
        .top-navbar {
            background: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 999;
            flex-wrap: wrap;
            gap: 10px;
            border-bottom: 1px solid rgba(0,0,0,0.03);
        }
        
        .top-navbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .toggle-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: var(--gray);
            cursor: pointer;
            padding: 8px 10px;
            border-radius: 10px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .toggle-btn:hover {
            background: var(--light);
            color: var(--primary);
        }
        
        .page-title-area {
            min-width: 0;
        }
        
        .page-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .breadcrumb-custom {
            font-size: 12px;
            color: var(--gray);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .breadcrumb-custom a {
            color: var(--primary);
            text-decoration: none;
        }
        .breadcrumb-custom a:hover {
            text-decoration: underline;
        }
        
        /* ============================================
           USER MENU
        ============================================ */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .currency-selector {
            background: var(--light);
            border-radius: 40px;
            padding: 4px;
            display: flex;
            gap: 3px;
        }
        
        .currency-btn {
            background: none;
            border: none;
            padding: 5px 12px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--gray);
        }
        
        .currency-btn.active {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 2px 8px rgba(26,86,219,0.3);
        }
        
        .currency-btn:hover:not(.active) {
            background: rgba(0,0,0,0.05);
        }
        
        .user-avatar {
            width: 38px;
            height: 38px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.2s;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(26,86,219,0.3);
        }
        
        .user-info-text {
            display: none;
        }
        
        /* ============================================
           DROPDOWN MENU
        ============================================ */
        .dropdown-menu-custom {
            position: absolute;
            top: 52px;
            right: 0;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            width: 280px;
            display: none;
            z-index: 1060;
            padding: 8px 0;
            border: none;
        }
        
        .dropdown-menu-custom.show {
            display: block;
            animation: fadeInDown 0.25s ease;
        }
        
        .dropdown-menu-custom .dropdown-header {
            padding: 12px 20px 16px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .dropdown-menu-custom .dropdown-header .name {
            font-weight: 600;
            color: var(--dark);
        }
        
        .dropdown-menu-custom .dropdown-header .email {
            font-size: 13px;
            color: var(--gray);
        }
        
        .dropdown-menu-custom .dropdown-item {
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--dark);
            text-decoration: none;
            transition: all 0.2s;
            font-size: 14px;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }
        
        .dropdown-menu-custom .dropdown-item:hover {
            background: var(--light);
            color: var(--primary);
        }
        
        .dropdown-menu-custom .dropdown-item i {
            width: 20px;
            color: var(--gray);
        }
        
        .dropdown-menu-custom .dropdown-divider {
            height: 1px;
            background: var(--gray-light);
            margin: 8px 16px;
        }
        
        .dropdown-menu-custom .dropdown-item.text-danger i {
            color: var(--danger);
        }
        .dropdown-menu-custom .dropdown-item.text-danger:hover {
            background: rgba(224,36,36,0.05);
            color: var(--danger);
        }
        
        /* ============================================
           CONTENT AREA
        ============================================ */
        .content-area {
            padding: 20px 24px 30px;
        }
        
        /* ============================================
           CARD STYLES
        ============================================ */
        .card-powerbi {
            background: white;
            border-radius: 16px;
            border: none;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 20px;
        }
        
        .card-powerbi:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transform: translateY(-3px);
        }
        
        .card-header-powerbi {
            padding: 16px 20px;
            border-bottom: 1px solid var(--gray-light);
            background: none;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .card-body-powerbi {
            padding: 20px;
        }
        
        /* ============================================
           KPI CARDS
        ============================================ */
        .kpi-card {
            background: white;
            border-radius: 16px;
            padding: 18px 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            height: 100%;
        }
        
        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        
        .kpi-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray);
            margin-bottom: 4px;
            font-weight: 500;
        }
        
        .kpi-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 4px;
            line-height: 1.2;
        }
        
        .kpi-trend {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .trend-up { color: var(--success); }
        .trend-down { color: var(--danger); }
        
        /* ============================================
           ANIMATIONS
        ============================================ */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .fade-in {
            animation: fadeInDown 0.4s ease-out;
        }
        
        /* ============================================
           ALERTS
        ============================================ */
        .alert-powerbi {
            border-radius: 12px;
            border: none;
            padding: 14px 18px;
            margin-bottom: 20px;
        }
        
        .alert-success-powerbi {
            background: linear-gradient(135deg, #0e9f6e15, #3abff815);
            border-left: 4px solid var(--success);
            color: var(--success);
        }
        
        .alert-danger-powerbi {
            background: linear-gradient(135deg, #e0242415, #f5a62315);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }
        
        /* ============================================
           RESPONSIVE - MOBILE FIRST
        ============================================ */
        
        /* Tablettes et petits écrans */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px !important;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .sidebar.collapsed {
                width: 280px !important;
            }
            .sidebar.collapsed .logo-text,
            .sidebar.collapsed .logo-sub,
            .sidebar.collapsed .nav-link span,
            .sidebar.collapsed .nav-label {
                display: block !important;
            }
            .sidebar.collapsed .nav-link {
                justify-content: flex-start !important;
                padding: 12px 16px !important;
            }
            .sidebar.collapsed .nav-link i {
                margin-right: 12px !important;
                font-size: 18px !important;
            }
            
            .sidebar-close-btn {
                display: block;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            .main-content.expanded {
                margin-left: 0 !important;
            }
            
            .top-navbar {
                padding: 10px 14px;
                border-radius: 0;
            }
            
            .page-title {
                font-size: 15px;
            }
            .breadcrumb-custom {
                font-size: 11px;
            }
            
            .kpi-value {
                font-size: 22px;
            }
            
            .content-area {
                padding: 14px 14px 20px;
            }
        }
        
        /* Smartphones */
        @media (max-width: 576px) {
            .sidebar {
                width: 100% !important;
                max-width: 300px;
            }
            
            .top-navbar {
                padding: 8px 12px;
                flex-direction: row;
                flex-wrap: wrap;
            }
            
            .top-navbar-left {
                flex: 1;
                min-width: 0;
            }
            
            .page-title {
                font-size: 13px;
                max-width: 120px;
            }
            .breadcrumb-custom {
                font-size: 10px;
                display: none;
            }
            
            .toggle-btn {
                padding: 6px 8px;
                font-size: 18px;
            }
            
            .user-menu {
                gap: 8px;
            }
            
            .currency-selector {
                padding: 3px;
            }
            .currency-btn {
                padding: 3px 8px;
                font-size: 10px;
            }
            
            .user-avatar {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }
            
            .user-info-text {
                display: none !important;
            }
            
            .dropdown-menu-custom {
                width: 260px;
                right: -10px;
            }
            
            .kpi-value {
                font-size: 18px;
            }
            .kpi-title {
                font-size: 10px;
            }
            
            .content-area {
                padding: 10px 10px 16px;
            }
            
            .card-powerbi {
                border-radius: 12px;
                margin-bottom: 14px;
            }
            .card-header-powerbi {
                padding: 12px 14px;
                font-size: 13px;
            }
            .card-body-powerbi {
                padding: 14px;
            }
            
            .row.g-2 {
                --bs-gutter-y: 0.5rem;
            }
            .col-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }
        
        /* Très petits écrans */
        @media (max-width: 400px) {
            .sidebar {
                max-width: 280px;
            }
            
            .page-title {
                font-size: 12px;
                max-width: 80px;
            }
            
            .currency-btn {
                padding: 2px 6px;
                font-size: 9px;
            }
            
            .col-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .kpi-card {
                padding: 12px;
            }
            .kpi-value {
                font-size: 16px;
            }
        }
        
        /* ============================================
           SCROLLBAR
        ============================================ */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: var(--gray-light);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
        
        /* ============================================
           LOADING SPINNER
        ============================================ */
        .spinner-powerbi {
            width: 40px;
            height: 40px;
            border: 3px solid var(--gray-light);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    
    {{-- ============================================ --}}
    {{-- OVERLAY POUR MOBILE --}}
    {{-- ============================================ --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
    
    {{-- ============================================ --}}
    {{-- SIDEBAR --}}
    {{-- ============================================ --}}
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <div class="logo-text">HYPER <span>PSARO</span></div>
                    <div class="logo-sub">Système Décisionnel</div>
                </div>
            </a>
            <button class="sidebar-close-btn" onclick="closeSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <ul class="nav-menu">
            @include('partials.sidebar-menu')
        </ul>
    </div>
    
    {{-- ============================================ --}}
    {{-- MAIN CONTENT --}}
    {{-- ============================================ --}}
    <div class="main-content" id="mainContent">
        
        {{-- TOP NAVBAR --}}
        <div class="top-navbar">
            <div class="top-navbar-left">
                <button class="toggle-btn" id="toggleSidebar" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-title-area">
                    <div class="page-title text-ellipsis">@yield('page-title', 'Tableau de bord')</div>
                    <div class="breadcrumb-custom">@yield('breadcrumb', '')</div>
                </div>
            </div>
            
            <div class="user-menu">
                {{-- Currency Selector --}}
                <div class="currency-selector">
                    <form action="{{ route('change-currency') }}" method="POST" class="d-flex gap-1 m-0">
                        @csrf
                        <button type="submit" name="devise" value="CDF" 
                            class="currency-btn {{ session('devise', 'USD') == 'CDF' ? 'active' : '' }}">
                            CDF
                        </button>
                        <button type="submit" name="devise" value="USD" 
                            class="currency-btn {{ session('devise', 'USD') == 'USD' ? 'active' : '' }}">
                            USD
                        </button>
                        <button type="submit" name="devise" value="EUR" 
                            class="currency-btn {{ session('devise', 'USD') == 'EUR' ? 'active' : '' }}">
                            EUR
                        </button>
                    </form>
                </div>
                
                {{-- User Menu --}}
                <div class="position-relative">
                    <div class="user-avatar" id="userMenuBtn" title="{{ Auth::user()->nom ?? 'Utilisateur' }}">
                        {{ strtoupper(substr(Auth::user()->nom ?? 'U', 0, 1)) }}
                    </div>
                    <div class="dropdown-menu-custom" id="userDropdown">
                        <div class="dropdown-header">
                            <div class="name">{{ Auth::user()->nom ?? 'Utilisateur' }}</div>
                            <div class="email">{{ Auth::user()->email ?? '' }}</div>
                            <div class="mt-2">
                                <span class="badge bg-primary">
                                    {{ Auth::user()->role->nom_role ?? 'Rôle' }}
                                </span>
                            </div>
                        </div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-user-circle"></i> Mon profil
                        </a>
                        <a href="{{ route('password.change') }}" class="dropdown-item">
                            <i class="fas fa-key"></i> Changer mot de passe
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- CONTENT AREA --}}
        <div class="content-area fade-in">
            @if(session('success'))
                <div class="alert alert-success-powerbi alert-powerbi">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger-powerbi alert-powerbi">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                </div>
            @endif
            
            @yield('content')
        </div>
        
    </div>
    
    {{-- ============================================ --}}
    {{-- SCRIPTS --}}
    {{-- ============================================ --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // ============================================
        // GESTION DE LA SIDEBAR
        // ============================================
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const mainContent = document.getElementById('mainContent');
        
        function toggleSidebar() {
            if (window.innerWidth <= 992) {
                // Mode mobile
                const isOpen = sidebar.classList.toggle('open');
                overlay.classList.toggle('show', isOpen);
                document.body.style.overflow = isOpen ? 'hidden' : '';
            } else {
                // Mode desktop - collapse/expand
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            }
        }
        
        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
        
        // Fermer en appuyant sur Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeSidebar();
        });
        
        // Fermer en cliquant sur le contenu principal (mobile)
        mainContent.addEventListener('click', function(e) {
            if (window.innerWidth <= 992 && sidebar.classList.contains('open')) {
                // Ne pas fermer si on clique sur un élément qui ouvre un dropdown
                if (!e.target.closest('.dropdown-menu-custom') && !e.target.closest('.user-avatar')) {
                    closeSidebar();
                }
            }
        });
        
        // ============================================
        // USER DROPDOWN
        // ============================================
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        
        document.addEventListener('click', function() {
            userDropdown.classList.remove('show');
        });
        
        // ============================================
        // ACTIVE LINK HIGHLIGHTING
        // ============================================
        const currentUrl = window.location.href;
        document.querySelectorAll('.nav-link').forEach(function(link) {
            if (link.getAttribute('href') && currentUrl.includes(link.getAttribute('href'))) {
                link.classList.add('active');
            }
        });
        
        // ============================================
        // REDIMENSIONNEMENT
        // ============================================
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth > 992 && sidebar.classList.contains('open')) {
                    closeSidebar();
                    sidebar.classList.remove('open');
                }
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('show');
                }
            }, 250);
        });
        
        // ============================================
        // META VIEWPORT DYNAMIQUE
        // ============================================
        function updateViewport() {
            const viewport = document.querySelector('meta[name=viewport]');
            if (window.innerWidth < 576) {
                viewport.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';
            } else {
                viewport.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes';
            }
        }
        updateViewport();
        
        // ============================================
        // UTILITY - TEXT ELLIPSIS
        // ============================================
        document.querySelectorAll('.text-ellipsis').forEach(function(el) {
            if (el.scrollWidth > el.clientWidth) {
                el.title = el.textContent;
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>