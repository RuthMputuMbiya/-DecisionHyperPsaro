{{-- resources/views/finance/simulations/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Simulations')
@section('page-title', 'Simulations financières')
@section('breadcrumb', 'Finance / Simulations')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-flask me-2" style="color: #7e3af2;"></i>
                Simulations financières
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }}
            </p>
        </div>
        <a href="{{ route('finance.simulations.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouvelle simulation
        </a>
    </div>

    {{-- Statistiques --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['total'] }}</div>
                <div class="stat-label">Total simulations</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['sauvegardees'] }}</div>
                <div class="stat-label">Sauvegardées</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['avec_decision'] }}</div>
                <div class="stat-label">Liées à une décision</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['moyenne_impact'], 0, ',', ' ') }}</div>
                <div class="stat-label">Impact moyen (USD)</div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.simulations.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small text-muted">Recherche</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nom de la simulation..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Période</label>
                    <select name="period" class="form-select">
                        <option value="">Toutes</option>
                        <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                        <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Ce mois</option>
                        <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>Cette année</option>
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

    {{-- Tableau des simulations --}}
    <div class="powerbi-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Impact trésorerie</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Créé par</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($simulations as $simulation)
                        @php
                            $resultats = is_string($simulation->resultats) ? json_decode($simulation->resultats, true) : $simulation->resultats;
                            $impact = $resultats['impact_tresorerie'] ?? 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-flask" style="color: #7e3af2;"></i>
                                    <strong>{{ $simulation->nom_simulation }}</strong>
                                </div>
                            </td>
                            <td>{{ Str::limit($simulation->description, 50) ?? '-' }}</td>
                            <td class="{{ $impact >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                {{ $impact >= 0 ? '+' : '' }}{{ number_format($impact, 0, ',', ' ') }} USD
                            </td>
                            <td>
                                @if($simulation->id_decision)
                                    <span class="badge bg-info"><i class="fas fa-link me-1"></i>Liée à décision</span>
                                @else
                                    <span class="badge bg-secondary">Sauvegardée</span>
                                @endif
                            </td>
                            <td>{{ $simulation->created_at->translatedFormat('d/m/Y H:i') }}</td>
                            <td>{{ $simulation->utilisateur->nom ?? '-' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('finance.simulations.show', $simulation->id_simulation) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('finance.simulations.duplicate', $simulation->id_simulation) }}" class="btn btn-outline-warning" title="Dupliquer">
                                        <i class="fas fa-copy"></i>
                                    </a>
                                    @if(!$simulation->id_decision)
                                        <button type="button" class="btn btn-outline-danger" onclick="deleteSimulation({{ $simulation->id_simulation }})" title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-flask fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Aucune simulation trouvée</p>
                                <a href="{{ route('finance.simulations.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="fas fa-plus me-2"></i>Créer votre première simulation
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            {{ $simulations->withQueryString()->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteSimulation(id) {
        Swal.fire({
            title: 'Supprimer la simulation ?',
            text: "Cette action est irréversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e02424',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('finance/simulations') }}/" + id + "/delete";
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