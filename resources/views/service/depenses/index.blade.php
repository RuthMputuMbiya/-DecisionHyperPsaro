{{-- resources/views/service/depenses/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Gestion des dépenses')
@section('page-title', 'Tableau de bord des dépenses')
@section('breadcrumb', 'Service / Dépenses')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">

    {{-- ============================================ --}}
    {{-- EN-TÊTE --}}
    {{-- ============================================ --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-receipt me-2" style="color: #e02424;"></i>
                Gestion des Dépenses
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
                <span class="badge bg-success ms-2">
                    <i class="fas fa-sync-alt me-1 fa-spin"></i> Données en temps réel
                </span>
            </p>
        </div>
       
            <a href="{{ route('service.depenses.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Nouvelle dépense
            </a>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- STATISTIQUES GLOBALES (KPI CARDS) --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #e02424, #b91c1c); color: white;">
                <div class="stat-icon">
                    <i class="fas fa-file-invoice fa-2x"></i>
                </div>
                <div class="stat-number display-6 fw-bold">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-label small">Total dépenses</div>
                <div class="stat-trend small">
                  
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave fa-2x"></i>
                </div>
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['total_montant'] ?? 0, 0, ',', ' ') }}</div>
                <div class="stat-label small">Montant total ({{ $devise ?? 'USD' }})</div>
                <div class="stat-trend small">
                  
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day fa-2x"></i>
                </div>
                <div class="stat-number display-6 fw-bold">{{ $stats['mois_courant'] ?? 0 }}</div>
                <div class="stat-label small">Dépenses ce mois</div>
                <div class="stat-trend small">
                    <i class="fas fa-clock me-1"></i> {{ now()->translatedFormat('F Y') }}
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); color: white;">
                <div class="stat-icon">
                    <i class="fas fa-tags fa-2x"></i>
                </div>
                <div class="stat-number display-6 fw-bold">{{ $stats['categories']->count() ?? 0 }}</div>
                <div class="stat-label small">Catégories</div>
                <div class="stat-trend small">
                    <i class="fas fa-list me-1"></i> Types de dépenses
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- GRAPHIQUES --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        
        {{-- GRAPHIQUE 1: Évolution des Dépenses --}}
        <div class="col-12 col-md-6">
            <div class="powerbi-card h-100">
                <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fs-6 fs-md-5">
                            <i class="fas fa-chart-line me-2" style="color: #e02424;"></i>
                            Évolution des Dépenses
                        </h5>
                        <small class="text-muted">6 derniers mois</small>
                    </div>
                    <span class="badge bg-danger">{{ number_format($stats['total_montant'] ?? 0, 0, ',', ' ') }} {{ $devise ?? 'USD' }}</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 250px;">
                        <canvas id="evolutionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRAPHIQUE 2: Répartition par Catégorie (DONUT) --}}
        <div class="col-12 col-md-6">
            <div class="powerbi-card h-100">
                <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fs-6 fs-md-5">
                            <i class="fas fa-chart-pie me-2" style="color: #f5a623;"></i>
                            Répartition par Catégorie
                        </h5>
                        <small class="text-muted">Répartition des dépenses</small>
                    </div>
                    <span class="badge bg-warning">{{ $stats['categories']->count() ?? 0 }} catégories</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 250px;">
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
            <form method="GET" action="{{ route('service.depenses.index') }}" class="row g-2 g-md-3">
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted">Date début</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted">Date fin</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted">Catégorie</label>
                    <select name="categorie" class="form-select form-select-sm">
                        <option value="">Toutes</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat }}" {{ request('categorie') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- TABLEAU DES DÉPENSES --}}
    {{-- ============================================ --}}
    <div class="powerbi-card">
        <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fs-6 fs-md-5">
                <i class="fas fa-table me-2" style="color: #e02424;"></i>
                Liste des dépenses
            </h5>
            <span class="badge bg-primary">{{ $depenses->count() ?? 0 }} enregistrements</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-sm table-md">
                    <thead class="table-light">
                        <tr>
                            <th class="py-2">Référence</th>
                            <th class="py-2">Date</th>
                            <th class="py-2">Montant</th>
                            <th class="py-2">Catégorie</th>
                            <th class="py-2 d-none d-md-table-cell">Justificatif</th>
                            <th class="py-2 d-none d-lg-table-cell">Description</th>
                            <th class="py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($depenses ?? [] as $depense)
                        <tr>
                            <td class="py-2"><code class="fw-bold">{{ $depense->reference ?? '#' . str_pad($depense->id_depense, 5, '0', STR_PAD_LEFT) }}</code></td>
                            <td class="py-2">{{ $depense->created_at->translatedFormat('d/m/Y') }}</td>
                            <td class="py-2 fw-bold text-danger">{{ number_format($depense->montant ?? 0, 0, ',', ' ') }} {{ $depense->devise->code ?? 'USD' }}</td>
                            <td class="py-2">
                                <span class="badge" style="background: #e0242420; color: #e02424;">
                                    {{ $depense->categorie_depense ?? 'Non catégorisé' }}
                                </span>
                            </td>
                            <td class="py-2 d-none d-md-table-cell">{{ $depense->justificatif ?? '-' }}</td>
                            <td class="py-2 d-none d-lg-table-cell">{{ Str::limit($depense->description, 30) ?? '-' }}</td>
                            <td class="py-2">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('service.depenses.show', $depense->id_depense) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('service.depenses.edit', $depense->id_depense) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteDepense({{ $depense->id_depense }})" title="Supprimer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-receipt fa-2x text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0 small">Aucune dépense enregistrée</p>
                                <a href="{{ route('service.depenses.create') }}" class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Ajouter une dépense
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
                {{ $depenses->withQueryString()->links() }}
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
    // DONNÉES DES GRAPHIQUES
    // ============================================
    const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'];
    const depensesData = @json($stats['evolution'] ?? array_fill(0, 6, 0));
    
    // Données des catégories
    const categoryData = @json($stats['categories_data'] ?? []);
    const categoryLabels = Object.keys(categoryData);
    const categoryValues = Object.values(categoryData);
    
    // Couleurs professionnelles
    const colors = {
        red: ['#e02424', '#E55050', '#EA7C7C', '#EFA8A8', '#F5D4D4'],
        blue: ['#1B6EF3', '#4A8AF4', '#78A6F7', '#A6C2FA', '#D4DEFD'],
        orange: ['#f5a623', '#F7B84D', '#F9CA77', '#FBDCA1', '#FDEDCB'],
        green: ['#0e9f6e', '#3EB28B', '#6EC5A8', '#9ED8C5', '#CEEBE2'],
        purple: ['#7e3af2', '#9A61F5', '#B688F8', '#D2AFFB', '#EED6FD']
    };

    // ============================================
    // 1. GRAPHIQUE ÉVOLUTION DES DÉPENSES (BARRES)
    // ============================================
    if (document.getElementById('evolutionChart')) {
        const ctxEvo = document.getElementById('evolutionChart').getContext('2d');
        new Chart(ctxEvo, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Dépenses (USD)',
                    data: depensesData,
                    backgroundColor: ['rgba(224, 36, 36, 0.7)', 'rgba(224, 36, 36, 0.5)', 'rgba(224, 36, 36, 0.3)'],
                    borderColor: '#e02424',
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
                                return 'Dépenses: ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' USD';
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
                animation: {
                    duration: 800,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // ============================================
    // 2. GRAPHIQUE RÉPARTITION PAR CATÉGORIE (DONUT)
    // ============================================
    if (document.getElementById('categoryChart')) {
        const ctxCat = document.getElementById('categoryChart').getContext('2d');
        const allColors = [...colors.red, ...colors.blue, ...colors.orange, ...colors.green, ...colors.purple];
        
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: categoryLabels.length > 0 ? categoryLabels : ['Aucune donnée'],
                datasets: [{
                    data: categoryValues.length > 0 ? categoryValues : [1],
                    backgroundColor: allColors.slice(0, categoryLabels.length || 1),
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
                                return context.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' USD (' + percentage + '%)';
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 1200
                }
            }
        });
    }
});

    // ============================================
    // FONCTIONS D'ACTION
    // ============================================
    function deleteDepense(id) {
        Swal.fire({
            title: 'Supprimer la dépense ?',
            text: "Cette action est irréversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e02424',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('service/depenses') }}/" + id + "/delete";
            }
        });
    }
    
    function exportDepenses() {
        Swal.fire({
            title: 'Exporter les dépenses',
            html: `
                <div class="mb-3 text-start">
                    <label class="form-label small fw-semibold">Date début</label>
                    <input type="date" id="export_date_from" class="form-control">
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label small fw-semibold">Date fin</label>
                    <input type="date" id="export_date_to" class="form-control">
                </div>
                <div class="text-start">
                    <label class="form-label small fw-semibold">Catégorie</label>
                    <select id="export_categorie" class="form-control">
                        <option value="">Toutes</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Exporter',
            preConfirm: () => {
                const date_from = document.getElementById('export_date_from').value;
                const date_to = document.getElementById('export_date_to').value;
                const categorie = document.getElementById('export_categorie').value;
                let url = "{{ route('service.depenses.export') }}";
                let params = [];
                if (date_from) params.push('date_from=' + date_from);
                if (date_to) params.push('date_to=' + date_to);
                if (categorie) params.push('categorie=' + categorie);
                if (params.length) url += '?' + params.join('&');
                window.location.href = url;
            }
        });
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