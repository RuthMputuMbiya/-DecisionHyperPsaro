{{-- resources/views/direction/indicateurs/show.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Détails indicateur')
@section('page-title', 'Détails de l\'indicateur')
@section('breadcrumb', 'Direction / Indicateurs / Détails')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        {{-- Carte indicateur --}}
        <div class="col-lg-4">
            <div class="powerbi-card text-center">
                <div class="card-body p-4">
                    <div class="indicator-icon" style="width: 80px; height: 80px; background: {{ $indicateur->couleur_alerte }}20; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="{{ $indicateur->icone }} fa-3x" style="color: {{ $indicateur->couleur_alerte }};"></i>
                    </div>
                    <h3 class="mb-1">{{ $indicateur->nom_kpi }}</h3>
                    <p class="text-muted mb-3"><code>{{ $indicateur->code_kpi }}</code></p>
                    
                    <div class="mb-3">
                        @if($indicateur->est_actif)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-danger">Inactif</span>
                        @endif
                        <span class="badge bg-secondary ms-1">{{ $indicateur->unite }}</span>
                    </div>
                    
                    <hr>
                    
                    <div class="text-start">
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted">Valeur actuelle ({{ date('Y') }})</span>
                            <span class="fw-bold fs-4 text-primary">{{ number_format($stats['total_annee'], 0, ',', ' ') }}</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted">Valeur du mois</span>
                            <span>{{ number_format($stats['total_mois'], 0, ',', ' ') }}</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted">Meilleur mois</span>
                            <span>{{ $stats['meilleur_mois'] ? date('F', mktime(0, 0, 0, $stats['meilleur_mois']->mois, 1)) . ' : ' . number_format($stats['meilleur_mois']->total, 0, ',', ' ') : '-' }}</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted">Seuil minimum</span>
                            <span class="{{ $indicateur->seuil_alerte_min && $stats['total_annee'] < $indicateur->seuil_alerte_min ? 'text-danger fw-bold' : '' }}">
                                {{ $indicateur->seuil_alerte_min ? number_format($indicateur->seuil_alerte_min, 0, ',', ' ') : '-' }}
                            </span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted">Seuil maximum</span>
                            <span class="{{ $indicateur->seuil_alerte_max && $stats['total_annee'] > $indicateur->seuil_alerte_max ? 'text-danger fw-bold' : '' }}">
                                {{ $indicateur->seuil_alerte_max ? number_format($indicateur->seuil_alerte_max, 0, ',', ' ') : '-' }}
                            </span>
                        </div>
                    </div>
                    
                    @if($indicateur->description)
                        <hr>
                        <div class="text-start mt-3">
                            <small class="text-muted">Description</small>
                            <p class="mb-0 mt-1">{{ $indicateur->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            {{-- Évolution mensuelle --}}
            <div class="powerbi-card mb-4">
                <div class="card-header-custom p-4 border-bottom">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Évolution mensuelle {{ date('Y') }}</h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="evolutionChart" height="300"></canvas>
                </div>
            </div>
            
            {{-- Historique --}}
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Dernières valeurs enregistrées</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Date</th><th>Service</th><th>Montant</th><th>Activité</th><th>Enregistré par</th></tr>
                            </thead>
                            <tbody>
                                @forelse($historique as $item)
                                <tr>
                                    <td>{{ $item->temps->date ? \Carbon\Carbon::parse($item->temps->date)->translatedFormat('d/m/Y') : '-' }}</td>
                                    <td>{{ $item->service->nom_service ?? '-' }}</td>
                                    <td class="fw-bold">{{ number_format($item->montant_usd, 0, ',', ' ') }} {{ $indicateur->unite }}</td>
                                    <td>{{ ucfirst($item->activite->type_activite ?? '-') }}</td>
                                    <td>{{ $item->utilisateur->nom ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4">Aucune donnée enregistrée</td></tr>
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
    new Chart(document.getElementById('evolutionChart'), {
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
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
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
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR').format(value) + ' {{ $indicateur->unite }}';
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection