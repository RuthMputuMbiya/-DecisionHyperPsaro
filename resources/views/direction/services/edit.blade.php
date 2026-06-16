{{-- resources/views/direction/services/edit.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Modifier un service')
@section('page-title', 'Modifier un service')
@section('breadcrumb', 'Direction / Services / Modifier')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon" style="width: 48px; height: 48px; background: linear-gradient(135deg, #f5a623, #e69500); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-edit fa-2x text-white"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Modifier le service</h4>
                            <p class="text-muted mb-0">{{ $service->nom_service }}</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('direction.services.update', $service->id_service) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom du service <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-tag"></i></span>
                                    <input type="text" name="nom_service" class="form-control @error('nom_service') is-invalid @enderror" 
                                           value="{{ old('nom_service', $service->nom_service) }}" required>
                                </div>
                                @error('nom_service')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Couleur</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-palette"></i></span>
                                    <input type="color" name="couleur" class="form-control form-control-color" style="width: 60px; padding: 4px;" value="{{ old('couleur', $service->couleur) }}">
                                    <input type="text" class="form-control" id="colorHex" value="{{ $service->couleur }}" style="flex: 1;">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Chef de service</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-user-tie"></i></span>
                                    <input type="text" name="chef_service" class="form-control @error('chef_service') is-invalid @enderror" 
                                           value="{{ old('chef_service', $service->chef_service) }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Budget annuel</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-coins"></i></span>
                                    <input type="number" name="budget_annuel" class="form-control @error('budget_annuel') is-invalid @enderror" 
                                           value="{{ old('budget_annuel', $service->budget_annuel) }}" step="1000" min="0">
                                    <select name="budget_devise" class="form-select" style="width: 100px;">
                                        <option value="USD" {{ old('budget_devise', $service->budget_devise) == 'USD' ? 'selected' : '' }}>USD</option>
                                        <option value="CDF" {{ old('budget_devise', $service->budget_devise) == 'CDF' ? 'selected' : '' }}>CDF</option>
                                        <option value="EUR" {{ old('budget_devise', $service->budget_devise) == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Statut</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-toggle-on"></i></span>
                                    <select name="est_actif" class="form-select">
                                        <option value="1" {{ $service->est_actif ? 'selected' : '' }}>Actif</option>
                                        <option value="0" {{ !$service->est_actif ? 'selected' : '' }}>Inactif</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-align-left"></i></span>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="4">{{ old('description', $service->description) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent p-4 border-top">
                        <div class="d-flex justify-content-between gap-3">
                            <a href="{{ route('direction.services.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer
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