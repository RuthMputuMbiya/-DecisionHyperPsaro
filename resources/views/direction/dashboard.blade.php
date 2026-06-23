{{-- resources/views/direction/dashboard.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Direction Générale - Dashboard Power BI')
@section('page-title', 'Tableau de bord Direction Générale')
@section('breadcrumb', 'Dashboard / Vue d\'ensemble')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">
    
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="powerbi-badge" style="display: inline-block; background: rgba(27,110,243,0.1); padding: 6px 16px; border-radius: 50px; font-size: 12px; color: #1B6EF3;">
                <i class="fas fa-chart-line me-2"></i> Vue stratégique
            </div>
            <h2 class="mt-3 mb-1 fw-bold" style="color: #1e293b;">
                <i class="fas fa-crown me-2" style="color: #F2C811;"></i>
                Tableau de bord stratégique
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
                <span class="badge bg-success ms-2">
                    <i class="fas fa-sync-alt me-1 fa-spin"></i> Données en temps réel
                </span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i>Actualiser
            </button>
           
        </div>
    </div>

    <!-- Sélecteur de devise -->
    <div class="row mb-4">
        <div class="col-12 text-end">
            <div class="currency-selector" style="display: inline-flex; gap: 8px; background: #f1f5f9; padding: 6px; border-radius: 40px;">
                <form action="{{ route('change-currency') }}" method="POST" class="d-flex gap-1 m-0">
                    @csrf
                    <button type="submit" name="devise" value="USD" class="currency-btn {{ session('devise', 'USD') == 'USD' ? 'active' : '' }}">
                        $ USD
                    </button>
                    <button type="submit" name="devise" value="CDF" class="currency-btn {{ session('devise') == 'CDF' ? 'active' : '' }}">
                        FC CDF
                    </button>
                    <button type="submit" name="devise" value="EUR" class="currency-btn {{ session('devise') == 'EUR' ? 'active' : '' }}">
                        € EUR
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- KPI Cards -->
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="powerbi-kpi-card" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); border-radius: 16px; padding: 18px 20px; color: white; min-height: 130px;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title" style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Chiffre d'affaires</div>
                        <div class="kpi-value" style="font-size: 24px; font-weight: 800;">
                            {{ number_format($stats['total_ca'] ?? 0, 0, ',', ' ') }}
                        </div>
                        <div class="kpi-unit" style="font-size: 10px; opacity: 0.7;">
                            {{ session('devise', 'USD') }}
                        </div>
                    </div>
                    <div class="kpi-icon" style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
                <div class="kpi-trend mt-2">
                    @if(($stats['croissance_ca'] ?? 0) >= 0)
                        <span class="text-success"><i class="fas fa-arrow-up"></i> +{{ number_format($stats['croissance_ca'], 1) }}%</span>
                    @else
                        <span class="text-danger"><i class="fas fa-arrow-down"></i> {{ number_format($stats['croissance_ca'], 1) }}%</span>
                    @endif
                    <span style="font-size: 10px; opacity: 0.7;"> vs année précédente</span>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="powerbi-kpi-card" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); border-radius: 16px; padding: 18px 20px; color: white; min-height: 130px;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title" style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Marge brute</div>
                        <div class="kpi-value" style="font-size: 24px; font-weight: 800;">
                            {{ number_format($stats['total_marge'] ?? 0, 0, ',', ' ') }}
                        </div>
                        <div class="kpi-unit" style="font-size: 10px; opacity: 0.7;">
                            {{ session('devise', 'USD') }}
                        </div>
                    </div>
                    <div class="kpi-icon" style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-percent fa-2x"></i>
                    </div>
                </div>
                <div class="kpi-trend mt-2">
                    <span class="text-light" style="font-size: 12px;">Taux: {{ number_format($stats['marge_percent'], 1) }}%</span>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="powerbi-kpi-card" style="background: linear-gradient(135deg, #f5a623, #e69500); border-radius: 16px; padding: 18px 20px; color: white; min-height: 130px;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title" style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Trésorerie</div>
                        <div class="kpi-value" style="font-size: 24px; font-weight: 800;">
                            {{ number_format($stats['tresorerie'] ?? 0, 0, ',', ' ') }}
                        </div>
                        <div class="kpi-unit" style="font-size: 10px; opacity: 0.7;">
                            {{ session('devise', 'USD') }}
                        </div>
                    </div>
                    <div class="kpi-icon" style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-coins fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="powerbi-kpi-card" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); border-radius: 16px; padding: 18px 20px; color: white; min-height: 130px;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title" style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Investissements</div>
                        <div class="kpi-value" style="font-size: 24px; font-weight: 800;">
                            {{ number_format($stats['investissements'] ?? 0, 0, ',', ' ') }}
                        </div>
                        <div class="kpi-unit" style="font-size: 10px; opacity: 0.7;">
                            {{ session('devise', 'USD') }}
                        </div>
                    </div>
                    <div class="kpi-icon" style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chart-pie fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top 5 Services -->
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-12">
            <div class="powerbi-card">
                <div class="card-header-custom p-3 p-md-4 border-bottom">
                    <h5 class="mb-0 fs-6 fs-md-5">
                        <i class="fas fa-trophy me-2" style="color: #F2C811;"></i> 
                        Top 5 Services
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    @forelse($topServices as $index => $service)
                    <div class="service-item d-flex align-items-center justify-content-between mb-2 p-2 rounded" style="background: {{ $index % 2 == 0 ? '#f8fafc' : 'white' }};">
                        <div class="d-flex align-items-center gap-2">
                            <div class="service-rank" style="width: 28px; height: 28px; background: {{ $service['couleur'] ?? '#1a56db' }}; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <div class="fw-semibold small">{{ $service['name'] }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold small">{{ number_format($service['total'], 0, ',', ' ') }}</div>
                            <small class="text-muted">{{ session('devise', 'USD') }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle fa-2x d-block mb-2"></i>
                        <p class="small mb-0">Aucun service disponible</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <!-- Performances par service -->
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-12">
            <div class="powerbi-card">
                <div class="card-header-custom p-3 p-md-4 border-bottom">
                    <h5 class="mb-0 fs-6 fs-md-5">
                        <i class="fas fa-building me-2" style="color: #0e9f6e;"></i> 
                        Performances par Service
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-sm table-md">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-2">Service</th>
                                    <th class="py-2">CA</th>
                                    <th class="py-2">Marge</th>
                                    <th class="py-2">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($servicePerformances as $service)
                                <tr>
                                    <td class="py-2"><strong class="small">{{ $service['name'] }}</strong></td>
                                    <td class="py-2">
                                        {{ number_format($service['ca'] ?? 0, 0, ',', ' ') }} {{ session('devise', 'USD') }}
                                    </td>
                                    <td class="py-2">
                                        {{ number_format($service['marge'] ?? 0, 0, ',', ' ') }} {{ session('devise', 'USD') }}
                                    </td>
                                    <td class="py-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress" style="width: 80px; height: 5px;">
                                                <div class="progress-bar bg-{{ ($service['performance'] ?? 0) >= 70 ? 'success' : (($service['performance'] ?? 0) >= 50 ? 'warning' : 'danger') }}" 
                                                     style="width: {{ min($service['performance'] ?? 0, 100) }}%;"></div>
                                            </div>
                                            <span class="small">{{ $service['performance'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-database fa-2x text-muted d-block mb-2"></i>
                                        <p class="text-muted mb-0 small">Aucune donnée de performance disponible</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="row mt-3">
        <div class="col-12 text-center text-muted small">
            <hr class="my-2">
            <p><i class="fas fa-chart-line me-1"></i> Mise à jour : {{ now()->translatedFormat('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function refreshData() {
        Swal.fire({
            title: 'Actualisation...',
            text: 'Les données sont en cours de mise à jour',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
</script>
@endpush

@push('styles')
<style>
    .powerbi-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.06);
        background: #fff;
        transition: transform 0.3s, box-shadow 0.3s;
        overflow: hidden;
    }
    .powerbi-card:hover {
        box-shadow: 0 8px 32px rgba(0,0,0,0.10);
        transform: translateY(-2px);
    }
    .card-header-custom {
        background: linear-gradient(135deg, #f8fafc, #f1f4f9);
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .powerbi-kpi-card {
        transition: transform 0.3s, box-shadow 0.3s;
        cursor: default;
        position: relative;
        overflow: hidden;
    }
    .powerbi-kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }
    .currency-btn {
        transition: all 0.2s;
        cursor: pointer;
        background: none;
        border: none;
        padding: 5px 14px;
        border-radius: 40px;
        font-weight: 600;
        font-size: 11px;
        color: #64748b;
    }
    .currency-btn.active {
        background: #1B6EF3;
        color: white;
        box-shadow: 0 2px 8px rgba(27, 110, 243, 0.3);
    }
    .currency-btn:hover:not(.active) {
        background: #e2e8f0;
    }
    .service-item {
        transition: all 0.2s;
        cursor: default;
    }
    .service-item:hover {
        background: #eef2ff !important;
    }
    .table-sm td, .table-sm th {
        padding: 8px 12px;
        font-size: 13px;
        vertical-align: middle;
    }
    .progress {
        border-radius: 10px;
        background: #e9ecef;
        overflow: hidden;
    }
    .progress-bar {
        border-radius: 10px;
        transition: width 1s ease;
    }
    
    @media (max-width: 576px) {
        .kpi-value {
            font-size: 18px !important;
        }
        .kpi-title {
            font-size: 9px !important;
        }
        .currency-btn {
            font-size: 9px;
            padding: 3px 10px;
        }
    }
</style>
@endpush
@endsection