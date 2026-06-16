{{-- resources/views/service/investissements/create.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Nouvel investissement')
@section('page-title', 'Enregistrer un investissement')
@section('breadcrumb', 'Service / Investissements / Nouveau')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon" style="width: 48px; height: 48px; background: linear-gradient(135deg, #7e3af2, #6b2fd9); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-line fa-2x text-white"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Nouvel investissement</h4>
                            <p class="text-muted mb-0">Service {{ $service->nom_service }}</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('service.investissements.store') }}">
                    @csrf
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Référence <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-hashtag"></i></span>
                                    <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror" 
                                           value="{{ old('reference', $reference) }}" required>
                                </div>
                                @error('reference')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom du projet <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-project-diagram"></i></span>
                                    <input type="text" name="nom_projet" class="form-control @error('nom_projet') is-invalid @enderror" 
                                           value="{{ old('nom_projet') }}" required placeholder="Ex: Rénovation, Nouveau logiciel...">
                                </div>
                                @error('nom_projet')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date de l'investissement</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-calendar"></i></span>
                                    <input type="date" name="date_investissement" class="form-control" value="{{ old('date_investissement', date('Y-m-d')) }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Montant <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" name="montant" class="form-control @error('montant') is-invalid @enderror" 
                                           value="{{ old('montant') }}" step="0.01" required>
                                </div>
                                @error('montant')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Devise <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-money-bill"></i></span>
                                    <select name="id_devise" class="form-select @error('id_devise') is-invalid @enderror" required>
                                        @foreach($devises as $devise)
                                            <option value="{{ $devise->id_devise }}" {{ old('id_devise') == $devise->id_devise ? 'selected' : '' }}>
                                                {{ $devise->code }} - {{ $devise->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Durée (mois) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-clock"></i></span>
                                    <input type="number" name="duree_mois" class="form-control @error('duree_mois') is-invalid @enderror" 
                                           value="{{ old('duree_mois', 12) }}" min="1" max="120" required>
                                </div>
                                @error('duree_mois')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">ROI estimé (%)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-percent"></i></span>
                                    <input type="number" name="roi_estime" class="form-control @error('roi_estime') is-invalid @enderror" 
                                           value="{{ old('roi_estime') }}" step="0.1" placeholder="Retour sur investissement estimé">
                                </div>
                                <small class="text-muted">Estimation du retour sur investissement</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-tag"></i></span>
                                    <select name="statut" class="form-select @error('statut') is-invalid @enderror" required>
                                        <option value="En cours" {{ old('statut') == 'En cours' ? 'selected' : '' }}>En cours</option>
                                        <option value="Terminé" {{ old('statut') == 'Terminé' ? 'selected' : '' }}>Terminé</option>
                                        <option value="Annulé" {{ old('statut') == 'Annulé' ? 'selected' : '' }}>Annulé</option>
                                        <option value="En attente" {{ old('statut') == 'En attente' ? 'selected' : '' }}>En attente</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3" placeholder="Description détaillée du projet d'investissement...">{{ old('description') }}</textarea>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <div class="d-flex gap-3">
                                <i class="fas fa-lightbulb fa-2x"></i>
                                <div>
                                    <strong class="d-block mb-1">Conseil</strong>
                                    <p class="mb-0 small">Le ROI (Return On Investment) est un indicateur clé pour évaluer la rentabilité de votre investissement. Un ROI positif indique un investissement rentable.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent p-4 border-top">
                        <div class="d-flex justify-content-between gap-3">
                            <a href="{{ route('service.investissements.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer l'investissement
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection