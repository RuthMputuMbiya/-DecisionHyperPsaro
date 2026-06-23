{{-- resources/views/direction/services/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Gestion des services')
@section('page-title', 'Tableau de bord des services')
@section('breadcrumb', 'Direction / Services')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">

    {{-- ============================================ --}}
    {{-- EN-TÊTE --}}
    {{-- ============================================ --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-building me-2" style="color: #1B6EF3;"></i>
                Services de l'entreprise
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
                <span class="badge bg-success ms-2">
                    <i class="fas fa-sync-alt me-1 fa-spin"></i> Données en temps réel
                </span>
            </p>
        </div>
        <a href="{{ route('direction.services.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Nouveau service
        </a>
    </div>

    {{-- ============================================ --}}
    {{-- STATISTIQUES GLOBALES --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-icon"><i class="fas fa-building fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-label small">Total services</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white;">
                <div class="stat-icon"><i class="fas fa-check-circle fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ $stats['actifs'] ?? 0 }}</div>
                <div class="stat-label small">Services actifs</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #e02424, #b91c1c); color: white;">
                <div class="stat-icon"><i class="fas fa-ban fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ $stats['inactifs'] ?? 0 }}</div>
                <div class="stat-label small">Services inactifs</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-icon"><i class="fas fa-users fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['employes_total'] ?? 0) }}</div>
                <div class="stat-label small">Employés</div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- GRAPHIQUES DE PERFORMANCE --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        
        {{-- GRAPHIQUE 1: Répartition par Statut (DONUT COLORÉ) --}}
        <div class="col-12 col-md-6">
            <div class="powerbi-card h-100">
                <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fs-6 fs-md-5">
                            <i class="fas fa-chart-pie me-2" style="color: #7e3af2;"></i>
                            Répartition des Services
                        </h5>
                        <small class="text-muted">Par statut</small>
                    </div>
                    <span class="badge bg-purple">{{ $stats['total'] ?? 0 }} services</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 280px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <span class="badge bg-success me-1">● Actifs</span> 
                            <span class="badge bg-danger me-1">● Inactifs</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRAPHIQUE 2: Budget par Service (BARRES COLORÉES) --}}
        <div class="col-12 col-md-6">
            <div class="powerbi-card h-100">
                <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fs-6 fs-md-5">
                            <i class="fas fa-chart-bar me-2" style="color: #1B6EF3;"></i>
                            Budget par Service
                        </h5>
                        <small class="text-muted">Répartition des budgets</small>
                    </div>
                    <span class="badge bg-primary">{{ number_format($stats['budget_total'] ?? 0, 0, ',', ' ') }} USD</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 280px;">
                        <canvas id="budgetChart"></canvas>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">Total budgets: <strong>{{ number_format($stats['budget_total'] ?? 0, 0, ',', ' ') }} USD</strong></small>
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
            <form method="GET" action="{{ route('direction.services.index') }}" class="row g-2 g-md-3 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label small text-muted fw-semibold">Recherche</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Nom, description ou chef de service..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted fw-semibold">Statut</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="actif" {{ request('status') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ request('status') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i> Filtrer
                    </button>
                </div>
                <div class="col-12 col-md-2">
                    <a href="{{ route('direction.services.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-undo-alt me-1"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- TABLEAU DES SERVICES AVEC DÉTAILS --}}
    {{-- ============================================ --}}
    <div class="powerbi-card">
        <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fs-6 fs-md-5">
                <i class="fas fa-table me-2" style="color: #1B6EF3;"></i>
                Détails des services
            </h5>
            <span class="badge bg-primary">{{ $services->count() ?? 0 }} enregistrements</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-sm table-md">
                    <thead class="table-light">
                        <tr>
                            <th class="py-2">#</th>
                            <th class="py-2">Service</th>
                            <th class="py-2 d-none d-sm-table-cell">Description</th>
                            <th class="py-2 d-none d-md-table-cell">Chef</th>
                            <th class="py-2">Budget</th>
                            <th class="py-2 text-center">Employés</th>
                            <th class="py-2">Statut</th>
                            <th class="py-2 d-none d-lg-table-cell">Créé le</th>
                            <th class="py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $index => $service)
                        <tr>
                            <td class="py-2 text-center">{{ $services->firstItem() + $index }}</td>
                            <td class="py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="service-dot" style="width: 10px; height: 10px; background: {{ $service->couleur ?? '#1a56db' }}; border-radius: 50%; display: inline-block;"></span>
                                    <strong class="small">{{ $service->nom_service }}</strong>
                                    @if($service->est_actif)
                                        <span class="badge bg-success" style="font-size: 7px; padding: 1px 6px;">✓</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-2 d-none d-sm-table-cell">
                                <span class="small text-muted">{{ Str::limit($service->description, 35) ?? '-' }}</span>
                            </td>
                            <td class="py-2 d-none d-md-table-cell">
                                <span class="small">{{ $service->chef_service ?? '-' }}</span>
                            </td>
                            <td class="py-2">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-semibold small">{{ number_format($service->budget_annuel ?? 0, 0, ',', ' ') }}</span>
                                    <span class="badge {{ $service->budget_devise == 'USD' ? 'bg-primary' : ($service->budget_devise == 'CDF' ? 'bg-warning text-dark' : 'bg-info') }}" style="font-size: 7px; padding: 1px 6px;">
                                        {{ $service->budget_devise ?? 'USD' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-2 text-center">
                                <span class="badge bg-secondary rounded-pill px-3">{{ $service->utilisateurs_count ?? $service->utilisateurs->count() ?? 0 }}</span>
                            </td>
                            <td class="py-2">
                                @if($service->est_actif)
                                    <span class="badge bg-success px-3 py-1">Actif</span>
                                @else
                                    <span class="badge bg-danger px-3 py-1">Inactif</span>
                                @endif
                            </td>
                            <td class="py-2 d-none d-lg-table-cell">
                                <small class="text-muted">{{ $service->created_at->translatedFormat('d/m/Y') }}</small>
                            </td>
                            <td class="py-2">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('direction.services.show', $service->id_service) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('direction.services.edit', $service->id_service) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('direction.services.toggle-status', $service->id_service) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-{{ $service->est_actif ? 'warning' : 'success' }}" 
                                                title="{{ $service->est_actif ? 'Désactiver' : 'Activer' }}"
                                                onclick="return confirm('{{ $service->est_actif ? 'Désactiver' : 'Activer' }} ce service ?')">
                                            <i class="fas {{ $service->est_actif ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('direction.services.destroy', $service->id_service) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Supprimer ce service ? Cette action est irréversible.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-building fa-2x text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0 small">Aucun service trouvé</p>
                                <a href="{{ route('direction.services.create') }}" class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Ajouter un service
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
                {{ $services->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // PALETTES DE COULEURS
    // ============================================
    const colorPalettes = {
        // 🔥 Couleurs pour la répartition des services
        statusColors: ['#0e9f6e', '#e02424'],
        // 🔥 Couleurs pour les budgets (8 couleurs vives)
        budgetColors: [
            '#1B6EF3', '#0e9f6e', '#f5a623', '#7e3af2', 
            '#e02424', '#3abff8', '#10b981', '#f97316',
            '#8b5cf6', '#14b8a6', '#ec4899', '#6366f1'
        ]
    };

    // ============================================
    // DONNÉES
    // ============================================
    
    // 🔥 Données des statuts
    const statusData = {
        'Actifs': {{ $stats['actifs'] ?? 0 }},
        'Inactifs': {{ $stats['inactifs'] ?? 0 }}
    };
    const statusLabels = Object.keys(statusData);
    const statusValues = Object.values(statusData);
    
    // 🔥 Données des budgets par service
    const budgetData = @json($stats['budgets_data'] ?? []);
    const budgetLabels = Object.keys(budgetData);
    const budgetValues = Object.values(budgetData);
    
    // Si pas de données de budget, utiliser des données de démonstration
    let finalBudgetLabels = budgetLabels;
    let finalBudgetValues = budgetValues;
    let finalBudgetColors = colorPalettes.budgetColors.slice(0, finalBudgetLabels.length);
    
    if (finalBudgetLabels.length === 0 || (finalBudgetLabels.length === 1 && finalBudgetLabels[0] === 'Aucun budget')) {
        // Utiliser les données réelles des services
        const realServices = @json($services->map(function($s) { 
            return ['nom' => $s->nom_service, 'budget' => $s->budget_annuel]; 
        }));
        
        const realData = realServices.filter(s => s.budget > 0);
        if (realData.length > 0) {
            finalBudgetLabels = realData.map(s => s.nom);
            finalBudgetValues = realData.map(s => s.budget);
            finalBudgetColors = colorPalettes.budgetColors.slice(0, finalBudgetLabels.length);
        } else {
            // Si vraiment aucune donnée, afficher un message
            finalBudgetLabels = ['Aucun service'];
            finalBudgetValues = [0];
            finalBudgetColors = ['#e9ecef'];
        }
    }

    // ============================================
    // 1. GRAPHIQUE RÉPARTITION PAR STATUT (DONUT COLORÉ)
    // ============================================
    if (document.getElementById('statusChart')) {
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: colorPalettes.statusColors,
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
    // 2. GRAPHIQUE BUDGET PAR SERVICE (BARRES COLORÉES)
    // ============================================
    if (document.getElementById('budgetChart')) {
        const ctxBud = document.getElementById('budgetChart').getContext('2d');
        
        new Chart(ctxBud, {
            type: 'bar',
            data: {
                labels: finalBudgetLabels,
                datasets: [{
                    label: 'Budget (USD)',
                    data: finalBudgetValues,
                    backgroundColor: finalBudgetColors.map(c => c + 'CC'),
                    borderColor: finalBudgetColors,
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
                                return 'Budget: ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' USD';
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
                        grid: { display: false },
                        ticks: {
                            maxRotation: 30,
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
    .service-dot {
        flex-shrink: 0;
    }
    .chart-container {
        min-height: 150px;
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