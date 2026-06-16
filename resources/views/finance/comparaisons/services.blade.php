{{-- resources/views/finance/comparaisons/services.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Comparaison par services')
@section('page-title', 'Performance des services')
@section('breadcrumb', 'Finance / Comparaisons / Services')

@section('content')
<div class="container-fluid px-0">

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.comparaisons.services') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Année</label>
                    <select name="year" class="form-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Indicateur</label>
                    <select name="indicateur_id" class="form-select">
                        <option value="">Chiffre d'affaires</option>
                        @foreach($indicateurs as $ind)
                            <option value="{{ $ind->id_indicateur }}" {{ $indicateurId == $ind->id_indicateur ? 'selected' : '' }}>{{ $ind->nom_kpi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-chart-bar me-2"></i>Actualiser
                    </button>
                </div>
            </form>
        </div>
    </div>

  

    {{-- Top et Bottom --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-trophy me-2" style="color: #f5a623;"></i>Top 5 services</h5>
                @foreach($topServices as $index => $service)
                <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded" style="background: #f8fafc;">
                    <div>
                        <span class="badge bg-warning me-2">{{ $index + 1 }}</span>
                        <strong>{{ $service['service'] }}</strong>
                    </div>
                    <div class="fw-bold text-success">{{ number_format($service['valeur'], 0, ',', ' ') }} {{ $devise }}</div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="col-md-6">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-chart-line me-2" style="color: #e02424;"></i>Services à améliorer</h5>
                @foreach($bottomServices as $index => $service)
                <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded" style="background: #f8fafc;">
                    <div>
                        <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                        <strong>{{ $service['service'] }}</strong>
                    </div>
                    <div class="fw-bold text-danger">{{ number_format($service['valeur'], 0, ',', ' ') }} {{ $devise }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Statistiques et tableau --}}
    <div class="row g-4">
        <div class="col-md-4">
            <div class="powerbi-card text-center">
                <div class="card-body">
                    <div class="display-4 text-primary">{{ number_format($moyenne, 0, ',', ' ') }}</div>
                    <div class="text-muted">Moyenne par service</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="powerbi-card text-center">
                <div class="card-body">
                    <div class="display-4 text-success">{{ number_format($mediane, 0, ',', ' ') }}</div>
                    <div class="text-muted">Médiane</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="powerbi-card text-center">
                <div class="card-body">
                    <div class="display-4 text-warning">{{ $servicesData ? count($servicesData) : 0 }}</div>
                    <div class="text-muted">Services actifs</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('servicesChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($servicesData)->pluck('service')) !!},
            datasets: [{
                label: 'Valeur ({{ $devise }})',
                data: {!! json_encode(collect($servicesData)->pluck('valeur')) !!},
                backgroundColor: {!! json_encode(collect($servicesData)->pluck('couleur')) !!},
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
                            let value = context.raw;
                            let performance = context.dataset.data[context.dataIndex].performance;
                            return `${new Intl.NumberFormat('fr-FR').format(value)} {{ $devise }} (Performance: ${performance}%)`;
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