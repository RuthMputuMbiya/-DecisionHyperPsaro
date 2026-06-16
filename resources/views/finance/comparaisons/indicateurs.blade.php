{{-- resources/views/finance/comparaisons/indicateurs.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Comparaison par indicateurs')
@section('page-title', 'Analyse des indicateurs')
@section('breadcrumb', 'Finance / Comparaisons / Indicateurs')

@section('content')
<div class="container-fluid px-0">

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.comparaisons.indicateurs') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Année</label>
                    <select name="year" class="form-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Service</label>
                    <select name="service_id" class="form-select">
                        <option value="">Tous les services</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id_service }}" {{ $serviceId == $service->id_service ? 'selected' : '' }}>{{ $service->nom_service }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-chart-simple me-2"></i>Actualiser
                    </button>
                </div>
            </form>
        </div>
    </div>

   

    {{-- Évolution des indicateurs --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>Évolution mensuelle</h5>
                <canvas id="evolutionChart" height="350"></canvas>
            </div>
        </div>
    </div>

    {{-- Corrélation --}}
    @if(count($correlation) > 0)
    <div class="row g-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-link me-2"></i>Corrélations entre indicateurs</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Indicateur 1</th>
                                <th>Indicateur 2</th>
                                <th>Corrélation</th>
                                <th>Interprétation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($correlation as $corr)
                            <tr>
                                <td>{{ $corr['indicateur1'] }}</td>
                                <td>{{ $corr['indicateur2'] }}</                                <td class="fw-bold">
                                    @php
                                        $corrClass = 'text-primary';
                                        if($corr['correlation'] >= 0.7) $corrClass = 'text-success';
                                        elseif($corr['correlation'] <= -0.5) $corrClass = 'text-danger';
                                    @endphp
                                    <span class="{{ $corrClass }}">{{ $corr['correlation'] }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $corr['interpretation'] }}</span>
                                    @if($corr['correlation'] >= 0.7)
                                        <i class="fas fa-arrow-up text-success ms-2"></i> Forte liaison positive
                                    @elseif($corr['correlation'] <= -0.5)
                                        <i class="fas fa-arrow-down text-danger ms-2"></i> Liaison inverse
                                    @else
                                        <i class="fas fa-minus-circle text-muted ms-2"></i> Indépendants
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Tableau des indicateurs --}}
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-table me-2"></i>Détail des indicateurs ({{ $year }})</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Indicateur</th>
                                <th>Code</th>
                                <th>Valeur</th>
                                <th>Unité</th>
                                <th>Seuil min</th>
                                <th>Seuil max</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($indicateursData as $ind)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="icon-bg" style="width: 32px; height: 32px; background: {{ $ind['couleur'] }}20; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="{{ $ind['icone'] }}" style="color: {{ $ind['couleur'] }};"></i>
                                        </div>
                                        <strong>{{ $ind['nom'] }}</strong>
                                    </div>
                                </td>
                                <td><code>{{ $ind['code'] }}</code></td>
                                <td class="fw-bold">{{ number_format($ind['valeur'], 0, ',', ' ') }}</td>
                                <td>{{ $ind['unite'] }}</td>
                                <td class="text-muted">{{ $ind['seuil_min'] ? number_format($ind['seuil_min'], 0, ',', ' ') : '-' }}</td>
                                <td class="text-muted">{{ $ind['seuil_max'] ? number_format($ind['seuil_max'], 0, ',', ' ') : '-' }}</td>
                                <td>
                                    @if($ind['seuil_min'] && $ind['valeur'] < $ind['seuil_min'])
                                        <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Sous seuil</span>
                                    @elseif($ind['seuil_max'] && $ind['valeur'] > $ind['seuil_max'])
                                        <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Dépassement</span>
                                    @else
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Normal</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique des indicateurs
    const indicatorsCtx = document.getElementById('indicatorsChart').getContext('2d');
    new Chart(indicatorsCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($indicateursData)->pluck('nom')) !!},
            datasets: [{
                label: 'Valeur ({{ $devise }})',
                data: {!! json_encode(collect($indicateursData)->pluck('valeur')) !!},
                backgroundColor: {!! json_encode(collect($indicateursData)->pluck('couleur')) !!},
                borderRadius: 8,
                barPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.raw;
                            let unit = context.dataset.data[context.dataIndex].unite;
                            return `Valeur: ${new Intl.NumberFormat('fr-FR').format(value)} {{ $devise }}`;
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
    
    // Graphique d'évolution des indicateurs
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    const evolutionDatasets = {!! json_encode($evolution) !!};
    
    new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
            datasets: evolutionDatasets.map(ind => ({
                label: ind.nom,
                data: ind.data,
                borderColor: ind.couleur,
                backgroundColor: ind.couleur + '20',
                tension: 0.4,
                fill: false,
                pointRadius: 3,
                pointHoverRadius: 6
            }))
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${new Intl.NumberFormat('fr-FR').format(context.raw)} {{ $devise }}`;
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
</script>
@endpush
@endsection