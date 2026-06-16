{{-- resources/views/finance/simulations/history.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Historique des simulations')
@section('page-title', 'Historique des simulations')
@section('breadcrumb', 'Finance / Simulations / Historique')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-history me-2" style="color: #7e3af2;"></i>
                Historique des simulations
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }}
            </p>
        </div>
        <a href="{{ route('finance.simulations.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouvelle simulation
        </a>
    </div>

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.simulations.history') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Utilisateur</label>
                    <select name="user_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id_utilisateur }}" {{ request('user_id') == $user->id_utilisateur ? 'selected' : '' }}>
                                {{ $user->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Mois</label>
                    <select name="month" class="form-select">
                        <option value="">Tous</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                {{ Carbon\Carbon::createFromDate(null, $i, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Année</label>
                    <select name="year" class="form-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ (request('year') ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('finance.simulations.history') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-undo me-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Graphique d'activité --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-chart-bar me-2" style="color: #7e3af2;"></i>Activité des simulations</h5>
                <canvas id="activityChart" height="250"></canvas>
            </div>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="powerbi-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Nom</th>
                            <th>Créé par</th>
                            <th>Impact</th>
                            <th>ROI</th>
                            <th>Décision liée</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($simulations as $simulation)
                        @php
                            $resultats = is_string($simulation->resultats) ? json_decode($simulation->resultats, true) : $simulation->resultats;
                            $impact = $resultats['impact_tresorerie'] ?? 0;
                            $roi = $resultats['roi'] ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $simulation->created_at->translatedFormat('d/m/Y H:i') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-flask" style="color: #7e3af2;"></i>
                                    <strong>{{ $simulation->nom_simulation }}</strong>
                                </div>
                            </td>
                            <td>{{ $simulation->utilisateur->nom ?? '-' }}</td>
                            <td class="{{ $impact >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                {{ $impact >= 0 ? '+' : '' }}{{ number_format($impact, 0, ',', ' ') }} USD
                            </td>
                            <td class="{{ $roi >= 15 ? 'text-success' : ($roi >= 5 ? 'text-warning' : 'text-danger') }} fw-bold">
                                {{ $roi }}%
                            </td>
                            <td>
                                @if($simulation->decision)
                                    <span class="badge bg-info"><i class="fas fa-check-circle me-1"></i>Validée</span>
                                @else
                                    <span class="badge bg-secondary">Sans décision</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('finance.simulations.show', $simulation->id_simulation) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$simulation->decision)
                                        <button class="btn btn-outline-success" onclick="createDecision({{ $simulation->id_simulation }})" title="Créer une décision">
                                            <i class="fas fa-gavel"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-history fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Aucune simulation trouvée</p>
                                                        </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            {{ $simulations->withQueryString()->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Graphique d'activité
    const ctx = document.getElementById('activityChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyStats->pluck('month')->map(function($m) { 
                return Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F'); 
            })) !!},
            datasets: [{
                label: 'Nombre de simulations',
                data: {!! json_encode($monthlyStats->pluck('total')) !!},
                borderColor: '#7e3af2',
                backgroundColor: 'rgba(126,58,242,0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: '#7e3af2'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Simulations: ${context.raw}`;
                        }
                    }
                }
            }
        }
    });
    
    function createDecision(simulationId) {
        Swal.fire({
            title: 'Créer une décision',
            text: 'Voulez-vous créer une décision basée sur cette simulation ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1B6EF3',
            confirmButtonText: 'Oui, créer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('finance/simulations') }}/" + simulationId + "/create-decision";
            }
        });
    }
</script>
@endpush
@endsection