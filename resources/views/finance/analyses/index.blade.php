{{-- resources/views/finance/analyses/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Analyse financière')
@section('page-title', 'Analyse financière détaillée')
@section('breadcrumb', 'Finance / Analyses')

@section('content')
<div class="container-fluid px-0">

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.analyses.index') }}" class="row g-3">
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
                            <option value="{{ $service->id_service }}" {{ $serviceId == $service->id_service ? 'selected' : '' }}>
                                {{ $service->nom_service }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-chart-line me-2"></i>Actualiser
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Comparaison annuelle --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-chart-line me-2" style="color: #1B6EF3;"></i>Évolution annuelle</h5>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: #f8fafc;">
                            <div class="small text-muted">{{ $year - 2 }}</div>
                            <div class="fs-4 fw-bold">{{ number_format($yearComparison['two_years_ago'], 0, ',', ' ') }}</div>
                            <div class="small">{{ $devise }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: #f8fafc;">
                            <div class="small text-muted">{{ $year - 1 }}</div>
                            <div class="fs-4 fw-bold">{{ number_format($yearComparison['previous_year'], 0, ',', ' ') }}</div>
                            <div class="small {{ $yearComparison['variation_prev'] >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="fas fa-arrow-{{ $yearComparison['variation_prev'] >= 0 ? 'up' : 'down' }}"></i>
                                {{ abs($yearComparison['variation_prev']) }}%
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: {{ $yearComparison['evolution'] == 'up' ? '#0e9f6e20' : '#e0242420' }}">
                            <div class="small text-muted">{{ $year }}</div>
                            <div class="fs-4 fw-bold text-{{ $yearComparison['evolution'] == 'up' ? 'success' : 'danger' }}">
                                {{ number_format($yearComparison['current_year'], 0, ',', ' ') }}
                            </div>
                            <div class="small text-{{ $yearComparison['evolution'] == 'up' ? 'success' : 'danger' }}">
                                <i class="fas fa-arrow-{{ $yearComparison['evolution'] }}"></i>
                                {{ abs($yearComparison['variation']) }}% vs {{ $year - 1 }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    {{-- Analyse mensuelle --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-table me-2"></i>Analyse mensuelle détaillée</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Mois</th>
                                <th>Chiffre d'affaires</th>
                                <th>Marge</th>
                                <th>Taux de marge</th>
                                <th>Variation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyData as $data)
                            <tr>
                                <td><strong>{{ $data['month'] }}</strong></td>
                                <td class="text-primary fw-bold">{{ number_format($data['ca'], 0, ',', ' ') }} {{ $devise }}</td>
                                <td class="text-success">{{ number_format($data['marge'], 0, ',', ' ') }} {{ $devise }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress" style="width: 80px; height: 6px;">
                                            <div class="progress-bar" style="width: {{ min($data['taux_marge'], 100) }}%; background: #0e9f6e;"></div>
                                        </div>
                                        <span>{{ $data['taux_marge'] }}%</span>
                                    </div>
                                </td>
                                <td class="{{ $data['variation_direction'] == 'up' ? 'text-success' : 'text-danger' }}">
                                    <i class="fas fa-arrow-{{ $data['variation_direction'] }}"></i>
                                    {{ abs($data['variation']) }}%
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Ratios financiers --}}
    <div class="row g-4">
        <div class="col-md-4">
            <div class="powerbi-card text-center">
                <div class="card-body">
                    <div class="display-4 text-primary">{{ $ratios['marge_nette'] }}%</div>
                    <div class="text-muted">Marge nette</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="powerbi-card text-center">
                <div class="card-body">
                    <div class="display-4 text-warning">{{ $ratios['ratio_depenses'] }}%</div>
                    <div class="text-muted">Ratio dépenses/CA</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="powerbi-card text-center">
                <div class="card-body">
                    <div class="display-4 text-success">{{ $ratios['rentabilite'] }}%</div>
                    <div class="text-muted">Rentabilité</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique comparatif mensuel
    const ctx = document.getElementById('monthlyComparisonChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($yearComparison['monthly_comparison'])->pluck('month')) !!},
            datasets: [
                {
                    label: '{{ $year }}',
                    data: {!! json_encode(collect($yearComparison['monthly_comparison'])->pluck('current')) !!},
                    backgroundColor: 'rgba(27,110,243,0.7)',
                    borderRadius: 8
                },
                {
                    label: '{{ $year - 1 }}',
                    data: {!! json_encode(collect($yearComparison['monthly_comparison'])->pluck('previous')) !!},
                    backgroundColor: 'rgba(148,163,184,0.7)',
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