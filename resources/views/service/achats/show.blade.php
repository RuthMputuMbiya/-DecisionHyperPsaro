{{-- resources/views/service/achats/show.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Détails achat')
@section('page-title', 'Détails de l\'achat')
@section('breadcrumb', 'Service / Achats / Détails')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations générales</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('service.achats.edit', $achat->id_achat) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit me-1"></i>Modifier
                            </a>
                            <a href="{{ route('service.achats.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Retour
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Référence</div>
                                <div class="fw-bold fs-5"><code>{{ $achat->reference }}</code></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Date</div>
                                <div>{{ $achat->created_at->translatedFormat('d/m/Y à H:i') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Montant</div>
                                <div class="fw-bold fs-4 text-primary">{{ number_format($achat->montant, 0, ',', ' ') }} {{ $achat->devise->code ?? 'USD' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Fournisseur</div>
                                <div>{{ $achat->fournisseur->nom ?? '-' }}</div>
                                @if($achat->fournisseur)
                                    <div class="small text-muted">{{ $achat->fournisseur->email ?? '' }} - {{ $achat->fournisseur->telephone ?? '' }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Bon de commande</div>
                                <div>{{ $achat->bon_commande ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Enregistré par</div>
                                <div>{{ $achat->utilisateur->nom ?? '-' }}</div>
                            </div>
                        </div>
                        @if($achat->description)
                        <div class="col-12">
                            <div class="info-item">
                                <div class="text-muted small">Description</div>
                                <div class="p-3 rounded" style="background: #f8fafc;">{{ $achat->description }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="powerbi-card">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Impact financier</h6>
                </div>
                <div class="card-body p-4">
                    <div class="text-center p-3 mb-3 rounded" style="background: #f8fafc;">
                        <div class="small text-muted">Montant en {{ $devise }}</div>
                        <div class="display-6 fw-bold text-primary">
                            {{ number_format($achat->montant, 0, ',', ' ') }} {{ $achat->devise->code ?? 'USD' }}
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">CDF</span>
                            <span class="fw-bold">{{ number_format($achat->montant * ($achat->devise->taux_cdf ?? 1), 0, ',', ' ') }} FC</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">USD</span>
                            <span class="fw-bold">{{ number_format($achat->montant * ($achat->devise->taux_usd ?? 1), 0, ',', ' ') }} USD</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">EUR</span>
                            <span class="fw-bold">{{ number_format($achat->montant * ($achat->devise->taux_eur ?? 1), 0, ',', ' ') }} EUR</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection