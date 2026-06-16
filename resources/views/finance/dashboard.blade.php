{{-- resources/views/finance/dashboard.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Conseiller Financier - Dashboard')
@section('page-title', 'Tableau de bord financier')
@section('breadcrumb', 'Finance / Dashboard')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-chart-line me-2" style="color: #1B6EF3;"></i>
                Analyse financière
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="window.location.href='{{ route('finance.analyses.index') }}'">
                <i class="fas fa-chart-simple me-2"></i>Analyse détaillée
            </button>
            <button class="btn btn-outline-success" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
        </div>
    </div>

    {{-- KPIs principaux --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); border-radius: 20px; padding: 20px; color: white;">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-label" style="font-size: 12px; opacity: 0.8;">CHIFFRE D'AFFAIRES</div>
                        <div class="kpi-value" style="font-size: 28px; font-weight: 800;">{{ number_format($stats['ca_total'], 0, ',', ' ') }}</div>
                        <div class="kpi-unit" style="font-size: 11px;">{{ $devise }}</div>
                    </div>
                    <i class="fas fa-chart-line fa-2x" style="opacity: 0.5;"></i>
                </div>
                <div class="mt-2">
                    @if($stats['croissance_ca'] >= 0)
                        <span class="text-success"><i class="fas fa-arrow-up"></i> +{{ $stats['croissance_ca'] }}%</span>
                    @else
                        <span class="text-danger"><i class="fas fa-arrow-down"></i> {{ $stats['croissance_ca'] }}%</span>
                    @endif
                    <span style="font-size: 11px;"> vs année précédente</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); border-radius: 20px; padding: 20px; color: white;">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-label" style="font-size: 12px; opacity: 0.8;">MARGE BRUTE</div>
                        <div class="kpi-value" style="font-size: 28px; font-weight: 800;">{{ number_format($stats['marge_totale'], 0, ',', ' ') }}</div>
                        <div class="kpi-unit" style="font-size: 11px;">{{ $devise }}</div>
                    </div>
                    <i class="fas fa-percent fa-2x" style="opacity: 0.5;"></i>
                </div>
                <div class="mt-2">
                    <span>Taux: {{ $stats['taux_marge'] }}%</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card" style="background: linear-gradient(135deg, #f5a623, #e69500); border-radius: 20px; padding: 20px; color: white;">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-label" style="font-size: 12px; opacity: 0.8;">TRÉSORERIE</div>
                        <div class="kpi-value" style="font-size: 28px; font-weight: 800;">{{ number_format($stats['tresorerie'], 0, ',', ' ') }}</div>
                        <div class="kpi-unit" style="font-size: 11px;">{{ $devise }}</div>
                    </div>
                    <i class="fas fa-coins fa-2x" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); border-radius: 20px; padding: 20px; color: white;">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-label" style="font-size: 12px; opacity: 0.8;">DÉPENSES</div>
                        <div class="kpi-value" style="font-size: 28px; font-weight: 800;">{{ number_format($stats['depenses'], 0, ',', ' ') }}</div>
                        <div class="kpi-unit" style="font-size: 11px;">{{ $devise }}</div>
                    </div>
                    <i class="fas fa-receipt fa-2x" style="opacity: 0.5;"></i>
                </div>
                <div class="mt-2">
                    <span>Ratio: {{ $stats['taux_endettement'] }}% du CA</span>
                </div>
            </div>
        </div>
    </div>

    

    {{-- Alertes et prévisions --}}
    <div class="row g-4">
        <div class="col-xl-6">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-exclamation-triangle me-2" style="color: #e02424;"></i>Alertes</h5>
                @forelse($alertIndicators as $alerte)
                <div class="alert-item d-flex align-items-center gap-3 p-3 mb-2 rounded" style="background: #fee2e2;">
                    <i class="{{ $alerte['icone'] }} fa-lg" style="color: #e02424;"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $alerte['nom'] }} ({{ $alerte['code'] }})</div>
                        <div class="small">Valeur: {{ number_format($alerte['valeur'], 0, ',', ' ') }} - Seuil: {{ number_format($alerte['seuil'], 0, ',', ' ') }}</div>
                    </div>
                    <span class="badge bg-danger">Alerte</span>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-2 d-block text-success"></i>
                    Aucune alerte en cours
                </div>
                @endforelse
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-chart-line me-2" style="color: #f5a623;"></i>Prévisions</h5>
                <div class="row g-3">
                    <div class="col-4 text-center">
                        <div class="p-3 rounded" style="background: #f8fafc;">
                            <div class="small text-muted">Mois prochain</div>
                            <div class="fs-5 fw-bold text-primary">{{ number_format($forecasts['next_month'], 0, ',', ' ') }}</div>
                            <div class="small">{{ $devise }}</div>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="p-3 rounded" style="background: #f8fafc;">
                            <div class="small text-muted">Trimestre</div>
                            <div class="fs-5 fw-bold text-primary">{{ number_format($forecasts['next_quarter'], 0, ',', ' ') }}</div>
                            <div class="small">{{ $devise }}</div>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="p-3 rounded" style="background: #f8fafc;">
                            <div class="small text-muted">Année</div>
                            <div class="fs-5 fw-bold text-primary">{{ number_format($forecasts['next_year'], 0, ',', ' ') }}</div>
                            <div class="small">{{ $devise }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h6 class="mb-2">Tendances</h6>
                    <div class="d-flex gap-4">
                        <div>
                            <span class="badge {{ $trends['ca']['direction'] == 'up' ? 'bg-success' : 'bg-danger' }}">
                                <i class="fas fa-arrow-{{ $trends['ca']['direction'] }}"></i> CA: {{ abs($trends['ca']['value']) }}%
                            </span>
                        </div>
                        <div>
                            <span class="badge {{ $trends['depenses']['direction'] == 'up' ? 'bg-danger' : 'bg-success' }}">
                                <i class="fas fa-arrow-{{ $trends['depenses']['direction'] }}"></i> Dépenses: {{ abs($trends['depenses']['value']) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    let revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($revenueEvolution)->pluck('month')) !!},
            datasets: [
                {
                    label: '{{ date("Y") }}',
                    data: {!! json_encode(collect($revenueEvolution)->pluck('current')) !!},
                    borderColor: '#1B6EF3',
                    backgroundColor: 'rgba(27,110,243,0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#1B6EF3',
                    pointBorderColor: 'white',
                    pointRadius: 4
                },
                {
                    label: '{{ date("Y") - 1 }}',
                    data: {!! json_encode(collect($revenueEvolution)->pluck('previous')) !!},
                    borderColor: '#94a3b8',
                    backgroundColor: 'rgba(148,163,184,0.05)',
                    tension: 0.4,
                    fill: true,
                    borderDash: [5, 5],
                    pointRadius: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${new Intl.NumberFormat('fr-FR').format(context.raw)} {{ $devise }}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR').format(value);
                        }
                    }
                }
            }
        }
    });
    
    function changeChartPeriod(period) {
        // Logique pour changer la période
        console.log('Changer période:', period);
    }
</script>
@endpush

<style>
    .kpi-card {
        transition: transform 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
    }
    .service-item {
        transition: transform 0.2s;
    }
    .service-item:hover {
        transform: translateX(5px);
        background: #eef2ff !important;
    }
    .alert-item {
        transition: transform 0.2s;
    }
    .alert-item:hover {
        transform: translateX(5px);
    }
</style>
@endsection