{{-- resources/views/service/performances/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Performances')
@section('page-title', 'Tableau de bord des performances')
@section('breadcrumb', 'Service / Performances')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">

    {{-- EN-TÊTE --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-chart-line me-2" style="color: #1B6EF3;"></i>
                Performance du Système
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
                <span class="badge bg-success ms-2">
                    <i class="fas fa-sync-alt me-1 fa-spin"></i> Données en temps réel
                </span>
            </p>
        </div>
        <div>
            <button class="btn btn-outline-secondary btn-sm" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i>Actualiser
            </button>
        </div>
    </div>

    {{-- STATISTIQUES GLOBALES --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-icon"><i class="fas fa-coins fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ number_format($caTotal ?? 0, 0, ',', ' ') }}</div>
                <div class="stat-label small">Chiffre d'affaires ({{ $devise ?? 'USD' }})</div>
                <div class="stat-trend small">
                    <i class="fas fa-arrow-up me-1"></i> {{ $caCroissance ?? 0 }}% vs mois dernier
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white;">
                <div class="stat-icon"><i class="fas fa-chart-pie fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ number_format($margeTotal ?? 0, 0, ',', ' ') }}</div>
                <div class="stat-label small">Marge brute ({{ $devise ?? 'USD' }})</div>
                <div class="stat-trend small">
                    <i class="fas fa-percent me-1"></i> {{ $tauxMarge ?? 0 }}% de marge
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-icon"><i class="fas fa-arrow-trend-down fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ number_format($depensesTotal ?? 0, 0, ',', ' ') }}</div>
                <div class="stat-label small">Dépenses ({{ $devise ?? 'USD' }})</div>
                <div class="stat-trend small">
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); color: white;">
                <div class="stat-icon"><i class="fas fa-trophy fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ $performanceScore ?? 0 }}%</div>
                <div class="stat-label small">Score de performance</div>
                <div class="stat-trend small">
                    <i class="fas {{ ($performanceScore ?? 0) >= 70 ? 'fa-check-circle text-success' : 'fa-exclamation-triangle text-warning' }} me-1"></i>
                    {{ ($performanceScore ?? 0) >= 70 ? 'Excellent' : 'À améliorer' }}
                </div>
            </div>
        </div>
    </div>

    {{-- GRAPHIQUES --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="powerbi-card h-100">
                <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fs-6 fs-md-5">
                            <i class="fas fa-chart-bar me-2" style="color: #1B6EF3;"></i>
                            Évolution du Chiffre d'affaires
                        </h5>
                        <small class="text-muted">6 derniers mois</small>
                    </div>
                    <span class="badge bg-primary">{{ number_format($caTotal ?? 0, 0, ',', ' ') }} {{ $devise ?? 'USD' }}</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 250px;">
                        <canvas id="caChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="powerbi-card h-100">
                <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fs-6 fs-md-5">
                            <i class="fas fa-chart-pie me-2" style="color: #f5a623;"></i>
                            Répartition des Dépenses
                        </h5>
                        <small class="text-muted">Par catégorie</small>
                    </div>
                    <span class="badge bg-warning">{{ number_format($depensesTotal ?? 0, 0, ',', ' ') }} {{ $devise ?? 'USD' }}</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 250px;">
                        <canvas id="depensesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="powerbi-card h-100">
                <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fs-6 fs-md-5">
                            <i class="fas fa-exchange-alt me-2" style="color: #7e3af2;"></i>
                            Comparaison CA vs Dépenses
                        </h5>
                        <small class="text-muted">Performance globale</small>
                    </div>
                    <span class="badge {{ ($margeTotal ?? 0) >= 0 ? 'bg-success' : 'bg-danger' }}">
                        Marge: {{ number_format($margeTotal ?? 0, 0, ',', ' ') }} {{ $devise ?? 'USD' }}
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 250px;">
                        <canvas id="comparaisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="powerbi-card h-100">
                <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fs-6 fs-md-5">
                            <i class="fas fa-bullseye me-2" style="color: #0e9f6e;"></i>
                            Objectifs de Performance
                        </h5>
                        <small class="text-muted">Progression mensuelle</small>
                    </div>
                    <span class="badge bg-info">{{ $performanceScore ?? 0 }}% atteint</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 250px;">
                        <canvas id="objectifsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- OBJECTIFS DÉTAILLÉS --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="powerbi-card">
                <div class="card-header-custom p-3 p-md-4 border-bottom">
                    <h5 class="mb-0 fs-6 fs-md-5">
                        <i class="fas fa-bullseye me-2" style="color: #1B6EF3;"></i>
                        Objectifs du service
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    @foreach($objectifs ?? [] as $key => $obj)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                            <span>
                                {{ number_format($obj['actuel'] ?? 0, 0, ',', ' ') }} / 
                                {{ number_format($obj['objectif'] ?? 0, 0, ',', ' ') }} {{ $devise ?? 'USD' }}
                            </span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 10px; background: #e9ecef;">
                            <div class="progress-bar bg-{{ $obj['couleur'] ?? 'primary' }}" 
                                 style="width: {{ min($obj['progression'] ?? 0, 100) }}%; border-radius: 10px;">
                                {{ min($obj['progression'] ?? 0, 100) }}%
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    @if(empty($objectifs))
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle fa-2x d-block mb-2"></i>
                        <p>Aucun objectif défini pour le moment</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="powerbi-card">
                <div class="card-header-custom p-3 p-md-4 border-bottom">
                    <h5 class="mb-0 fs-6 fs-md-5">
                        <i class="fas fa-lightbulb me-2" style="color: #f5a623;"></i>
                        Recommandations
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    @forelse($recommandations ?? [] as $rec)
                    <div class="recommendation-item p-3 mb-2 rounded-3" style="background: {{ $rec['color'] ?? '#f8fafc' }}; border-left: 4px solid {{ $rec['color'] ?? '#7e3af2' }};">
                        <div class="d-flex gap-2">
                            <i class="fas {{ $rec['icon'] ?? 'fa-circle' }}" style="color: {{ $rec['color'] ?? '#7e3af2' }};"></i>
                            <div>
                                <strong class="small">{{ $rec['title'] ?? 'Recommandation' }}</strong>
                                <p class="mb-0 small text-muted">{{ $rec['message'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-check-circle fa-2x text-success d-block mb-2"></i>
                        <p>Tout est sur la bonne voie !</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- TABLEAU DES PERFORMANCES --}}
    <div class="powerbi-card">
        <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fs-6 fs-md-5">
                <i class="fas fa-table me-2" style="color: #1B6EF3;"></i>
                Détails des performances
            </h5>
            <span class="badge bg-primary">{{ $performances->count() ?? 0 }} enregistrements</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-sm table-md">
                    <thead class="table-light">
                        <tr>
                            <th class="py-2">Période</th>
                            <th class="py-2">CA ({{ $devise ?? 'USD' }})</th>
                            <th class="py-2">Marge ({{ $devise ?? 'USD' }})</th>
                            <th class="py-2">Dépenses ({{ $devise ?? 'USD' }})</th>
                            <th class="py-2">Performance</th>
                            <th class="py-2">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($performances ?? [] as $perf)
                        <tr>
                            <td class="py-2">{{ $perf->periode ?? '-' }}</td>
                            <td class="py-2 fw-bold text-primary">{{ number_format($perf->ca ?? 0, 0, ',', ' ') }}</td>
                            <td class="py-2 text-success">{{ number_format($perf->marge ?? 0, 0, ',', ' ') }}</td>
                            <td class="py-2 text-danger">{{ number_format($perf->depenses ?? 0, 0, ',', ' ') }}</td>
                            <td class="py-2">
                                <div class="progress" style="height: 6px; width: 80px;">
                                    <div class="progress-bar bg-{{ ($perf->performance ?? 0) >= 70 ? 'success' : (($perf->performance ?? 0) >= 50 ? 'warning' : 'danger') }}" 
                                         style="width: {{ min($perf->performance ?? 0, 100) }}%;"></div>
                                </div>
                               
                            </td>
                            <td class="py-2">
                                <span class="badge bg-{{ ($perf->performance ?? 0) >= 70 ? 'success' : (($perf->performance ?? 0) >= 50 ? 'warning' : 'danger') }}">
                                    {{ ($perf->performance ?? 0) >= 70 ? '✅ Excellent' : (($perf->performance ?? 0) >= 50 ? '⚡ Moyen' : '🔴 À améliorer') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-database fa-2x text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0 small">Aucune donnée de performance disponible</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Dernière mise à jour: {{ now()->translatedFormat('d/m/Y à H:i') }}
                </small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DONNÉES
    const caData = @json($caMensuel ?? array_fill(0, 6, 0));
    const depensesData = @json($depensesMensuelles ?? array_fill(0, 6, 0));
    const objectifsData = @json($objectifsData ?? ['atteint' => 0, 'objectif' => 100]);
    
    const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'];
    const last6Months = months.slice(-caData.length);

    // PALETTE DE COULEURS
    const colors = {
        blue: ['#1B6EF3', '#4A8AF4', '#78A6F7', '#A6C2FA', '#D4DEFD'],
        orange: ['#f5a623', '#F7B84D', '#F9CA77', '#FBDCA1', '#FDEDCB']
    };

    // 1. GRAPHIQUE CA
    if (document.getElementById('caChart')) {
        const ctxCA = document.getElementById('caChart').getContext('2d');
        new Chart(ctxCA, {
            type: 'bar',
            data: {
                labels: last6Months,
                datasets: [{
                    label: 'CA (USD)',
                    data: caData,
                    backgroundColor: ['rgba(27, 110, 243, 0.7)', 'rgba(27, 110, 243, 0.5)', 'rgba(27, 110, 243, 0.3)'],
                    borderColor: '#1B6EF3',
                    borderWidth: 2,
                    borderRadius: 8,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                return 'CA: ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' USD';
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
                                if (value >= 1000) return (value / 1000) + 'k';
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                animation: { duration: 800 }
            }
        });
    }

    // 2. GRAPHIQUE DÉPENSES
    if (document.getElementById('depensesChart')) {
        const ctxDep = document.getElementById('depensesChart').getContext('2d');
        
        const depensesDataCateg = @json($depensesParCategorie ?? []);
        const depensesLabels = Object.keys(depensesDataCateg);
        const depensesValues = Object.values(depensesDataCateg);
        
        const finalLabels = depensesLabels.length > 0 ? depensesLabels : ['Achats', 'Salaires', 'Charges', 'Marketing', 'Autres'];
        const finalValues = depensesValues.length > 0 ? depensesValues : [0, 0, 0, 0, 0];
        
        new Chart(ctxDep, {
            type: 'doughnut',
            data: {
                labels: finalLabels,
                datasets: [{
                    data: finalValues,
                    backgroundColor: colors.orange,
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 10 },
                            boxWidth: 12,
                            padding: 8,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' USD (' + percentage + '%)';
                            }
                        }
                    }
                },
                animation: { animateRotate: true, duration: 1200 }
            }
        });
    }

    // 3. COMPARAISON
    if (document.getElementById('comparaisonChart')) {
        const ctxComp = document.getElementById('comparaisonChart').getContext('2d');
        new Chart(ctxComp, {
            type: 'bar',
            data: {
                labels: last6Months,
                datasets: [{
                    label: 'CA',
                    data: caData,
                    backgroundColor: 'rgba(27, 110, 243, 0.7)',
                    borderColor: '#1B6EF3',
                    borderWidth: 2,
                    borderRadius: 4,
                    barThickness: 25
                }, {
                    label: 'Dépenses',
                    data: depensesData,
                    backgroundColor: 'rgba(245, 166, 35, 0.7)',
                    borderColor: '#f5a623',
                    borderWidth: 2,
                    borderRadius: 4,
                    barThickness: 25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 10 },
                            boxWidth: 14,
                            padding: 12,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' USD';
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
                                if (value >= 1000) return (value / 1000) + 'k';
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                animation: { duration: 800 }
            }
        });
    }

    // 4. OBJECTIFS
    if (document.getElementById('objectifsChart')) {
        const ctxObj = document.getElementById('objectifsChart').getContext('2d');
        const atteint = objectifsData['atteint'] ?? 0;
        const objectif = objectifsData['objectif'] ?? 100;
        const progression = Math.min((atteint / objectif) * 100, 100);
        
        new Chart(ctxObj, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [progression, 100 - progression],
                    backgroundColor: ['#0e9f6e', '#e9ecef'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                animation: { animateRotate: true, duration: 1500 }
            },
            plugins: [{
                id: 'centerText',
                beforeDraw: function(chart) {
                    const { width, height, ctx } = chart;
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = 'bold 32px Segoe UI, Arial';
                    ctx.fillStyle = '#1e293b';
                    ctx.fillText(Math.round(progression) + '%', width / 2, height / 2 - 10);
                    ctx.font = '12px Segoe UI, Arial';
                    ctx.fillStyle = '#64748b';
                    ctx.fillText('Objectif atteint', width / 2, height / 2 + 20);
                    ctx.restore();
                }
            }]
        });
    }
});

function refreshData() {
    Swal.fire({
        title: 'Actualisation...',
        text: 'Les données sont en cours de mise à jour',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
    setTimeout(() => { window.location.reload(); }, 1000);
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
        transform: translateY(-3px);
    }
    .card-header-custom {
        background: linear-gradient(135deg, #f8fafc, #f1f4f9);
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .stat-card {
        transition: transform 0.3s, box-shadow 0.3s;
        cursor: default;
        border-radius: 14px;
        position: relative;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .stat-card .stat-icon {
        position: absolute;
        top: 10px;
        right: 15px;
        opacity: 0.2;
    }
    .stat-number {
        font-size: 2.2rem;
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    .stat-label {
        font-size: 0.75rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-trend {
        font-size: 0.7rem;
        opacity: 0.8;
        margin-top: 4px;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table th, .table td {
        white-space: nowrap;
        vertical-align: middle;
    }
    .badge {
        font-size: 0.7rem;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 500;
    }
    .chart-container {
        min-height: 150px;
    }
    .progress {
        border-radius: 10px;
        background: #e9ecef;
    }
    .progress-bar {
        border-radius: 10px;
        transition: width 1s ease;
    }
    .recommendation-item {
        transition: transform 0.2s;
    }
    .recommendation-item:hover {
        transform: translateX(5px);
    }
    
    @media (min-width: 768px) {
        .stat-number {
            font-size: 2.8rem !important;
        }
        .chart-container {
            min-height: 200px;
        }
    }
    @media (min-width: 992px) {
        .stat-number {
            font-size: 3.2rem !important;
        }
        .chart-container {
            min-height: 250px;
        }
    }
</style>
@endpush
@endsection