{{-- resources/views/finance/reporting/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Reporting & Export')
@section('page-title', 'Reporting et Export de données')
@section('breadcrumb', 'Finance / Reporting')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-file-alt me-2" style="color: #1B6EF3;"></i>
                Reporting et Export
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
            </p>
        </div>
    </div>

    {{-- Statistiques des exports --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $exportStats['total_exports'] }}</div>
                <div class="stat-label">Total exports</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #e02424, #b91c1c); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $exportStats['pdf_count'] }}</div>
                <div class="stat-label">Exports PDF</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $exportStats['excel_count'] }}</div>
                <div class="stat-label">Exports Excel/CSV</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $exportStats['last_export'] ? Carbon\Carbon::parse($exportStats['last_export'])->translatedFormat('d/m/Y') : '-' }}</div>
                <div class="stat-label">Dernier export</div>
            </div>
        </div>
    </div>

    {{-- Formulaire d'export --}}
    <div class="powerbi-card mb-4">
        <div class="card-header-custom p-4 border-bottom">
            <h5 class="mb-0"><i class="fas fa-download me-2"></i>Générer un rapport</h5>
        </div>
        <div class="card-body p-4">
            <form id="exportForm" method="POST" action="{{ route('finance.reporting.export') }}">
                @csrf
                <div class="row g-4">
                    {{-- Type de rapport --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Type de rapport</label>
                        <select name="report_type" class="form-select" id="reportType" required>
                            <option value="financial">📊 Rapport financier synthétique</option>
                            <option value="kpi">📈 Tableau de bord des KPIs</option>
                            <option value="services">🏢 Performance par service</option>
                            <option value="decisions">📋 Historique des décisions</option>
                            <option value="monthly">📅 Rapport mensuel</option>
                            <option value="yearly">📆 Rapport annuel</option>
                            <option value="custom">⚙️ Rapport personnalisé</option>
                        </select>
                    </div>
                    
                    {{-- Format d'export --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Format</label>
                        <select name="type" class="form-select" required>
                            <option value="pdf">📄 PDF</option>
                            <option value="excel">📊 Excel</option>
                            <option value="csv">📑 CSV</option>
                        </select>
                    </div>
                    
                    {{-- Orientation --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Orientation</label>
                        <select name="orientation" class="form-select">
                            <option value="portrait">Portrait</option>
                            <option value="landscape">Paysage</option>
                        </select>
                    </div>
                    
                    {{-- Format papier --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Format papier</label>
                        <select name="format" class="form-select">
                            <option value="A4">A4</option>
                            <option value="Letter">Letter</option>
                            <option value="Legal">Legal</option>
                        </select>
                    </div>
                </div>
                
                {{-- Options spécifiques --}}
                <div class="row g-4 mt-2" id="dynamicFields">
                    <div class="col-md-4" id="yearField">
                        <label class="form-label fw-semibold">Année</label>
                        <select name="year" class="form-select">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4" id="monthField" style="display: none;">
                        <label class="form-label fw-semibold">Mois</label>
                        <select name="month" class="form-select">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $i == date('m') ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::createFromDate(null, $i, 1)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="col-md-4" id="serviceField" style="display: none;">
                        <label class="form-label fw-semibold">Service</label>
                        <select name="service_id" class="form-select">
                            <option value="">Tous les services</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id_service }}">{{ $service->nom_service }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4" id="indicateurField" style="display: none;">
                        <label class="form-label fw-semibold">Indicateur</label>
                        <select name="indicateur_id" class="form-select">
                            <option value="">Tous les indicateurs</option>
                            @foreach($indicateurs as $ind)
                                <option value="{{ $ind->id_indicateur }}">{{ $ind->nom_kpi }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4" id="dateRangeField" style="display: none;">
                        <label class="form-label fw-semibold">Date début</label>
                        <input type="date" name="start_date" class="form-control" value="{{ Carbon\Carbon::now()->subMonth()->format('Y-m-d') }}">
                    </div>
                    
                    <div class="col-md-4" id="dateEndField" style="display: none;">
                        <label class="form-label fw-semibold">Date fin</label>
                        <input type="date" name="end_date" class="form-control" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
                    </div>
                </div>
                
                {{-- Options supplémentaires --}}
                <div class="row g-4 mt-2">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_charts" value="1" id="includeCharts">
                            <label class="form-check-label" for="includeCharts">
                                <i class="fas fa-chart-line me-1"></i> Inclure les graphiques
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="include_summary" value="1" id="includeSummary" checked>
                            <label class="form-check-label" for="includeSummary">
                                <i class="fas fa-align-left me-1"></i> Inclure le résumé exécutif
                            </label>
                        </div>
                    </div>
                </div>
                
                {{-- Boutons d'action --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-download me-2"></i>Générer et exporter
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo me-2"></i>Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Aperçu des données --}}
    <div class="row g-4">
        {{-- Évolution mensuelle --}}
        <div class="col-xl-6">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2" style="color: #1B6EF3;"></i>Évolution mensuelle {{ date('Y') }}</h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        {{-- Top services --}}
        <div class="col-xl-6">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2" style="color: #f5a623;"></i>Top 5 services</h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="servicesChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Dernières décisions --}}
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <h5 class="mb-0"><i class="fas fa-gavel me-2" style="color: #7e3af2;"></i>Dernières décisions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Titre</th><th>Statut</th><th>Impact</th><th>Date</th><th>Créé par</th></tr>
                            </thead>
                            <tbody>
                                @foreach($recentDecisions as $decision)
                                <tr>
                                    <td><strong>{{ Str::limit($decision->titre, 50) }}</strong></td>
                                    <td><span class="badge bg-{{ $decision->statut == 'Validée' ? 'success' : ($decision->statut == 'Rejetée' ? 'danger' : 'warning') }}">{{ $decision->statut }}</span></td>
                                    <td class="{{ $decision->impact_tresorerie >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $decision->impact_tresorerie >= 0 ? '+' : '' }}{{ number_format($decision->impact_tresorerie, 0, ',', ' ') }} {{ $devise }}
                                    </td>
                                    <td>{{ $decision->created_at->translatedFormat('d/m/Y') }}</td>
                                    <td>{{ $decision->createur->nom ?? '-' }}</td>
                                </tr>
                                @endforeach
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
    // Graphique mensuel
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($monthlyEvolution)->pluck('month')) !!},
            datasets: [{
                label: 'Chiffre d\'affaires ({{ $devise }})',
                data: {!! json_encode(collect($monthlyEvolution)->pluck('ca')) !!},
                borderColor: '#1B6EF3',
                backgroundColor: 'rgba(27,110,243,0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4
            }]
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
    
    // Graphique services
    new Chart(document.getElementById('servicesChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($topServices->pluck('name')) !!},
            datasets: [{
                label: 'CA ({{ $devise }})',
                data: {!! json_encode($topServices->pluck('ca')) !!},
                backgroundColor: {!! json_encode($topServices->pluck('couleur')) !!},
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `CA: ${new Intl.NumberFormat('fr-FR').format(context.raw)} {{ $devise }}`;
                        }
                    }
                }
            }
        }
    });
    
    // Dynamique des champs
    const reportType = document.getElementById('reportType');
    const yearField = document.getElementById('yearField');
    const monthField = document.getElementById('monthField');
    const serviceField = document.getElementById('serviceField');
    const indicateurField = document.getElementById('indicateurField');
    const dateRangeField = document.getElementById('dateRangeField');
    const dateEndField = document.getElementById('dateEndField');
    
    function updateFields() {
        const type = reportType.value;
        
        // Cacher tous
        monthField.style.display = 'none';
        serviceField.style.display = 'none';
        indicateurField.style.display = 'none';
        dateRangeField.style.display = 'none';
        dateEndField.style.display = 'none';
        yearField.style.display = 'block';
        
        switch(type) {
            case 'monthly':
                monthField.style.display = 'block';
                break;
            case 'services':
                serviceField.style.display = 'block';
                break;
            case 'kpi':
                indicateurField.style.display = 'block';
                break;
            case 'custom':
                dateRangeField.style.display = 'block';
                dateEndField.style.display = 'block';
                yearField.style.display = 'none';
                break;
            case 'decisions':
                // Champs par défaut
                break;
        }
    }
    
    reportType.addEventListener('change', updateFields);
    updateFields();
</script>
@endpush

<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
</style>
@endsection