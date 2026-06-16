{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
           SIDEBAR STYLE POWER BI
        ============================================ */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            background: var(--dark);
            color: white;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar.collapsed {
            width: 80px;
        }
        
        .sidebar-header {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }
        
        .logo-text {
            font-size: 18px;
            font-weight: 700;
            color: white;
        }
        
        .logo-text span {
            color: #3abff8;
        }
        
        .sidebar.collapsed .logo-text,
        .sidebar.collapsed .logo-sub {
            display: none;
        }
        
        .logo-sub {
            font-size: 10px;
            color: var(--gray);
            margin-top: 2px;
        }
        
        /* Navigation */
        .nav-menu {
            padding: 20px 0;
        }
        
        .nav-item {
            list-style: none;
            margin: 4px 12px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #9ca3af;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.2s;
            font-size: 14px;
            font-weight: 500;
        }
        
        .nav-link i {
            width: 20px;
            font-size: 18px;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }
        
        .nav-link.active {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 10px rgba(26,86,219,0.3);
        }
        
        .sidebar.collapsed .nav-link span {
            display: none;
        }
        
        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 12px;
        }
        
        .sidebar.collapsed .nav-link i {
            margin: 0;
        }
        
        /* Nav divider */
        .nav-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 16px 20px;
        }
        
        /* ============================================
           MAIN CONTENT
        ============================================ */
        .main-content {
            margin-left: 280px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }
        
        .main-content.expanded {
            margin-left: 80px;
        }
        
        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .toggle-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: var(--gray);
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .toggle-btn:hover {
            background: var(--gray-light);
            color: var(--primary);
        }
        
        .page-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
        }
        
        .breadcrumb-custom {
            font-size: 13px;
            color: var(--gray);
        }
        
        .breadcrumb-custom a {
            color: var(--primary);
            text-decoration: none;
        }
        
        /* User Menu */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .currency-selector {
            background: var(--light);
            border-radius: 12px;
            padding: 6px 12px;
            display: flex;
            gap: 8px;
        }
        
        .currency-btn {
            background: none;
            border: none;
            padding: 4px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--gray);
        }
        
        .currency-btn.active {
            background: var(--gradient-primary);
            color: white;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }
        
        .dropdown-menu-custom {
            position: absolute;
            top: 60px;
            right: 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            width: 280px;
            display: none;
            z-index: 1000;
        }
        
        .dropdown-menu-custom.show {
            display: block;
            animation: fadeIn 0.2s ease;
        }
        
        /* Content Area */
        .content-area {
            padding: 24px;
        }
        
        /* ============================================
           CARD STYLES
        ============================================ */
        .card-powerbi {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.3s;
            margin-bottom: 24px;
        }
        
        .card-powerbi:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .card-header-powerbi {
            padding: 20px 24px;
            border-bottom: 1px solid var(--gray-light);
            background: none;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body-powerbi {
            padding: 24px;
        }
        
        /* KPI Cards */
        .kpi-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s;
            border-left: 4px solid;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .kpi-title {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gray);
            margin-bottom: 8px;
        }
        
        .kpi-value {
            font-size: 32px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .kpi-trend {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .trend-up { color: var(--success); }
        .trend-down { color: var(--danger); }
        
        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }
        
        .chart-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-left: 12px;
            border-left: 3px solid var(--primary);
        }
        
        /* Table Styles */
        .table-powerbi {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table-powerbi thead tr {
            background: var(--light);
            border-radius: 12px;
        }
        
        .table-powerbi th {
            padding: 14px 16px;
            font-size: 13px;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table-powerbi td {
            padding: 14px 16px;
            font-size: 14px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .table-powerbi tbody tr:hover {
            background: rgba(26,86,219,0.05);
            cursor: pointer;
        }
        
        /* Buttons */
        .btn-powerbi {
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            border: none;
        }
        
        .btn-primary-powerbi {
            background: var(--gradient-primary);
            color: white;
        }
        
        .btn-primary-powerbi:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26,86,219,0.3);
        }
        
        .btn-outline-powerbi {
            background: transparent;
            border: 1px solid var(--gray-light);
            color: var(--dark);
        }
        
        .btn-outline-powerbi:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        /* Alert */
        .alert-powerbi {
            border-radius: 16px;
            border: none;
            padding: 16px 20px;
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
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.4s ease-out;
        }
        
        .slide-in {
            animation: slideIn 0.4s ease-out;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .kpi-value {
                font-size: 24px;
            }
        }
        
        /* Scrollbar */
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
        
        /* Loading Spinner */
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
    
    <!-- SIDEBAR -->
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
        </div>
        
        <ul class="nav-menu">
            @include('partials.sidebar-menu')
        </ul>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="main-content" id="mainContent">
        
        <!-- TOP NAVBAR -->
        <div class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="toggle-btn" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <div class="page-title">@yield('page-title', 'Tableau de bord')</div>
                    <div class="breadcrumb-custom">@yield('breadcrumb')</div>
                </div>
            </div>
            
            <div class="user-menu">
                <!-- Currency Selector -->
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
                
                <!-- User Menu -->
                <div class="position-relative">
                    <div class="user-avatar" id="userMenuBtn">
                        {{ strtoupper(substr(Auth::user()->nom ?? 'U', 0, 1)) }}
                    </div>
                    <div class="dropdown-menu-custom" id="userDropdown">
                        <div class="p-3 border-bottom">
                            <div class="fw-bold">{{ Auth::user()->nom ?? 'Utilisateur' }}</div>
                            <div class="small text-muted">{{ Auth::user()->email ?? '' }}</div>
                            <div class="mt-1">
                                <span class="badge bg-primary">
                                    {{ Auth::user()->role->nom_role ?? Auth::user()->role ?? 'Rôle' }}
                                </span>
                            </div>
                        </div>
                        <div class="p-2">
                            <a href="{{ route('profile') }}" class="dropdown-item">
                                <i class="fas fa-user-circle"></i> Mon profil
                            </a>
                            <a href="{{ route('password.change') }}" class="dropdown-item">
                                <i class="fas fa-key"></i> Changer mot de passe
                            </a>
                            <hr class="my-2">
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
        </div>
        
        <!-- CONTENT AREA -->
        <div class="content-area fade-in">
            @if(session('success'))
                <div class="alert alert-success-powerbi alert-powerbi mb-4">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger-powerbi alert-powerbi mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                </div>
            @endif
            
            @yield('content')
        </div>
        
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Toggle sidebar
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');
        
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
        
        // Toggle user dropdown
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        
        document.addEventListener('click', () => {
            userDropdown.classList.remove('show');
        });
        
        // Active link highlighting
        const currentUrl = window.location.href;
        document.querySelectorAll('.nav-link').forEach(link => {
            if (currentUrl.includes(link.getAttribute('href'))) {
                link.classList.add('active');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>