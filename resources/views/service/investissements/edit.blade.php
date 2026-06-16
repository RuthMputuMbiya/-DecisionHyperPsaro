{{-- resources/views/service/investissements/edit.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Modifier investissement')
@section('page-title', 'Modifier l\'investissement')
@section('breadcrumb', 'Service / Investissements / Modifier')

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
                            <h4 class="mb-1 fw-bold">Modifier l'investissement</h4>
                            <p class="text-muted mb-0">Projet: {{ $investissement->nom_projet }}</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('service.investissements.update', $investissement->id_investissement) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Référence <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-hashtag"></i></span>
                                    <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror" 
                                           value="{{ old('reference', $investissement->reference) }}" required>
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
                                           value="{{ old('nom_projet', $investissement->nom_projet) }}" required>
                                </div>
                                @error('nom_projet')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-calendar"></i></span>
                                    <input type="text" class="form-control" value="{{ $investissement->created_at->translatedFormat('d/m/Y H:i') }}" disabled>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Montant <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" name="montant" class="form-control @error('montant') is-invalid @enderror" 
                                           value="{{ old('montant', $investissement->montant) }}" step="0.01" required>
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
                                            <option value="{{ $devise->id_devise }}" {{ old('id_devise', $investissement->id_devise) == $devise->id_devise ? 'selected' : '' }}>
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
                                           value="{{ old('duree_mois', $investissement->duree_mois) }}" min="1" max="120" required>
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
                                           value="{{ old('roi_estime', $investissement->roi_estime) }}" step="0.1">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-tag"></i></span>
                                    <select name="statut" class="form-select @error('statut') is-invalid @enderror" required>
                                        @foreach($statuts as $statut)
                                            <option value="{{ $statut }}" {{ old('statut', $investissement->statut) == $statut ? 'selected' : '' }}>
                                                {{ $statut }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3">{{ old('description', $investissement->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent p-4 border-top">
                        <div class="d-flex justify-content-between gap-3">
                            <a href="{{ route('service.investissements.index') }}" class="btn btn-secondary">
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
@endsection