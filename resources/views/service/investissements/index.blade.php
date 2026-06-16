{{-- resources/views/service/investissements/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Gestion des investissements')
@section('page-title', 'Investissements - ' . $service->nom_service)
@section('breadcrumb', 'Service / Investissements')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-chart-line me-2" style="color: #7e3af2;"></i>
                Investissements
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success" onclick="exportInvestissements()">
                <i class="fas fa-download me-2"></i>Exporter
            </button>
            <a href="{{ route('service.investissements.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouvel investissement
            </a>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); color: white;">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['total']) }}</div>
                <div class="stat-label">Total projets</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['total_montant'], 0, ',', ' ') }}</div>
                <div class="stat-label">Investissement total ({{ $devise }})</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['en_cours'] }}</div>
                <div class="stat-label">Projets en cours</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['roi_moyen'], 1) }}%</div>
                <div class="stat-label">ROI moyen estimé</div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('service.investissements.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Date début</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="">Tous</option>
                        @foreach($statuts as $statut)
                            <option value="{{ $statut }}" {{ request('statut') == $statut ? 'selected' : '' }}>{{ $statut }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tableau des investissements --}}
    <div class="powerbi-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Référence</th>
                            <th>Projet</th>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Durée</th>
                            <th>ROI estimé</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($investissements as $invest)
                        <tr>
                            <td><code>{{ $invest->reference }}</code></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-chart-line" style="color: #7e3af2;"></i>
                                    <strong>{{ $invest->nom_projet }}</strong>
                                </div>
                            </td>
                            <td>{{ $invest->created_at->translatedFormat('d/m/Y') }}</td>
                            <td class="fw-bold text-primary">{{ number_format($invest->montant, 0, ',', ' ') }} {{ $invest->devise->code ?? 'USD' }}</td>
                            <td>{{ $invest->duree_mois }} mois</td>
                            <td>
                                @php
                                    $roiClass = 'text-success';
                                    if($invest->roi_estime < 0) $roiClass = 'text-danger';
                                    elseif($invest->roi_estime < 10) $roiClass = 'text-warning';
                                @endphp
                                <span class="fw-bold {{ $roiClass }}">{{ $invest->roi_estime ?? '-' }}%</span>
                            </td>
                            <td>
                                @php
                                    $statutClass = [
                                        'En cours' => 'primary',
                                        'Terminé' => 'success',
                                        'Annulé' => 'danger',
                                        'En attente' => 'warning'
                                    ][$invest->statut] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statutClass }}">{{ $invest->statut }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('service.investissements.show', $invest->id_investissement) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('service.investissements.edit', $invest->id_investissement) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteInvestissement({{ $invest->id_investissement }})" title="Supprimer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Aucun investissement enregistré</p>
                                <a href="{{ route('service.investissements.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="fas fa-plus me-2"></i>Ajouter un investissement
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            {{ $investissements->withQueryString()->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteInvestissement(id) {
        Swal.fire({
            title: 'Supprimer l\'investissement ?',
            text: "Cette action est irréversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e02424',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('service/investissements') }}/" + id + "/delete";
            }
        });
    }
    
    function exportInvestissements() {
        Swal.fire({
            title: 'Exporter les investissements',
            html: `
                <div class="mb-3">
                    <label>Date début</label>
                    <input type="date" id="export_date_from" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Date fin</label>
                    <input type="date" id="export_date_to" class="form-control">
                </div>
                <div>
                    <label>Statut</label>
                    <select id="export_statut" class="form-control">
                        <option value="">Tous</option>
                        @foreach($statuts as $statut)
                            <option value="{{ $statut }}">{{ $statut }}</option>
                        @endforeach
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Exporter',
            preConfirm: () => {
                const date_from = document.getElementById('export_date_from').value;
                const date_to = document.getElementById('export_date_to').value;
                const statut = document.getElementById('export_statut').value;
                let url = "{{ route('service.investissements.export') }}";
                let params = [];
                if (date_from) params.push('date_from=' + date_from);
                if (date_to) params.push('date_to=' + date_to);
                if (statut) params.push('statut=' + statut);
                if (params.length) url += '?' + params.join('&');
                window.location.href = url;
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
</style>
@endsection