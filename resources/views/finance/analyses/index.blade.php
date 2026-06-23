{{-- resources/views/finance/analyses/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Analyse financière')
@section('page-title', 'Analyse financière détaillée')
@section('breadcrumb', 'Finance / Analyses')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">

    {{-- ============================================ --}}
    {{-- EN-TÊTE --}}
    {{-- ============================================ --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold" style="color: #0f172a;">
                <i class="fas fa-chart-pie me-2" style="color: #1B6EF3;"></i>
                Analyse financière détaillée
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
    {{-- FILTRES --}}
    {{-- ============================================ --}}
    <div class="card mb-4" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('finance.analyses.index') }}" class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted fw-bold"><i class="fas fa-calendar me-1"></i>Année</label>
                    <select name="year" class="form-select form-select-sm">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted fw-bold"><i class="fas fa-building me-1"></i>Service</label>
                    <select name="service_id" class="form-select form-select-sm">
                        <option value="">Tous les services</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id_service }}" {{ $serviceId == $service->id_service ? 'selected' : '' }}>
                                {{ $service->nom_service }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-chart-line me-2"></i>Actualiser
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- COMPARAISON ANNUELLE (UNIQUEMENT 2026) --}}
    {{-- ============================================ --}}
    @if(($yearComparison['current_year'] ?? 0) > 0)
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-12">
            <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #e8f0fe, #d4e2fc); border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fs-6 fw-bold" style="color: #1B6EF3;">
                        <i class="fas fa-chart-line me-2"></i>Chiffre d'affaires {{ $year }}
                    </h5>
                    <span class="badge bg-primary">{{ number_format($yearComparison['current_year'] ?? 0, 0, ',', ' ') }} {{ $devise }}</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row text-center g-3">
                        <div class="col-12">
                            <div class="p-4 rounded" style="background: linear-gradient(135deg, #1B6EF320, #1B6EF305); border-radius: 16px; border: 2px solid #1B6EF3;">
                                <div class="small text-muted">Total {{ $year }}</div>
                                <div class="display-4 fw-bold text-primary">
                                    {{ number_format($yearComparison['current_year'] ?? 0, 0, ',', ' ') }}
                                </div>
                                <div class="small">{{ $devise }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- GRAPHIQUE COMPARATIF MENSUEL --}}
    {{-- ============================================ --}}
    @if(!empty($yearComparison['monthly_comparison']) && collect($yearComparison['monthly_comparison'])->sum('current') > 0)
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-12">
            <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f0ecff, #e0d8f5); border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fs-6 fw-bold" style="color: #7e3af2;">
                        <i class="fas fa-chart-bar me-2"></i>Évolution mensuelle {{ $year }}
                    </h5>
                    <span class="badge bg-primary">{{ $year }}</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 320px;">
                        <canvas id="monthlyComparisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- ANALYSE MENSUELLE DÉTAILLÉE --}}
    {{-- ============================================ --}}
    @if(!empty($monthlyData) && collect($monthlyData)->sum('ca') > 0)
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-12">
            <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #e8f5f0, #d4ede4); border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fs-6 fw-bold" style="color: #0e9f6e;">
                        <i class="fas fa-table me-2"></i>Analyse mensuelle {{ $year }}
                    </h5>
                    <span class="badge bg-success">{{ $year }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-sm table-md">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">Mois</th>
                                    <th class="py-3 text-end">Chiffre d'affaires</th>
                                    <th class="py-3 text-end">Achats</th>
                                    <th class="py-3 text-end">Marge</th>
                                    <th class="py-3 text-center">Taux de marge</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyData as $data)
                                @if($data['ca'] > 0 || $data['marge'] > 0)
                                <tr>
                                    <td class="py-3"><strong class="small">{{ $data['month'] }}</strong></td>
                                    <td class="py-3 text-end text-primary fw-bold">{{ number_format($data['ca'], 0, ',', ' ') }}</td>
                                    <td class="py-3 text-end text-warning">{{ number_format($data['achats'] ?? 0, 0, ',', ' ') }}</td>
                                    <td class="py-3 text-end text-success">{{ number_format($data['marge'], 0, ',', ' ') }}</td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2 justify-content-center">
                                            <div class="progress" style="width: 80px; height: 6px;">
                                                <div class="progress-bar" style="width: {{ min($data['taux_marge'], 100) }}%; background: {{ $data['taux_marge'] >= 30 ? '#0e9f6e' : ($data['taux_marge'] >= 15 ? '#f5a623' : '#e02424') }};"></div>
                                            </div>
                                            <span class="small">{{ $data['taux_marge'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- RATIOS FINANCIERS --}}
    {{-- ============================================ --}}
    @if(($ratios['marge_nette'] ?? 0) > 0 || ($ratios['ratio_depenses'] ?? 0) > 0)
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-12">
            <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #fff8e8, #ffeed4); border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fs-6 fw-bold" style="color: #f5a623;">
                        <i class="fas fa-calculator me-2"></i>Ratios financiers {{ $year }}
                    </h5>
                    <span class="badge bg-warning" style="color: #000;">{{ $year }}</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded" style="background: linear-gradient(135deg, #1B6EF315, #1B6EF305); border-radius: 12px;">
                                <div class="display-6 fw-bold text-primary">{{ $ratios['marge_nette'] ?? 0 }}%</div>
                                <div class="small text-muted">Marge nette</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded" style="background: linear-gradient(135deg, #f5a62315, #f5a62305); border-radius: 12px;">
                                <div class="display-6 fw-bold text-warning">{{ $ratios['ratio_depenses'] ?? 0 }}%</div>
                                <div class="small text-muted">Ratio dépenses/CA</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded" style="background: linear-gradient(135deg, #0e9f6e15, #0e9f6e05); border-radius: 12px;">
                                <div class="display-6 fw-bold text-success">{{ $ratios['rentabilite'] ?? 0 }}%</div>
                                <div class="small text-muted">Rentabilité</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded" style="background: linear-gradient(135deg, #7e3af215, #7e3af205); border-radius: 12px;">
                                <div class="display-6 fw-bold text-purple">{{ number_format($ratios['ca_total'] ?? 0, 0, ',', ' ') }}</div>
                                <div class="small text-muted">CA Total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- GRAPHIQUE ÉVOLUTION CA/MARGE --}}
    {{-- ============================================ --}}
    @if(!empty($monthlyData) && collect($monthlyData)->sum('ca') > 0)
    <div class="row g-2 g-md-3 g-lg-4">
        <div class="col-12">
            <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f0ecff, #e0d8f5); border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fs-6 fw-bold" style="color: #7e3af2;">
                        <i class="fas fa-chart-line me-2"></i>Évolution CA et Marge {{ $year }}
                    </h5>
                    <span class="badge bg-primary">{{ $year }}</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="caMargeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // DONNÉES
    // ============================================
    const monthlyData = @json($monthlyData);
    const yearComparison = @json($yearComparison);
    const devise = '{{ $devise }}';
    const year = '{{ $year }}';

    // ============================================
    // 1. GRAPHIQUE COMPARAISON MENSUELLE
    // ============================================
    @if(!empty($yearComparison['monthly_comparison']) && collect($yearComparison['monthly_comparison'])->sum('current') > 0)
    const ctx1 = document.getElementById('monthlyComparisonChart')?.getContext('2d');
    if (ctx1) {
        const compLabels = @json(collect($yearComparison['monthly_comparison'])->pluck('month'));
        const compCurrent = @json(collect($yearComparison['monthly_comparison'])->pluck('current'));
        
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: compLabels,
                datasets: [{
                    label: year,
                    data: compCurrent,
                    backgroundColor: 'rgba(27,110,243,0.8)',
                    borderColor: '#1B6EF3',
                    borderWidth: 2,
                    borderRadius: 6,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, pointStyle: 'circle', padding: 20, font: { size: 12, weight: '600' } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        titleFont: { size: 14, weight: '700' },
                        bodyFont: { size: 13 },
                        padding: 16,
                        cornerRadius: 12,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' ' + devise;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.06)' },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
                                return new Intl.NumberFormat('fr-FR').format(value);
                            },
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '500' } }
                    }
                },
                animation: { duration: 800 }
            }
        });
    }
    @endif

    // ============================================
    // 2. GRAPHIQUE ÉVOLUTION CA ET MARGE
    // ============================================
    @if(!empty($monthlyData) && collect($monthlyData)->sum('ca') > 0)
    const ctx2 = document.getElementById('caMargeChart')?.getContext('2d');
    if (ctx2) {
        const labels = @json(collect($monthlyData)->pluck('month'));
        const caData = @json(collect($monthlyData)->pluck('ca'));
        const margeData = @json(collect($monthlyData)->pluck('marge'));
        const tauxMarge = @json(collect($monthlyData)->pluck('taux_marge'));
        
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Chiffre d\'affaires',
                        data: caData,
                        borderColor: '#1B6EF3',
                        backgroundColor: 'rgba(27,110,243,0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#1B6EF3',
                        pointBorderColor: 'white',
                        pointBorderWidth: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Marge',
                        data: margeData,
                        borderColor: '#0e9f6e',
                        backgroundColor: 'rgba(14,159,110,0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#0e9f6e',
                        pointBorderColor: 'white',
                        pointBorderWidth: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Taux de marge (%)',
                        data: tauxMarge,
                        borderColor: '#f5a623',
                        backgroundColor: 'rgba(245,166,35,0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#f5a623',
                        pointBorderColor: 'white',
                        pointBorderWidth: 2,
                        borderDash: [5, 5],
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, pointStyle: 'circle', padding: 20, font: { size: 12, weight: '600' } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        titleFont: { size: 14, weight: '700' },
                        bodyFont: { size: 13 },
                        padding: 16,
                        cornerRadius: 12,
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.label === 'Taux de marge (%)') {
                                    return context.dataset.label + ': ' + context.raw + '%';
                                }
                                return context.dataset.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' ' + devise;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.06)' },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
                                return new Intl.NumberFormat('fr-FR').format(value);
                            },
                            font: { size: 11 }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        max: 100,
                        grid: { drawOnChartArea: false },
                        ticks: {
                            callback: function(value) { return value + '%'; },
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '500' } }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                animation: { duration: 1000 }
            }
        });
    }
    @endif
});

// ============================================
// FONCTIONS
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
    .card-header-custom {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .text-purple { color: #7e3af2; }
    
    .chart-container {
        min-height: 200px;
        position: relative;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
    }
    .table td {
        vertical-align: middle;
        font-size: 0.85rem;
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
    
    @media (max-width: 768px) {
        .chart-container { min-height: 220px; }
        .display-6 { font-size: 1.5rem !important; }
        .table td, .table th { font-size: 0.75rem; padding: 8px 6px; }
        .display-4 { font-size: 2rem !important; }
    }
    
    @media (min-width: 992px) {
        .chart-container { min-height: 300px; }
        .display-6 { font-size: 2.2rem !important; }
    }
</style>
@endpush
@endsection