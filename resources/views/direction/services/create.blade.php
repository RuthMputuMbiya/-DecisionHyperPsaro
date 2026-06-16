{{-- resources/views/direction/services/create.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Ajouter un service')
@section('page-title', 'Ajouter un service')
@section('breadcrumb', 'Direction / Services / Ajouter')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon" style="width: 48px; height: 48px; background: linear-gradient(135deg, #1B6EF3, #7e3af2); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-building fa-2x text-white"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Nouveau service</h4>
                            <p class="text-muted mb-0">Créez un nouveau département ou service</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('direction.services.store') }}">
                    @csrf
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom du service <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-tag"></i></span>
                                    <input type="text" name="nom_service" class="form-control @error('nom_service') is-invalid @enderror" 
                                           value="{{ old('nom_service') }}" required placeholder="Ex: Commercial, Finance, RH...">
                                </div>
                                @error('nom_service')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Couleur</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-palette"></i></span>
                                    <input type="color" name="couleur" class="form-control form-control-color" style="width: 60px; padding: 4px;" value="{{ old('couleur', '#1a56db') }}">
                                    <input type="text" class="form-control" id="colorHex" value="#1a56db" style="flex: 1;">
                                </div>
                                <small class="text-muted">Couleur d'identification du service</small>
                                @error('couleur')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Chef de service</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-user-tie"></i></span>
                                    <input type="text" name="chef_service" class="form-control @error('chef_service') is-invalid @enderror" 
                                           value="{{ old('chef_service') }}" placeholder="Nom du responsable">
                                </div>
                                @error('chef_service')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Budget annuel</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-coins"></i></span>
                                    <input type="number" name="budget_annuel" class="form-control @error('budget_annuel') is-invalid @enderror" 
                                           value="{{ old('budget_annuel', 0) }}" step="1000" min="0" placeholder="Montant">
                                    <select name="budget_devise" class="form-select" style="width: 100px;">
                                        <option value="USD" {{ old('budget_devise') == 'USD' ? 'selected' : '' }}>USD</option>
                                        <option value="CDF" {{ old('budget_devise') == 'CDF' ? 'selected' : '' }}>CDF</option>
                                        <option value="EUR" {{ old('budget_devise') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    </select>
                                </div>
                                @error('budget_annuel')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-align-left"></i></span>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="4" placeholder="Description du service, ses missions, ses objectifs...">{{ old('description') }}</textarea>
                                </div>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent p-4 border-top">
                        <div class="d-flex justify-content-between gap-3">
                            <a href="{{ route('direction.services.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Créer le service
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
    const colorPicker = document.querySelector('input[type="color"]');
    const colorHex = document.getElementById('colorHex');
    
    colorPicker.addEventListener('input', function() {
        colorHex.value = this.value;
    });
    
    colorHex.addEventListener('input', function() {
        if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
            colorPicker.value = this.value;
        }
    });
</script>
@endpush
@endsection