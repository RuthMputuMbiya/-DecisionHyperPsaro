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
                                
                                {{-- 🔥 SÉLECTION DES INDICATEURS PRÉ-DÉFINIS --}}
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    <strong>Astuce :</strong> Sélectionnez un indicateur pré-défini pour remplir automatiquement les champs !
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Indicateur pré-défini</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-list"></i></span>
                                        <select id="predefinedSelect" class="form-select">
                                            <option value="">-- Choisir un indicateur pré-défini --</option>
                                            @foreach($predefinedIndicators as $indicator)
                                                <option value="{{ $indicator['code_kpi'] }}">
                                                    [{{ $indicator['code_kpi'] }}] {{ $indicator['nom_kpi'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom de l'indicateur <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-tag"></i></span>
                                    <input type="text" name="nom_kpi" id="nom_kpi" class="form-control @error('nom_kpi') is-invalid @enderror" 
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
                                    <input type="text" name="code_kpi" id="code_kpi" class="form-control @error('code_kpi') is-invalid @enderror" 
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
                                    <select name="unite" id="unite" class="form-select @error('unite') is-invalid @enderror" required>
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
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="Description détaillée de l'indicateur...">{{ old('description') }}</textarea>
                                </div>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            {{-- 🔥 FORMULE DE CALCUL (VISIBLE MAINTENANT) --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Formule de calcul <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-calculator"></i></span>
                                    <input type="text" name="formule" id="formule" class="form-control @error('formule') is-invalid @enderror" 
                                           value="{{ old('formule') }}" placeholder="Ex: SUM(ventes.montant_usd)">
                                </div>
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Formule automatique</small>
                                @error('formule')
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
                                    <input type="number" name="seuil_alerte_min" id="seuil_alerte_min" class="form-control @error('seuil_alerte_min') is-invalid @enderror" 
                                           value="{{ old('seuil_alerte_min') }}" step="0.01" placeholder="Valeur minimale">
                                </div>
                                <small class="text-muted">En dessous de ce seuil → alerte</small>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Seuil maximum</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-arrow-up"></i></span>
                                    <input type="number" name="seuil_alerte_max" id="seuil_alerte_max" class="form-control @error('seuil_alerte_max') is-invalid @enderror" 
                                           value="{{ old('seuil_alerte_max') }}" step="0.01" placeholder="Valeur maximale">
                                </div>
                                <small class="text-muted">Au-dessus de ce seuil → alerte</small>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Couleur d'alerte</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-palette"></i></span>
                                    <select name="couleur_alerte" id="couleur_alerte" class="form-select">
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
                                    <input type="text" name="icone" id="icone" class="form-control @error('icone') is-invalid @enderror" 
                                           value="{{ old('icone', 'fas fa-chart-line') }}" placeholder="fas fa-chart-line">
                                </div>
                                <small class="text-muted">Ex: fas fa-chart-line, fas fa-dollar-sign, fas fa-percent</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Aperçu</label>
                                <div class="preview-box p-3 rounded text-center" style="background: #f8fafc;">
                                    <i class="{{ old('icone', 'fas fa-chart-line') }}" id="iconPreview" style="font-size: 32px; color: #1a56db;"></i>
                                    <div class="mt-2">
                                        <span class="badge bg-primary" id="previewCode">KPI</span>
                                        <span class="badge bg-secondary" id="previewUnite">Unité</span>
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
    // 🔥 DONNÉES DES INDICATEURS PRÉ-DÉFINIS
    const predefinedIndicators = @json($predefinedIndicators);
    
    // Références aux champs
    const select = document.getElementById('predefinedSelect');
    const nomKpi = document.getElementById('nom_kpi');
    const codeKpi = document.getElementById('code_kpi');
    const unite = document.getElementById('unite');
    const description = document.getElementById('description');
    const formule = document.getElementById('formule');
    const seuilMin = document.getElementById('seuil_alerte_min');
    const seuilMax = document.getElementById('seuil_alerte_max');
    const couleurAlerte = document.getElementById('couleur_alerte');
    const icone = document.getElementById('icone');
    const iconPreview = document.getElementById('iconPreview');
    const previewCode = document.getElementById('previewCode');
    const previewUnite = document.getElementById('previewUnite');
    
    // 🔥 ÉVÉNEMENT DE SÉLECTION
    select.addEventListener('change', function() {
        const code = this.value;
        
        if (!code) {
            // Réinitialiser les champs
            nomKpi.value = '';
            codeKpi.value = '';
            unite.value = '';
            description.value = '';
            formule.value = '';
            seuilMin.value = '';
            seuilMax.value = '';
            icone.value = 'fas fa-chart-line';
            return;
        }
        
        // Trouver l'indicateur sélectionné
        const indicator = predefinedIndicators.find(k => k.code_kpi === code);
        
        if (indicator) {
            // 🔥 Remplir automatiquement tous les champs
            nomKpi.value = indicator.nom_kpi;
            codeKpi.value = indicator.code_kpi;
            unite.value = indicator.unite;
            description.value = indicator.description;
            formule.value = indicator.formule;
            seuilMin.value = indicator.seuil_alerte_min || '';
            seuilMax.value = indicator.seuil_alerte_max || '';
            couleurAlerte.value = indicator.couleur_alerte || '#e02424';
            icone.value = indicator.icone || 'fas fa-chart-line';
            
            // 🔥 Mettre à jour l'aperçu
            iconPreview.className = icone.value;
            previewCode.textContent = codeKpi.value;
            previewUnite.textContent = unite.value;
            
            // Effet visuel
            const cardBody = document.querySelector('.card-body');
            cardBody.style.transition = 'background-color 0.3s';
            cardBody.style.backgroundColor = '#f0f7ff';
            setTimeout(() => {
                cardBody.style.backgroundColor = '';
            }, 500);
            
            // Afficher un message de confirmation
            showToast('✅ Indicateur chargé : ' + indicator.nom_kpi, 'success');
        }
    });
    
    // 🔥 PRÉVISUALISATION DE L'ICÔNE EN TEMPS RÉEL
    icone.addEventListener('input', function() {
        iconPreview.className = this.value || 'fas fa-chart-line';
    });
    
    // 🔥 PRÉVISUALISATION DU CODE KPI
    codeKpi.addEventListener('input', function() {
        previewCode.textContent = this.value || 'KPI';
    });
    
    // 🔥 PRÉVISUALISATION DE L'UNITÉ
    unite.addEventListener('change', function() {
        previewUnite.textContent = this.value || 'Unité';
    });
    
    // 🔥 TOAST NOTIFICATION PERSONNALISÉE
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
        toast.role = 'alert';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }
</script>

<style>
    .powerbi-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        background: #fff;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .powerbi-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.10);
    }
    .card-header-custom {
        background: #f8fafc;
        border-radius: 16px 16px 0 0;
    }
    .form-control, .form-select {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 10px 16px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #1B6EF3;
        box-shadow: 0 0 0 3px rgba(27, 110, 243, 0.1);
    }
    .input-group-text {
        border-radius: 10px 0 0 10px;
        border: 1px solid #e2e8f0;
        border-right: none;
        background: #f8fafc;
    }
    .input-group .form-control, .input-group .form-select {
        border-radius: 0 10px 10px 0;
    }
    .preview-box {
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        min-height: 80px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: border-color 0.3s;
    }
    .preview-box:hover {
        border-color: #1B6EF3;
    }
    #predefinedSelect {
        cursor: pointer;
    }
    #predefinedSelect:focus {
        border-color: #1B6EF3;
        box-shadow: 0 0 0 3px rgba(27, 110, 243, 0.1);
    }
    .alert-info {
        background: #ebf5ff;
        border: 1px solid #b8d4f0;
        color: #1a56db;
        border-radius: 12px;
    }
</style>
@endpush
@endsection