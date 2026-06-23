{{-- resources/views/finance/comparaisons/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Comparaisons')
@section('page-title', 'Analyse comparative')
@section('breadcrumb', 'Finance / Comparaisons')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">

    {{-- ============================================ --}}
    {{-- EN-TÊTE --}}
    {{-- ============================================ --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold" style="color: #0f172a;">
                <i class="fas fa-chart-simple me-2" style="color: #1B6EF3;"></i>
                Analyses comparatives
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
                <span class="badge bg-success ms-2">
                    <i class="fas fa-sync-alt me-1 fa-spin"></i> Données en temps réel
                </span>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-outline-secondary btn-sm" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i>Actualiser
            </button>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- CARTES DE NAVIGATION --}}
    {{-- ============================================ --}}
    <div class="row g-3 g-md-4 mb-4">
        
        {{-- CARTE 1: PÉRIODES --}}
        <div class="col-12 col-md-4">
            <a href="{{ route('finance.comparaisons.periodes') }}" class="text-decoration-none">
                <div class="card comparison-card h-100" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); transition: all 0.3s; cursor: pointer; overflow: hidden;">
                    <div class="card-body p-4 text-center">
                        <div class="icon-circle mx-auto mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #1B6EF3, #1a56db); border-radius: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(27,110,243,0.3); transition: all 0.3s;">
                            <i class="fas fa-calendar-alt fa-3x text-white"></i>
                        </div>
                        <h5 class="mb-2 fw-bold" style="color: #0f172a;">Comparaison par périodes</h5>
                        <p class="text-muted small mb-3">Comparez deux périodes distinctes et analysez les écarts</p>
                        <span class="badge bg-primary">
                            <i class="fas fa-arrow-right me-1"></i>Analyser
                        </span>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-3">
                        <div class="d-flex justify-content-center gap-3">
                            <span class="badge bg-light text-dark"><i class="fas fa-calendar me-1"></i>Mensuel</span>
                            <span class="badge bg-light text-dark"><i class="fas fa-calendar me-1"></i>Annuel</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        
        {{-- CARTE 2: SERVICES --}}
        <div class="col-12 col-md-4">
            <a href="{{ route('finance.comparaisons.services') }}" class="text-decoration-none">
                <div class="card comparison-card h-100" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); transition: all 0.3s; cursor: pointer; overflow: hidden;">
                    <div class="card-body p-4 text-center">
                        <div class="icon-circle mx-auto mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #0e9f6e, #0a7c5a); border-radius: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(14,159,110,0.3); transition: all 0.3s;">
                            <i class="fas fa-building fa-3x text-white"></i>
                        </div>
                        <h5 class="mb-2 fw-bold" style="color: #0f172a;">Comparaison par services</h5>
                        <p class="text-muted small mb-3">Comparez les performances entre les différents services</p>
                        <span class="badge bg-success">
                            <i class="fas fa-arrow-right me-1"></i>Analyser
                        </span>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-3">
                        <div class="d-flex justify-content-center gap-3">
                            <span class="badge bg-light text-dark"><i class="fas fa-building me-1"></i>Top 5</span>
                            <span class="badge bg-light text-dark"><i class="fas fa-chart-bar me-1"></i>Performance</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        
        {{-- CARTE 3: INDICATEURS --}}
        <div class="col-12 col-md-4">
            <a href="{{ route('finance.comparaisons.indicateurs') }}" class="text-decoration-none">
                <div class="card comparison-card h-100" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); transition: all 0.3s; cursor: pointer; overflow: hidden;">
                    <div class="card-body p-4 text-center">
                        <div class="icon-circle mx-auto mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #f5a623, #e69500); border-radius: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(245,166,35,0.3); transition: all 0.3s;">
                            <i class="fas fa-chart-line fa-3x text-white"></i>
                        </div>
                        <h5 class="mb-2 fw-bold" style="color: #0f172a;">Comparaison par indicateurs</h5>
                        <p class="text-muted small mb-3">Comparez tous les indicateurs entre eux</p>
                        <span class="badge bg-warning" style="color: #000;">
                            <i class="fas fa-arrow-right me-1"></i>Analyser
                        </span>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-3">
                        <div class="d-flex justify-content-center gap-3">
                            <span class="badge bg-light text-dark"><i class="fas fa-chart-line me-1"></i>KPI</span>
                            <span class="badge bg-light text-dark"><i class="fas fa-chart-pie me-1"></i>Corrélation</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- INFORMATIONS SUPPLÉMENTAIRES --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3">
        <div class="col-12">
            <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); background: linear-gradient(135deg, #f8fafc, #f1f4f9);">
                <div class="card-body p-4 text-center">
                    <div class="row align-items-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <i class="fas fa-chart-simple fa-3x text-primary" style="opacity: 0.5;"></i>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-2 fw-bold" style="color: #0f172a;">Analyse comparative avancée</h5>
                            <p class="text-muted mb-0 small">
                                Sélectionnez l'un des trois types de comparaison ci-dessus pour analyser vos données financières en profondeur.
                            </p>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <span class="badge bg-primary"><i class="fas fa-calendar-alt me-1"></i>Périodes</span>
                                <span class="badge bg-success"><i class="fas fa-building me-1"></i>Services</span>
                                <span class="badge bg-warning" style="color: #000;"><i class="fas fa-chart-line me-1"></i>Indicateurs</span>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-4">
                            <div class="small text-muted">
                                <i class="fas fa-calendar-alt text-primary me-1"></i> Périodes
                            </div>
                            <div class="small fw-bold">Comparaison temporelle</div>
                        </div>
                        <div class="col-4">
                            <div class="small text-muted">
                                <i class="fas fa-building text-success me-1"></i> Services
                            </div>
                            <div class="small fw-bold">Benchmarking</div>
                        </div>
                        <div class="col-4">
                            <div class="small text-muted">
                                <i class="fas fa-chart-line text-warning me-1"></i> Indicateurs
                            </div>
                            <div class="small fw-bold">Analyse croisée</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ============================================
    // EFFET DE SURVOL DES CARTES
    // ============================================
    document.querySelectorAll('.comparison-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 12px 40px rgba(0,0,0,0.12)';
            this.querySelector('.icon-circle').style.transform = 'scale(1.05)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 16px rgba(0,0,0,0.06)';
            this.querySelector('.icon-circle').style.transform = 'scale(1)';
        });
    });

    // ============================================
    // FONCTION ACTUALISATION
    // ============================================
    function refreshData() {
        Swal.fire({
            title: '🔄 Actualisation',
            text: 'Les données sont en cours de mise à jour...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        setTimeout(() => { window.location.reload(); }, 1500);
    }
</script>
@endpush

@push('styles')
<style>
    .comparison-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .comparison-card .icon-circle {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .comparison-card:hover .icon-circle {
        transform: scale(1.05);
    }
    
    .comparison-card .badge {
        transition: all 0.3s;
    }
    
    .comparison-card:hover .badge {
        transform: scale(1.05);
    }
    
    .comparison-card .card-footer .badge {
        opacity: 0.7;
    }
    
    .comparison-card:hover .card-footer .badge {
        opacity: 1;
    }
    
    @media (max-width: 768px) {
        .comparison-card .icon-circle {
            width: 60px !important;
            height: 60px !important;
        }
        .comparison-card .icon-circle i {
            font-size: 2rem !important;
        }
        .comparison-card .card-body {
            padding: 1.5rem !important;
        }
    }
</style>
@endpush
@endsection