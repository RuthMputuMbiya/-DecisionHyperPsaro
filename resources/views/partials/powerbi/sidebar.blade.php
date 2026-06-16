{{-- resources/views/partials/powerbi/sidebar.blade.php --}}
@php
    $user = auth()->user();
@endphp

@if($user->isDG())
    <a class="nav-link {{ request()->routeIs('direction.dashboard') ? 'active' : '' }}" 
       href="{{ route('direction.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a class="nav-link {{ request()->routeIs('direction.users.*') ? 'active' : '' }}" 
       href="{{ route('direction.users.index') }}">
        <i class="fas fa-users"></i> Utilisateurs
    </a>
    <a class="nav-link {{ request()->routeIs('direction.services.*') ? 'active' : '' }}" 
       href="{{ route('direction.services.index') }}">
        <i class="fas fa-building"></i> Services
    </a>
    <a class="nav-link {{ request()->routeIs('direction.indicateurs.*') ? 'active' : '' }}" 
       href="{{ route('direction.indicateurs.index') }}">
        <i class="fas fa-chart-line"></i> Indicateurs
    </a>
    <a class="nav-link {{ request()->routeIs('direction.reports.*') ? 'active' : '' }}" 
       href="{{ route('direction.reports.index') }}">
        <i class="fas fa-file-alt"></i> Rapports
    </a>
    <a class="nav-link {{ request()->routeIs('direction.decisions.*') ? 'active' : '' }}" 
       href="{{ route('direction.decisions.index') }}">
        <i class="fas fa-gavel"></i> Décisions
    </a>
@endif

@if($user->isConseiller())
    <a class="nav-link {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}" 
       href="{{ route('finance.dashboard') }}">
        <i class="fas fa-chart-pie"></i> Dashboard
    </a>
    <a class="nav-link {{ request()->routeIs('finance.analyses.*') ? 'active' : '' }}" 
       href="{{ route('finance.analyses.index') }}">
        <i class="fas fa-magnifying-glass-chart"></i> Analyses
    </a>
    <a class="nav-link {{ request()->routeIs('finance.simulations.*') ? 'active' : '' }}" 
       href="{{ route('finance.simulations.index') }}">
        <i class="fas fa-flask"></i> Simulations
    </a>
    <a class="nav-link {{ request()->routeIs('finance.comparaisons.*') ? 'active' : '' }}" 
       href="{{ route('finance.comparaisons.index') }}">
        <i class="fas fa-chart-line"></i> Comparaisons
    </a>
    <a class="nav-link {{ request()->routeIs('finance.reporting.*') ? 'active' : '' }}" 
       href="{{ route('finance.reporting.index') }}">
        <i class="fas fa-file-pdf"></i> Reporting
    </a>
@endif

@if($user->isChef())
    <a class="nav-link {{ request()->routeIs('service.dashboard') ? 'active' : '' }}" 
       href="{{ route('service.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a class="nav-link {{ request()->routeIs('service.ventes.*') ? 'active' : '' }}" 
       href="{{ route('service.ventes.index') }}">
        <i class="fas fa-shopping-cart"></i> Ventes
    </a>
    <a class="nav-link {{ request()->routeIs('service.achats.*') ? 'active' : '' }}" 
       href="{{ route('service.achats.index') }}">
        <i class="fas fa-truck"></i> Achats
    </a>
    <a class="nav-link {{ request()->routeIs('service.depenses.*') ? 'active' : '' }}" 
       href="{{ route('service.depenses.index') }}">
        <i class="fas fa-receipt"></i> Dépenses
    </a>
    <a class="nav-link {{ request()->routeIs('service.investissements.*') ? 'active' : '' }}" 
       href="{{ route('service.investissements.index') }}">
        <i class="fas fa-chart-line"></i> Investissements
    </a>
    <a class="nav-link {{ request()->routeIs('service.performances.*') ? 'active' : '' }}" 
       href="{{ route('service.performances.index') }}">
        <i class="fas fa-chart-simple"></i> Performances
    </a>
@endif

<hr class="bg-secondary mt-auto">

<form method="POST" action="{{ route('logout') }}" class="mt-3">
    @csrf
    <button type="submit" class="nav-link text-danger w-100 text-start">
        <i class="fas fa-sign-out-alt"></i> Déconnexion
    </button>
</form>