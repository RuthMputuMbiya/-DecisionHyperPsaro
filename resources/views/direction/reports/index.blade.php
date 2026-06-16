{{-- resources/views/direction/reports/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Rapports')
@section('page-title', 'Génération de rapports')
@section('breadcrumb', 'Direction / Rapports')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-file-alt me-2" style="color: #1B6EF3;"></i>
                Rapports et analyses
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }}
            </p>
        </div>
    </div>

    {{-- Grille des rapports --}}
    <div class="row g-4">
        @foreach($reports as $report)
        <div class="col-xl-4 col-md-6">
            <div class="report-card powerbi-card h-100" data-report-id="{{ $report['id'] }}">
                <div class="card-body p-4 text-center">
                    <div class="report-icon" style="width: 64px; height: 64px; background: {{ $report['color'] }}20; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="{{ $report['icon'] }} fa-2x" style="color: {{ $report['color'] }};"></i>
                    </div>
                    <h5 class="mb-2">{{ $report['name'] }}</h5>
                    <p class="text-muted small mb-3">{{ $report['description'] }}</p>
                    <button class="btn btn-primary btn-sm" onclick="generateReport('{{ $report['id'] }}')">
                        <i class="fas fa-download me-1"></i> Générer PDF
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Modal pour les options du rapport --}}
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1B6EF3, #7e3af2); color: white;">
                <h5 class="modal-title"><i class="fas fa-chart-line me-2"></i>Options du rapport</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Année</label>
                    <select id="reportYear" class="form-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3" id="monthField" style="display: none;">
                    <label class="form-label fw-semibold">Mois</label>
                    <select id="reportMonth" class="form-select">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $i == date('m') ? 'selected' : '' }}>
                                {{ Carbon\Carbon::createFromDate(null, $i, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="mb-3" id="serviceField" style="display: none;">
                    <label class="form-label fw-semibold">Service</label>
                    <select id="reportService" class="form-select">
                        <option value="">Tous les services</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id_service }}">{{ $service->nom_service }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="confirmGenerate">Générer</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let currentReportId = null;
    
    function generateReport(reportId) {
        currentReportId = reportId;
        
        // Réinitialiser les champs
        document.getElementById('monthField').style.display = 'none';
        document.getElementById('serviceField').style.display = 'none';
        
        // Afficher les options selon le rapport
        if (reportId === 'monthly_report') {
            document.getElementById('monthField').style.display = 'block';
        } else if (reportId === 'services_performance') {
            document.getElementById('serviceField').style.display = 'block';
        }
        
        new bootstrap.Modal(document.getElementById('reportModal')).show();
    }
    
    document.getElementById('confirmGenerate').addEventListener('click', function() {
        const year = document.getElementById('reportYear').value;
        let url = "{{ url('direction/reports') }}/" + currentReportId + "/generate?year=" + year;
        
        if (currentReportId === 'monthly_report') {
            const month = document.getElementById('reportMonth').value;
            url += "&month=" + month;
        }
        if (currentReportId === 'services_performance') {
            const service = document.getElementById('reportService').value;
            if (service) url += "&service_id=" + service;
        }
        
        window.location.href = url;
    });
</script>
@endpush

<style>
    .report-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
</style>
@endsection