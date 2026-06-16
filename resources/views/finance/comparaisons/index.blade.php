{{-- resources/views/finance/comparaisons/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Comparaisons')
@section('page-title', 'Analyse comparative')
@section('breadcrumb', 'Finance / Comparaisons')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-chart-simple me-2" style="color: #1B6EF3;"></i>
                Analyses comparatives
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }}
            </p>
        </div>
    </div>

    {{-- Cartes de navigation --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <a href="{{ route('finance.comparaisons.periodes') }}" class="text-decoration-none">
                <div class="powerbi-card text-center h-100" style="cursor: pointer;">
                    <div class="card-body p-4">
                        <div class="icon-circle mx-auto mb-3" style="width: 70px; height: 70px; background: linear-gradient(135deg, #1B6EF3, #1a56db); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-calendar-alt fa-3x text-white"></i>
                        </div>
                        <h5 class="mb-2">Comparaison par périodes</h5>
                        <p class="text-muted small mb-0">Comparez deux périodes distinctes et analysez les écarts</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4">
            <a href="{{ route('finance.comparaisons.services') }}" class="text-decoration-none">
                <div class="powerbi-card text-center h-100" style="cursor: pointer;">
                    <div class="card-body p-4">
                        <div class="icon-circle mx-auto mb-3" style="width: 70px; height: 70px; background: linear-gradient(135deg, #0e9f6e, #0a7c5a); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-building fa-3x text-white"></i>
                        </div>
                        <h5 class="mb-2">Comparaison par services</h5>
                        <p class="text-muted small mb-0">Comparez les performances entre les différents services</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-4">
            <a href="{{ route('finance.comparaisons.indicateurs') }}" class="text-decoration-none">
                <div class="powerbi-card text-center h-100" style="cursor: pointer;">
                    <div class="card-body p-4">
                        <div class="icon-circle mx-auto mb-3" style="width: 70px; height: 70px; background: linear-gradient(135deg, #f5a623, #e69500); border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-line fa-3x text-white"></i>
                        </div>
                        <h5 class="mb-2">Comparaison par indicateurs</h5>
                        <p class="text-muted small mb-0">Comparez tous les indicateurs entre eux</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Informations supplémentaires (sans graphiques) --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="powerbi-card text-center p-4">
                <i class="fas fa-chart-simple fa-3x text-primary mb-3"></i>
                <h5 class="mb-2">Analyse comparative avancée</h5>
                <p class="text-muted mb-0">
                    Sélectionnez l'un des trois types de comparaison ci-dessus pour analyser vos données financières.
                </p>
                <hr class="my-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="small text-muted">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Périodes
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">
                            <i class="fas fa-building text-success me-1"></i> Services
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">
                            <i class="fas fa-chart-line text-warning me-1"></i> Indicateurs
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection