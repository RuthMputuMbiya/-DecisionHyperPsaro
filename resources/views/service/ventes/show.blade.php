{{-- resources/views/service/ventes/show.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Détails vente')
@section('page-title', 'Détails de la vente')
@section('breadcrumb', 'Service / Ventes / Détails')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations générales</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('service.ventes.edit', $vente->id_vente) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit me-1"></i>Modifier
                            </a>
                            <a href="{{ route('service.ventes.index') }}" class="btn btn-sm btn-secondary">
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
                                <div class="fw-bold fs-5"><code>{{ $vente->reference }}</code></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Date</div>
                                <div>{{ $vente->created_at->translatedFormat('d/m/Y à H:i') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Montant</div>
                                <div class="fw-bold fs-4 text-success">{{ number_format($vente->montant, 0, ',', ' ') }} {{ $vente->devise->code ?? 'USD' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Client</div>
                                <div>{{ $vente->client->nom ?? '-' }}</div>
                                @if($vente->client)
                                    <div class="small text-muted">{{ $vente->client->email ?? '' }} - {{ $vente->client->telephone ?? '' }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Numéro de facture</div>
                                <div>{{ $vente->facture_numero ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Enregistré par</div>
                                <div>{{ $vente->utilisateur->nom ?? '-' }}</div>
                            </div>
                        </div>
                        @if($vente->description)
                        <div class="col-12">
                            <div class="info-item">
                                <div class="text-muted small">Description</div>
                                <div class="p-3 rounded" style="background: #f8fafc;">{{ $vente->description }}</div>
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
                        <div class="display-6 fw-bold text-success">
                            {{ number_format($vente->montant, 0, ',', ' ') }} {{ $vente->devise->code ?? 'USD' }}
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">CDF</span>
                            <span class="fw-bold">{{ number_format($vente->montant * ($vente->devise->taux_cdf ?? 1), 0, ',', ' ') }} FC</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">USD</span>
                            <span class="fw-bold">{{ number_format($vente->montant * ($vente->devise->taux_usd ?? 1), 0, ',', ' ') }} USD</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">EUR</span>
                            <span class="fw-bold">{{ number_format($vente->montant * ($vente->devise->taux_eur ?? 1), 0, ',', ' ') }} EUR</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="powerbi-card mt-4">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0"><i class="fas fa-percent me-2"></i>Objectif mensuel</h6>
                </div>
                <div class="card-body p-4">
                    @php
                        $totalMois = \App\Models\Vente::where('id_service', $service->id_service)
                            ->whereMonth('created_at', date('m'))
                            ->whereYear('created_at', date('Y'))
                            ->sum('montant');
                        $objectifMensuel = $service->budget_annuel / 12;
                        $pourcentage = $objectifMensuel > 0 ? round(($totalMois / $objectifMensuel) * 100, 1) : 0;
                    @endphp
                    <div class="text-center">
                        <div class="display-6 fw-bold {{ $pourcentage >= 100 ? 'text-success' : ($pourcentage >= 70 ? 'text-warning' : 'text-danger') }}">
                            {{ $pourcentage }}%
                        </div>
                        <div class="small text-muted">Objectif mensuel atteint</div>
                    </div>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar {{ $pourcentage >= 100 ? 'bg-success' : ($pourcentage >= 70 ? 'bg-warning' : 'bg-danger') }}" 
                             style="width: {{ min($pourcentage, 100) }}%"></div>
                    </div>
                    <div class="small text-muted mt-2 text-center">
                        Objectif: {{ number_format($objectifMensuel, 0, ',', ' ') }} USD / Mois
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection