{{-- resources/views/service/investissements/show.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Détails investissement')
@section('page-title', 'Détails de l\'investissement')
@section('breadcrumb', 'Service / Investissements / Détails')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations générales</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('service.investissements.edit', $investissement->id_investissement) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit me-1"></i>Modifier
                            </a>
                            <a href="{{ route('service.investissements.index') }}" class="btn btn-sm btn-secondary">
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
                                <div class="fw-bold fs-5"><code>{{ $investissement->reference }}</code></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Date</div>
                                <div>{{ $investissement->created_at->translatedFormat('d/m/Y à H:i') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Nom du projet</div>
                                <div class="fw-bold fs-5">{{ $investissement->nom_projet }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Statut</div>
                                @php
                                    $statutClass = [
                                        'En cours' => 'primary',
                                        'Terminé' => 'success',
                                        'Annulé' => 'danger',
                                        'En attente' => 'warning'
                                    ][$investissement->statut] ?? 'secondary';
                                @endphp
                                <div><span class="badge bg-{{ $statutClass }} fs-6">{{ $investissement->statut }}</span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Montant</div>
                                <div class="fw-bold fs-4 text-primary">{{ number_format($investissement->montant, 0, ',', ' ') }} {{ $investissement->devise->code ?? 'USD' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Durée</div>
                                <div>{{ $investissement->duree_mois }} mois</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">ROI estimé</div>
                                @php
                                    $roiClass = 'text-success';
                                    if($investissement->roi_estime < 0) $roiClass = 'text-danger';
                                    elseif($investissement->roi_estime < 10) $roiClass = 'text-warning';
                                @endphp
                                <div class="fw-bold fs-4 {{ $roiClass }}">{{ $investissement->roi_estime ?? 0 }}%</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Enregistré par</div>
                                <div>{{ $investissement->utilisateur->nom ?? '-' }}</div>
                            </div>
                        </div>
                        @if($investissement->description)
                        <div class="col-12">
                            <div class="info-item">
                                <div class="text-muted small">Description</div>
                                <div class="p-3 rounded" style="background: #f8fafc;">{{ $investissement->description }}</div>
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
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Analyse financière</h6>
                </div>
                <div class="card-body p-4">
                    <div class="text-center p-3 mb-3 rounded" style="background: #f8fafc;">
                        <div class="small text-muted">Montant en {{ $devise }}</div>
                        <div class="display-6 fw-bold text-primary">
                            {{ number_format($investissement->montant, 0, ',', ' ') }} {{ $investissement->devise->code ?? 'USD' }}
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">CDF</span>
                            <span class="fw-bold">{{ number_format($investissement->montant * ($investissement->devise->taux_cdf ?? 1), 0, ',', ' ') }} FC</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">USD</span>
                            <span class="fw-bold">{{ number_format($investissement->montant * ($investissement->devise->taux_usd ?? 1), 0, ',', ' ') }} USD</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">EUR</span>
                            <span class="fw-bold">{{ number_format($investissement->montant * ($investissement->devise->taux_eur ?? 1), 0, ',', ' ') }} EUR</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="powerbi-card mt-4">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0"><i class="fas fa-chart-simple me-2"></i>Métriques clés</h6>
                </div>
                <div class="card-body p-4">
                    <div class="metric-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Gain mensuel estimé</span>
                            <span class="fw-bold text-success">{{ number_format($metrics['gain_mensuel_estime'], 0, ',', ' ') }} {{ $devise }}</span>
                        </div>
                    </div>
                    <div class="metric-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">ROI annualisé</span>
                            <span class="fw-bold {{ $metrics['roi_annualise'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $metrics['roi_annualise'] >= 0 ? '+' : '' }}{{ $metrics['roi_annualise'] }}%
                            </span>
                        </div>
                    </div>
                    <div class="metric-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Retour sur investissement</span>
                            <span class="fw-bold">{{ $metrics['retour_sur_investissement_mois'] }} mois</span>
                        </div>
                    </div>
                    <div class="metric-item">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Performance</span>
                            @php
                                $perfClass = [
                                    'excellent' => 'success',
                                    'bon' => 'primary',
                                    'moyen' => 'warning',
                                    'normal' => 'secondary',
                                    'critique' => 'danger'
                                ][$metrics['performance']] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $perfClass }}">{{ ucfirst($metrics['performance']) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection