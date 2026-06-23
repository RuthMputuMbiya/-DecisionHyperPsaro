{{-- resources/views/direction/decisions/show.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Détails de la décision')
@section('page-title', 'Décision stratégique')
@section('breadcrumb', 'Direction / Décisions / Détails')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">
    <div class="row g-2 g-md-3 g-lg-4">
        
        {{-- COLONNE PRINCIPALE (GAUCHE) --}}
        <div class="col-12 col-lg-8">
            <div class="powerbi-card">
                {{-- En-tête --}}
                <div class="card-header-custom p-3 p-md-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 gap-md-3">
                            <div class="decision-icon" style="width: 40px; height: 40px; background: linear-gradient(135deg, #7e3af2, #6b2fd9); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-gavel text-white" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold fs-5 fs-md-4">{{ $decision->titre }}</h4>
                                <p class="text-muted mb-0 small">
                                    Référence: <code class="fw-bold">#{{ str_pad($decision->id_decision, 5, '0', STR_PAD_LEFT) }}</code>
                                    @if(isset($decision->is_auto) && $decision->is_auto)
                                        <span class="badge bg-info ms-2">
                                            <i class="fas fa-robot me-1"></i>Auto-générée
                                        </span>
                                    @endif
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
                                <i class="fas {{ $decision->statut == 'Validée' ? 'fa-check-circle' : ($decision->statut == 'Rejetée' ? 'fa-times-circle' : 'fa-clock') }} me-1 me-md-2"></i>
                                {{ $decision->statut }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-3 p-md-4">
                    
                    {{-- 1. DESCRIPTION --}}
                    @if($decision->description)
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-2">
                            <i class="fas fa-align-left me-2" style="color: #7e3af2;"></i>Description
                        </h6>
                        <div class="p-3 rounded-3" style="background: #f8fafc; border-left: 3px solid #7e3af2;">
                            <p class="mb-0">{{ $decision->description }}</p>
                        </div>
                    </div>
                    @endif
                    
                    {{-- 2. DÉCISION PRISE --}}
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-2">
                            <i class="fas fa-check-circle me-2" style="color: #0e9f6e;"></i>Décision prise
                        </h6>
                        <div class="p-3 rounded-3" style="background: #f0fdf4; border-left: 4px solid #0e9f6e;">
                            <p class="mb-0">{{ $decision->decision_prise ?? 'En attente de validation...' }}</p>
                        </div>
                    </div>
                    
                    {{-- 3. PARAMÈTRES --}}
                    @if($decision->parametres)
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-2">
                            <i class="fas fa-sliders-h me-2" style="color: #f5a623;"></i>Paramètres de la décision
                        </h6>
                        <div class="bg-light p-3 rounded-3" style="overflow-x: auto;">
                            <pre class="mb-0" style="font-size: 12px; white-space: pre-wrap;">
@php
    $params = is_string($decision->parametres) ? json_decode($decision->parametres, true) : $decision->parametres;
    echo json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
@endphp
                            </pre>
                        </div>
                    </div>
                    @endif
                    
                    {{-- 4. STATISTIQUES AUTOMATIQUES --}}
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3">
                            <i class="fas fa-chart-pie me-2" style="color: #1B6EF3;"></i>Statistiques automatiques
                        </h6>
                        
                        <div class="row g-2">
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 text-center bg-primary bg-opacity-10">
                                    <div class="display-6 fw-bold text-primary">{{ $stats['total_indicateurs'] ?? 0 }}</div>
                                    <div class="small text-muted">Indicateurs</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 text-center bg-success bg-opacity-10">
                                    <div class="display-6 fw-bold text-success">{{ $stats['taux_reussite'] ?? 0 }}%</div>
                                    <div class="small text-muted">Taux de réussite</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 text-center bg-warning bg-opacity-10">
                                    <div class="display-6 fw-bold text-warning">{{ number_format($stats['impact_total'] ?? 0, 0, ',', ' ') }} USD</div>
                                    <div class="small text-muted">Impact total</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 text-center bg-info bg-opacity-10">
                                    <div class="display-6 fw-bold text-info">{{ $stats['decisions_liees'] ?? 0 }}</div>
                                    <div class="small text-muted">Décisions liées</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- 5. CHRONOLOGIE --}}
                    <div class="mt-4">
                        <h6 class="fw-semibold mb-3">
                            <i class="fas fa-history me-2" style="color: #7e3af2;"></i>Chronologie
                        </h6>
                        <div class="timeline">
                            @forelse($timeline ?? [] as $event)
                            <div class="timeline-item d-flex gap-2 gap-md-3 mb-3">
                                <div class="timeline-icon flex-shrink-0" style="width: 32px; height: 32px; background: {{ $event['color'] ?? '#7e3af2' }}20; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas {{ $event['icon'] ?? 'fa-circle' }}" style="color: {{ $event['color'] ?? '#7e3af2' }}; font-size: 14px;"></i>
                                </div>
                                <div class="timeline-content flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                        <strong class="small">{{ $event['action'] ?? 'Action' }}</strong>
                                        <small class="text-muted">{{ isset($event['date']) ? \Carbon\Carbon::parse($event['date'])->translatedFormat('d/m/Y à H:i') : '-' }}</small>
                                    </div>
                                    <div class="text-muted small">Par {{ $event['user'] ?? 'Système' }}</div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-clock fa-2x d-block mb-2"></i>
                                <p class="small mb-0">Aucun événement enregistré</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- COLONNE SIDEBAR (DROITE) --}}
        <div class="col-12 col-lg-4">
            
            {{-- Impact financier --}}
            <div class="powerbi-card mb-3 mb-md-4">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0 fw-semibold fs-6">
                        <i class="fas fa-chart-line me-2" style="color: #1B6EF3;"></i>Impact financier
                    </h6>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="text-center mb-4">
                        <div class="impact-circle mx-auto" style="width: 80px; height: 80px; background: {{ ($decision->impact_tresorerie ?? 0) >= 0 ? '#0e9f6e20' : '#e0242420' }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid {{ ($decision->impact_tresorerie ?? 0) >= 0 ? '#0e9f6e' : '#e02424' }};">
                            <i class="fas {{ ($decision->impact_tresorerie ?? 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}" style="font-size: 2rem; color: {{ ($decision->impact_tresorerie ?? 0) >= 0 ? '#0e9f6e' : '#e02424' }};"></i>
                        </div>
                    </div>
                    
                    <div class="impact-details">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">Résultat simulé</span>
                            <span class="fw-bold">{{ number_format($decision->resultat_simule ?? 0, 0, ',', ' ') }} USD</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">Impact trésorerie</span>
                            <span class="fw-bold {{ ($decision->impact_tresorerie ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ ($decision->impact_tresorerie ?? 0) >= 0 ? '+' : '' }}{{ number_format($decision->impact_tresorerie ?? 0, 0, ',', ' ') }} USD
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">Variation</span>
                            <span class="fw-bold {{ ($impact_details['variation'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ ($impact_details['variation'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($impact_details['variation_percent'] ?? 0, 1) }}%
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted small">Priorité</span>
                            @php
                                $prioriteColors = [
                                    'Critique' => 'danger',
                                    'Élevée' => 'warning',
                                    'Moyenne' => 'info',
                                    'Faible' => 'success'
                                ];
                            @endphp
                            <span class="badge bg-{{ $prioriteColors[$decision->priorite ?? 'Moyenne'] ?? 'secondary' }}">
                                {{ $decision->priorite ?? 'Moyenne' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-{{ ($decision->impact_tresorerie ?? 0) >= 0 ? 'success' : 'danger' }}" 
                             style="width: {{ min(abs($impact_details['variation_percent'] ?? 0), 100) }}%; transition: width 0.5s;">
                        </div>
                    </div>
                    <div class="text-center mt-1">
                        <small class="text-muted">Niveau d'impact</small>
                    </div>
                </div>
            </div>
            
            {{-- Informations générales --}}
            <div class="powerbi-card mb-3 mb-md-4">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0 fw-semibold fs-6">
                        <i class="fas fa-info-circle me-2" style="color: #f5a623;"></i>Informations
                    </h6>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="info-row d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Créé par</span>
                        <span class="fw-semibold small">{{ $decision->createur->nom ?? 'Système' }}</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Date de création</span>
                        <span class="small">{{ $decision->created_at->translatedFormat('d/m/Y H:i') }}</span>
                    </div>
                    @if($decision->date_validation)
                    <div class="info-row d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Date de validation</span>
                        <span class="small">{{ \Carbon\Carbon::parse($decision->date_validation)->translatedFormat('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($decision->motif_rejet)
                    <div class="info-row d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Motif du rejet</span>
                        <span class="small text-danger">{{ $decision->motif_rejet }}</span>
                    </div>
                    @endif
                    <div class="info-row d-flex justify-content-between py-2">
                        <span class="text-muted small">Simulations liées</span>
                        <span class="badge bg-info">{{ $simulations->count() ?? 0 }}</span>
                    </div>
                </div>
            </div>
            
            {{-- Simulations associées --}}
            @if(($simulations ?? collect())->count() > 0)
            <div class="powerbi-card mb-3 mb-md-4">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0 fw-semibold fs-6">
                        <i class="fas fa-flask me-2" style="color: #7e3af2;"></i>Simulations associées
                    </h6>
                </div>
                <div class="card-body p-0">
                    @foreach($simulations as $simulation)
                    <div class="simulation-item p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong class="small">{{ $simulation->nom_simulation ?? 'Simulation' }}</strong>
                                <br>
                                <small class="text-muted">{{ $simulation->created_at->translatedFormat('d/m/Y H:i') }}</small>
                            </div>
                            <span class="badge bg-secondary">
                                <i class="fas fa-save me-1"></i>Sauvegardée
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Recommandations intelligentes --}}
            @if(isset($recommandations) && count($recommandations) > 0)
            <div class="powerbi-card">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0 fw-semibold fs-6">
                        <i class="fas fa-lightbulb me-2" style="color: #f5a623;"></i>Recommandations
                    </h6>
                </div>
                <div class="card-body p-3 p-md-4">
                    @foreach($recommandations as $rec)
                    <div class="recommendation-item mb-3 p-3 rounded-3" style="background: {{ $rec['color'] ?? '#7e3af2' }}10; border-left: 3px solid {{ $rec['color'] ?? '#7e3af2' }};">
                        <div class="d-flex gap-2">
                            <i class="fas {{ $rec['icon'] ?? 'fa-circle' }}" style="color: {{ $rec['color'] ?? '#7e3af2' }}; font-size: 1.2rem;"></i>
                            <div>
                                <strong class="small">{{ $rec['title'] ?? 'Recommandation' }}</strong>
                                <p class="mb-0 small text-muted mt-1">{{ $rec['message'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- 🔥 BOUTONS D'ACTION --}}
            <div class="mt-3">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('direction.decisions.index') }}" class="btn btn-secondary btn-sm flex-grow-1">
                        <i class="fas fa-arrow-left me-1"></i>Retour
                    </a>
                    @if($decision->statut == 'En attente')
                        {{-- Formulaire de validation --}}
                        <form action="{{ route('direction.decisions.validate', $decision->id_decision) }}" method="POST" style="display: inline-block; flex: 1;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="fas fa-check-circle me-1"></i>Valider
                            </button>
                        </form>
                        
                        {{-- Formulaire de rejet avec bouton personnalisé --}}
                        <form id="rejectForm" action="{{ route('direction.decisions.reject', $decision->id_decision) }}" method="POST" style="display: none;">
                            @csrf
                            <input type="hidden" name="motif" id="rejectMotif" value="">
                        </form>
                        <button onclick="showRejectModal({{ $decision->id_decision }})" class="btn btn-danger btn-sm flex-grow-1">
                            <i class="fas fa-times-circle me-1"></i>Rejeter
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showRejectModal(decisionId) {
        Swal.fire({
            title: 'Rejeter cette décision ?',
            input: 'textarea',
            inputLabel: 'Motif du rejet',
            inputPlaceholder: 'Expliquez pourquoi cette décision est rejetée...',
            inputAttributes: {
                'aria-label': 'Motif du rejet'
            },
            showCancelButton: true,
            confirmButtonColor: '#e02424',
            confirmButtonText: 'Oui, rejeter',
            cancelButtonText: 'Annuler',
            preConfirm: (motif) => {
                if (!motif) {
                    Swal.showValidationMessage('Veuillez indiquer un motif');
                }
                return motif;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                document.getElementById('rejectMotif').value = result.value;
                document.getElementById('rejectForm').submit();
            }
        });
    }
</script>
@endpush

@push('styles')
<style>
    .powerbi-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        background: #fff;
        transition: transform 0.2s, box-shadow 0.2s;
        overflow: hidden;
    }
    .powerbi-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.10);
    }
    .card-header-custom {
        background: #f8fafc;
    }
    .timeline-item {
        transition: all 0.2s;
    }
    .timeline-item:hover {
        transform: translateX(5px);
    }
    .simulation-item:hover {
        background: #f8fafc;
    }
    .info-row {
        transition: background-color 0.2s;
    }
    .info-row:hover {
        background-color: #f8fafc;
    }
    .impact-circle {
        transition: transform 0.3s;
    }
    .impact-circle:hover {
        transform: scale(1.05);
    }
    @media (max-width: 576px) {
        .powerbi-card {
            border-radius: 8px;
        }
        .card-header-custom {
            padding: 12px !important;
        }
        .card-body {
            padding: 12px !important;
        }
        .display-6 {
            font-size: 1.5rem !important;
        }
        .timeline-item {
            gap: 8px !important;
        }
        .timeline-icon {
            width: 28px !important;
            height: 28px !important;
        }
        .timeline-icon i {
            font-size: 12px !important;
        }
        .impact-circle {
            width: 60px !important;
            height: 60px !important;
        }
        .impact-circle i {
            font-size: 1.5rem !important;
        }
    }
    @media (min-width: 768px) {
        .powerbi-card {
            border-radius: 16px;
        }
        .display-6 {
            font-size: 2rem !important;
        }
    }
    @media (min-width: 992px) {
        .powerbi-card {
            border-radius: 20px;
        }
        .display-6 {
            font-size: 2.5rem !important;
        }
    }
</style>
@endpush
@endsection