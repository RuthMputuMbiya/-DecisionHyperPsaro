{{-- resources/views/direction/services/show.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Détails du service')
@section('page-title', 'Détails du service')
@section('breadcrumb', 'Direction / Services / Détails')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        <div class="col-lg-4">
            {{-- Carte Service --}}
            <div class="powerbi-card text-center">
                <div class="card-body p-4">
                    <div class="service-icon" style="width: 80px; height: 80px; background: {{ $service->couleur }}20; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i class="fas fa-building fa-3x" style="color: {{ $service->couleur }};"></i>
                    </div>
                    <h3 class="mb-1">{{ $service->nom_service }}</h3>
                    <div class="mb-3">
                        @if($service->est_actif)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-danger">Inactif</span>
                        @endif
                    </div>
                    
                    <hr>
                    
                    <div class="text-start">
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted"><i class="fas fa-user-tie me-2"></i>Chef de service</span>
                            <span class="fw-semibold">{{ $service->chef_service ?? 'Non défini' }}</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted"><i class="fas fa-dollar-sign me-2"></i>Budget annuel</span>
                            <span class="fw-semibold">{{ number_format($service->budget_annuel, 0, ',', ' ') }} USD</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted"><i class="fas fa-users me-2"></i>Employés</span>
                            <span class="fw-semibold">{{ $stats['employes'] }}</span>
                        </div>
                        <div class="info-row d-flex justify-content-between py-2">
                            <span class="text-muted"><i class="fas fa-calendar-plus me-2"></i>Date création</span>
                            <span>{{ $service->created_at->translatedFormat('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            {{-- Description --}}
            @if($service->description)
            <div class="powerbi-card mb-4">
                <div class="card-header-custom p-4 border-bottom">
                    <h5 class="mb-0"><i class="fas fa-align-left me-2"></i>Description</h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-0">{{ $service->description }}</p>
                </div>
            </div>
            @endif
            
            {{-- Performance financière --}}
            <div class="powerbi-card mb-4">
                <div class="card-header-custom p-4 border-bottom">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Performance financière</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="perf-item p-3 rounded" style="background: #f8fafc;">
                                <div class="small text-muted">Chiffre d'affaires</div>
                                <div class="fs-4 fw-bold text-primary">{{ number_format($stats['ca_total'], 0, ',', ' ') }} USD</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="perf-item p-3 rounded" style="background: #f8fafc;">
                                <div class="small text-muted">Marge brute</div>
                                <div class="fs-4 fw-bold text-success">{{ number_format($stats['marge_total'], 0, ',', ' ') }} USD</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="perf-item p-3 rounded" style="background: #f8fafc;">
                                <div class="small text-muted">Dépenses totales</div>
                                <div class="fs-4 fw-bold text-danger">{{ number_format($stats['depenses_total'], 0, ',', ' ') }} USD</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="perf-item p-3 rounded" style="background: #f8fafc;">
                                <div class="small text-muted">Investissements</div>
                                <div class="fs-4 fw-bold text-warning">{{ number_format($stats['investissements'], 0, ',', ' ') }} USD</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Évolution mensuelle --}}
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Évolution mensuelle du CA</h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>
            
            {{-- Liste des employés --}}
            @if($service->utilisateurs->count() > 0)
            <div class="powerbi-card mt-4">
                <div class="card-header-custom p-4 border-bottom">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Équipe ({{ $service->utilisateurs->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Matricule</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th></tr>
                            </thead>
                            <tbody>
                                @foreach($service->utilisateurs as $user)
                                <tr>
                                    <td><code>{{ $user->matricule }}</code></td>
                                    <td>{{ $user->nom }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge bg-secondary">{{ $user->role->nom_role ?? '-' }}</span></td>
                                    <td>@if($user->est_actif)<span class="badge bg-success">Actif</span>@else<span class="badge bg-danger">Inactif</span>@endif</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
            datasets: [{
                label: 'Chiffre d\'affaires (USD)',
                data: {{ json_encode($monthlyData) }},
                backgroundColor: '{{ $service->couleur }}80',
                borderColor: '{{ $service->couleur }}',
                borderWidth: 2,
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
                            return `CA: ${new Intl.NumberFormat('fr-FR').format(context.raw)} USD`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR').format(value) + ' USD';
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection