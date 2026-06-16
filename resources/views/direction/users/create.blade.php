{{-- resources/views/direction/users/create.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Ajouter un utilisateur')
@section('page-title', 'Ajouter un utilisateur')
@section('breadcrumb', 'Direction / Utilisateurs / Ajouter')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon" style="width: 48px; height: 48px; background: linear-gradient(135deg, #1B6EF3, #7e3af2); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user-plus fa-2x text-white"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Nouvel utilisateur</h4>
                            <p class="text-muted mb-0">Remplissez le formulaire pour créer un compte utilisateur</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('direction.users.store') }}" id="userForm">
                    @csrf
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-user"></i></span>
                                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" 
                                           value="{{ old('nom') }}" required placeholder="PERPETUE">
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
                                           value="{{ old('email') }}" required placeholder="utilisateur@hyperpsaro.com">
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
                                        <option value="">Sélectionner un rôle</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id_role }}" {{ old('role_id') == $role->id_role ? 'selected' : '' }}>
                                                {{ $role->nom_role }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('role_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6" id="serviceField" style="display: none;">
                                <label class="form-label fw-semibold">Service <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-building"></i></span>
                                    <select name="service_id" class="form-select @error('service_id') is-invalid @enderror">
                                        <option value="">Sélectionner un service</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id_service }}" {{ old('service_id') == $service->id_service ? 'selected' : '' }}>
                                                {{ $service->nom_service }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="text-muted">Requis pour les chefs de service</small>
                                @error('service_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-phone"></i></span>
                                    <input type="tel" name="telephone" class="form-control @error('telephone') is-invalid @enderror" 
                                           value="{{ old('telephone') }}" placeholder="+243 xxx xxx xxx">
                                </div>
                                @error('telephone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <div class="d-flex gap-3">
                                <i class="fas fa-info-circle fa-2x"></i>
                                <div>
                                    <strong class="d-block mb-1">Informations importantes</strong>
                                    <ul class="mb-0 small">
                                        <li>Le matricule sera généré automatiquement</li>
                                        <li>Le mot de passe par défaut est : <code>password123</code></li>
                                        <li>L'utilisateur devra changer son mot de passe à la première connexion</li>
                                    </ul>
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
                                <i class="fas fa-save me-2"></i>Créer l'utilisateur
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
        const roleId = this.value;
        const serviceField = document.getElementById('serviceField');
        const roleName = this.options[this.selectedIndex]?.text;
        
        if (roleName === 'Chef de Service' || roleName === 'CHEF') {
            serviceField.style.display = 'block';
            document.querySelector('select[name="service_id"]').required = true;
        } else {
            serviceField.style.display = 'none';
            document.querySelector('select[name="service_id"]').required = false;
        }
    });
    
    // Vérifier au chargement
    if (document.getElementById('roleSelect').value) {
        document.getElementById('roleSelect').dispatchEvent(new Event('change'));
    }
</script>
@endpush
@endsection