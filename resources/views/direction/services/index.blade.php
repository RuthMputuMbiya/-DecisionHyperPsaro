{{-- resources/views/direction/services/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Gestion des services')
@section('page-title', 'Gestion des services')
@section('breadcrumb', 'Direction / Services')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-building me-2" style="color: #1B6EF3;"></i>
                Services de l'entreprise
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
            </p>
        </div>
        <a href="{{ route('direction.services.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau service
        </a>
    </div>

    {{-- Statistiques --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['total'] }}</div>
                <div class="stat-label">Total services</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['actifs'] }}</div>
                <div class="stat-label">Services actifs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #e02424, #b91c1c); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['inactifs'] }}</div>
                <div class="stat-label">Services inactifs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['employes_total']) }}</div>
                <div class="stat-label">Employés</div>
            </div>
        </div>
    </div>

    {{-- Filtres sur une seule ligne --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('direction.services.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-muted fw-semibold">Recherche</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Nom, description ou chef de service..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-semibold">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="actif" {{ request('status') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ request('status') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filtrer
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('direction.services.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-undo-alt me-1"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tableau des services --}}
    <div class="powerbi-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%">Service</th>
                            <th style="width: 20%">Description</th>
                            <th style="width: 15%">Chef de service</th>
                            <th style="width: 12%">Budget annuel</th>
                            <th style="width: 8%">Employés</th>
                            <th style="width: 10%">Statut</th>
                            <th style="width: 10%">Date création</th>
                            <th style="width: 10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                        <tr>
                            <td class="align-middle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="service-dot" style="width: 10px; height: 10px; background: {{ $service->couleur }}; border-radius: 50%; display: inline-block;"></span>
                                    <strong class="small">{{ $service->nom_service }}</strong>
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="small text-muted">{{ Str::limit($service->description, 50) ?? '-' }}</span>
                            </td>
                            <td class="align-middle">
                                <span class="small">{{ $service->chef_service ?? '-' }}</span>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-semibold small">{{ number_format($service->budget_annuel, 0, ',', ' ') }}</span>
                                    <span class="badge {{ $service->budget_devise == 'USD' ? 'bg-primary' : ($service->budget_devise == 'CDF' ? 'bg-warning text-dark' : 'bg-info') }}" style="font-size: 9px; padding: 3px 8px;">
                                        {{ $service->budget_devise ?? 'USD' }}
                                    </span>
                                </div>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge bg-secondary rounded-pill px-3">{{ $service->utilisateurs_count ?? $service->utilisateurs->count() ?? 0 }}</span>
                            </td>
                            <td class="align-middle">
                                @if($service->est_actif)
                                    <span class="badge bg-success px-3 py-1">Actif</span>
                                @else
                                    <span class="badge bg-danger px-3 py-1">Inactif</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                <small class="text-muted">{{ $service->created_at->translatedFormat('d/m/Y') }}</small>
                            </td>
                            <td class="align-middle">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('direction.services.show', $service->id_service) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('direction.services.edit', $service->id_service) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-{{ $service->est_actif ? 'warning' : 'success' }}" 
                                            onclick="toggleStatus({{ $service->id_service }}, {{ $service->est_actif ? 'false' : 'true' }})"
                                            title="{{ $service->est_actif ? 'Désactiver' : 'Activer' }}">
                                        <i class="fas {{ $service->est_actif ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteService({{ $service->id_service }}, '{{ $service->nom_service }}')" title="Supprimer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-building fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Aucun service trouvé</p>
                                <a href="{{ route('direction.services.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="fas fa-plus me-2"></i>Ajouter un service
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
                    <i class="fas fa-database me-1"></i> Affichage de {{ $services->firstItem() ?? 0 }} à {{ $services->lastItem() ?? 0 }} sur {{ $services->total() }} services
                </small>
                <div>
                    {{ $services->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleStatus(serviceId, activate) {
        Swal.fire({
            title: activate ? 'Activer le service ?' : 'Désactiver le service ?',
            text: activate ? 'Le service sera à nouveau disponible.' : 'Le service ne sera plus disponible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: activate ? '#0e9f6e' : '#e02424',
            confirmButtonText: activate ? 'Oui, activer' : 'Oui, désactiver',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('direction/services') }}/" + serviceId + "/toggle-status";
            }
        });
    }
    
    function deleteService(serviceId, serviceName) {
        Swal.fire({
            title: 'Supprimer le service ?',
            html: `Vous allez supprimer le service <strong>${serviceName}</strong>.<br>Cette action est irréversible.`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#e02424',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('direction/services') }}/" + serviceId + "/delete";
            }
        });
    }
</script>
@endpush

<style>
    .stat-card {
        transition: transform 0.2s;
        cursor: pointer;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .table tbody tr:hover {
        background: #f8fafc;
        cursor: pointer;
    }
    .input-group-text {
        background-color: #f8fafc;
    }
    .form-control:focus {
        border-color: #1B6EF3;
        box-shadow: none;
    }
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
    }
    .badge {
        font-weight: 500;
    }
    .table th {
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        vertical-align: middle;
    }
    .table td {
        vertical-align: middle;
        font-size: 13px;
    }
    .align-middle {
        vertical-align: middle !important;
    }
    .pagination {
        margin-bottom: 0;
    }
    .rounded-pill {
        border-radius: 50px !important;
    }
</style>
@endsection