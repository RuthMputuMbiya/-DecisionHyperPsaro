{{-- resources/views/direction/users/edit.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Modifier un utilisateur')
@section('page-title', 'Modifier un utilisateur')
@section('breadcrumb', 'Direction / Utilisateurs / Modifier')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon" style="width: 48px; height: 48px; background: linear-gradient(135deg, #f5a623, #e69500); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user-edit fa-2x text-white"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Modifier l'utilisateur</h4>
                            <p class="text-muted mb-0">{{ $user->nom }} - {{ $user->matricule }}</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('direction.users.update', $user->id_utilisateur) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Matricule</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-id-card"></i></span>
                                    <input type="text" class="form-control" value="{{ $user->matricule }}" disabled>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
                                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" 
                                           value="{{ old('nom', $user->nom) }}" required>
                                </div>
                                @error('nom')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $user->email) }}" required>
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rôle <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-tag"></i></span>
                                    <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required id="roleSelect">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id_role }}" {{ old('role_id', $user->role_id) == $role->id_role ? 'selected' : '' }}>
                                                {{ $role->nom_role }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('role_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6" id="serviceField" style="{{ $user->role->code_role == 'CHEF' ? '' : 'display: none;' }}">
                                <label class="form-label fw-semibold">Service</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-building"></i></span>
                                    <select name="service_id" class="form-select @error('service_id') is-invalid @enderror">
                                        <option value="">Sélectionner un service</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id_service }}" {{ old('service_id', $user->service_id) == $service->id_service ? 'selected' : '' }}>
                                                {{ $service->nom_service }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('service_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-phone"></i></span>
                                    <input type="tel" name="telephone" class="form-control @error('telephone') is-invalid @enderror" 
                                           value="{{ old('telephone', $user->telephone) }}" placeholder="+243 xxx xxx xxx">
                                </div>
                                @error('telephone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Statut</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-toggle-on"></i></span>
                                    <select name="est_actif" class="form-select">
                                        <option value="1" {{ $user->est_actif ? 'selected' : '' }}>Actif</option>
                                        <option value="0" {{ !$user->est_actif ? 'selected' : '' }}>Inactif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-4">
                            <div class="d-flex gap-3">
                                <i class="fas fa-key fa-2x"></i>
                                <div>
                                    <strong class="d-block mb-1">Gestion du mot de passe</strong>
                                    <p class="mb-0 small">Pour réinitialiser le mot de passe, utilisez le bouton "Réinitialiser" dans la liste des utilisateurs.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent p-4 border-top">
                        <div class="d-flex justify-content-between gap-3">
                            <a href="{{ route('direction.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('roleSelect').addEventListener('change', function() {
        const serviceField = document.getElementById('serviceField');
        const selectedOption = this.options[this.selectedIndex];
        const roleName = selectedOption.text;
        
        if (roleName === 'Chef de Service' || roleName === 'CHEF') {
            serviceField.style.display = 'block';
        } else {
            serviceField.style.display = 'none';
            document.querySelector('select[name="service_id"]').value = '';
        }
    });
</script>
@endpush
@endsection