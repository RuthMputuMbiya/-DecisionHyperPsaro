{{-- resources/views/direction/indicateurs/show.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Détails indicateur')
@section('page-title', 'Détails de l\'indicateur')
@section('breadcrumb', 'Direction / Indicateurs / Détails')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">
    <div class="row g-2 g-md-3 g-lg-4">
        
        {{-- Carte indicateur --}}
        <div class="col-12 col-lg-4">
            <div class="powerbi-card text-center h-100">
                <div class="card-body p-3 p-md-4">
                    {{-- Icône --}}
                    <div class="indicator-icon mx-auto" style="width: 60px; height: 60px; background: {{ $indicateur->couleur_alerte }}20; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i class="{{ $indicateur->icone }} fa-2x fa-3x-md" style="color: {{ $indicateur->couleur_alerte }};"></i>
                    </div>
                    
                    {{-- Titre --}}
                    <h3 class="mb-1 fs-4 fs-md-3">{{ $indicateur->nom_kpi }}</h3>
                    <p class="text-muted mb-3"><code>{{ $indicateur->code_kpi }}</code></p>
                    
                    {{-- Badges --}}
                    <div class="mb-3 d-flex flex-wrap justify-content-center gap-1">
                        @if($indicateur->est_actif)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-danger">Inactif</span>
                        @endif
                        <span class="badge bg-secondary">{{ $indicateur->unite }}</span>
                        @if($indicateur->formule)
                            <span class="badge bg-info text-dark" title="Formule de calcul">
                                <i class="fas fa-calculator me-1"></i>Formule
                            </span>
                        @endif
                    </div>
                    
                    <hr>
                    
                    {{-- Informations --}}
                    <div class="text-start">
                        {{-- 🔥 FORMULE AFFICHÉE --}}
                        @if($indicateur->formule)
                            <div class="info-row d-flex flex-column flex-sm-row justify-content-between py-2 border-bottom">
                                <span class="text-muted small">Formule de calcul</span>
                                <code class="text-primary text-break" style="font-size: 12px; word-break: break-all;">
                                    {{ $indicateur->formule }}
                                </code>
                            </div>
                        @endif
                        
                        <div class="info-row d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">Valeur actuelle ({{ date('Y') }})</span>
                            <span class="fw-bold fs-4 text-primary">{{ number_format($stats['total_annee'], 0, ',', ' ') }}</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">Valeur du mois</span>
                            <span>{{ number_format($stats['total_mois'], 0, ',', ' ') }}</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">Meilleur mois</span>
                            <span>{{ $stats['meilleur_mois'] ? date('F', mktime(0, 0, 0, $stats['meilleur_mois']->mois, 1)) . ' : ' . number_format($stats['meilleur_mois']->total, 0, ',', ' ') : '-' }}</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">Seuil minimum</span>
                            <span class="{{ $indicateur->seuil_alerte_min && $stats['total_annee'] < $indicateur->seuil_alerte_min ? 'text-danger fw-bold' : '' }}">
                                {{ $indicateur->seuil_alerte_min ? number_format($indicateur->seuil_alerte_min, 0, ',', ' ') : '-' }}
                            </span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted small">Seuil maximum</span>
                            <span class="{{ $indicateur->seuil_alerte_max && $stats['total_annee'] > $indicateur->seuil_alerte_max ? 'text-danger fw-bold' : '' }}">
                                {{ $indicateur->seuil_alerte_max ? number_format($indicateur->seuil_alerte_max, 0, ',', ' ') : '-' }}
                            </span>
                        </div>
                    </div>
                    
                    {{-- Description --}}
                    @if($indicateur->description)
                        <hr>
                        <div class="text-start mt-3">
                            <small class="text-muted">Description</small>
                            <p class="mb-0 mt-1 small">{{ $indicateur->description }}</p>
                        </div>
                    @endif
                </div>
                
                {{-- Boutons d'action --}}
                <div class="card-footer bg-transparent p-3 border-top">
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="{{ route('direction.indicateurs.edit', $indicateur->id_indicateur) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Modifier
                        </a>
                        <a href="{{ route('direction.indicateurs.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Graphique et historique --}}
        <div class="col-12 col-lg-8">
            {{-- Évolution mensuelle --}}
            <div class="powerbi-card mb-3 mb-md-4">
                <div class="card-header-custom p-3 p-md-4 border-bottom">
                    <h5 class="mb-0 fs-6 fs-md-5">
                        <i class="fas fa-chart-line me-2"></i>Évolution mensuelle {{ date('Y') }}
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 250px; height: 300px-md;">
                        <canvas id="evolutionChart"></canvas>
                    </div>
                </div>
            </div>
            
            {{-- Historique --}}
            <div class="powerbi-card">
                <div class="card-header-custom p-3 p-md-4 border-bottom">
                    <h5 class="mb-0 fs-6 fs-md-5">
                        <i class="fas fa-history me-2"></i>Dernières valeurs enregistrées
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-sm table-md">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-2 py-md-3">Date</th>
                                    <th class="py-2 py-md-3">Service</th>
                                    <th class="py-2 py-md-3">Montant</th>
                                    <th class="py-2 py-md-3 d-none d-sm-table-cell">Activité</th>
                                    <th class="py-2 py-md-3 d-none d-lg-table-cell">Enregistré par</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historique as $item)
                                <tr>
                                    <td class="py-2 small">{{ $item->temps->date ? \Carbon\Carbon::parse($item->temps->date)->translatedFormat('d/m/Y') : '-' }}</td>
                                    <td class="py-2 small">{{ $item->service->nom_service ?? '-' }}</td>
                                    <td class="py-2 fw-bold small">{{ number_format($item->montant_usd, 0, ',', ' ') }} {{ $indicateur->unite }}</td>
                                    <td class="py-2 small d-none d-sm-table-cell">{{ ucfirst($item->activite->type_activite ?? '-') }}</td>
                                    <td class="py-2 small d-none d-lg-table-cell">{{ $item->utilisateur->nom ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-database fa-2x text-muted mb-2 d-block"></i>
                                        <p class="text-muted mb-0 small">Aucune donnée enregistrée</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('evolutionChart').getContext('2d');
        
        // Déterminer la taille des points selon l'écran
        const isMobile = window.innerWidth < 576;
        const pointRadius = isMobile ? 3 : 5;
        const fontSize = isMobile ? 10 : 12;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                datasets: [{
                    label: '{{ $indicateur->nom_kpi }} ({{ $indicateur->unite }})',
                    data: {{ json_encode($evolution) }},
                    borderColor: '{{ $indicateur->couleur_alerte }}',
                    backgroundColor: '{{ $indicateur->couleur_alerte }}20',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '{{ $indicateur->couleur_alerte }}',
                    pointBorderColor: 'white',
                    pointRadius: pointRadius,
                    pointHoverRadius: pointRadius + 3,
                    borderWidth: isMobile ? 2 : 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                size: fontSize
                            },
                            boxWidth: isMobile ? 10 : 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `{{ $indicateur->nom_kpi }}: ${new Intl.NumberFormat('fr-FR').format(context.raw)} {{ $indicateur->unite }}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: fontSize
                            },
                            callback: function(value) {
                                if (value >= 1000) {
                                    return new Intl.NumberFormat('fr-FR').format(value / 1000) + 'k';
                                }
                                return new Intl.NumberFormat('fr-FR').format(value);
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: fontSize
                            },
                            maxRotation: isMobile ? 45 : 0
                        }
                    }
                }
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    /* ============================================
       RESPONSIVE DESIGN
       ============================================ */
    
    /* Mobile First (par défaut) */
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
    
    /* Table responsive */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table th, .table td {
        white-space: nowrap;
    }
    
    .table th:last-child, .table td:last-child {
        white-space: normal;
    }
    
    /* Code blocks */
    code.text-primary {
        background: #e8f0fe;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
        word-break: break-all;
    }
    
    /* Info rows */
    .info-row {
        transition: background-color 0.2s;
    }
    
    .info-row:hover {
        background-color: #f8fafc;
    }
    
    /* ============================================
       TABLETTE (>= 768px)
       ============================================ */
    @media (min-width: 768px) {
        .powerbi-card {
            border-radius: 16px;
        }
        
        .indicator-icon {
            width: 80px !important;
            height: 80px !important;
            border-radius: 20px !important;
        }
        
        .indicator-icon i {
            font-size: 2.5rem !important;
        }
        
        .chart-container {
            height: 300px !important;
        }
        
        .table th, .table td {
            white-space: normal;
        }
    }
    
    /* ============================================
       DESKTOP (>= 992px)
       ============================================ */
    @media (min-width: 992px) {
        .powerbi-card {
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        
        .powerbi-card:hover {
            box-shadow: 0 8px 40px rgba(0,0,0,0.12);
        }
        
        .chart-container {
            height: 350px !important;
        }
        
        .info-row {
            padding: 12px 0;
        }
        
        .table th, .table td {
            padding: 12px 16px !important;
        }
        
        code.text-primary {
            font-size: 13px;
            padding: 3px 10px;
        }
    }
    
    /* ============================================
       GRAND ÉCRAN (>= 1200px)
       ============================================ */
    @media (min-width: 1200px) {
        .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .chart-container {
            height: 400px !important;
        }
    }
    
    /* ============================================
       IMPRESSION
       ============================================ */
    @media print {
        .powerbi-card {
            box-shadow: none !important;
            border: 1px solid #ddd;
        }
        
        .btn, .btn-group {
            display: none !important;
        }
    }
</style>
@endpush
@endsection