{{-- resources/views/finance/analyses/previsions.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Prévisions financières')
@section('page-title', 'Prévisions et projections')
@section('breadcrumb', 'Finance / Prévisions')

@section('content')
<div class="container-fluid px-0">

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.analyses.previsions') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Horizon de prévision</label>
                    <select name="months" class="form-select" onchange="this.form.submit()">
                        <option value="3" {{ $months == 3 ? 'selected' : '' }}>3 mois</option>
                        <option value="6" {{ $months == 6 ? 'selected' : '' }}>6 mois</option>
                        <option value="12" {{ $months == 12 ? 'selected' : '' }}>12 mois</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- Graphique des prévisions --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-chart-line me-2" style="color: #1B6EF3;"></i>Prévisions Chiffre d'Affaires</h5>
                <canvas id="forecastChart" height="350"></canvas>
            </div>
        </div>
    </div>

    {{-- Scénarios --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-chart-pie me-2" style="color: #f5a623;"></i>Scénarios pour l'année prochaine</h5>
                <div class="row text-center">
                    @foreach($scenarios as $key => $scenario)
                    <div class="col-md-4">
                        <div class="p-4 rounded-3" style="background: {{ $scenario['color'] }}10; border-left: 4px solid {{ $scenario['color'] }};">
                            <div class="fs-5 fw-bold">{{ $scenario['label'] }}</div>
                            <div class="display-6 fw-bold" style="color: {{ $scenario['color'] }};">
                                {{ number_format($scenario['value'], 0, ',', ' ') }}
                            </div>
                            <div class="small text-muted">{{ $devise }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Analyse de sensibilité --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-chart-line me-2" style="color: #7e3af2;"></i>Analyse de sensibilité</h5>
                <canvas id="sensitivityChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique des prévisions
    const forecastCtx = document.getElementById('forecastChart').getContext('2d');
    new Chart(forecastCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($caForecast['months']) !!},
            datasets: [
                {
                    label: 'Prévision CA',
                    data: {!! json_encode($caForecast['values']) !!},
                    borderColor: '#1B6EF3',
                    backgroundColor: 'rgba(27,110,243,0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#1B6EF3'
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
                            return `Prévision CA: ${new Intl.NumberFormat('fr-FR').format(context.raw)} {{ $devise }}`;
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
    
    // Graphique de sensibilité
    const sensitivityCtx = document.getElementById('sensitivityChart').getContext('2d');
    new Chart(sensitivityCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($sensitivity)->pluck('variation')->map(function($v) { return $v . '%'; })) !!},
            datasets: [
                {
                    label: 'Impact sur CA',
                    data: {!! json_encode(collect($sensitivity)->pluck('ca')) !!},
                    borderColor: '#0e9f6e',
                    backgroundColor: 'rgba(14,159,110,0.1)',
                    tension: 0.4,
                    fill: true
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
                            return `CA: ${new Intl.NumberFormat('fr-FR').format(context.raw)} {{ $devise }}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    title: { display: true, text: 'Chiffre d\'affaires ({{ $devise }})' }
                },
                x: {
                    title: { display: true, text: 'Variation (%)' }
                }
            }
        }
    });
</script>
@endpush
@endsection