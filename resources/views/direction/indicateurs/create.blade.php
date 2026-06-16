{{-- resources/views/direction/indicateurs/create.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Ajouter un indicateur')
@section('page-title', 'Ajouter un indicateur de performance')
@section('breadcrumb', 'Direction / Indicateurs / Ajouter')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon" style="width: 48px; height: 48px; background: linear-gradient(135deg, #1B6EF3, #7e3af2); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-line fa-2x text-white"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Nouvel indicateur KPI</h4>
                            <p class="text-muted mb-0">Définissez un indicateur clé de performance</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('direction.indicateurs.store') }}">
                    @csrf
                    <div class="card-body p-4">
                        <div class="row g-4">
                            {{-- Informations générales --}}
                            <div class="col-12">
                                <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Informations générales</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom de l'indicateur <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-tag"></i></span>
                                    <input type="text" name="nom_kpi" class="form-control @error('nom_kpi') is-invalid @enderror" 
                                           value="{{ old('nom_kpi') }}" required placeholder="Ex: Chiffre d'affaires">
                                </div>
                                @error('nom_kpi')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Code KPI <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-code"></i></span>
                                    <input type="text" name="code_kpi" class="form-control @error('code_kpi') is-invalid @enderror" 
                                           value="{{ old('code_kpi') }}" required placeholder="Ex: CA, MARGE, ROI">
                                </div>
                                <small class="text-muted">Code unique en majuscules</small>
                                @error('code_kpi')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Unité <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-ruler"></i></span>
                                    <select name="unite" class="form-select @error('unite') is-invalid @enderror" required>
                                        <option value="">Sélectionner</option>
                                        @foreach($unites as $unite)
                                            <option value="{{ $unite }}" {{ old('unite') == $unite ? 'selected' : '' }}>{{ $unite }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('unite')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-align-left"></i></span>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="Description détaillée de l'indicateur...">{{ old('description') }}</textarea>
                                </div>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            {{-- Seuils d'alerte --}}
                            <div class="col-12 mt-3">
                                <h6 class="mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Seuils d'alerte</h6>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Seuil minimum</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-arrow-down"></i></span>
                                    <input type="number" name="seuil_alerte_min" class="form-control @error('seuil_alerte_min') is-invalid @enderror" 
                                           value="{{ old('seuil_alerte_min') }}" step="0.01" placeholder="Valeur minimale">
                                </div>
                                <small class="text-muted">En dessous de ce seuil → alerte</small>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Seuil maximum</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-arrow-up"></i></span>
                                    <input type="number" name="seuil_alerte_max" class="form-control @error('seuil_alerte_max') is-invalid @enderror" 
                                           value="{{ old('seuil_alerte_max') }}" step="0.01" placeholder="Valeur maximale">
                                </div>
                                <small class="text-muted">Au-dessus de ce seuil → alerte</small>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Couleur d'alerte</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-palette"></i></span>
                                    <select name="couleur_alerte" class="form-select">
                                        @foreach($couleurs as $code => $nom)
                                            <option value="{{ $code }}" {{ old('couleur_alerte', '#e02424') == $code ? 'selected' : '' }} style="background: {{ $code }}20;">
                                                <span style="display: inline-block; width: 16px; height: 16px; background: {{ $code }}; border-radius: 3px; margin-right: 8px;"></span>
                                                {{ $nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            {{-- Icône et style --}}
                            <div class="col-12 mt-3">
                                <h6 class="mb-3"><i class="fas fa-paintbrush me-2"></i>Apparence</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Icône Font Awesome</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-icons"></i></span>
                                    <input type="text" name="icone" class="form-control @error('icone') is-invalid @enderror" 
                                           value="{{ old('icone', 'fas fa-chart-line') }}" placeholder="fas fa-chart-line">
                                </div>
                                <small class="text-muted">Ex: fas fa-chart-line, fas fa-dollar-sign, fas fa-percent</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Aperçu</label>
                                <div class="preview-box p-3 rounded text-center" style="background: #f8fafc;">
                                    <i class="{{ old('icone', 'fas fa-chart-line') }}" id="iconPreview" style="font-size: 32px; color: #1a56db;"></i>
                                    <div class="mt-2">
                                        <span class="badge bg-primary">{{ old('code_kpi', 'KPI') }}</span>
                                        <span class="badge bg-secondary">{{ old('unite', 'Unité') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent p-4 border-top">
                        <div class="d-flex justify-content-between gap-3">
                            <a href="{{ route('direction.indicateurs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Créer l'indicateur
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
    // Prévisualisation de l'icône
    const iconInput = document.querySelector('input[name="icone"]');
    const iconPreview = document.getElementById('iconPreview');
    
    iconInput.addEventListener('input', function() {
        const iconClass = this.value;
        iconPreview.className = iconClass;
    });
</script>
@endpush
@endsection