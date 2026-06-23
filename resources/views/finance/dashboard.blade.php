{{-- resources/views/finance/dashboard.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Conseiller Financier - Dashboard')
@section('page-title', 'Tableau de bord financier')
@section('breadcrumb', 'Finance / Dashboard')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">

    {{-- ============================================ --}}
    {{-- EN-TÊTE --}}
    {{-- ============================================ --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold" style="color: #0f172a;">
                <i class="fas fa-chart-line me-2" style="color: #1B6EF3;"></i>
                Analyse financière
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
            <button class="btn btn-outline-primary btn-sm" onclick="window.location.href='{{ route('finance.analyses.index') }}'">
                <i class="fas fa-chart-simple me-1"></i>Analyse détaillée
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i>Actualiser
            </button>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- KPIS PRINCIPAUX --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white; box-shadow: 0 4px 20px rgba(27,110,243,0.3);">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['ca_total'] ?? 0, 0, ',', ' ') }}</div>
                <div class="stat-label small"><i class="fas fa-chart-line me-1"></i>Chiffre d'affaires</div>
                <div class="stat-trend small">
                    @if(($stats['croissance_ca'] ?? 0) >= 0)
                        <span><i class="fas fa-arrow-up"></i> +{{ number_format($stats['croissance_ca'], 1) }}%</span>
                    @else
                        <span><i class="fas fa-arrow-down"></i> {{ number_format($stats['croissance_ca'], 1) }}%</span>
                    @endif
                    vs année précédente
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white; box-shadow: 0 4px 20px rgba(14,159,110,0.3);">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['marge_totale'] ?? 0, 0, ',', ' ') }}</div>
                <div class="stat-label small"><i class="fas fa-percent me-1"></i>Marge brute</div>
                <div class="stat-trend small">Taux: {{ number_format($stats['taux_marge'] ?? 0, 1) }}%</div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white; box-shadow: 0 4px 20px rgba(245,166,35,0.3);">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['tresorerie'] ?? 0, 0, ',', ' ') }}</div>
                <div class="stat-label small"><i class="fas fa-coins me-1"></i>Trésorerie</div>
                <div class="stat-trend small">{{ $devise }}</div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); color: white; box-shadow: 0 4px 20px rgba(126,58,242,0.3);">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['investissements'] ?? 0, 0, ',', ' ') }}</div>
                <div class="stat-label small"><i class="fas fa-chart-pie me-1"></i>Investissements</div>
                <div class="stat-trend small">Total des investissements</div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- GRAPHIQUE ÉVOLUTION DU CA --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-12">
            <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #e8f0fe, #d4e2fc); border-radius: 16px 16px 0 0;">
                    <div>
                        <h5 class="mb-0 fs-6 fw-bold" style="color: #1B6EF3;">
                            <i class="fas fa-chart-line me-2"></i>
                            Évolution du Chiffre d'affaires
                        </h5>
                        <small class="text-muted">Comparaison {{ date('Y') }} vs {{ date('Y') - 1 }}</small>
                    </div>
                    <span class="badge bg-primary" style="font-size: 0.9rem; padding: 8px 16px;">
                        <i class="fas fa-calendar me-1"></i>{{ date('Y') }}
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- PERFORMANCE PAR SERVICE & ALERTES --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        
        {{-- PERFORMANCE PAR SERVICE --}}
        <div class="col-12 col-md-6">
            <div class="card h-100" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f0ecff, #e0d8f5); border-radius: 16px 16px 0 0;">
                    <div>
                        <h5 class="mb-0 fs-6 fw-bold" style="color: #7e3af2;">
                            <i class="fas fa-building me-2"></i>
                            Performance par Service
                        </h5>
                        <small class="text-muted">CA et marge par service</small>
                    </div>
                    <span class="badge bg-primary" style="font-size: 0.9rem; padding: 8px 16px;">
                        <i class="fas fa-users me-1"></i>
                        {{ $servicePerformance->count() }} services
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-sm table-md">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">Service</th>
                                    <th class="py-3 text-end">CA</th>
                                    <th class="py-3 text-end">Marge</th>
                                    <th class="py-3 text-center">Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($servicePerformance as $service)
                                <tr>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width: 10px; height: 10px; background: {{ $service['couleur'] ?? '#1a56db' }}; border-radius: 50%;"></div>
                                            <strong class="small">{{ $service['name'] }}</strong>
                                        </div>
                                    </td>
                                    <td class="py-3 text-end fw-bold">
                                        {{ number_format($service['ca'] ?? 0, 0, ',', ' ') }}
                                    </td>
                                    <td class="py-3 text-end {{ ($service['marge'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($service['marge'] ?? 0, 0, ',', ' ') }}
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2 justify-content-center">
                                            <div class="progress" style="width: 80px; height: 6px;">
                                                <div class="progress-bar bg-{{ ($service['performance'] ?? 0) >= 70 ? 'success' : (($service['performance'] ?? 0) >= 50 ? 'warning' : 'danger') }}" 
                                                     style="width: {{ min($service['performance'] ?? 0, 100) }}%;"></div>
                                            </div>
                                            <span class="small">{{ $service['performance'] ?? 0 }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-building fa-2x text-muted mb-2 d-block"></i>
                                        <p class="text-muted mb-0">Aucun service disponible</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ALERTES ET PRÉVISIONS --}}
        <div class="col-12 col-md-6">
            <div class="card h-100" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #fff8e8, #ffeed4); border-radius: 16px 16px 0 0;">
                    <div>
                        <h5 class="mb-0 fs-6 fw-bold" style="color: #f5a623;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Alertes & Prévisions
                        </h5>
                        <small class="text-muted">Indicateurs en alerte et prévisions</small>
                    </div>
                    <span class="badge bg-warning" style="font-size: 0.9rem; padding: 8px 16px; color: #000;">
                        <i class="fas fa-bell me-1"></i>
                        {{ count($alertIndicators) }} alertes
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    {{-- Alertes --}}
                    @forelse($alertIndicators as $alerte)
                    <div class="alert-item d-flex align-items-center gap-3 p-2 mb-2 rounded" style="background: #fee2e2; border-radius: 10px;">
                        <i class="fas {{ $alerte['icone'] ?? 'fa-exclamation-triangle' }} fa-lg" style="color: #e02424;"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $alerte['nom'] }} ({{ $alerte['code'] }})</div>
                            <div class="small text-muted">
                                Valeur: {{ number_format($alerte['valeur'], 0, ',', ' ') }} - 
                                Seuil: {{ number_format($alerte['seuil'], 0, ',', ' ') }}
                            </div>
                        </div>
                        <span class="badge bg-danger">{{ $alerte['type'] == 'min' ? '⚠️ Bas' : '⚠️ Haut' }}</span>
                    </div>
                    @empty
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
                        <p class="text-muted mb-0">✅ Aucune alerte en cours</p>
                    </div>
                    @endforelse
                    
                    {{-- Prévisions --}}
                    <div class="mt-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-chart-line me-2"></i>Prévisions</h6>
                        <div class="row g-2">
                            <div class="col-4 text-center">
                                <div class="p-2 rounded" style="background: #f8fafc;">
                                    <div class="small text-muted">Mois prochain</div>
                                    <div class="fw-bold text-primary">{{ number_format($forecasts['next_month'] ?? 0, 0, ',', ' ') }}</div>
                                    <div class="small">{{ $devise }}</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="p-2 rounded" style="background: #f8fafc;">
                                    <div class="small text-muted">Trimestre</div>
                                    <div class="fw-bold text-primary">{{ number_format($forecasts['next_quarter'] ?? 0, 0, ',', ' ') }}</div>
                                    <div class="small">{{ $devise }}</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="p-2 rounded" style="background: #f8fafc;">
                                    <div class="small text-muted">Année</div>
                                    <div class="fw-bold text-primary">{{ number_format($forecasts['next_year'] ?? 0, 0, ',', ' ') }}</div>
                                    <div class="small">{{ $devise }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Tendances --}}
                    <div class="mt-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-arrow-trend-up me-2"></i>Tendances</h6>
                        <div class="d-flex gap-3 flex-wrap">
                            <span class="badge {{ ($trends['ca']['direction'] ?? 'up') == 'up' ? 'bg-success' : 'bg-danger' }}" style="padding: 8px 16px;">
                                <i class="fas fa-arrow-{{ $trends['ca']['direction'] ?? 'up' }} me-1"></i> 
                                CA: {{ abs($trends['ca']['value'] ?? 0) }}%
                            </span>
                            <span class="badge {{ ($trends['depenses']['direction'] ?? 'up') == 'up' ? 'bg-danger' : 'bg-success' }}" style="padding: 8px 16px;">
                                <i class="fas fa-arrow-{{ $trends['depenses']['direction'] ?? 'up' }} me-1"></i> 
                                Dépenses: {{ abs($trends['depenses']['value'] ?? 0) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- TOP SERVICES & DERNIÈRES DÉCISIONS --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3 g-lg-4">
        
        {{-- TOP SERVICES --}}
        <div class="col-12 col-md-6">
            <div class="card h-100" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #e8f5f0, #d4ede4); border-radius: 16px 16px 0 0;">
                    <div>
                        <h5 class="mb-0 fs-6 fw-bold" style="color: #0e9f6e;">
                            <i class="fas fa-trophy me-2"></i>
                            Top Services
                        </h5>
                        <small class="text-muted">Services avec le plus grand CA</small>
                    </div>
                    <span class="badge bg-success" style="font-size: 0.9rem; padding: 8px 16px;">
                        <i class="fas fa-chart-line me-1"></i>
                        {{ $topServices->count() }} services
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    @forelse($topServices as $index => $service)
                    <div class="service-item d-flex align-items-center justify-content-between p-3 mb-2 rounded" style="background: {{ $index % 2 == 0 ? '#f8fafc' : 'white' }}; border-radius: 10px;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rank-badge" style="width: 32px; height: 32px; background: {{ $service['couleur'] ?? '#1a56db' }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <div class="fw-semibold small">{{ $service['name'] }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">{{ number_format($service['ca'] ?? 0, 0, ',', ' ') }}</div>
                            <small class="text-muted">{{ $devise }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-building fa-2x text-muted mb-2 d-block"></i>
                        <p class="text-muted mb-0">Aucun service disponible</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- DERNIÈRES DÉCISIONS --}}
        <div class="col-12 col-md-6">
            <div class="card h-100" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f8fafc, #f1f4f9); border-radius: 16px 16px 0 0;">
                    <div>
                        <h5 class="mb-0 fs-6 fw-bold" style="color: #1B6EF3;">
                            <i class="fas fa-gavel me-2"></i>
                            Dernières Décisions
                        </h5>
                        <small class="text-muted">Décisions récentes</small>
                    </div>
                    <span class="badge bg-primary" style="font-size: 0.9rem; padding: 8px 16px;">
                        <i class="fas fa-clock me-1"></i>
                        {{ $recentDecisions->count() }} décisions
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentDecisions as $decision)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="small">{{ $decision->titre }}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    {{ $decision->created_at->translatedFormat('d/m/Y H:i') }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $decision->statut == 'Validée' ? 'success' : ($decision->statut == 'Rejetée' ? 'danger' : 'warning') }}">
                                {{ $decision->statut ?? 'En attente' }}
                            </span>
                        </div>
                        @empty
                        <div class="list-group-item text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                            <p class="text-muted mb-0">Aucune décision</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // GRAPHIQUE ÉVOLUTION DU CA
    // ============================================
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    
    const revenueData = @json($revenueEvolution);
    const labels = revenueData.map(item => item.month);
    const currentData = revenueData.map(item => item.current);
    const previousData = revenueData.map(item => item.previous);
    const growthData = revenueData.map(item => item.growth);
    
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: '{{ date("Y") }}',
                    data: currentData,
                    borderColor: '#1B6EF3',
                    backgroundColor: 'rgba(27,110,243,0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#1B6EF3',
                    pointBorderColor: 'white',
                    pointRadius: 5,
                    pointHoverRadius: 7
                },
                {
                    label: '{{ date("Y") - 1 }}',
                    data: previousData,
                    borderColor: '#94a3b8',
                    backgroundColor: 'rgba(148,163,184,0.05)',
                    tension: 0.4,
                    fill: true,
                    borderDash: [5, 5],
                    pointRadius: 4,
                    pointBackgroundColor: '#94a3b8',
                    pointBorderColor: 'white'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 20,
                        font: { size: 12, weight: '600' }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.85)',
                    titleFont: { size: 14, weight: '700' },
                    bodyFont: { size: 13 },
                    padding: 16,
                    cornerRadius: 12,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' {{ $devise }}';
                        },
                        afterLabel: function(context) {
                            const growth = growthData[context.dataIndex];
                            if (growth !== undefined && context.datasetIndex === 0) {
                                return '📈 Croissance: ' + (growth >= 0 ? '+' : '') + growth + '%';
                            }
                            return null;
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
            interaction: {
                intersect: false,
                mode: 'index'
            },
            animation: { duration: 1000 }
        }
    });
});

// ============================================
// FONCTIONS D'ACTION
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
    
    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: default;
        border-radius: 16px;
        position: relative;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        letter-spacing: -0.5px;
        line-height: 1.2;
        position: relative;
        z-index: 1;
    }
    .stat-label {
        font-size: 0.75rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        z-index: 1;
    }
    .stat-trend {
        font-size: 0.65rem;
        opacity: 0.7;
        margin-top: 2px;
        position: relative;
        z-index: 1;
    }
    
    .chart-container {
        min-height: 200px;
        position: relative;
    }
    
    .service-item {
        transition: all 0.2s;
        cursor: default;
    }
    .service-item:hover {
        background: #eef2ff !important;
        transform: translateX(5px);
    }
    
    .alert-item {
        transition: all 0.2s;
    }
    .alert-item:hover {
        transform: translateX(5px);
    }
    
    .rank-badge {
        transition: all 0.2s;
    }
    .rank-badge:hover {
        transform: scale(1.1);
    }
    
    .list-group-item {
        transition: all 0.2s;
    }
    .list-group-item:hover {
        background: #f8fafc;
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
        .stat-number { font-size: 1.8rem !important; }
        .chart-container { min-height: 220px; }
    }
    
    @media (min-width: 992px) {
        .stat-number { font-size: 3rem !important; }
        .chart-container { min-height: 300px; }
    }
</style>
@endpush
@endsection