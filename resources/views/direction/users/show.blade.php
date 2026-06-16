{{-- resources/views/direction/users/show.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Détails utilisateur')
@section('page-title', 'Détails utilisateur')
@section('breadcrumb', 'Direction / Utilisateurs / Détails')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        <div class="col-lg-4">
            {{-- Carte Profil --}}
            <div class="powerbi-card text-center">
                <div class="card-body p-4">
                    <div class="user-avatar-lg" style="width: 100px; height: 100px; background: linear-gradient(135deg, #1B6EF3, #7e3af2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <span class="display-4 text-white">{{ strtoupper(substr($user->nom, 0, 1)) }}</span>
                    </div>
                    <h3 class="mb-1">{{ $user->nom }}</h3>
                    <p class="text-muted mb-3">{{ $user->role->nom_role ?? '-' }}</p>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-2 rounded" style="background: #f8fafc;">
                                <div class="small text-muted">Statut</div>
                                <div class="fw-bold">
                                    @if($user->est_actif)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-danger">Inactif</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded" style="background: #f8fafc;">
                                <div class="small text-muted">Première connexion</div>
                                <div class="fw-bold">
                                    @if($user->premiere_connexion)
                                        <span class="badge bg-warning">En attente</span>
                                    @else
                                        <span class="badge bg-success">Effectuée</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-start">
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted"><i class="fas fa-id-card me-2"></i>Matricule</span>
                            <span class="fw-semibold"><code>{{ $user->matricule }}</code></span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted"><i class="fas fa-envelope me-2"></i>Email</span>
                            <span>{{ $user->email }}</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted"><i class="fas fa-phone me-2"></i>Téléphone</span>
                            <span>{{ $user->telephone ?? '-' }}</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted"><i class="fas fa-building me-2"></i>Service</span>
                            <span>{{ $user->service->nom_service ?? '-' }}</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted"><i class="fas fa-calendar-plus me-2"></i>Date création</span>
                            <span>{{ $user->created_at->translatedFormat('d/m/Y à H:i') }}</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted"><i class="fas fa-calendar-check me-2"></i>Dernière connexion</span>
                            <span>{{ $user->last_login_at ? $user->last_login_at->translatedFormat('d/m/Y à H:i') : 'Jamais' }}</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted"><i class="fas fa-key me-2"></i>Dernier changement MDP</span>
                            <span>{{ $user->last_password_change ? $user->last_password_change->translatedFormat('d/m/Y à H:i') : 'Jamais' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            {{-- Statistiques d'activité --}}
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                        <div class="stat-number display-5 fw-bold">{{ $stats['total_operations'] }}</div>
                        <div class="stat-label">Opérations</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white;">
                        <div class="stat-number display-5 fw-bold">{{ $stats['total_decisions'] }}</div>
                        <div class="stat-label">Décisions</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); color: white;">
                        <div class="stat-number display-5 fw-bold">{{ $stats['total_simulations'] }}</div>
                        <div class="stat-label">Simulations</div>
                    </div>
                </div>
            </div>
            
            {{-- Actions --}}
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Actions</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('direction.users.edit', $user->id_utilisateur) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Modifier le profil
                        </a>
                        <button type="button" class="btn btn-warning" onclick="resetPassword({{ $user->id_utilisateur }})">
                            <i class="fas fa-key me-2"></i>Réinitialiser mot de passe
                        </button>
                        @if($user->est_actif)
                            <button type="button" class="btn btn-danger" onclick="toggleStatus({{ $user->id_utilisateur }}, false)">
                                <i class="fas fa-ban me-2"></i>Désactiver le compte
                            </button>
                        @else
                            <button type="button" class="btn btn-success" onclick="toggleStatus({{ $user->id_utilisateur }}, true)">
                                <i class="fas fa-check-circle me-2"></i>Activer le compte
                            </button>
                        @endif
                    </div>
                </div>
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
</script>
@endpush
@endsection