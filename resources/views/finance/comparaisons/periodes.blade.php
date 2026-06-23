{{-- resources/views/finance/comparaisons/periodes.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Comparaison par périodes')
@section('page-title', 'Comparaison de périodes')
@section('breadcrumb', 'Finance / Comparaisons / Périodes')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">

    {{-- ============================================ --}}
    {{-- EN-TÊTE --}}
    {{-- ============================================ --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold" style="color: #0f172a;">
                <i class="fas fa-calendar-alt me-2" style="color: #1B6EF3;"></i>
                Comparaison par périodes
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
                <span class="badge bg-success ms-2">
                    <i class="fas fa-sync-alt me-1 fa-spin"></i> Données en temps réel
                </span>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('finance.comparaisons.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
            <button class="btn btn-outline-secondary btn-sm" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i>Actualiser
            </button>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- FILTRES --}}
    {{-- ============================================ --}}
    <div class="card mb-4" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('finance.comparaisons.periodes') }}" class="row g-3" id="comparisonForm">
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted fw-bold"><i class="fas fa-calendar me-1"></i>Période 1</label>
                    <select name="period1" class="form-select form-select-sm">
                        @foreach($availablePeriods as $p)
                            <option value="{{ $p['value'] }}" {{ $period1 == $p['value'] ? 'selected' : '' }}>{{ $p['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted fw-bold"><i class="fas fa-calendar me-1"></i>Période 2</label>
                    <select name="period2" class="form-select form-select-sm">
                        @foreach($availablePeriods as $p)
                            <option value="{{ $p['value'] }}" {{ $period2 == $p['value'] ? 'selected' : '' }}>{{ $p['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted fw-bold"><i class="fas fa-chart-line me-1"></i>Indicateur</label>
                    <select name="indicateur_id" class="form-select form-select-sm">
                        <option value="">Tous les indicateurs</option>
                        @foreach($indicateurs as $ind)
                            <option value="{{ $ind->id_indicateur }}" {{ $indicateurId == $ind->id_indicateur ? 'selected' : '' }}>{{ $ind->nom_kpi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" id="compareBtn">
                        <i class="fas fa-chart-line me-2"></i>Comparer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- RÉSULTATS DE LA COMPARAISON --}}
    {{-- ============================================ --}}
    @if(isset($data1) && isset($data2) && ($data1['total'] > 0 || $data2['total'] > 0))
    
    {{-- RÉSUMÉ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-5 col-md-5">
            <div class="card text-center h-100" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); background: linear-gradient(135deg, #e8f0fe, #d4e2fc);">
                <div class="card-body p-3 p-md-4">
                    <div class="small text-muted text-uppercase">{{ $data1['period'] }}</div>
                    <div class="display-6 fw-bold text-primary">{{ number_format($data1['total'], 0, ',', ' ') }}</div>
                    <div class="small">{{ $devise }}</div>
                </div>
            </div>
        </div>
        <div class="col-2 col-md-2 text-center d-flex align-items-center">
            <div class="w-100">
                <div class="display-6 {{ $comparison['direction'] == 'up' ? 'text-success' : 'text-danger' }}">
                    <i class="fas fa-arrow-{{ $comparison['direction'] == 'up' ? 'up' : 'down' }} fa-2x"></i>
                </div>
                <div class="fw-bold fs-5 {{ $comparison['direction'] == 'up' ? 'text-success' : 'text-danger' }}">
                    {{ $comparison['direction'] == 'up' ? '+' : '' }}{{ number_format($comparison['difference'], 0, ',', ' ') }}
                </div>
                <div class="small text-muted">{{ $comparison['percentage'] }}%</div>
            </div>
        </div>
        <div class="col-5 col-md-5">
            <div class="card text-center h-100" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); background: linear-gradient(135deg, #e8f5f0, #d4ede4);">
                <div class="card-body p-3 p-md-4">
                    <div class="small text-muted text-uppercase">{{ $data2['period'] }}</div>
                    <div class="display-6 fw-bold text-success">{{ number_format($data2['total'], 0, ',', ' ') }}</div>
                    <div class="small">{{ $devise }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- GRAPHIQUE COMPARATIF --}}
    @if(count($servicesData) > 0)
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-12">
            <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f0ecff, #e0d8f5); border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fs-6 fw-bold" style="color: #7e3af2;">
                        <i class="fas fa-chart-bar me-2"></i>Comparaison par service
                    </h5>
                    <span class="badge bg-primary">{{ count($servicesData) }} services</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 320px;">
                        <canvas id="comparisonChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TABLEAU DÉTAILLÉ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-12">
            <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #e8f5f0, #d4ede4); border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fs-6 fw-bold" style="color: #0e9f6e;">
                        <i class="fas fa-table me-2"></i>Détail par service
                    </h5>
                    <span class="badge bg-success">{{ count($servicesData) }} services</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-sm table-md">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">Service</th>
                                    <th class="py-3 text-end">{{ $data1['period'] }}</th>
                                    <th class="py-3 text-end">{{ $data2['period'] }}</th>
                                    <th class="py-3 text-end">Écart</th>
                                    <th class="py-3 text-center">Variation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($servicesData as $service)
                                <tr>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width: 10px; height: 10px; background: {{ $service['couleur'] ?? '#1a56db' }}; border-radius: 50%;"></div>
                                            <strong class="small">{{ $service['service'] }}</strong>
                                        </div>
                                    </td>
                                    <td class="py-3 text-end">{{ number_format($service['valeur1'], 0, ',', ' ') }}</td>
                                    <td class="py-3 text-end">{{ number_format($service['valeur2'], 0, ',', ' ') }}</td>
                                    <td class="py-3 text-end fw-bold {{ $service['difference'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $service['difference'] >= 0 ? '+' : '' }}{{ number_format($service['difference'], 0, ',', ' ') }}
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge {{ $service['variation'] >= 0 ? 'bg-success' : 'bg-danger' }}" style="padding: 6px 14px;">
                                            <i class="fas fa-arrow-{{ $service['variation'] >= 0 ? 'up' : 'down' }} me-1"></i>
                                            {{ $service['variation'] >= 0 ? '+' : '' }}{{ number_format($service['variation'], 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-chart-line fa-2x text-muted mb-2 d-block"></i>
                                        <p class="text-muted mb-0">Aucune donnée disponible pour ces périodes</p>
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

    {{-- SYNTHÈSE DES ÉCARTS --}}
    @if(count($servicesData) > 0)
    <div class="row g-2 g-md-3">
        <div class="col-12">
            <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); background: linear-gradient(135deg, #fff8e8, #ffeed4);">
                <div class="card-body p-3 p-md-4">
                    <h6 class="mb-3 fw-bold" style="color: #f5a623;">
                        <i class="fas fa-chart-simple me-2"></i>Synthèse des écarts
                    </h6>
                    <div class="row text-center g-3">
                        <div class="col-4 col-md-4">
                            <div class="p-2 p-md-3 rounded" style="background: white; border-radius: 12px;">
                                <div class="small text-muted">Meilleure progression</div>
                                @php
                                    $best = collect($servicesData)->sortByDesc('variation')->first();
                                @endphp
                                @if($best)
                                    <div class="fw-bold text-success fs-5">{{ $best['service'] }}</div>
                                    <div class="small text-success">+{{ number_format($best['variation'], 1) }}%</div>
                                @else
                                    <div class="text-muted">-</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="p-2 p-md-3 rounded" style="background: white; border-radius: 12px;">
                                <div class="small text-muted">Plus forte baisse</div>
                                @php
                                    $worst = collect($servicesData)->sortBy('variation')->first();
                                @endphp
                                @if($worst && $worst['variation'] < 0)
                                    <div class="fw-bold text-danger fs-5">{{ $worst['service'] }}</div>
                                    <div class="small text-danger">{{ number_format($worst['variation'], 1) }}%</div>
                                @else
                                    <div class="text-success">✅ Aucune baisse</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="p-2 p-md-3 rounded" style="background: white; border-radius: 12px;">
                                <div class="small text-muted">Services en hausse</div>
                                <div class="fw-bold text-primary fs-5">
                                    {{ collect($servicesData)->where('variation', '>', 0)->count() }} / {{ count($servicesData) }}
                                </div>
                                <div class="small text-muted">services</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @else
    {{-- MESSAGE SI AUCUNE DONNÉE --}}
    <div class="row g-2 g-md-3">
        <div class="col-12">
            <div class="card text-center" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-body p-5">
                    <i class="fas fa-chart-line fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                    <h5 class="fw-bold" style="color: #0f172a;">Sélectionnez deux périodes</h5>
                    <p class="text-muted mb-0">Choisissez une période 1, une période 2 et un indicateur pour commencer l'analyse</p>
                    <div class="mt-3 d-flex justify-content-center gap-3 flex-wrap">
                        <span class="badge bg-primary"><i class="fas fa-calendar me-1"></i>Période 1</span>
                        <span class="badge bg-success"><i class="fas fa-calendar me-1"></i>Période 2</span>
                        <span class="badge bg-warning" style="color: #000;"><i class="fas fa-chart-line me-1"></i>Indicateur</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // GRAPHIQUE COMPARATIF
    // ============================================
    @if(isset($servicesData) && count($servicesData) > 0)
    const ctx = document.getElementById('comparisonChart')?.getContext('2d');
    if (ctx) {
        const labels = @json(collect($servicesData)->pluck('service'));
        const data1 = @json(collect($servicesData)->pluck('valeur1'));
        const data2 = @json(collect($servicesData)->pluck('valeur2'));
        const colors = @json(collect($servicesData)->pluck('couleur'));
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '{{ $data1['period'] ?? 'Période 1' }}',
                        data: data1,
                        backgroundColor: 'rgba(27,110,243,0.7)',
                        borderColor: '#1B6EF3',
                        borderWidth: 2,
                        borderRadius: 6,
                        barPercentage: 0.4,
                        categoryPercentage: 0.6
                    },
                    {
                        label: '{{ $data2['period'] ?? 'Période 2' }}',
                        data: data2,
                        backgroundColor: 'rgba(14,159,110,0.7)',
                        borderColor: '#0e9f6e',
                        borderWidth: 2,
                        borderRadius: 6,
                        barPercentage: 0.4,
                        categoryPercentage: 0.6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 20,
                            font: { size: 12, weight: '600' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        titleFont: { size: 14, weight: '700' },
                        bodyFont: { size: 13 },
                        padding: 16,
                        cornerRadius: 12,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' {{ $devise }}';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.06)' },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
                                return new Intl.NumberFormat('fr-FR').format(value);
                            },
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '500' } }
                    }
                },
                animation: { duration: 800 }
            }
        });
    }
    @endif
});

// ============================================
// FONCTIONS
// ============================================
function refreshData() {
    Swal.fire({
        title: '🔄 Actualisation',
        text: 'Les données sont en cours de mise à jour...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
    setTimeout(() => { window.location.reload(); }, 1500);
}
</script>
@endpush

@push('styles')
<style>
    .card-header-custom {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .chart-container {
        min-height: 200px;
        position: relative;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
    }
    .table td {
        vertical-align: middle;
        font-size: 0.85rem;
    }
    
    @media (max-width: 768px) {
        .chart-container { min-height: 220px; }
        .display-6 { font-size: 1.5rem !important; }
        .table td, .table th { font-size: 0.75rem; padding: 8px 6px; }
    }
    
    @media (min-width: 992px) {
        .chart-container { min-height: 300px; }
        .display-6 { font-size: 2.2rem !important; }
    }
</style>
@endpush
@endsection