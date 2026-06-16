{{-- resources/views/service/depenses/show.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Détails dépense')
@section('page-title', 'Détails de la dépense')
@section('breadcrumb', 'Service / Dépenses / Détails')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations générales</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('service.depenses.edit', $depense->id_depense) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit me-1"></i>Modifier
                            </a>
                            <a href="{{ route('service.depenses.index') }}" class="btn btn-sm btn-secondary">
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
                                <div class="fw-bold fs-5"><code>{{ $depense->reference }}</code></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Date</div>
                                <div>{{ $depense->created_at->translatedFormat('d/m/Y à H:i') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Montant</div>
                                <div class="fw-bold fs-4 text-danger">{{ number_format($depense->montant, 0, ',', ' ') }} {{ $depense->devise->code ?? 'USD' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Catégorie</div>
                                <div>
                                    <span class="badge" style="background: #e0242420; color: #e02424; font-size: 14px;">
                                        {{ $depense->categorie_depense }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Justificatif</div>
                                <div>{{ $depense->justificatif ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Enregistré par</div>
                                <div>{{ $depense->utilisateur->nom ?? '-' }}</div>
                            </div>
                        </div>
                        @if($depense->description)
                        <div class="col-12">
                            <div class="info-item">
                                <div class="text-muted small">Description</div>
                                <div class="p-3 rounded" style="background: #f8fafc;">{{ $depense->description }}</div>
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
                        <div class="display-6 fw-bold text-danger">
                            {{ number_format($depense->montant, 0, ',', ' ') }} {{ $depense->devise->code ?? 'USD' }}
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">CDF</span>
                            <span class="fw-bold">{{ number_format($depense->montant * ($depense->devise->taux_cdf ?? 1), 0, ',', ' ') }} FC</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">USD</span>
                            <span class="fw-bold">{{ number_format($depense->montant * ($depense->devise->taux_usd ?? 1), 0, ',', ' ') }} USD</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">EUR</span>
                            <span class="fw-bold">{{ number_format($depense->montant * ($depense->devise->taux_eur ?? 1), 0, ',', ' ') }} EUR</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="powerbi-card mt-4">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0"><i class="fas fa-percent me-2"></i>Ratio par rapport au budget</h6>
                </div>
                <div class="card-body p-4">
                    @php
                        $totalDepenses = \App\Models\Depense::where('id_service', $service->id_service)->sum('montant');
                        $ratio = $service->budget_annuel > 0 ? round(($totalDepenses / $service->budget_annuel) * 100, 1) : 0;
                    @endphp
                    <div class="text-center">
                        <div class="display-6 fw-bold {{ $ratio > 100 ? 'text-danger' : ($ratio > 80 ? 'text-warning' : 'text-success') }}">
                            {{ $ratio }}%
                        </div>
                        <div class="small text-muted">Budget consommé</div>
                    </div>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar {{ $ratio > 100 ? 'bg-danger' : ($ratio > 80 ? 'bg-warning' : 'bg-success') }}" style="width: {{ min($ratio, 100) }}%"></div>
                    </div>
                    <div class="small text-muted mt-2 text-center">
                        Budget total: {{ number_format($service->budget_annuel, 0, ',', ' ') }} USD
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection