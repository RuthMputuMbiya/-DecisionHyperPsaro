{{-- resources/views/direction/decisions/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Décisions stratégiques')
@section('page-title', 'Décisions stratégiques')
@section('breadcrumb', 'Direction / Décisions')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-gavel me-2" style="color: #1B6EF3;"></i>
                Décisions stratégiques
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
            <button class="btn btn-outline-success" onclick="exportDecisions()">
                <i class="fas fa-download me-2"></i>Exporter
            </button>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['total'] }}</div>
                <div class="stat-label">Total décisions</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['validees'] }}</div>
                <div class="stat-label">Validées</div>
                <div class="stat-trend">{{ $stats['taux_validation'] }}% des décisions</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #e02424, #b91c1c); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['rejetees'] }}</div>
                <div class="stat-label">Rejetées</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['impact_total'], 0, ',', ' ') }} USD</div>
                <div class="stat-label">Impact total</div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('direction.decisions.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Recherche</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Titre, description..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="En attente" {{ request('status') == 'En attente' ? 'selected' : '' }}>En attente</option>
                        <option value="Validée" {{ request('status') == 'Validée' ? 'selected' : '' }}>Validée</option>
                        <option value="Rejetée" {{ request('status') == 'Rejetée' ? 'selected' : '' }}>Rejetée</option>
                        <option value="En cours" {{ request('status') == 'En cours' ? 'selected' : '' }}>En cours</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Période</label>
                    <select name="period" class="form-select">
                        <option value="">Toutes</option>
                        <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                        <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Ce mois</option>
                        <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>Cette année</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tableau des décisions --}}
    <div class="powerbi-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Référence</th>
                            <th>Titre</th>
                            <th>Résultat simulé</th>
                            <th>Impact trésorerie</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Créé par</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($decisions as $decision)
                        <tr>
                            <td><code class="fw-bold">#{{ str_pad($decision->id_decision, 5, '0', STR_PAD_LEFT) }}</code></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-gavel" style="color: #7e3af2;"></i>
                                    <strong>{{ $decision->titre }}</strong>
                                </div>
                                <small class="text-muted">{{ Str::limit($decision->description, 50) }}</small>
                            </td>
                            <td class="fw-bold text-primary">{{ number_format($decision->resultat_simule, 0, ',', ' ') }} USD</td>
                            <td class="fw-bold {{ $decision->impact_tresorerie >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $decision->impact_tresorerie >= 0 ? '+' : '' }}{{ number_format($decision->impact_tresorerie, 0, ',', ' ') }} USD
                            </td>
                            <td>
                                @php
                                    $statusBadge = [
                                        'En attente' => 'warning',
                                        'Validée' => 'success',
                                        'Rejetée' => 'danger',
                                        'En cours' => 'info'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusBadge[$decision->statut] ?? 'secondary' }}">
                                    <i class="fas {{ $decision->statut == 'Validée' ? 'fa-check-circle' : ($decision->statut == 'Rejetée' ? 'fa-times-circle' : 'fa-clock') }} me-1"></i>
                                    {{ $decision->statut }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $decision->created_at->translatedFormat('d/m/Y H:i') }}</small>
                            </td>
                            <td>{{ $decision->createur->nom ?? '-' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('direction.decisions.show', $decision->id_decision) }}" class="btn btn-outline-info" title="Détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($decision->statut == 'En attente')
                                        <button type="button" class="btn btn-outline-success" onclick="validateDecision({{ $decision->id_decision }})" title="Valider">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="rejectDecision({{ $decision->id_decision }})" title="Rejeter">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Aucune décision trouvée</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            {{ $decisions->withQueryString()->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function validateDecision(decisionId) {
        Swal.fire({
            title: 'Valider cette décision ?',
            text: "Cette action est irréversible. La décision sera officiellement validée.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0e9f6e',
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('direction/decisions') }}/" + decisionId + "/validate";
            }
        });
    }
    
    function rejectDecision(decisionId) {
        Swal.fire({
            title: 'Rejeter cette décision ?',
            input: 'textarea',
            inputLabel: 'Motif du rejet',
            inputPlaceholder: 'Expliquez pourquoi cette décision est rejetée...',
            inputAttributes: {
                'aria-label': 'Motif du rejet'
            },
            showCancelButton: true,
            confirmButtonColor: '#e02424',
            confirmButtonText: 'Oui, rejeter',
            cancelButtonText: 'Annuler',
            preConfirm: (motif) => {
                if (!motif) {
                    Swal.showValidationMessage('Veuillez indiquer un motif');
                }
                return motif;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                window.location.href = "{{ url('direction/decisions') }}/" + decisionId + "/reject?motif=" + encodeURIComponent(result.value);
            }
        });
    }
    
    function exportDecisions() {
        Swal.fire({
            title: 'Exporter les décisions',
            html: `
                <select id="exportFormat" class="form-control mt-2">
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                </select>
            `,
            showCancelButton: true,
            confirmButtonText: 'Exporter',
            preConfirm: () => {
                const format = document.getElementById('exportFormat').value;
                window.location.href = "{{ route('direction.decisions.export') }}?format=" + format;
            }
        });
    }
</script>
@endpush

<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .table tbody tr:hover {
        background: #f8fafc;
        cursor: pointer;
    }
    .badge {
        font-size: 11px;
        padding: 5px 10px;
    }
</style>
@endsection