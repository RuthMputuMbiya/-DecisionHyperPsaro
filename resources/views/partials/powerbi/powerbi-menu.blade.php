{{-- resources/views/partials/powerbi-menu.blade.php --}}
@php
    $user = Auth::user();
@endphp

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