{{-- resources/views/service/achats/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Gestion des achats')
@section('page-title', 'Achats - ' . $service->nom_service)
@section('breadcrumb', 'Service / Achats')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-truck me-2" style="color: #f5a623;"></i>
                Achats
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success" onclick="exportAchats()">
                <i class="fas fa-download me-2"></i>Exporter
            </button>
            <a href="{{ route('service.achats.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouvel achat
            </a>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['total']) }}</div>
                <div class="stat-label">Total achats</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['total_montant'], 0, ',', ' ') }}</div>
                <div class="stat-label">Montant total ({{ $devise }})</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['mois_courant'] }}</div>
                <div class="stat-label">Achats ce mois</div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('service.achats.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Date début</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                    <a href="{{ route('service.achats.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-undo-alt me-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tableau des achats --}}
    <div class="powerbi-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Référence</th>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Bon de commande</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($achats as $achat)
                        <tr>
                            <td><code>{{ $achat->reference }}</code></td>
                            <td>{{ $achat->created_at->translatedFormat('d/m/Y') }}</td>
                            <td class="fw-bold text-warning">{{ number_format($achat->montant, 0, ',', ' ') }} {{ $achat->devise->code ?? 'USD' }}</td>
                            <td>{{ $achat->bon_commande ?? '-' }}</td>
                            <td>{{ Str::limit($achat->description, 40) ?? '-' }}</td>
                            <td>
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
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-truck fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Aucun achat enregistré</p>
                                <a href="{{ route('service.achats.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="fas fa-plus me-2"></i>Ajouter un achat
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    <i class="fas fa-database me-1"></i> Affichage de {{ $achats->firstItem() ?? 0 }} à {{ $achats->lastItem() ?? 0 }} sur {{ $achats->total() }} achats
                </small>
                {{ $achats->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
                <div class="mb-3">
                    <label>Date début</label>
                    <input type="date" id="export_date_from" class="form-control">
                </div>
                <div>
                    <label>Date fin</label>
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
</style>
@endsection