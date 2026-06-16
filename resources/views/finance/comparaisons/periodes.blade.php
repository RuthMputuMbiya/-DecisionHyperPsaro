{{-- resources/views/finance/comparaisons/periodes.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Comparaison par périodes')
@section('page-title', 'Comparaison de périodes')
@section('breadcrumb', 'Finance / Comparaisons / Périodes')

@section('content')
<div class="container-fluid px-0">

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.comparaisons.periodes') }}" class="row g-3" id="comparisonForm">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Période 1</label>
                    <select name="period1" class="form-select">
                        @foreach($availablePeriods as $p)
                            <option value="{{ $p['value'] }}" {{ $period1 == $p['value'] ? 'selected' : '' }}>{{ $p['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Période 2</label>
                    <select name="period2" class="form-select">
                        @foreach($availablePeriods as $p)
                            <option value="{{ $p['value'] }}" {{ $period2 == $p['value'] ? 'selected' : '' }}>{{ $p['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Indicateur</label>
                    <select name="indicateur_id" class="form-select">
                        <option value="">Tous les indicateurs</option>
                        @foreach($indicateurs as $ind)
                            <option value="{{ $ind->id_indicateur }}" {{ $indicateurId == $ind->id_indicateur ? 'selected' : '' }}>{{ $ind->nom_kpi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" id="compareBtn">
                        <i class="fas fa-chart-line me-2"></i>Comparer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Résumé de la comparaison --}}
    @if(isset($data1) && isset($data2))
    <div class="row g-4 mb-4">
        <div class="col-md-5">
            <div class="powerbi-card text-center">
                <div class="card-body">
                    <div class="text-muted small">{{ $data1['period'] }}</div>
                    <div class="display-5 fw-bold text-primary">{{ number_format($data1['total'], 0, ',', ' ') }}</div>
                    <div class="small">{{ $devise }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 text-center d-flex align-items-center">
            <div class="w-100">
                <div class="display-6 {{ $comparison['direction'] == 'up' ? 'text-success' : 'text-danger' }}">
                    <i class="fas fa-arrow-{{ $comparison['direction'] }} fa-2x"></i>
                </div>
                <div class="fw-bold {{ $comparison['direction'] == 'up' ? 'text-success' : 'text-danger' }}">
                    {{ $comparison['direction'] == 'up' ? '+' : '' }}{{ number_format($comparison['difference'], 0, ',', ' ') }}
                </div>
                <div class="small">({{ $comparison['percentage'] }}%)</div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="powerbi-card text-center">
                <div class="card-body">
                    <div class="text-muted small">{{ $data2['period'] }}</div>
                    <div class="display-5 fw-bold text-primary">{{ number_format($data2['total'], 0, ',', ' ') }}</div>
                    <div class="small">{{ $devise }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Comparaison par service --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-building me-2"></i>Comparaison détaillée par service</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Service</th>
                                <th class="text-end">{{ $data1['period'] }}</th>
                                <th class="text-end">{{ $data2['period'] }}</th>
                                <th class="text-end">Écart</th>
                                <th class="text-end">Variation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($servicesData as $service)
                            <tr>
                                <td><strong>{{ $service['service'] }}</strong></td>
                                <td class="text-end">{{ number_format($service['valeur1'], 0, ',', ' ') }} {{ $devise }}</td>
                                <td class="text-end">{{ number_format($service['valeur2'], 0, ',', ' ') }} {{ $devise }}</td>
                                <td class="text-end {{ $service['difference'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $service['difference'] >= 0 ? '+' : '' }}{{ number_format($service['difference'], 0, ',', ' ') }}
                                </td>
                                <td class="text-end {{ $service['variation'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $service['variation'] >= 0 ? '+' : '' }}{{ number_format($service['variation'], 1) }}%
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-chart-line fa-2x mb-2 d-block"></i>
                                    Aucune donnée disponible pour ces périodes
                                </td
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Résumé des écarts --}}
    @if(count($servicesData) > 0)
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-chart-simple me-2"></i>Synthèse des écarts</h5>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: #f8fafc;">
                            <div class="small text-muted">Meilleure progression</div>
                            <div class="fw-bold text-success">
                                @php
                                    $best = collect($servicesData)->sortByDesc('variation')->first();
                                @endphp
                                @if($best)
                                    {{ $best['service'] }} : +{{ number_format($best['variation'], 1) }}%
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: #f8fafc;">
                            <div class="small text-muted">Plus forte baisse</div>
                            <div class="fw-bold text-danger">
                                @php
                                    $worst = collect($servicesData)->sortBy('variation')->first();
                                @endphp
                                @if($worst && $worst['variation'] < 0)
                                    {{ $worst['service'] }} : {{ number_format($worst['variation'], 1) }}%
                                @else
                                    Aucune baisse
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background: #f8fafc;">
                            <div class="small text-muted">Services en hausse</div>
                            <div class="fw-bold text-primary">
                                {{ collect($servicesData)->where('variation', '>', 0)->count() }} / {{ count($servicesData) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @else
    <div class="row g-4">
        <div class="col-12">
            <div class="powerbi-card text-center p-5">
                <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                <h5>Sélectionnez deux périodes et cliquez sur "Comparer"</h5>
                <p class="text-muted">Choisissez une période 1, une période 2 et un indicateur pour commencer l'analyse</p>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    // 🔥 Animation du bouton lors du clic
    const form = document.getElementById('comparisonForm');
    const compareBtn = document.getElementById('compareBtn');
    
    if (form && compareBtn) {
        form.addEventListener('submit', function(e) {
            // Sauvegarder le texte original
            const originalText = compareBtn.innerHTML;
            
            // Changer le bouton en mode chargement
            compareBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Traitement en cours...';
            compareBtn.disabled = true;
            
            // Laisser le formulaire se soumettre normalement
            // Le bouton restera en mode chargement jusqu'à la réponse
        });
    }
</script>
@endpush
@endsection