{{-- resources/views/layouts/powerbi.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            --powerbi-dark: #1E1E2E;
            --powerbi-gray: #2D2D3D;
            --powerbi-light: #F3F2F1;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: var(--powerbi-light);
        }
        
        /* Power BI Sidebar */
        .powerbi-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: var(--powerbi-dark);
            color: white;
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .powerbi-sidebar-header {
            padding: 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .powerbi-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .powerbi-logo-icon {
            width: 36px;
            height: 36px;
            background: var(--powerbi-yellow);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--powerbi-dark);
            font-weight: 800;
        }
        
        .powerbi-logo-text {
            font-size: 18px;
            font-weight: 700;
        }
        
        .powerbi-logo-text span {
            color: var(--powerbi-yellow);
        }
        
        /* Navigation Power BI */
        .powerbi-nav {
            padding: 20px 16px;
            list-style: none;
        }
        
        .powerbi-nav-item {
            margin-bottom: 4px;
        }
        
        .powerbi-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #a0a0b0;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.2s;
            font-size: 14px;
        }
        
        .powerbi-nav-link i {
            width: 20px;
            font-size: 18px;
        }
        
        .powerbi-nav-link:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }
        
        .powerbi-nav-link.active {
            background: var(--powerbi-blue);
            color: white;
        }
        
        /* Main Content Power BI */
        .powerbi-main {
            margin-left: 260px;
            padding: 20px;
        }
        
        /* Power BI Navbar */
        .powerbi-navbar {
            background: white;
            border-radius: 16px;
            padding: 12px 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        /* Power BI Cards */
        .powerbi-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .powerbi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        /* KPI Cards Power BI Style */
        .powerbi-kpi {
            background: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            border-bottom: 3px solid;
            transition: all 0.2s;
        }
        
        .powerbi-kpi:hover {
            transform: translateY(-3px);
        }
        
        .powerbi-kpi-value {
            font-size: 36px;
            font-weight: 800;
            margin: 10px 0;
        }
        
        .powerbi-kpi-title {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #666;
        }
        
        /* Animations Power BI */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .loading-pulse {
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        /* Currency Selector */
        .currency-selector {
            display: flex;
            gap: 5px;
            background: #f1f5f9;
            padding: 5px;
            border-radius: 40px;
        }
        
        .currency-btn {
            background: none;
            border: none;
            padding: 6px 14px;
            border-radius: 40px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: #475569;
        }
        
        .currency-btn.active {
            background: #1B6EF3;
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .powerbi-sidebar {
                transform: translateX(-100%);
            }
            .powerbi-sidebar.show {
                transform: translateX(0);
            }
            .powerbi-main {
                margin-left: 0;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    
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
        </div>
        
        {{-- 🔥 MENU DIRECTEMENT ICI SANS INCLUDE --}}
        <ul class="powerbi-nav">
            @php $user = Auth::user(); @endphp
            
            @if($user && $user->role)
                @if($user->role->code_role == 'DG')
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('direction.dashboard') ? 'active' : '' }}" 
                           href="{{ route('direction.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('direction.users.*') ? 'active' : '' }}" 
                           href="{{ route('direction.users.index') }}">
                            <i class="fas fa-users"></i> Utilisateurs
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('direction.services.*') ? 'active' : '' }}" 
                           href="{{ route('direction.services.index') }}">
                            <i class="fas fa-building"></i> Services
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('direction.indicateurs.*') ? 'active' : '' }}" 
                           href="{{ route('direction.indicateurs.index') }}">
                            <i class="fas fa-chart-line"></i> Indicateurs
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('direction.decisions.*') ? 'active' : '' }}" 
                           href="{{ route('direction.decisions.index') }}">
                            <i class="fas fa-gavel"></i> Décisions
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('direction.reports.*') ? 'active' : '' }}" 
                           href="{{ route('direction.reports.index') }}">
                            <i class="fas fa-file-alt"></i> Rapports
                        </a>
                    </li>
                @endif

                @if($user->role->code_role == 'CONSEILLER')
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}" 
                           href="{{ route('finance.dashboard') }}">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('finance.analyses.*') ? 'active' : '' }}" 
                           href="{{ route('finance.analyses.index') }}">
                            <i class="fas fa-chart-simple"></i> Analyses
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('finance.simulations.*') ? 'active' : '' }}" 
                           href="{{ route('finance.simulations.index') }}">
                            <i class="fas fa-flask"></i> Simulations
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('finance.comparaisons.*') ? 'active' : '' }}" 
                           href="{{ route('finance.comparaisons.index') }}">
                            <i class="fas fa-chart-line"></i> Comparaisons
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('finance.reporting.*') ? 'active' : '' }}" 
                           href="{{ route('finance.reporting.index') }}">
                            <i class="fas fa-file-pdf"></i> Reporting
                        </a>
                    </li>
                @endif

                @if($user->role->code_role == 'CHEF')
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('service.dashboard') ? 'active' : '' }}" 
                           href="{{ route('service.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('service.ventes.*') ? 'active' : '' }}" 
                           href="{{ route('service.ventes.index') }}">
                            <i class="fas fa-shopping-cart"></i> Ventes
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('service.achats.*') ? 'active' : '' }}" 
                           href="{{ route('service.achats.index') }}">
                            <i class="fas fa-truck"></i> Achats
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('service.depenses.*') ? 'active' : '' }}" 
                           href="{{ route('service.depenses.index') }}">
                            <i class="fas fa-receipt"></i> Dépenses
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('service.investissements.*') ? 'active' : '' }}" 
                           href="{{ route('service.investissements.index') }}">
                            <i class="fas fa-chart-line"></i> Investissements
                        </a>
                    </li>
                    <li class="powerbi-nav-item">
                        <a class="powerbi-nav-link {{ request()->routeIs('service.performances.index') ? 'active' : '' }}" 
                           href="{{ route('service.performances.index') }}">
                            <i class="fas fa-chart-simple"></i> Performances
                        </a>
                    </li>
                @endif

                <hr style="border-color: rgba(255,255,255,0.1); margin: 15px 0;">

                <li class="powerbi-nav-item">
                    <form method="POST" action="{{ route('logout') }}" class="w-100">
                        @csrf
                        <button type="submit" class="powerbi-nav-link w-100 text-start" style="background: none; border: none; color: #e02424;">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </button>
                    </form>
                </li>
            @endif
        </ul>
    </div>
    
    <div class="powerbi-main">
        <div class="powerbi-navbar">
            <div>
                <h5 class="mb-0">@yield('page-title')</h5>
                <small class="text-muted">@yield('breadcrumb')</small>
            </div>
            <div class="d-flex gap-3">
                {{-- Currency Selector direct --}}
                <div class="currency-selector">
                    <form action="{{ route('change-currency') }}" method="POST" class="d-flex gap-1 m-0">
                        @csrf
                        <button type="submit" name="devise" value="CDF" class="currency-btn {{ session('devise', 'USD') == 'CDF' ? 'active' : '' }}">CDF</button>
                        <button type="submit" name="devise" value="USD" class="currency-btn {{ session('devise', 'USD') == 'USD' ? 'active' : '' }}">USD</button>
                        <button type="submit" name="devise" value="EUR" class="currency-btn {{ session('devise', 'USD') == 'EUR' ? 'active' : '' }}">EUR</button>
                    </form>
                </div>
                
                {{-- User Menu direct --}}
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            {{ substr(Auth::user()->nom, 0, 1) }}
                        </div>
                        <div class="text-start">
                            <div class="fw-semibold small">{{ Auth::user()->nom }}</div>
                            <div class="text-muted small">{{ Auth::user()->matricule }}</div>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle"></i> Mon profil</a></li>
                        <li><a class="dropdown-item" href="{{ route('password.change') }}"><i class="fas fa-key"></i> Changer mot de passe</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="container-fluid px-0">
            @yield('content')
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('powerbiSidebar');
        function toggleSidebar() {
            sidebar.classList.toggle('show');
        }
    </script>
    
    @stack('scripts')
</body>
</html>