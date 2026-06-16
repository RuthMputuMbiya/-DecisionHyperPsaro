{{-- resources/views/finance/simulations/show.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Détails simulation')
@section('page-title', 'Simulation : ' . $simulation->nom_simulation)
@section('breadcrumb', 'Finance / Simulations / Détails')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-flask me-2" style="color: #7e3af2;"></i>Informations générales</h5>
                        <div>
                            @if($simulation->decision)
                                <span class="badge bg-info"><i class="fas fa-link me-1"></i>Liée à une décision</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Nom de la simulation</div>
                                <div class="fw-bold fs-5">{{ $simulation->nom_simulation }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Date de création</div>
                                <div>{{ $simulation->created_at->translatedFormat('d/m/Y à H:i') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Créé par</div>
                                <div>{{ $simulation->utilisateur->nom ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="text-muted small">Dernière modification</div>
                                <div>{{ $simulation->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        @if($simulation->description)
                        <div class="col-12">
                            <div class="info-item">
                                <div class="text-muted small">Description</div>
                                <div class="p-3 rounded" style="background: #f8fafc;">{{ $simulation->description }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="powerbi-card mt-4">
                <div class="card-header-custom p-4 border-bottom">
                    <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Paramètres saisis</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="param-item d-flex justify-content-between p-3 rounded" style="background: #f8fafc;">
                                <span class="text-muted">Variation du CA</span>
                                <span class="fw-bold {{ ($parametres['ca_variation'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ ($parametres['ca_variation'] ?? 0) >= 0 ? '+' : '' }}{{ $parametres['ca_variation'] ?? 0 }}%
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="param-item d-flex justify-content-between p-3 rounded" style="background: #f8fafc;">
                                <span class="text-muted">Variation des dépenses</span>
                                <span class="fw-bold {{ ($parametres['depenses_variation'] ?? 0) <= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ ($parametres['depenses_variation'] ?? 0) >= 0 ? '+' : '' }}{{ $parametres['depenses_variation'] ?? 0 }}%
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="param-item d-flex justify-content-between p-3 rounded" style="background: #f8fafc;">
                                <span class="text-muted">Investissement</span>
                                <span class="fw-bold">{{ number_format($parametres['investissement'] ?? 0, 0, ',', ' ') }} {{ $resultats['devise'] ?? 'USD' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="param-item d-flex justify-content-between p-3 rounded" style="background: #f8fafc;">
                                <span class="text-muted">Période de simulation</span>
                                <span class="fw-bold">{{ $parametres['periode'] ?? 12 }} mois</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            {{-- Résultats --}}
            <div class="powerbi-card">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Résultats</h6>
                </div>
                <div class="card-body p-4">
                    <div class="text-center p-3 mb-3 rounded" style="background: #f8fafc;">
                        <div class="small text-muted">Impact sur trésorerie</div>
                        <div class="display-6 fw-bold {{ ($resultats['impact_tresorerie'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="fas {{ ($resultats['impact_tresorerie'] ?? 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                            {{ ($resultats['impact_tresorerie'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($resultats['impact_tresorerie'] ?? 0, 0, ',', ' ') }} {{ $resultats['devise'] ?? 'USD' }}
                        </div>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 rounded text-center" style="background: #f8fafc;">
                                <div class="small text-muted">ROI estimé</div>
                                <div class="fw-bold text-info">{{ $resultats['roi'] ?? 0 }}%</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded text-center" style="background: #f8fafc;">
                                <div class="small text-muted">Retour invest.</div>
                                <div class="fw-bold text-info">{{ $resultats['retour_investissement'] ?? 0 }} mois</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">CA actuel</span>
                            <span>{{ number_format($resultats['ca_actuel'] ?? 0, 0, ',', ' ') }} {{ $resultats['devise'] ?? 'USD' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="text-muted">CA après</span>
                            <span class="fw-bold text-primary">{{ number_format($resultats['ca_apres'] ?? 0, 0, ',', ' ') }} {{ $resultats['devise'] ?? 'USD' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="text-muted">Taux de marge actuel</span>
                            <span>{{ $resultats['taux_marge_actuel'] ?? 0 }}%</span>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="text-muted">Taux de marge après</span>
                            <span class="fw-bold text-success">{{ $resultats['taux_marge_apres'] ?? 0 }}%</span>
                        </div>
                    </div>
                    
                    @if(!empty($resultats['alertes']))
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted">⚠️ Alertes</small>
                        @foreach($resultats['alertes'] as $alerte)
                            <div class="small p-2 mt-2 rounded" style="background: #f5a62320;">
                                {{ $alerte['message'] }}
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            
            {{-- Lien vers décision --}}
            @if($simulation->decision)
            <div class="powerbi-card mt-4">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0"><i class="fas fa-gavel me-2"></i>Décision associée</h6>
                </div>
                <div class="card-body p-4">
                    <div class="fw-semibold">{{ $simulation->decision->titre }}</div>
                    <div class="small text-muted mt-1">Statut: 
                        <span class="badge bg-{{ $simulation->decision->statut == 'Validée' ? 'success' : ($simulation->decision->statut == 'Rejetée' ? 'danger' : 'warning') }}">
                            {{ $simulation->decision->statut }}
                        </span>
                    </div>
                    <a href="{{ route('direction.decisions.show', $simulation->decision->id_decision) }}" class="btn btn-sm btn-outline-primary mt-3">
                        <i class="fas fa-eye me-1"></i>Voir la décision
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique de comparaison
    const ctx = document.getElementById('comparisonChart')?.getContext('2d');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['CA', 'Marge', 'Dépenses'],
                datasets: [
                    {
                        label: 'Avant simulation',
                        data: [
                            {{ $resultats['ca_actuel'] ?? 0 }},
                            {{ $resultats['marge_actuelle'] ?? 0 }},
                            {{ $resultats['depenses_actuelles'] ?? 0 }}
                        ],
                        backgroundColor: '#94a3b8',
                        borderRadius: 8
                    },
                    {
                        label: 'Après simulation',
                        data: [
                            {{ $resultats['ca_apres'] ?? 0 }},
                            {{ $resultats['marge_apres'] ?? 0 }},
                            {{ $resultats['depenses_apres'] ?? 0 }}
                        ],
                        backgroundColor: '#1B6EF3',
                        borderRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${new Intl.NumberFormat('fr-FR').format(context.raw)} {{ $resultats['devise'] ?? 'USD' }}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value);
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
@endsection