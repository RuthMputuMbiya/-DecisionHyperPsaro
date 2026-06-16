{{-- resources/views/direction/users/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Gestion des utilisateurs')
@section('page-title', 'Gestion des utilisateurs')
@section('breadcrumb', 'Direction / Utilisateurs')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-users me-2" style="color: #1B6EF3;"></i>
                Utilisateurs du système
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
            </p>
        </div>
        <a href="{{ route('direction.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouvel utilisateur
        </a>
    </div>

    {{-- Statistiques --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['total'] }}</div>
                <div class="stat-label">Total utilisateurs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['actifs'] }}</div>
                <div class="stat-label">Utilisateurs actifs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['inactifs'] }}</div>
                <div class="stat-label">Utilisateurs inactifs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['conseillers'] + $stats['chefs'] }}</div>
                <div class="stat-label">Personnel encadrant</div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('direction.users.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Recherche</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nom, email ou matricule..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Rôle</label>
                    <select name="role" class="form-select">
                        <option value="">Tous les rôles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id_role }}" {{ request('role') == $role->id_role ? 'selected' : '' }}>
                                {{ $role->nom_role }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="actif" {{ request('status') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ request('status') == 'inactif' ? 'selected' : '' }}>Inactif</option>
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

    {{-- Tableau des utilisateurs --}}
    <div class="powerbi-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Service</th>
                            <th>Téléphone</th>
                            <th>Statut</th>
                            <th>Date création</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td><code>{{ $user->matricule }}</code></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-sm" style="width: 32px; height: 32px; background: linear-gradient(135deg, #1B6EF3, #7e3af2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">
                                            {{ strtoupper(substr($user->nom, 0, 1)) }}
                                        </div>
                                        <strong>{{ $user->nom }}</strong>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @php
                                        $roleColors = [
                                            'DG' => 'danger',
                                            'CONSEILLER' => 'info',
                                            'CHEF' => 'success'
                                        ];
                                        $color = $roleColors[$user->role->code_role ?? ''] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $user->role->nom_role ?? '-' }}</span>
                                </td>
                                <td>{{ $user->service->nom_service ?? '-' }}</td>
                                <td>{{ $user->telephone ?? '-' }}</td>
                                <td>
                                    @if($user->est_actif)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-danger">Inactif</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $user->created_at->translatedFormat('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('direction.users.show', $user->id_utilisateur) }}" class="btn btn-outline-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('direction.users.edit', $user->id_utilisateur) }}" class="btn btn-outline-primary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-warning" onclick="resetPassword({{ $user->id_utilisateur }})" title="Réinitialiser mot de passe">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-{{ $user->est_actif ? 'danger' : 'success' }}" 
                                                onclick="toggleStatus({{ $user->id_utilisateur }}, {{ $user->est_actif ? 'false' : 'true' }})"
                                                title="{{ $user->est_actif ? 'Désactiver' : 'Activer' }}">
                                            <i class="fas {{ $user->est_actif ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="deleteUser({{ $user->id_utilisateur }}, '{{ $user->nom }}')" title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">Aucun utilisateur trouvé</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                {{ $users->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleStatus(userId, activate) {
        Swal.fire({
            title: activate ? 'Activer l\'utilisateur ?' : 'Désactiver l\'utilisateur ?',
            text: activate ? 'L\'utilisateur pourra se reconnecter.' : 'L\'utilisateur ne pourra plus se connecter.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: activate ? '#0e9f6e' : '#e02424',
            confirmButtonText: activate ? 'Oui, activer' : 'Oui, désactiver',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('direction/users') }}/" + userId + "/toggle-status";
            }
        });
    }
    
    function resetPassword(userId) {
        Swal.fire({
            title: 'Réinitialiser le mot de passe ?',
            text: 'Le mot de passe sera réinitialisé à "password123". L\'utilisateur devra le changer à la prochaine connexion.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f5a623',
            confirmButtonText: 'Oui, réinitialiser',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('direction/users') }}/" + userId + "/reset-password";
            }
        });
    }
    
    function deleteUser(userId, userName) {
        Swal.fire({
            title: 'Supprimer l\'utilisateur ?',
            html: `Vous allez supprimer <strong>${userName}</strong>.<br>Cette action est irréversible.`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#e02424',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('direction/users') }}/" + userId + "/delete";
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
    }
</style>
@endsection