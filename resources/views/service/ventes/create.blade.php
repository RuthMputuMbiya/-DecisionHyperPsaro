{{-- resources/views/service/ventes/create.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Nouvelle vente')
@section('page-title', 'Enregistrer une vente')
@section('breadcrumb', 'Service / Ventes / Nouvelle')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="card-icon" style="width: 48px; height: 48px; background: linear-gradient(135deg, #0e9f6e, #0a7c5a); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-shopping-cart fa-2x text-white"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">Nouvelle vente</h4>
                            <p class="text-muted mb-0">Service {{ $service->nom_service }}</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('service.ventes.store') }}" id="venteForm">
                    @csrf
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Référence <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-hashtag"></i></span>
                                    <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror" 
                                           value="{{ old('reference', $reference ?? '') }}" required readonly style="background-color: #f8fafc;">
                                </div>
                                @error('reference')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date de vente</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-calendar"></i></span>
                                    <input type="date" name="date_vente" class="form-control" value="{{ old('date_vente', date('Y-m-d')) }}">
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
                                <label class="form-label fw-semibold">Numéro de facture</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-file-invoice"></i></span>
                                    <input type="text" name="facture_numero" class="form-control" value="{{ old('facture_numero') }}">
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                          rows="3" placeholder="Description détaillée de la vente...">{{ old('description') }}</textarea>
                            </div>
                        </div>
                        
                        <div class="alert alert-success mt-4">
                            <div class="d-flex gap-3">
                                <i class="fas fa-info-circle fa-2x"></i>
                                <div>
                                    <strong class="d-block mb-1">Information</strong>
                                    <p class="mb-0 small">Les montants seront automatiquement convertis dans les différentes devises (CDF, USD, EUR).</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent p-4 border-top">
                        <div class="d-flex justify-content-between gap-3">
                            <a href="{{ route('service.ventes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer la vente
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
    document.getElementById('venteForm').addEventListener('submit', function(e) {
        const montant = document.querySelector('input[name="montant"]').value;
        if (montant <= 0) {
            e.preventDefault();
            alert('Le montant doit être supérieur à 0');
        }
    });
</script>
@endpush
@endsection