{{-- resources/views/service/achats/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Gestion des achats')
@section('page-title', 'Tableau de bord des achats')
@section('breadcrumb', 'Service / Achats')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">

    {{-- EN-TÊTE --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-truck me-2" style="color: #f5a623;"></i>
                Gestion des Achats
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
                <span class="badge bg-success ms-2">
                    <i class="fas fa-sync-alt me-1 fa-spin"></i> Données en temps réel
                </span>
            </p>
        </div>
    
            <a href="{{ route('service.achats.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Nouvel achat
            </a>
        </div>
    </div>

    {{-- STATISTIQUES GLOBALES --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-icon"><i class="fas fa-boxes fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-label small">Total achats</div>
                
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-icon"><i class="fas fa-money-bill-wave fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['total_montant'] ?? 0, 0, ',', ' ') }}</div>
                <div class="stat-label small">Montant total ({{ $devise ?? 'USD' }})</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white;">
                <div class="stat-icon"><i class="fas fa-calendar-day fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ $stats['mois_courant'] ?? 0 }}</div>
                <div class="stat-label small">Achats ce mois</div>
                <div class="stat-trend small">
                    <i class="fas fa-clock me-1"></i> {{ now()->translatedFormat('F Y') }}
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); color: white;">
                <div class="stat-icon"><i class="fas fa-calculator fa-2x"></i></div>
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['moyenne_achat'] ?? 0, 0, ',', ' ') }}</div>
                <div class="stat-label small">Moyenne par achat</div>
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
                            <i class="fas fa-chart-line me-2" style="color: #f5a623;"></i>
                            Évolution des Achats
                        </h5>
                        <small class="text-muted">6 derniers mois</small>
                    </div>
                    <span class="badge bg-warning">{{ number_format($stats['total_montant'] ?? 0, 0, ',', ' ') }} {{ $devise ?? 'USD' }}</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 250px;">
                        <canvas id="evolutionChart"></canvas>
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
                            Répartition des Achats
                        </h5>
                        <small class="text-muted">Par période</small>
                    </div>
                    <span class="badge bg-warning">{{ $stats['total'] ?? 0 }} achats</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 250px;">
                        <canvas id="repartitionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTRES --}}
    <div class="powerbi-card mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('service.achats.index') }}" class="row g-2 g-md-3">
                <div class="col-6 col-md-4">
                    <label class="form-label small text-muted">Date début</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-6 col-md-4">
                    <label class="form-label small text-muted">Date fin</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-12 col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('service.achats.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-undo-alt me-1"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLEAU --}}
    <div class="powerbi-card">
        <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fs-6 fs-md-5">
                <i class="fas fa-table me-2" style="color: #f5a623;"></i>
                Liste des achats
            </h5>
            <span class="badge bg-primary">{{ $achats->count() ?? 0 }} enregistrements</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-sm table-md">
                    <thead class="table-light">
                        <tr>
                            <th class="py-2">Référence</th>
                            <th class="py-2">Date</th>
                            <th class="py-2">Montant</th>
                            <th class="py-2 d-none d-md-table-cell">Bon de commande</th>
                            <th class="py-2 d-none d-lg-table-cell">Description</th>
                            <th class="py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($achats ?? [] as $achat)
                        <tr>
                            <td class="py-2"><code class="fw-bold">{{ $achat->reference ?? '#' . str_pad($achat->id_achat, 5, '0', STR_PAD_LEFT) }}</code></td>
                            <td class="py-2">{{ $achat->created_at->translatedFormat('d/m/Y') }}</td>
                            <td class="py-2 fw-bold text-warning">{{ number_format($achat->montant ?? 0, 0, ',', ' ') }} {{ $achat->devise->code ?? 'USD' }}</td>
                            <td class="py-2 d-none d-md-table-cell">{{ $achat->bon_commande ?? '-' }}</td>
                            <td class="py-2 d-none d-lg-table-cell">{{ Str::limit($achat->description, 30) ?? '-' }}</td>
                            <td class="py-2">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('service.achats.show', $achat->id_achat) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('service.achats.edit', $achat->id_achat) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteAchat({{ $achat->id_achat }})" title="Supprimer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-truck fa-2x text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0 small">Aucun achat enregistré</p>
                                <a href="{{ route('service.achats.create') }}" class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Ajouter un achat
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
                {{ $achats->withQueryString()->links() }}
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
    const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'];
    const achatsData = @json($stats['evolution'] ?? array_fill(0, 6, 0));
    
    // Couleurs
    const colors = {
        orange: ['#f5a623', '#F7B84D', '#F9CA77', '#FBDCA1', '#FDEDCB'],
        blue: ['#1B6EF3', '#4A8AF4', '#78A6F7', '#A6C2FA', '#D4DEFD']
    };

    // 1. Évolution des Achats (BARRES)
    if (document.getElementById('evolutionChart')) {
        const ctxEvo = document.getElementById('evolutionChart').getContext('2d');
        new Chart(ctxEvo, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Achats (USD)',
                    data: achatsData,
                    backgroundColor: ['rgba(245, 166, 35, 0.7)', 'rgba(245, 166, 35, 0.5)', 'rgba(245, 166, 35, 0.3)'],
                    borderColor: '#f5a623',
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
                                return 'Achats: ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' USD';
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

    // 2. Répartition des Achats (DONUT)
    if (document.getElementById('repartitionChart')) {
        const ctxRep = document.getElementById('repartitionChart').getContext('2d');
        
        // Données de répartition par mois
        const repartitionLabels = months;
        const repartitionValues = achatsData;
        
        new Chart(ctxRep, {
            type: 'doughnut',
            data: {
                labels: repartitionLabels,
                datasets: [{
                    data: repartitionValues,
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
                animation: { animateRotate: true, duration: 1200 }
            }
        });
    }
});

function deleteAchat(id) {
    Swal.fire({
        title: 'Supprimer l\'achat ?',
        text: "Cette action est irréversible.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e02424',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ url('service/achats') }}/" + id + "/delete";
        }
    });
}

function exportAchats() {
    Swal.fire({
        title: 'Exporter les achats',
        html: `
            <div class="mb-3 text-start">
                <label class="form-label small fw-semibold">Date début</label>
                <input type="date" id="export_date_from" class="form-control">
            </div>
            <div class="text-start">
                <label class="form-label small fw-semibold">Date fin</label>
                <input type="date" id="export_date_to" class="form-control">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Exporter',
        preConfirm: () => {
            const date_from = document.getElementById('export_date_from').value;
            const date_to = document.getElementById('export_date_to').value;
            let url = "{{ route('service.achats.export') }}";
            let params = [];
            if (date_from) params.push('date_from=' + date_from);
            if (date_to) params.push('date_to=' + date_to);
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