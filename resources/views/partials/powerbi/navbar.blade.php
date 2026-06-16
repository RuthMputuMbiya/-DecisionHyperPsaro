{{-- resources/views/partials/powerbi/navbar.blade.php --}}
<div class="navbar-pbi d-flex justify-content-between align-items-center">
    <div>
        <h5 class="mb-0">@yield('page-title', 'Tableau de bord')</h5>
        @hasSection('breadcrumb')
            <small class="text-muted">@yield('breadcrumb')</small>
        @endif
    </div>
    <div class="d-flex align-items-center gap-3">
        @include('partials.powerbi.currency-selector')
        @include('partials.powerbi.user-menu')
    </div>
</div>