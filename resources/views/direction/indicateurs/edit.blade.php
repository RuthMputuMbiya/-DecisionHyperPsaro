{{-- resources/views/direction/indicateurs/edit.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Modifier un indicateur')
@section('page-title', 'Modifier l\'indicateur')
@section('breadcrumb', 'Direction / Indicateurs / Modifier')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon" style="width: 48px; height: 48px; background: linear-gradient(135deg, #f5a623, #e69500); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-edit fa-2x text-white"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Modifier l'indicateur</h4>
                            <p class="text-muted mb-0">{{ $indicateur->nom_kpi }} ({{ $indicateur->code_kpi }})</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('direction.indicateurs.update', $indicateur->id_indicateur) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom de l'indicateur <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-tag"></i></span>
                                    <input type="text" name="nom_kpi" class="form-control @error('nom_kpi') is-invalid @enderror" 
                                           value="{{ old('nom_kpi', $indicateur->nom_kpi) }}" required>
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
                                           value="{{ old('code_kpi', $indicateur->code_kpi) }}" required>
                                </div>
                                @error('code_kpi')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Unité <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-ruler"></i></span>
                                    <select name="unite" class="form-select @error('unite') is-invalid @enderror" required>
                                        @foreach($unites as $unite)
                                            <option value="{{ $unite }}" {{ old('unite', $indicateur->unite) == $unite ? 'selected' : '' }}>{{ $unite }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('unite')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3">{{ old('description', $indicateur->description) }}</textarea>
                            </div>
                            
                            {{-- 🔥 FORMULE DE CALCUL (VISIBLE) --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Formule de calcul <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-calculator"></i></span>
                                    <input type="text" name="formule" class="form-control @error('formule') is-invalid @enderror" 
                                           value="{{ old('formule', $indicateur->formule) }}" placeholder="Ex: SUM(ventes.montant_usd)">
                                </div>
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Formule SQL pour le calcul automatique de l'indicateur</small>
                                @error('formule')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Seuil minimum</label>
                                <input type="number" name="seuil_alerte_min" class="form-control @error('seuil_alerte_min') is-invalid @enderror" 
                                       value="{{ old('seuil_alerte_min', $indicateur->seuil_alerte_min) }}" step="0.01">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Seuil maximum</label>
                                <input type="number" name="seuil_alerte_max" class="form-control @error('seuil_alerte_max') is-invalid @enderror" 
                                       value="{{ old('seuil_alerte_max', $indicateur->seuil_alerte_max) }}" step="0.01">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Couleur d'alerte</label>
                                <select name="couleur_alerte" class="form-select">
                                    @foreach($couleurs as $code => $nom)
                                        <option value="{{ $code }}" {{ old('couleur_alerte', $indicateur->couleur_alerte) == $code ? 'selected' : '' }}>
                                            {{ $nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Icône</label>
                                <input type="text" name="icone" class="form-control @error('icone') is-invalid @enderror" 
                                       value="{{ old('icone', $indicateur->icone) }}" placeholder="fas fa-chart-line">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Statut</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="est_actif" value="1" 
                                           id="estActif" {{ $indicateur->est_actif ? 'checked' : '' }} style="width: 40px; height: 20px;">
                                    <label class="form-check-label ms-2" for="estActif">
                                        <span class="badge {{ $indicateur->est_actif ? 'bg-success' : 'bg-danger' }}" id="statutLabel">
                                            {{ $indicateur->est_actif ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </label>
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
    document.getElementById('estActif')?.addEventListener('change', function() {
        const label = document.getElementById('statutLabel');
        if (this.checked) {
            label.className = 'badge bg-success';
            label.textContent = 'Actif';
        } else {
            label.className = 'badge bg-danger';
            label.textContent = 'Inactif';
        }
    });
</script>
@endpush
@endsection