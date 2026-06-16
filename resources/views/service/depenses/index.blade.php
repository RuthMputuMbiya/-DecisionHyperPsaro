{{-- resources/views/service/depenses/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Gestion des dépenses')
@section('page-title', 'Dépenses - ' . $service->nom_service)
@section('breadcrumb', 'Service / Dépenses')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-receipt me-2" style="color: #e02424;"></i>
                Dépenses
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success" onclick="exportDepenses()">
                <i class="fas fa-download me-2"></i>Exporter
            </button>
            <a href="{{ route('service.depenses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouvelle dépense
            </a>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #e02424, #b91c1c); color: white;">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['total']) }}</div>
                <div class="stat-label">Total dépenses</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['total_montant'], 0, ',', ' ') }}</div>
                <div class="stat-label">Montant total ({{ $devise }})</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['mois_courant'] }}</div>
                <div class="stat-label">Ce mois</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['categories']->count() }}</div>
                <div class="stat-label">Catégories</div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('service.depenses.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Date début</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Catégorie</label>
                    <select name="categorie" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('categorie') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
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

    {{-- Répartition par catégorie --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-chart-pie me-2" style="color: #e02424;"></i>Répartition par catégorie</h5>
                <canvas id="categoryChart" height="250"></canvas>
            </div>
        </div>
    </div>

    {{-- Tableau des dépenses --}}
    <div class="powerbi-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Référence</th>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Catégorie</th>
                            <th>Justificatif</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($depenses as $depense)
                        <tr>
                            <td><code>{{ $depense->reference }}</code></td>
                            <td>{{ $depense->created_at->translatedFormat('d/m/Y') }}</td>
                            <td class="fw-bold text-danger">{{ number_format($depense->montant, 0, ',', ' ') }} {{ $depense->devise->code ?? 'USD' }}</td>
                            <td>
                                <span class="badge" style="background: #e0242420; color: #e02424;">
                                    {{ $depense->categorie_depense }}
                                </span>
                            </td>
                            <td>{{ $depense->justificatif ?? '-' }}</td>
                            <td>{{ Str::limit($depense->description, 40) ?? '-' }}</td>
                            <td>
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
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-receipt fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Aucune dépense enregistrée</p>
                                <a href="{{ route('service.depenses.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="fas fa-plus me-2"></i>Ajouter une dépense
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            {{ $depenses->withQueryString()->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

    
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
                <div class="mb-3">
                    <label>Date début</label>
                    <input type="date" id="export_date_from" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Date fin</label>
                    <input type="date" id="export_date_to" class="form-control">
                </div>
                <div>
                    <label>Catégorie</label>
                    <select id="export_categorie" class="form-control">
                        <option value="">Toutes</option>
                        @foreach($categories as $cat)
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

<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
</style>
@endsection