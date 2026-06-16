{{-- resources/views/direction/dashboard.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Direction Générale - Dashboard Power BI')
@section('page-title', 'Tableau de bord Direction Générale')
@section('breadcrumb', 'Dashboard / Vue d\'ensemble')

@section('content')
<div class="container-fluid px-0">
    
    <!-- En-tête avec date française -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="powerbi-badge" style="display: inline-block; background: rgba(27,110,243,0.1); padding: 6px 16px; border-radius: 50px; font-size: 12px; color: #1B6EF3;">
                <i class="fas fa-chart-line me-2"></i> Vue stratégique
            </div>
            <h2 class="mt-3 mb-1 fw-bold" style="color: #1e293b;">
                <i class="fas fa-crown me-2" style="color: #F2C811;"></i>
                Panorama financier
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-light border" onclick="window.print()" style="border-radius: 12px;">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
            <button class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#exportModal" style="border-radius: 12px;">
                <i class="fas fa-download me-2"></i>Exporter
            </button>
        </div>
    </div>

    <!-- Sélecteur de devise -->
    <div class="row mb-4">
        <div class="col-12 text-end">
            <div class="currency-selector" style="display: inline-flex; gap: 8px; background: #f1f5f9; padding: 6px; border-radius: 40px;">
                <form action="{{ route('change-currency') }}" method="POST" class="d-flex gap-1 m-0">
                    @csrf
                    <button type="submit" name="devise" value="USD" class="currency-btn {{ session('devise', 'USD') == 'USD' ? 'active' : '' }}" style="padding: 6px 18px; border-radius: 40px; font-weight: 600;">
                        $ USD
                    </button>
                    <button type="submit" name="devise" value="CDF" class="currency-btn {{ session('devise') == 'CDF' ? 'active' : '' }}" style="padding: 6px 18px; border-radius: 40px; font-weight: 600;">
                        FC CDF
                    </button>
                    <button type="submit" name="devise" value="EUR" class="currency-btn {{ session('devise') == 'EUR' ? 'active' : '' }}" style="padding: 6px 18px; border-radius: 40px; font-weight: 600;">
                        € EUR
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- KPI Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="powerbi-kpi-card" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); border-radius: 20px; padding: 20px; color: white;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title" style="font-size: 13px; opacity: 0.8;">CHIFFRE D'AFFAIRES</div>
                        <div class="kpi-value" style="font-size: 32px; font-weight: 800;">{{ number_format($stats['total_ca'], 0, ',', ' ') }}</div>
                        <div class="kpi-unit" style="font-size: 12px; opacity: 0.7;">
                            @if(session('devise', 'USD') == 'USD') USD
                            @elseif(session('devise') == 'CDF') FC
                            @else EUR @endif
                        </div>
                    </div>
                    <div class="kpi-icon" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
                <div class="kpi-trend mt-3">
                    @if($stats['croissance_ca'] >= 0)
                        <span class="text-success"><i class="fas fa-arrow-up"></i> +{{ number_format($stats['croissance_ca'], 1) }}%</span>
                    @else
                        <span class="text-danger"><i class="fas fa-arrow-down"></i> {{ number_format($stats['croissance_ca'], 1) }}%</span>
                    @endif
                    <span style="font-size: 12px; opacity: 0.7;"> vs année précédente</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="powerbi-kpi-card" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); border-radius: 20px; padding: 20px; color: white;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title" style="font-size: 13px; opacity: 0.8;">MARGE BRUTE</div>
                        <div class="kpi-value" style="font-size: 32px; font-weight: 800;">{{ number_format($stats['total_marge'], 0, ',', ' ') }}</div>
                        <div class="kpi-unit" style="font-size: 12px; opacity: 0.7;">
                            @if(session('devise', 'USD') == 'USD') USD
                            @elseif(session('devise') == 'CDF') FC
                            @else EUR @endif
                        </div>
                    </div>
                    <div class="kpi-icon" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-percent fa-2x"></i>
                    </div>
                </div>
                <div class="kpi-trend mt-3">
                    <span class="text-light">Taux: {{ number_format($stats['marge_percent'], 1) }}%</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="powerbi-kpi-card" style="background: linear-gradient(135deg, #f5a623, #e69500); border-radius: 20px; padding: 20px; color: white;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title" style="font-size: 13px; opacity: 0.8;">TRÉSORERIE</div>
                        <div class="kpi-value" style="font-size: 32px; font-weight: 800;">{{ number_format($stats['tresorerie'], 0, ',', ' ') }}</div>
                        <div class="kpi-unit" style="font-size: 12px; opacity: 0.7;">
                            @if(session('devise', 'USD') == 'USD') USD
                            @elseif(session('devise') == 'CDF') FC
                            @else EUR @endif
                        </div>
                    </div>
                    <div class="kpi-icon" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-coins fa-2x"></i>
                    </div>
                </div>
                <div class="kpi-trend mt-3">
                    <span style="font-size: 12px; opacity: 0.7;">Disponible immédiat</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="powerbi-kpi-card" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); border-radius: 20px; padding: 20px; color: white;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-title" style="font-size: 13px; opacity: 0.8;">INVESTISSEMENTS</div>
                        <div class="kpi-value" style="font-size: 32px; font-weight: 800;">{{ number_format($stats['investissements'], 0, ',', ' ') }}</div>
                        <div class="kpi-unit" style="font-size: 12px; opacity: 0.7;">
                            @if(session('devise', 'USD') == 'USD') USD
                            @elseif(session('devise') == 'CDF') FC
                            @else EUR @endif
                        </div>
                    </div>
                    <div class="kpi-icon" style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chart-pie fa-2x"></i>
                    </div>
                </div>
                <div class="kpi-trend mt-3">
                    <span style="font-size: 12px; opacity: 0.7;">Année en cours</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top 5 Services (pleine largeur) -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-chart-pie me-2" style="color: #F2C811;"></i> Top 5 Services</h5>
                <div class="top-services-list">
                    @foreach($topServices as $index => $service)
                    <div class="service-item d-flex align-items-center justify-content-between mb-2 p-2 rounded" style="background: {{ $index % 2 == 0 ? '#f8fafc' : 'white' }};">
                        <div class="d-flex align-items-center gap-2">
                            <div class="service-rank" style="width: 28px; height: 28px; background: {{ $service['couleur'] }}; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $service['name'] }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">{{ number_format($service['total'], 0, ',', ' ') }}</div>
                            <small class="text-muted">
                                @if(session('devise', 'USD') == 'USD') USD
                                @elseif(session('devise') == 'CDF') FC
                                @else EUR @endif
                            </small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Performances par service -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-building me-2" style="color: #0e9f6e;"></i> Performances par Service</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Service</th>
                                <th>CA</th>
                                <th>Marge</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servicePerformances as $service)
                            <tr>
                                <td><strong>{{ $service['name'] }}</strong></td>
                                <td>{{ number_format($service['ca'], 0, ',', ' ') }} 
                                    @if(session('devise', 'USD') == 'USD') USD
                                    @elseif(session('devise') == 'CDF') FC
                                    @else EUR @endif
                                </td>
                                <td>{{ number_format($service['marge'], 0, ',', ' ') }} 
                                    @if(session('devise', 'USD') == 'USD') USD
                                    @elseif(session('devise') == 'CDF') FC
                                    @else EUR @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress" style="width: 80px; height: 5px;">
                                            <div class="progress-bar bg-success" style="width: {{ $service['performance'] }}%"></div>
                                        </div>
                                        <span class="small">{{ $service['performance'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
@endsection

@push('styles')
<style>
    .powerbi-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .powerbi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .service-item:hover {
        background: #eef2ff !important;
    }
    .currency-btn {
        transition: all 0.2s;
        cursor: pointer;
    }
    .currency-btn.active {
        background: #1B6EF3;
        color: white;
    }
    .currency-btn:hover:not(.active) {
        background: #e2e8f0;
    }
    .table-sm td, .table-sm th {
        padding: 8px;
        font-size: 13px;
    }
</style>
@endpush

@push('scripts')
<script>
    function viewServiceDetails(serviceId) {
        window.location.href = '/direction/services/' + serviceId;
    }
</script>
@endpush