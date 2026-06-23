{{-- resources/views/layouts/powerbi.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HYPER PSARO') - Power BI Dashboard</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --powerbi-yellow: #F2C811;
            --powerbi-blue: #1B6EF3;
            --powerbi-dark: #0f172a;
            --powerbi-gray: #1e293b;
            --powerbi-light: #f1f5f9;
            --sidebar-width: 260px;
            --sidebar-collapsed: 70px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: var(--powerbi-light);
            overflow-x: hidden;
        }
        
        /* ============================================ */
        /* POWER BI SIDEBAR - RESPONSIVE */
        /* ============================================ */
        .powerbi-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--powerbi-dark);
            color: white;
            z-index: 1050;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }
        
        .powerbi-sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .powerbi-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .powerbi-sidebar::-webkit-scrollbar-thumb {
            background: var(--powerbi-blue);
            border-radius: 10px;
        }
        
        .powerbi-sidebar-header {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .powerbi-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .powerbi-logo-icon {
            width: 40px;
            height: 40px;
            background: var(--powerbi-yellow);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--powerbi-dark);
            font-weight: 800;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .powerbi-logo-text {
            font-size: 18px;
            font-weight: 700;
            white-space: nowrap;
        }
        
        .powerbi-logo-text span {
            color: var(--powerbi-yellow);
        }
        
        /* Bouton fermeture sidebar mobile */
        .sidebar-close-btn {
            display: none;
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 24px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .sidebar-close-btn:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }
        
        /* Navigation */
        .powerbi-nav {
            padding: 16px 12px;
            list-style: none;
            margin: 0;
        }
        
        .powerbi-nav-item {
            margin-bottom: 2px;
        }
        
        .powerbi-nav-link {
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
        }
        
        .powerbi-nav-link i {
            width: 22px;
            font-size: 18px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .powerbi-nav-link .nav-label {
            white-space: nowrap;
            transition: opacity 0.2s;
        }
        
        .powerbi-nav-link:hover {
            background: rgba(255,255,255,0.06);
            color: white;
        }
        
        .powerbi-nav-link.active {
            background: var(--powerbi-blue);
            color: white;
            box-shadow: 0 4px 15px rgba(27,110,243,0.3);
        }
        
        .powerbi-nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 24px;
            background: var(--powerbi-yellow);
            border-radius: 0 4px 4px 0;
        }
        
        .powerbi-nav-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.06);
            margin: 12px 16px;
        }
        
        /* Bouton logout dans sidebar */
        .powerbi-nav-logout {
            color: #f87171 !important;
        }
        .powerbi-nav-logout:hover {
            background: rgba(248,113,113,0.1) !important;
            color: #fca5a5 !important;
        }
        
        /* ============================================ */
        /* OVERLAY POUR MOBILE */
        /* ============================================ */
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
        
        /* ============================================ */
        /* MAIN CONTENT */
        /* ============================================ */
        .powerbi-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 16px 20px 30px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* ============================================ */
        /* POWER BI NAVBAR */
        /* ============================================ */
        .powerbi-navbar {
            background: white;
            border-radius: 16px;
            padding: 10px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .powerbi-navbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        /* Bouton hamburger */
        .sidebar-toggle-btn {
            display: none;
            background: none;
            border: none;
            color: var(--powerbi-dark);
            font-size: 24px;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 10px;
            transition: all 0.2s;
        }
        .sidebar-toggle-btn:hover {
            background: var(--powerbi-light);
        }
        
        .powerbi-navbar-title h5 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            color: #0f172a;
        }
        .powerbi-navbar-title small {
            font-size: 0.75rem;
            color: #94a3b8;
        }
        
        .powerbi-navbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        /* Currency Selector */
        .currency-selector {
            display: flex;
            gap: 4px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 40px;
        }
        
        .currency-btn {
            background: none;
            border: none;
            padding: 5px 12px;
            border-radius: 40px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: #475569;
        }
        
        .currency-btn.active {
            background: var(--powerbi-blue);
            color: white;
        }
        .currency-btn:hover:not(.active) {
            background: #e2e8f0;
        }
        
        /* User Menu */
        .user-menu-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px 6px 6px;
            border-radius: 40px;
            border: none;
            background: #f1f5f9;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 13px;
            font-weight: 500;
        }
        .user-menu-btn:hover {
            background: #e2e8f0;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            color: white;
            flex-shrink: 0;
            background: linear-gradient(135deg, #1B6EF3, #7e3af2);
        }
        
        .user-info {
            text-align: left;
            line-height: 1.2;
        }
        .user-info .name {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
        }
        .user-info .role {
            font-size: 10px;
            color: #94a3b8;
        }
        
        /* Dropdown Menu */
        .dropdown-menu-custom {
            position: absolute;
            top: 50px;
            right: 0;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            width: 220px;
            display: none;
            z-index: 1060;
            padding: 8px 0;
            border: none;
        }
        .dropdown-menu-custom.show {
            display: block;
            animation: fadeInDown 0.25s ease;
        }
        .dropdown-menu-custom .dropdown-item {
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--powerbi-dark);
            text-decoration: none;
            transition: all 0.2s;
            font-size: 14px;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }
        .dropdown-menu-custom .dropdown-item:hover {
            background: var(--powerbi-light);
            color: var(--powerbi-blue);
        }
        .dropdown-menu-custom .dropdown-item i {
            width: 20px;
            color: #94a3b8;
        }
        .dropdown-menu-custom .dropdown-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 6px 16px;
        }
        .dropdown-menu-custom .dropdown-item.text-danger i {
            color: #e02424;
        }
        .dropdown-menu-custom .dropdown-item.text-danger:hover {
            background: rgba(224,36,36,0.05);
            color: #e02424;
        }
        
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* ============================================ */
        /* RESPONSIVE - MOBILE FIRST */
        /* ============================================ */
        @media (max-width: 992px) {
            .powerbi-sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            .powerbi-sidebar.open {
                transform: translateX(0);
            }
            .sidebar-close-btn {
                display: block;
            }
            .sidebar-toggle-btn {
                display: flex;
            }
            .powerbi-main {
                margin-left: 0;
                padding: 12px 14px 20px;
            }
            .powerbi-navbar {
                padding: 10px 14px;
                border-radius: 12px;
            }
            .powerbi-navbar-title h5 {
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            .powerbi-sidebar {
                width: 100%;
                max-width: 320px;
            }
            .powerbi-main {
                padding: 10px 10px 16px;
            }
            .powerbi-navbar {
                padding: 8px 12px;
                border-radius: 10px;
                flex-direction: row;
                flex-wrap: wrap;
            }
            .powerbi-navbar-left {
                flex: 1;
            }
            .powerbi-navbar-right {
                flex: 1;
                justify-content: flex-end;
            }
            .powerbi-navbar-title h5 {
                font-size: 0.8rem;
            }
            .powerbi-navbar-title small {
                font-size: 0.65rem;
                display: none;
            }
            .currency-selector {
                gap: 2px;
                padding: 3px;
            }
            .currency-btn {
                padding: 4px 8px;
                font-size: 10px;
            }
            .user-menu-btn {
                padding: 4px 8px 4px 4px;
            }
            .user-info .name {
                font-size: 11px;
            }
            .user-info .role {
                display: none;
            }
            .user-avatar {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
        }
        
        @media (max-width: 400px) {
            .powerbi-sidebar {
                max-width: 280px;
            }
            .powerbi-nav-link {
                padding: 10px 14px;
                font-size: 13px;
            }
            .powerbi-nav-link i {
                font-size: 16px;
                width: 20px;
            }
            .powerbi-logo-text {
                font-size: 16px;
            }
            .powerbi-logo-icon {
                width: 34px;
                height: 34px;
                font-size: 14px;
            }
            .currency-btn {
                padding: 3px 6px;
                font-size: 9px;
            }
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
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
    <div class="powerbi-sidebar" id="powerbiSidebar">
        <div class="powerbi-sidebar-header">
            <div class="powerbi-logo">
                <div class="powerbi-logo-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="powerbi-logo-text">
                    HYPER <span>PSARO</span>
                </div>
            </div>
            <button class="sidebar-close-btn" onclick="closeSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        {{-- NAVIGATION --}}
        <ul class="powerbi-nav">
            @php $user = Auth::user(); @endphp
            
            @if($user && $user->role)
                @if($user->role->code_role == 'DG')
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('direction.dashboard') ? 'active' : '' }}" 
                           href="{{ route('direction.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="nav-label">Dashboard</span>
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('direction.users.*') ? 'active' : '' }}" 
                           href="{{ route('direction.users.index') }}">
                            <i class="fas fa-users"></i>
                            <span class="nav-label">Utilisateurs</span>
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('direction.services.*') ? 'active' : '' }}" 
                           href="{{ route('direction.services.index') }}">
                            <i class="fas fa-building"></i>
                            <span class="nav-label">Services</span>
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('direction.indicateurs.*') ? 'active' : '' }}" 
                           href="{{ route('direction.indicateurs.index') }}">
                            <i class="fas fa-chart-line"></i>
                            <span class="nav-label">Indicateurs</span>
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('direction.decisions.*') ? 'active' : '' }}" 
                           href="{{ route('direction.decisions.index') }}">
                            <i class="fas fa-gavel"></i>
                            <span class="nav-label">Décisions</span>
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('direction.reports.*') ? 'active' : '' }}" 
                           href="{{ route('direction.reports.index') }}">
                            <i class="fas fa-file-alt"></i>
                            <span class="nav-label">Rapports</span>
                        </a>
                    </li>
                @endif

                @if($user->role->code_role == 'CONSEILLER')
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}" 
                           href="{{ route('finance.dashboard') }}">
                            <i class="fas fa-chart-line"></i>
                            <span class="nav-label">Dashboard</span>
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('finance.analyses.*') ? 'active' : '' }}" 
                           href="{{ route('finance.analyses.index') }}">
                            <i class="fas fa-chart-simple"></i>
                            <span class="nav-label">Analyses</span>
                        </a>
                    </li>
                   
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('finance.comparaisons.*') ? 'active' : '' }}" 
                           href="{{ route('finance.comparaisons.index') }}">
                            <i class="fas fa-chart-line"></i>
                            <span class="nav-label">Comparaisons</span>
                        </a>
                    </li>
                  
                @endif

                @if($user->role->code_role == 'CHEF')
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('service.dashboard') ? 'active' : '' }}" 
                           href="{{ route('service.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="nav-label">Dashboard</span>
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('service.ventes.*') ? 'active' : '' }}" 
                           href="{{ route('service.ventes.index') }}">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="nav-label">Ventes</span>
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('service.achats.*') ? 'active' : '' }}" 
                           href="{{ route('service.achats.index') }}">
                            <i class="fas fa-truck"></i>
                            <span class="nav-label">Achats</span>
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('service.depenses.*') ? 'active' : '' }}" 
                           href="{{ route('service.depenses.index') }}">
                            <i class="fas fa-receipt"></i>
                            <span class="nav-label">Dépenses</span>
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('service.investissements.*') ? 'active' : '' }}" 
                           href="{{ route('service.investissements.index') }}">
                            <i class="fas fa-chart-line"></i>
                            <span class="nav-label">Investissements</span>
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('service.performances.index') ? 'active' : '' }}" 
                           href="{{ route('service.performances.index') }}">
                            <i class="fas fa-chart-simple"></i>
                            <span class="nav-label">Performances</span>
                        </a>
                    </li>
                @endif

                <hr class="powerbi-nav-divider">

                <li class="powerbi-nav-item">
                    <form method="POST" action="{{ route('logout') }}" class="w-100">
                        @csrf
                        <button type="submit" class="powerbi-nav-link powerbi-nav-logout w-100 text-start" style="background: none; border: none;">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="nav-label">Déconnexion</span>
                        </button>
                    </form>
                </li>
            @endif
        </ul>
    </div>
    
    {{-- ============================================ --}}
    {{-- MAIN CONTENT --}}
    {{-- ============================================ --}}
    <div class="powerbi-main" id="powerbiMain">
        
        {{-- NAVBAR --}}
        <div class="powerbi-navbar">
            <div class="powerbi-navbar-left">
                <button class="sidebar-toggle-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="powerbi-navbar-title">
                    <h5 class="text-ellipsis" style="max-width: 120px;">@yield('page-title', 'Dashboard')</h5>
                    <small>@yield('breadcrumb', '')</small>
                </div>
            </div>
            <div class="powerbi-navbar-right">
                {{-- Currency Selector --}}
                <div class="currency-selector">
                    <form action="{{ route('change-currency') }}" method="POST" class="d-flex gap-1 m-0">
                        @csrf
                        <button type="submit" name="devise" value="CDF" class="currency-btn {{ session('devise', 'USD') == 'CDF' ? 'active' : '' }}">CDF</button>
                        <button type="submit" name="devise" value="USD" class="currency-btn {{ session('devise', 'USD') == 'USD' ? 'active' : '' }}">USD</button>
                        <button type="submit" name="devise" value="EUR" class="currency-btn {{ session('devise', 'USD') == 'EUR' ? 'active' : '' }}">EUR</button>
                    </form>
                </div>
                
                {{-- User Menu --}}
                <div class="position-relative" style="display: inline-block;">
                    <button class="user-menu-btn dropdown-toggle" type="button" onclick="toggleUserDropdown()">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->nom ?? 'U', 0, 1)) }}
                        </div>
                        <div class="user-info d-none d-sm-block">
                            <div class="name">{{ Auth::user()->nom ?? 'Utilisateur' }}</div>
                            <div class="role">{{ Auth::user()->role->nom_role ?? '' }}</div>
                        </div>
                    </button>
                    
                    {{-- Dropdown --}}
                    <div class="dropdown-menu-custom" id="userDropdown">
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
        
        {{-- CONTENU --}}
        <div class="container-fluid px-0">
            @yield('content')
        </div>
    </div>
    
    {{-- ============================================ --}}
    {{-- SCRIPTS --}}
    {{-- ============================================ --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ============================================
        // GESTION DE LA SIDEBAR
        // ============================================
        const sidebar = document.getElementById('powerbiSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const main = document.getElementById('powerbiMain');
        
        function toggleSidebar() {
            const isOpen = sidebar.classList.toggle('open');
            overlay.classList.toggle('show', isOpen);
            document.body.style.overflow = isOpen ? 'hidden' : '';
        }
        
        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
        
        // Fermer la sidebar en appuyant sur Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeSidebar();
        });
        
        // Fermer en cliquant sur le contenu principal (mobile)
        main.addEventListener('click', function() {
            if (window.innerWidth <= 992 && sidebar.classList.contains('open')) {
                closeSidebar();
            }
        });
        
        // ============================================
        // USER DROPDOWN
        // ============================================
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }
        
        // Fermer le dropdown en cliquant ailleurs
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('userDropdown');
            const btn = document.querySelector('.user-menu-btn');
            if (dropdown && !dropdown.contains(e.target) && !btn.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
        
        // ============================================
        // ACTIVE LINK
        // ============================================
        const currentUrl = window.location.href;
        document.querySelectorAll('.powerbi-nav-link').forEach(function(link) {
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
                }
            }, 250);
        });
    </script>
    
    @stack('scripts')
</body>
</html>