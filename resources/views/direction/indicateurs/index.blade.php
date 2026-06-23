{{-- resources/views/direction/indicateurs/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Indicateurs de performance')
@section('page-title', 'Tableau de bord des indicateurs')
@section('breadcrumb', 'Direction / Indicateurs')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">

    {{-- ============================================ --}}
    {{-- EN-TÊTE --}}
    {{-- ============================================ --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-chart-line me-2" style="color: #1B6EF3;"></i>
                Indicateurs clés de performance
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
                <span class="badge bg-success ms-2">
                    <i class="fas fa-sync-alt me-1 fa-spin"></i> Données en temps réel
                </span>
            </p>
        </div>
        <a href="{{ route('direction.indicateurs.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Nouvel indicateur
        </a>
    </div>

    {{-- ============================================ --}}
    {{-- STATISTIQUES GLOBALES --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-icon"><i class="fas fa-chart-simple fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-label small">Total indicateurs</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white;">
                <div class="stat-icon"><i class="fas fa-check-circle fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ $stats['actifs'] ?? 0 }}</div>
                <div class="stat-label small">Indicateurs actifs</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #e02424, #b91c1c); color: white;">
                <div class="stat-icon"><i class="fas fa-ban fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ $stats['inactifs'] ?? 0 }}</div>
                <div class="stat-label small">Indicateurs inactifs</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ $stats['avec_alertes'] ?? 0 }}</div>
                <div class="stat-label small">Avec seuils d'alerte</div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- GRAPHIQUES DE PERFORMANCE --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        
        {{-- GRAPHIQUE 1: Répartition par Statut (DONUT) --}}
        <div class="col-12 col-md-6">
            <div class="powerbi-card h-100">
                <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fs-6 fs-md-5">
                            <i class="fas fa-chart-pie me-2" style="color: #7e3af2;"></i>
                            Répartition des Indicateurs
                        </h5>
                        <small class="text-muted">Par statut</small>
                    </div>
                    <span class="badge bg-purple">{{ $stats['total'] ?? 0 }} indicateurs</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 280px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRAPHIQUE 2: Indicateurs par Catégorie (BARRES) --}}
        <div class="col-12 col-md-6">
            <div class="powerbi-card h-100">
                <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fs-6 fs-md-5">
                            <i class="fas fa-chart-bar me-2" style="color: #1B6EF3;"></i>
                            Indicateurs par Catégorie
                        </h5>
                        <small class="text-muted">Répartition des KPI</small>
                    </div>
                    <span class="badge bg-primary">{{ $stats['total'] ?? 0 }} indicateurs</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 280px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- FILTRES --}}
    {{-- ============================================ --}}
    <div class="powerbi-card mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('direction.indicateurs.index') }}" class="row g-2 g-md-3">
                <div class="col-12 col-md-5">
                    <label class="form-label small text-muted">Recherche</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nom, code ou description..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted">Statut</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="actif" {{ request('status') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ request('status') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="col-6 col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i> Filtrer
                    </button>
                    <a href="{{ route('direction.indicateurs.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-undo-alt me-1"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- TABLEAU DES INDICATEURS --}}
    {{-- ============================================ --}}
    <div class="powerbi-card">
        <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fs-6 fs-md-5">
                <i class="fas fa-table me-2" style="color: #1B6EF3;"></i>
                Liste des indicateurs
            </h5>
            <span class="badge bg-primary">{{ $indicateurs->count() ?? 0 }} enregistrements</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-sm table-md">
                    <thead class="table-light">
                        <tr>
                            <th class="py-2">Code</th>
                            <th class="py-2">Indicateur</th>
                            <th class="py-2">Unité</th>
                            <th class="py-2 d-none d-md-table-cell">Formule</th>
                            <th class="py-2">Valeur</th>
                            <th class="py-2 d-none d-sm-table-cell">Seuil min</th>
                            <th class="py-2 d-none d-sm-table-cell">Seuil max</th>
                            <th class="py-2">Statut</th>
                            <th class="py-2 d-none d-lg-table-cell">Alerte</th>
                            <th class="py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indicateurs as $indicateur)
                        @php
                            $estAlerte = false;
                            if ($indicateur->seuil_alerte_min && $indicateur->valeur_actuelle < $indicateur->seuil_alerte_min) {
                                $estAlerte = true;
                            } elseif ($indicateur->seuil_alerte_max && $indicateur->valeur_actuelle > $indicateur->seuil_alerte_max) {
                                $estAlerte = true;
                            }
                        @endphp
                        <tr>
                            <td class="py-2"><code class="fw-bold">{{ $indicateur->code_kpi }}</code></td>
                            <td class="py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="icon-bg" style="width: 28px; height: 28px; background: {{ $indicateur->couleur_alerte ?? '#1a56db' }}20; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="{{ $indicateur->icone ?? 'fas fa-chart-line' }}" style="color: {{ $indicateur->couleur_alerte ?? '#1a56db' }}; font-size: 12px;"></i>
                                    </div>
                                    <div>
                                        <strong class="small">{{ $indicateur->nom_kpi }}</strong>
                                        <br><small class="text-muted d-none d-sm-inline">{{ Str::limit($indicateur->description, 30) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2"><span class="badge bg-secondary">{{ $indicateur->unite }}</span></td>
                            <td class="py-2 d-none d-md-table-cell">
                                <code class="text-primary" style="font-size: 10px;">
                                    {{ Str::limit($indicateur->formule, 25) ?: '-' }}
                                </code>
                            </td>
                            <td class="py-2 fw-bold">{{ number_format($indicateur->valeur_actuelle ?? 0, 0, ',', ' ') }}</td>
                            <td class="py-2 d-none d-sm-table-cell text-muted">{{ $indicateur->seuil_alerte_min ? number_format($indicateur->seuil_alerte_min, 0, ',', ' ') : '-' }}</td>
                            <td class="py-2 d-none d-sm-table-cell text-muted">{{ $indicateur->seuil_alerte_max ? number_format($indicateur->seuil_alerte_max, 0, ',', ' ') : '-' }}</td>
                            <td class="py-2">
                                @if($indicateur->est_actif)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </td>
                            <td class="py-2 d-none d-lg-table-cell">
                                @if($estAlerte)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Alerte
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Normal
                                    </span>
                                @endif
                            </td>
                            <td class="py-2">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('direction.indicateurs.show', $indicateur->id_indicateur) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('direction.indicateurs.edit', $indicateur->id_indicateur) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <form method="POST" action="{{ route('direction.indicateurs.toggle-status', $indicateur->id_indicateur) }}" style="display: inline-block;" 
                                          onsubmit="return confirm('{{ $indicateur->est_actif ? 'Désactiver' : 'Activer' }} cet indicateur ?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $indicateur->est_actif ? 'warning' : 'success' }}" 
                                                title="{{ $indicateur->est_actif ? 'Désactiver' : 'Activer' }}">
                                            <i class="fas {{ $indicateur->est_actif ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="{{ route('direction.indicateurs.destroy', $indicateur->id_indicateur) }}" style="display: inline-block;" 
                                          onsubmit="return confirm('Supprimer cet indicateur ? Cette action est irréversible.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-chart-line fa-2x text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0 small">Aucun indicateur trouvé</p>
                                <a href="{{ route('direction.indicateurs.create') }}" class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Ajouter un indicateur
                                </a>
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
                {{ $indicateurs->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // DONNÉES POUR LES GRAPHIQUES
    // ============================================
    
    // 🔥 Données des statuts
    const statusData = {
        'Actifs': {{ $stats['actifs'] ?? 0 }},
        'Inactifs': {{ $stats['inactifs'] ?? 0 }}
    };
    const statusLabels = Object.keys(statusData);
    const statusValues = Object.values(statusData);
    const statusColors = ['#0e9f6e', '#e02424'];
    
    // 🔥 Données des catégories d'indicateurs
    const categoryData = @json($stats['categories_data'] ?? []);
    const categoryLabels = Object.keys(categoryData).length > 0 ? Object.keys(categoryData) : ['Financiers', 'Activité', 'Performance', 'Gestion', 'Autres'];
    const categoryValues = Object.values(categoryData).length > 0 ? Object.values(categoryData) : [5, 4, 3, 2, 1];
    const categoryColors = ['#1B6EF3', '#0e9f6e', '#f5a623', '#7e3af2', '#e02424'];

    // ============================================
    // 1. GRAPHIQUE RÉPARTITION PAR STATUT (DONUT)
    // ============================================
    if (document.getElementById('statusChart')) {
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: statusColors,
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
                            font: { size: 10, weight: '500' },
                            boxWidth: 14,
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.raw + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                animation: { animateRotate: true, duration: 1200 }
            }
        });
    }

    // ============================================
    // 2. GRAPHIQUE INDICATEURS PAR CATÉGORIE (BARRES)
    // ============================================
    if (document.getElementById('categoryChart')) {
        const ctxCat = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctxCat, {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Nombre d\'indicateurs',
                    data: categoryValues,
                    backgroundColor: categoryColors,
                    borderColor: categoryColors,
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
                                return context.raw + ' indicateur(s)';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.06)' },
                        ticks: {
                            stepSize: 1,
                            font: { size: 10 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10 }
                        }
                    }
                },
                animation: { duration: 800 }
            }
        });
    }
});
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
    .stat-card {
        transition: transform 0.3s, box-shadow 0.3s;
        cursor: default;
        border-radius: 14px;
        position: relative;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }
    .stat-card .stat-icon {
        position: absolute;
        top: 10px;
        right: 15px;
        opacity: 0.2;
    }
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    .stat-label {
        font-size: 0.7rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table th, .table td {
        white-space: nowrap;
        vertical-align: middle;
    }
    .table th {
        font-weight: 600;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .table td {
        font-size: 12px;
    }
    .badge {
        font-size: 0.65rem;
        padding: 3px 10px;
        border-radius: 20px;
        font-weight: 500;
    }
    .btn-group .btn {
        padding: 0.15rem 0.4rem;
        font-size: 0.65rem;
    }
    .chart-container {
        min-height: 150px;
    }
    code.text-primary {
        background: #e8f0fe;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
    }
    .icon-bg {
        flex-shrink: 0;
    }
    .pagination {
        margin-bottom: 0;
    }
    
    @media (max-width: 576px) {
        .stat-number {
            font-size: 1.5rem !important;
        }
        .table td, .table th {
            font-size: 10px;
        }
        .btn-group .btn {
            padding: 0.1rem 0.3rem;
            font-size: 0.55rem;
        }
        .badge {
            font-size: 0.55rem;
            padding: 2px 6px;
        }
        .chart-container {
            min-height: 200px;
        }
    }
    @media (min-width: 768px) {
        .stat-number {
            font-size: 2.5rem !important;
        }
        .chart-container {
            min-height: 200px;
        }
    }
    @media (min-width: 992px) {
        .stat-number {
            font-size: 3rem !important;
        }
        .chart-container {
            min-height: 280px;
        }
    }
</style>
@endpush
@endsection