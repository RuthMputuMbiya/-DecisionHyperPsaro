{{-- resources/views/finance/analyses/tendances.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Tendances financières')
@section('page-title', 'Analyse des tendances')
@section('breadcrumb', 'Finance / Tendances')

@section('content')
<div class="container-fluid px-0">

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.analyses.tendances') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Période</label>
                    <select name="period" class="form-select" onchange="this.form.submit()">
                        <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Mensuel</option>
                        <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>Trimestriel</option>
                        <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Annuel</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- Graphique de tendance --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-chart-line me-2" style="color: #1B6EF3;"></i>Évolution et tendance</h5>
                <canvas id="trendChart" height="350"></canvas>
            </div>
        </div>
    </div>

    {{-- Indicateurs --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="powerbi-card text-center">
                <div class="card-body">
                    <div class="display-4 text-primary">{{ number_format($indicators['moving_average_3m'], 0, ',', ' ') }}</div>
                    <div class="text-muted">Moyenne mobile 3 mois</div>
                    <div class="small {{ $indicators['moving_average_trend'] == 'up' ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-arrow-{{ $indicators['moving_average_trend'] }}"></i> Tendance
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="powerbi-card text-center">
                <div class="card-body">
                    <div class="display-4 text-success">{{ $indicators['cagr'] }}%</div>
                    <div class="text-muted">CAGR (Croissance annuelle)</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="powerbi-card text-center">
                <div class="card-body">
                    <div class="display-4 text-warning">{{ number_format($indicators['volatility'], 1) }}</div>
                    <div class="text-muted">Volatilité</div>
                    <div class="small">{{ $indicators['stability'] == 'stable' ? '📈 Stable' : ($indicators['stability'] == 'moderate' ? '⚠️ Modérée' : '🔴 Volatile') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Saisonnalité --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-calendar-alt me-2" style="color: #f5a623;"></i>Saisonnalité</h5>
                <canvas id="seasonalityChart" height="300"></canvas>
                <div class="row text-center mt-3">
                    <div class="col-md-6">
                        <div class="alert alert-success">
                            <i class="fas fa-chart-line"></i> Pic d'activité : <strong>{{ $seasonality['peak_month'] }}</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <i class="fas fa-chart-line"></i> Creux d'activité : <strong>{{ $seasonality['low_month'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique de tendance
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($trendData['ca']->pluck('period')) !!},
            datasets: [
                {
                    label: 'Chiffre d\'affaires',
                    data: {!! json_encode($trendData['ca']->pluck('total')) !!},
                    borderColor: '#1B6EF3',
                    backgroundColor: 'rgba(27,110,243,0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4
                },
                {
                    label: 'Marge',
                    data: {!! json_encode($trendData['marge']->pluck('total')) !!},
                    borderColor: '#0e9f6e',
                    backgroundColor: 'rgba(14,159,110,0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4
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
    
    // Graphique de saisonnalité
    const seasonCtx = document.getElementById('seasonalityChart').getContext('2d');
    new Chart(seasonCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($seasonality['data'])->pluck('month')) !!},
            datasets: [{
                label: 'Moyenne mensuelle ({{ $devise }})',
                data: {!! json_encode(collect($seasonality['data'])->pluck('average')) !!},
                backgroundColor: function(context) {
                    const value = context.raw;
                    const avg = {!! json_encode(collect($seasonality['data'])->avg('average')) !!};
                    return value >= avg ? 'rgba(14,159,110,0.7)' : 'rgba(245,166,35,0.7)';
                },
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.raw.toLocaleString('fr-FR')} {{ $devise }} (Indice: ${context.dataset.data[context.dataIndex].index}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection