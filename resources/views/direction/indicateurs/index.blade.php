{{-- resources/views/direction/indicateurs/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Indicateurs de performance')
@section('page-title', 'Indicateurs de performance (KPIs)')
@section('breadcrumb', 'Direction / Indicateurs')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-chart-line me-2" style="color: #1B6EF3;"></i>
                Indicateurs clés de performance
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-clock ms-2 me-1"></i> {{ now()->format('H:i') }}
            </p>
        </div>
        <a href="{{ route('direction.indicateurs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouvel indicateur
        </a>
    </div>

    {{-- Statistiques --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['total'] }}</div>
                <div class="stat-label">Total indicateurs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['actifs'] }}</div>
                <div class="stat-label">Indicateurs actifs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #e02424, #b91c1c); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['inactifs'] }}</div>
                <div class="stat-label">Indicateurs inactifs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center p-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white;">
                <div class="stat-number display-6 fw-bold">{{ $stats['avec_alertes'] }}</div>
                <div class="stat-label">Avec seuils d'alerte</div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="powerbi-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('direction.indicateurs.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small text-muted">Recherche</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nom, code ou description..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="actif" {{ request('status') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ request('status') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tableau des indicateurs --}}
    <div class="powerbi-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Indicateur</th>
                            <th>Unité</th>
                            <th>Valeur actuelle</th>
                            <th>Seuil min</th>
                            <th>Seuil max</th>
                            <th>Statut</th>
                            <th>Alertes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indicateurs as $indicateur)
                        @php
                            $estAlerte = false;
                            if ($indicateur->seuil_alerte_min && $indicateur->valeur_actuelle < $indicateur->seuil_alerte_min) {
                                $estAlerte = true;
                            } elseif ($indicateur->seuil_alerte_max && $indicateur->valeur_actuelle > $indicateur->seuil_alerte_max) {
                                $estAlerte = true;
                            }
                        @endphp
                        <tr>
                            <td><code class="fw-bold">{{ $indicateur->code_kpi }}</code></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="icon-bg" style="width: 32px; height: 32px; background: {{ $indicateur->couleur_alerte }}20; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="{{ $indicateur->icone }}" style="color: {{ $indicateur->couleur_alerte }}; font-size: 14px;"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $indicateur->nom_kpi }}</strong>
                                        <br><small class="text-muted">{{ Str::limit($indicateur->description, 40) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary">{{ $indicateur->unite }}</span></td>
                            <td class="fw-bold">{{ number_format($indicateur->valeur_actuelle, 0, ',', ' ') }}</td>
                            <td class="text-muted">{{ $indicateur->seuil_alerte_min ? number_format($indicateur->seuil_alerte_min, 0, ',', ' ') : '-' }}</td>
                            <td class="text-muted">{{ $indicateur->seuil_alerte_max ? number_format($indicateur->seuil_alerte_max, 0, ',', ' ') : '-' }}</td>
                            <td>
                                @if($indicateur->est_actif)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </td>
                            <td>
                                @if($estAlerte)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Alerte
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Normal
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('direction.indicateurs.show', $indicateur->id_indicateur) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('direction.indicateurs.edit', $indicateur->id_indicateur) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    {{-- 🔥 TOGGLE STATUS - POST --}}
                                    <form method="POST" action="{{ route('direction.indicateurs.toggle-status', $indicateur->id_indicateur) }}" style="display: inline-block;" 
                                          onsubmit="return confirm('{{ $indicateur->est_actif ? 'Désactiver' : 'Activer' }} cet indicateur ?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $indicateur->est_actif ? 'warning' : 'success' }}" 
                                                title="{{ $indicateur->est_actif ? 'Désactiver' : 'Activer' }}">
                                            <i class="fas {{ $indicateur->est_actif ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                        </button>
                                    </form>
                                    
                                    {{-- 🔥 SUPPRIMER - DELETE --}}
                                    <form method="POST" action="{{ route('direction.indicateurs.destroy', $indicateur->id_indicateur) }}" style="display: inline-block;" 
                                          onsubmit="return confirm('Supprimer cet indicateur ? Cette action est irréversible.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Aucun indicateur trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            {{ $indicateurs->withQueryString()->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }
    .table tbody tr:hover {
        background: #f8fafc;
        cursor: pointer;
    }
</style>
@endpush
@endsection