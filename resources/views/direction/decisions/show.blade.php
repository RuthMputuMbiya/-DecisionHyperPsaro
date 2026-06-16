{{-- resources/views/direction/decisions/show.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Détails de la décision')
@section('page-title', 'Décision stratégique')
@section('breadcrumb', 'Direction / Décisions / Détails')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        
        {{-- Carte principale --}}
        <div class="col-lg-8">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="decision-icon" style="width: 48px; height: 48px; background: linear-gradient(135deg, #7e3af2, #6b2fd9); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-gavel fa-2x text-white"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">{{ $decision->titre }}</h4>
                                <p class="text-muted mb-0">
                                    Référence: <code>#{{ str_pad($decision->id_decision, 5, '0', STR_PAD_LEFT) }}</code>
                                </p>
                            </div>
                        </div>
                        <div>
                            @php
                                $statusBadge = [
                                    'En attente' => 'warning',
                                    'Validée' => 'success',
                                    'Rejetée' => 'danger',
                                    'En cours' => 'info'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusBadge[$decision->statut] ?? 'secondary' }} px-3 py-2 fs-6">
                                <i class="fas {{ $decision->statut == 'Validée' ? 'fa-check-circle' : ($decision->statut == 'Rejetée' ? 'fa-times-circle' : 'fa-clock') }} me-2"></i>
                                {{ $decision->statut }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    {{-- Description --}}
                    @if($decision->description)
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-2"><i class="fas fa-align-left me-2"></i>Description</h6>
                        <p class="text-secondary">{{ $decision->description }}</p>
                    </div>
                    @endif
                    
                    {{-- Paramètres de la décision --}}
                    @if($decision->parametres)
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-2"><i class="fas fa-sliders-h me-2"></i>Paramètres</h6>
                        <div class="bg-light p-3 rounded-3">
                            <pre class="mb-0" style="font-size: 13px;">{{ json_encode(json_decode($decision->parametres), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                    
                    {{-- Décision prise --}}
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-2"><i class="fas fa-check-circle me-2"></i>Décision prise</h6>
                        <div class="p-3 rounded-3" style="background: #f0fdf4; border-left: 4px solid #0e9f6e;">
                            <p class="mb-0">{{ $decision->decision_prise }}</p>
                        </div>
                    </div>
                    
                    {{-- Chronologie --}}
                    <div class="mt-4">
                        <h6 class="fw-semibold mb-3"><i class="fas fa-history me-2"></i>Chronologie</h6>
                        <div class="timeline">
                            @foreach($timeline as $event)
                            <div class="timeline-item d-flex gap-3 mb-3">
                                <div class="timeline-icon" style="width: 36px; height: 36px; background: {{ $event['color'] }}20; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas {{ $event['icon'] }}" style="color: {{ $event['color'] }};"></i>
                                </div>
                                <div class="timeline-content flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                        <strong>{{ $event['action'] }}</strong>
                                        <small class="text-muted">{{ $event['date']->translatedFormat('d/m/Y à H:i') }}</small>
                                    </div>
                                    <div class="text-muted small">Par {{ $event['user'] }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Sidebar --}}
        <div class="col-lg-4">
            
            {{-- Impact financier --}}
            <div class="powerbi-card mb-4">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-chart-line me-2"></i>Impact financier</h6>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="impact-circle {{ $decision->impact_tresorerie >= 0 ? 'positive' : 'negative' }}" style="width: 100px; height: 100px; margin: 0 auto; background: {{ $decision->impact_tresorerie >= 0 ? '#0e9f6e20' : '#e0242420' }}; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas {{ $decision->impact_tresorerie >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} fa-3x" style="color: {{ $decision->impact_tresorerie >= 0 ? '#0e9f6e' : '#e02424' }};"></i>
                        </div>
                    </div>
                    
                    <div class="impact-details">
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Résultat simulé</span>
                            <span class="fw-bold">{{ number_format($decision->resultat_simule, 0, ',', ' ') }} USD</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Impact trésorerie</span>
                            <span class="fw-bold {{ $decision->impact_tresorerie >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $decision->impact_tresorerie >= 0 ? '+' : '' }}{{ number_format($decision->impact_tresorerie, 0, ',', ' ') }} USD
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Variation</span>
                            <span class="fw-bold {{ $impact_details['variation'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $impact_details['variation'] >= 0 ? '+' : '' }}{{ number_format($impact_details['variation_percent'], 1) }}%
                            </span>
                        </div>
                    </div>
                    
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-{{ $decision->impact_tresorerie >= 0 ? 'success' : 'danger' }}" 
                             style="width: {{ min(abs($impact_details['variation_percent']), 100) }}%"></div>
                    </div>
                </div>
            </div>
            
            {{-- Informations générales --}}
            <div class="powerbi-card mb-4">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Informations</h6>
                </div>
                <div class="card-body p-4">
                    <div class="info-row d-flex justify-content-between py-2">
                        <span class="text-muted">Créé par</span>
                        <span class="fw-semibold">{{ $decision->createur->nom ?? 'Système' }}</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-2">
                        <span class="text-muted">Date de création</span>
                        <span>{{ $decision->created_at->translatedFormat('d/m/Y à H:i') }}</span>
                    </div>
                    @if($decision->date_validation)
                    <div class="info-row d-flex justify-content-between py-2">
                        <span class="text-muted">Date de validation</span>
                        <span>{{ \Carbon\Carbon::parse($decision->date_validation)->translatedFormat('d/m/Y à H:i') }}</span>
                    </div>
                    @endif
                    <div class="info-row d-flex justify-content-between py-2">
                        <span class="text-muted">Simulations liées</span>
                        <span class="badge bg-info">{{ $simulations->count() }}</span>
                    </div>
                </div>
            </div>
            
            {{-- Simulations associées --}}
            @if($simulations->count() > 0)
            <div class="powerbi-card">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-flask me-2"></i>Simulations associées</h6>
                </div>
                <div class="card-body p-0">
                    @foreach($simulations as $simulation)
                    <div class="simulation-item p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $simulation->nom_simulation }}</strong>
                                <br><small class="text-muted">{{ $simulation->created_at->translatedFormat('d/m/Y H:i') }}</small>
                            </div>
                            <span class="badge bg-secondary">Sauvegardée</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Recommandations --}}
            @if(count($recommandations) > 0)
            <div class="powerbi-card mt-4">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-lightbulb me-2"></i>Recommandations</h6>
                </div>
                <div class="card-body p-4">
                    @foreach($recommandations as $rec)
                    <div class="recommendation-item mb-3 p-3 rounded-3" style="background: {{ $rec['color'] }}10; border-left: 3px solid {{ $rec['color'] }};">
                        <div class="d-flex gap-2">
                            <i class="fas {{ $rec['icon'] }}" style="color: {{ $rec['color'] }};"></i>
                            <div>
                                <strong>{{ $rec['title'] }}</strong>
                                <p class="mb-0 small text-muted mt-1">{{ $rec['message'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<style>
    .timeline-item {
        transition: all 0.2s;
    }
    .timeline-item:hover {
        transform: translateX(5px);
    }
    .simulation-item:hover {
        background: #f8fafc;
    }
</style>
@endpush
@endsection