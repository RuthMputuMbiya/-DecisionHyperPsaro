{{-- resources/views/direction/decisions/index.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Décisions stratégiques')
@section('page-title', 'Tableau de bord décisionnel')
@section('breadcrumb', 'Direction / Décisions')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">

    {{-- ============================================ --}}
    {{-- EN-TÊTE --}}
    {{-- ============================================ --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold" style="color: #0f172a;">
                <i class="fas fa-robot me-2" style="color: #1B6EF3;"></i>
                Système Décisionnel Intelligent
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
            <button class="btn btn-outline-success btn-sm" onclick="exportDecisions('pdf')">
                <i class="fas fa-file-pdf me-1"></i>PDF
            </button>
            <button class="btn btn-outline-info btn-sm" onclick="exportDecisions('excel')">
                <i class="fas fa-file-excel me-1"></i>Excel
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i>Actualiser
            </button>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- STATISTIQUES GLOBALES --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); color: white; box-shadow: 0 4px 20px rgba(27,110,243,0.3);">
                <div class="stat-number display-6 fw-bold">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-label small"><i class="fas fa-folder-open me-1"></i>Total décisions</div>
                <div class="stat-trend small">{{ $stats['en_attente'] ?? 0 }} en attente</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); color: white; box-shadow: 0 4px 20px rgba(14,159,110,0.3);">
                <div class="stat-number display-6 fw-bold">{{ $stats['validees'] ?? 0 }}</div>
                <div class="stat-label small"><i class="fas fa-check-circle me-1"></i>Validées</div>
                <div class="stat-trend small">{{ $stats['taux_validation'] ?? 0 }}% des décisions</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #e02424, #b91c1c); color: white; box-shadow: 0 4px 20px rgba(224,36,36,0.3);">
                <div class="stat-number display-6 fw-bold">{{ $stats['rejetees'] ?? 0 }}</div>
                <div class="stat-label small"><i class="fas fa-times-circle me-1"></i>Rejetées</div>
                <div class="stat-trend small">Nécessitent révision</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center p-2 p-md-3 rounded-3" style="background: linear-gradient(135deg, #f5a623, #e69500); color: white; box-shadow: 0 4px 20px rgba(245,166,35,0.3);">
                <div class="stat-number display-6 fw-bold">{{ number_format($stats['impact_total'] ?? 0, 0, ',', ' ') }} {{ session('devise', 'USD') }}</div>
                <div class="stat-label small"><i class="fas fa-coins me-1"></i>Impact total</div>
                <div class="stat-trend small">Marge: {{ number_format($stats['marge_brute'] ?? 0, 0, ',', ' ') }} {{ session('devise', 'USD') }}</div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- FILTRES --}}
    {{-- ============================================ --}}
    <div class="card mb-4" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
        <div class="card-body p-3 p-md-4">
            <div class="row g-2 g-md-3 align-items-end">
                <div class="col-12 col-md-2">
                    <label class="form-label small text-muted fw-bold"><i class="fas fa-calendar me-1"></i>Année</label>
                    <select id="yearFilter" class="form-select form-select-sm">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small text-muted fw-bold"><i class="fas fa-filter me-1"></i>Statut</label>
                    <select id="statusFilter" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="En attente">⏳ En attente</option>
                        <option value="Validée">✅ Validée</option>
                        <option value="Rejetée">❌ Rejetée</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted fw-bold"><i class="fas fa-search me-1"></i>Recherche</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher...">
                        <button class="btn btn-primary" onclick="applyFilters()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted fw-bold"><i class="fas fa-chart-pie me-1"></i>Type de vue</label>
                    <select id="viewType" class="form-select form-select-sm">
                        <option value="all">📊 Tous les graphiques</option>
                        <option value="ventes">📈 Ventes</option>
                        <option value="achats">📉 Achats</option>
                        <option value="investissements">💰 Investissements</option>
                        <option value="indicateurs">🎯 Indicateurs</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button class="btn btn-primary w-100" onclick="applyFilters()">
                        <i class="fas fa-sliders-h me-1"></i>Appliquer
                    </button>
                    <button class="btn btn-success" onclick="generateDecisions()" title="Générer des décisions">
                        <i class="fas fa-robot"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- GRAPHIQUES EN CERCLE (DOUGHNUT) --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        
        {{-- GRAPHIQUE 1: VENTES --}}
        <div class="col-12 col-md-6">
            <div class="card h-100" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #e8f0fe, #d4e2fc); border-radius: 16px 16px 0 0;">
                    <div>
                        <h5 class="mb-0 fs-6 fw-bold" style="color: #1B6EF3;">
                            <i class="fas fa-chart-pie me-2"></i>
                            Évolution des Ventes ({{ $year }})
                        </h5>
                        <small class="text-muted">🔵 Cliquez sur un secteur pour les détails</small>
                    </div>
                    <span class="badge bg-primary" style="font-size: 0.9rem; padding: 8px 16px;">
                        <i class="fas fa-dollar-sign me-1"></i>
                        {{ number_format($stats['total_ventes'] ?? 0, 0, ',', ' ') }} {{ session('devise', 'USD') }}
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 280px;">
                        <canvas id="ventesChart"></canvas>
                    </div>
                    <div id="ventesDetails" class="mt-3 p-3 rounded-3" style="background: #f0f4ff; display: none; border-left: 4px solid #1B6EF3; border-radius: 10px;">
                        <h6 class="mb-2" style="color: #1B6EF3;"><i class="fas fa-info-circle me-2"></i>Détails du mois</h6>
                        <div id="ventesDetailContent"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRAPHIQUE 2: ACHATS --}}
        <div class="col-12 col-md-6">
            <div class="card h-100" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #fff8e8, #ffeed4); border-radius: 16px 16px 0 0;">
                    <div>
                        <h5 class="mb-0 fs-6 fw-bold" style="color: #f5a623;">
                            <i class="fas fa-chart-pie me-2"></i>
                            Évolution des Achats ({{ $year }})
                        </h5>
                        <small class="text-muted">🟠 Cliquez sur un secteur pour les détails</small>
                    </div>
                    <span class="badge bg-warning" style="font-size: 0.9rem; padding: 8px 16px; color: #000;">
                        <i class="fas fa-shopping-cart me-1"></i>
                        {{ number_format($stats['total_achats'] ?? 0, 0, ',', ' ') }} {{ session('devise', 'USD') }}
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 280px;">
                        <canvas id="achatsChart"></canvas>
                    </div>
                    <div id="achatsDetails" class="mt-3 p-3 rounded-3" style="background: #fff8f0; display: none; border-left: 4px solid #f5a623; border-radius: 10px;">
                        <h6 class="mb-2" style="color: #f5a623;"><i class="fas fa-info-circle me-2"></i>Détails du mois</h6>
                        <div id="achatsDetailContent"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRAPHIQUE 3: INVESTISSEMENTS --}}
        <div class="col-12 col-md-6">
            <div class="card h-100" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #e8f5f0, #d4ede4); border-radius: 16px 16px 0 0;">
                    <div>
                        <h5 class="mb-0 fs-6 fw-bold" style="color: #0e9f6e;">
                            <i class="fas fa-chart-pie me-2"></i>
                            Répartition des Investissements
                        </h5>
                        <small class="text-muted">🟢 Cliquez pour les détails</small>
                    </div>
                    <span class="badge bg-info" style="font-size: 0.9rem; padding: 8px 16px; color: #fff;">
                        <i class="fas fa-coins me-1"></i>
                        {{ number_format($stats['total_investissements'] ?? 0, 0, ',', ' ') }} {{ session('devise', 'USD') }}
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 280px;">
                        <canvas id="investissementsChart"></canvas>
                    </div>
                    <div id="investissementsDetails" class="mt-3 p-3 rounded-3" style="background: #f0f8f5; display: none; border-left: 4px solid #0e9f6e; border-radius: 10px;">
                        <h6 class="mb-2" style="color: #0e9f6e;"><i class="fas fa-info-circle me-2"></i>Détails du type</h6>
                        <div id="investissementsDetailContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- INDICATEURS (BARRES) --}}
    {{-- ============================================ --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-12">
            <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #e8f0fe, #d4e2fc); border-radius: 16px 16px 0 0;">
                    <div>
                        <h5 class="mb-0 fs-6 fw-bold" style="color: #1B6EF3;">
                            <i class="fas fa-chart-bar me-2"></i>
                            Indicateurs de Performance
                        </h5>
                        <small class="text-muted">🔵 Cliquez sur une barre pour les détails</small>
                    </div>
                    <span class="badge bg-primary" style="font-size: 0.9rem; padding: 8px 16px;">
                        <i class="fas fa-chart-simple me-1"></i>
                        {{ count($stats['indicateurs_data'] ?? []) }} indicateurs
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="chart-container" style="position: relative; height: 320px;">
                        <canvas id="indicateursChart"></canvas>
                    </div>
                    <div id="indicateursDetails" class="mt-3 p-3 rounded-3" style="background: #f0f4ff; display: none; border-left: 4px solid #1B6EF3; border-radius: 10px;">
                        <h6 class="mb-2" style="color: #1B6EF3;"><i class="fas fa-info-circle me-2"></i>Détails de l'indicateur</h6>
                        <div id="indicateursDetailContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- DÉCISIONS AUTOMATIQUES --}}
    {{-- ============================================ --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
                <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f0ecff, #e0d8f5); border-radius: 16px 16px 0 0;">
                    <div>
                        <h5 class="mb-0 fs-6 fw-bold" style="color: #7e3af2;">
                            <i class="fas fa-robot me-2"></i>
                            Décisions Intelligentes Générées Automatiquement
                        </h5>
                        <small class="text-muted">🤖 Analyse IA en temps réel</small>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary" style="font-size: 0.9rem; padding: 8px 16px;">
                            <i class="fas fa-sync-alt me-1 fa-spin"></i>
                            {{ $decisions_auto->count() }} décisions
                        </span>
                        <button class="btn btn-success btn-sm" onclick="generateDecisions()">
                            <i class="fas fa-plus me-1"></i>Générer
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-sm table-md">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">#</th>
                                    <th class="py-3">Décision</th>
                                    <th class="py-3 d-none d-sm-table-cell">Type</th>
                                    <th class="py-3">Priorité</th>
                                    <th class="py-3">Impact</th>
                                    <th class="py-3 d-none d-md-table-cell">Statut</th>
                                    <th class="py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($decisions_auto as $decision)
                                <tr>
                                    <td class="py-3">
                                        <code class="fw-bold">#{{ str_pad($decision->id_decision, 5, '0', STR_PAD_LEFT) }}</code>
                                    </td>
                                    <td class="py-3">
                                        <div>
                                            <strong class="small">{{ $decision->titre }}</strong>
                                            <br>
                                            <small class="text-muted d-none d-sm-inline">{{ Str::limit($decision->description, 60) }}</small>
                                        </div>
                                    </td>
                                    <td class="py-3 d-none d-sm-table-cell">
                                        <span class="badge bg-info">
                                            <i class="fas fa-robot me-1"></i>Auto
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        @php
                                            $prioriteColors = [
                                                'Critique' => 'danger',
                                                'Élevée' => 'warning',
                                                'Moyenne' => 'info',
                                                'Faible' => 'success'
                                            ];
                                            $prioriteIcon = [
                                                'Critique' => 'fa-exclamation-circle',
                                                'Élevée' => 'fa-arrow-up',
                                                'Moyenne' => 'fa-minus',
                                                'Faible' => 'fa-arrow-down'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $prioriteColors[$decision->priorite ?? 'Moyenne'] ?? 'secondary' }}" style="padding: 6px 14px;">
                                            <i class="fas {{ $prioriteIcon[$decision->priorite ?? 'Moyenne'] ?? 'fa-minus' }} me-1"></i>
                                            {{ $decision->priorite ?? 'Moyenne' }}
                                        </span>
                                    </td>
                                    <td class="py-3 fw-bold {{ ($decision->impact_tresorerie ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ ($decision->impact_tresorerie ?? 0) >= 0 ? '+' : '' }}
                                        {{ number_format($decision->impact_tresorerie ?? 0, 0, ',', ' ') }} {{ session('devise', 'USD') }}
                                    </td>
                                    <td class="py-3 d-none d-md-table-cell">
                                        @php
                                            $statusBadge = [
                                                'En attente' => 'warning',
                                                'Validée' => 'success',
                                                'Rejetée' => 'danger',
                                                'En cours' => 'info'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusBadge[$decision->statut ?? 'En attente'] ?? 'secondary' }}" style="padding: 6px 14px;">
                                            @if($decision->statut == 'En attente')
                                                <i class="fas fa-clock me-1"></i>
                                            @elseif($decision->statut == 'Validée')
                                                <i class="fas fa-check-circle me-1"></i>
                                            @elseif($decision->statut == 'Rejetée')
                                                <i class="fas fa-times-circle me-1"></i>
                                            @endif
                                            {{ $decision->statut ?? 'En attente' }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('direction.decisions.show', $decision->id_decision) }}" class="btn btn-outline-info" title="Détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(($decision->statut ?? 'En attente') == 'En attente')
                                                <form action="{{ route('direction.decisions.validate', $decision->id_decision) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success btn-sm" title="Valider">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('direction.decisions.reject', $decision->id_decision) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <input type="hidden" name="motif" value="Rejeté automatiquement">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Rejeter">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-robot fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-2">Aucune décision automatique générée</p>
                                        <button class="btn btn-primary mt-2" onclick="generateDecisions()">
                                            <i class="fas fa-plus me-1"></i>Générer des décisions
                                        </button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white p-3 border-top">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Les décisions sont générées automatiquement à partir des indicateurs
                        </small>
                        <div class="d-flex gap-2">
                            <a href="{{ route('direction.decisions.export') }}?format=pdf" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </a>
                            <a href="{{ route('direction.decisions.export') }}?format=excel" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-file-excel me-1"></i>Excel
                            </a>
                            <button class="btn btn-sm btn-outline-primary" onclick="exportAll()">
                                <i class="fas fa-download me-1"></i>Tout exporter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- LISTE COMPLÈTE DES DÉCISIONS --}}
    {{-- ============================================ --}}
    <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);">
        <div class="card-header-custom p-3 p-md-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f8fafc, #f1f4f9); border-radius: 16px 16px 0 0;">
            <h5 class="mb-0 fs-6 fw-bold" style="color: #1B6EF3;">
                <i class="fas fa-list me-2"></i>
                Toutes les décisions
            </h5>
            <span class="badge bg-secondary" style="font-size: 0.8rem; padding: 6px 14px;">
                <i class="fas fa-database me-1"></i>
                {{ $decisions->total() }} décisions
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-sm table-md">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Réf.</th>
                            <th class="py-3">Titre</th>
                            <th class="py-3 d-none d-sm-table-cell">Résultat simulé</th>
                            <th class="py-3">Impact</th>
                            <th class="py-3 d-none d-lg-table-cell">Statut</th>
                            <th class="py-3">Date</th>
                            <th class="py-3 d-none d-md-table-cell">Créé par</th>
                            <th class="py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($decisions as $decision)
                        <tr>
                            <td class="py-3"><code class="fw-bold">#{{ str_pad($decision->id_decision, 5, '0', STR_PAD_LEFT) }}</code></td>
                            <td class="py-3">
                                <div>
                                    <strong class="small">{{ $decision->titre }}</strong>
                                    <br>
                                    <small class="text-muted d-none d-md-inline">{{ Str::limit($decision->description, 50) }}</small>
                                </div>
                            </td>
                            <td class="py-3 d-none d-sm-table-cell fw-bold text-primary">
                                <i class="fas fa-chart-line me-1"></i>
                                {{ number_format($decision->resultat_simule ?? 0, 0, ',', ' ') }} {{ session('devise', 'USD') }}
                            </td>
                            <td class="py-3 fw-bold {{ ($decision->impact_tresorerie ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ ($decision->impact_tresorerie ?? 0) >= 0 ? '+' : '' }}
                                {{ number_format($decision->impact_tresorerie ?? 0, 0, ',', ' ') }} {{ session('devise', 'USD') }}
                            </td>
                            <td class="py-3 d-none d-lg-table-cell">
                                @php
                                    $statusBadge = [
                                        'En attente' => 'warning',
                                        'Validée' => 'success',
                                        'Rejetée' => 'danger',
                                        'En cours' => 'info'
                                    ];
                                    $statusIcon = [
                                        'En attente' => 'fa-clock',
                                        'Validée' => 'fa-check-circle',
                                        'Rejetée' => 'fa-times-circle',
                                        'En cours' => 'fa-spinner'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusBadge[$decision->statut ?? 'En attente'] ?? 'secondary' }}" style="padding: 6px 14px;">
                                    <i class="fas {{ $statusIcon[$decision->statut ?? 'En attente'] ?? 'fa-circle' }} me-1"></i>
                                    {{ $decision->statut ?? 'En attente' }}
                                </span>
                            </td>
                            <td class="py-3">
                                <small><i class="far fa-calendar-alt me-1"></i>{{ $decision->created_at->translatedFormat('d/m/Y') }}</small>
                                <br>
                                <small class="text-muted">{{ $decision->created_at->translatedFormat('H:i') }}</small>
                            </td>
                            <td class="py-3 d-none d-md-table-cell">
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-user me-1"></i>
                                    {{ $decision->createur->nom ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('direction.decisions.show', $decision->id_decision) }}" class="btn btn-outline-info" title="Détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(($decision->statut ?? 'En attente') == 'En attente')
                                        <form action="{{ route('direction.decisions.validate', $decision->id_decision) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm" title="Valider">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('direction.decisions.reject', $decision->id_decision) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="motif" value="Rejeté depuis la liste">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Rejeter">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Aucune décision trouvée</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white p-3 border-top">
            <div class="d-flex justify-content-center">
                {{ $decisions->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // PALETTES DE COULEURS
    // ============================================
    const colors = {
        blue: ['#1B6EF3', '#4A8AF4', '#78A6F7', '#A6C2FA', '#D4DEFD', '#E8EFFE', '#1a56db', '#2d7cf5', '#5b9cf7'],
        orange: ['#f5a623', '#F7B84D', '#F9CA77', '#FBDCA1', '#FDEDCB', '#FFF4E5', '#e69500', '#f0b820', '#f8cc5a'],
        green: ['#0e9f6e', '#3EB28B', '#6EC5A8', '#9ED8C5', '#CEEBE2', '#E7F5F0', '#0a7c5a', '#2b9d78', '#56b896'],
        invest: ['#1B6EF3', '#0e9f6e', '#f5a623', '#e02424', '#7e3af2', '#3abff8', '#10b981', '#f97316', '#8b5cf6', '#ec4899']
    };

    // ============================================
    // DONNÉES DES GRAPHIQUES
    // ============================================
    const allMonths = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
    const allVentes = @json($stats['ventes_mensuelles'] ?? array_fill(0, 12, 0));
    const allAchats = @json($stats['achats_mensuels'] ?? array_fill(0, 12, 0));
    const investData = @json($stats['investissements_par_type'] ?? []);
    const ventesDetails = @json($stats['ventes_details'] ?? []);
    const achatsDetails = @json($stats['achats_details'] ?? []);
    const indicateursData = @json($stats['indicateurs_data'] ?? []);

    // ============================================
    // 1. GRAPHIQUE VENTES (DOUGHNUT)
    // ============================================
    const ctxVentes = document.getElementById('ventesChart')?.getContext('2d');
    if (ctxVentes) {
        new Chart(ctxVentes, {
            type: 'doughnut',
            data: {
                labels: allMonths,
                datasets: [{
                    data: allVentes,
                    backgroundColor: colors.blue,
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 9, weight: '600' }, boxWidth: 12, padding: 10, usePointStyle: true, pointStyle: 'circle' } },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        titleFont: { size: 14, weight: '700' },
                        bodyFont: { size: 13 },
                        padding: 16,
                        cornerRadius: 12,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' USD (' + percentage + '%)';
                            }
                        }
                    }
                },
                onClick: function(evt, elements) {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const label = this.data.labels[index];
                        const value = this.data.datasets[0].data[index];
                        const details = ventesDetails[label] || {};
                        showVentesDetails(label, value, details);
                    }
                },
                animation: { animateRotate: true, duration: 1200 }
            }
        });
    }

    // ============================================
    // 2. GRAPHIQUE ACHATS (DOUGHNUT)
    // ============================================
    const ctxAchats = document.getElementById('achatsChart')?.getContext('2d');
    if (ctxAchats) {
        new Chart(ctxAchats, {
            type: 'doughnut',
            data: {
                labels: allMonths,
                datasets: [{
                    data: allAchats,
                    backgroundColor: colors.orange,
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 9, weight: '600' }, boxWidth: 12, padding: 10, usePointStyle: true, pointStyle: 'circle' } },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        titleFont: { size: 14, weight: '700' },
                        bodyFont: { size: 13 },
                        padding: 16,
                        cornerRadius: 12,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' USD (' + percentage + '%)';
                            }
                        }
                    }
                },
                onClick: function(evt, elements) {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const label = this.data.labels[index];
                        const value = this.data.datasets[0].data[index];
                        const details = achatsDetails[label] || {};
                        showAchatsDetails(label, value, details);
                    }
                },
                animation: { animateRotate: true, duration: 1200 }
            }
        });
    }

    // ============================================
    // 3. INVESTISSEMENTS (DOUGHNUT)
    // ============================================
    const ctxInvest = document.getElementById('investissementsChart')?.getContext('2d');
    if (ctxInvest) {
        const investLabels = Object.keys(investData).length > 0 ? Object.keys(investData) : ['Aucune donnée'];
        const investValues = Object.values(investData).length > 0 ? Object.values(investData) : [1];
        
        new Chart(ctxInvest, {
            type: 'doughnut',
            data: {
                labels: investLabels,
                datasets: [{
                    data: investValues,
                    backgroundColor: colors.invest.slice(0, investLabels.length),
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 9, weight: '600' }, boxWidth: 12, padding: 10, usePointStyle: true, pointStyle: 'circle' } },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        titleFont: { size: 14, weight: '700' },
                        bodyFont: { size: 13 },
                        padding: 16,
                        cornerRadius: 12,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' USD (' + percentage + '%)';
                            }
                        }
                    }
                },
                onClick: function(evt, elements) {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const label = this.data.labels[index];
                        const value = this.data.datasets[0].data[index];
                        const details = investData[label] || {};
                        showInvestissementsDetails(label, value, details);
                    }
                },
                animation: { animateRotate: true, duration: 1200 }
            }
        });
    }

    // ============================================
    // 4. INDICATEURS (BARRES)
    // ============================================
    const ctxInd = document.getElementById('indicateursChart')?.getContext('2d');
    if (ctxInd) {
        const indicateursLabels = Object.keys(indicateursData);
        const indicateursValues = indicateursLabels.map(key => indicateursData[key]?.valeur || 0);
        const indicateursColors = indicateursLabels.map(key => indicateursData[key]?.couleur || '#1B6EF3');
        
        new Chart(ctxInd, {
            type: 'bar',
            data: {
                labels: indicateursLabels,
                datasets: [{
                    label: 'Valeur des indicateurs',
                    data: indicateursValues,
                    backgroundColor: indicateursColors.map(c => c + 'CC'),
                    borderColor: indicateursColors,
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        titleFont: { size: 14, weight: '700' },
                        bodyFont: { size: 13 },
                        padding: 16,
                        cornerRadius: 12,
                        callbacks: {
                            label: function(context) {
                                const key = context.label;
                                const data = indicateursData[key];
                                return data?.nom + ': ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' ' + data?.unite;
                            }
                        }
                    }
                },
                onClick: function(evt, elements) {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const key = this.data.labels[index];
                        const data = indicateursData[key];
                        showIndicateursDetails(key, data);
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
                                return value;
                            },
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 12, weight: '600' } }
                    }
                },
                animation: { duration: 800 }
            }
        });
    }

    // ============================================
    // FONCTIONS D'AFFICHAGE DES DÉTAILS
    // ============================================

    function showVentesDetails(label, value, details) {
        const detailDiv = document.getElementById('ventesDetails');
        const contentDiv = document.getElementById('ventesDetailContent');
        if (!detailDiv || !contentDiv) return;
        
        const total = allVentes.reduce((a, b) => a + b, 0);
        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
        const transactions = details?.total_transactions ?? 0;
        
        detailDiv.style.display = 'block';
        contentDiv.innerHTML = `
            <div class="row g-2">
                <div class="col-4">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Mois</small>
                        <div class="fw-bold fs-5 text-primary">${label}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Montant</small>
                        <div class="fw-bold fs-5 text-primary">${new Intl.NumberFormat('fr-FR').format(value)} USD</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Transactions</small>
                        <div class="fw-bold fs-5 text-primary">${transactions}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Contribution</small>
                        <div class="fw-bold text-primary">${percentage}%</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Cumul annuel</small>
                        <div class="fw-bold text-primary">${new Intl.NumberFormat('fr-FR').format(total)} USD</div>
                    </div>
                </div>
                ${transactions > 0 ? `
                <div class="col-12">
                    <div class="p-2 rounded bg-light text-center">
                        <small class="text-muted">📊 ${transactions} transaction(s) enregistrée(s) pour ce mois</small>
                    </div>
                </div>
                ` : `
                <div class="col-12">
                    <div class="p-2 rounded bg-warning bg-opacity-10 text-center">
                        <small class="text-warning">⚠️ Aucune transaction enregistrée pour ce mois</small>
                    </div>
                </div>
                `}
            </div>
        `;
    }

    function showAchatsDetails(label, value, details) {
        const detailDiv = document.getElementById('achatsDetails');
        const contentDiv = document.getElementById('achatsDetailContent');
        if (!detailDiv || !contentDiv) return;
        
        const total = allAchats.reduce((a, b) => a + b, 0);
        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
        const transactions = details?.total_transactions ?? 0;
        
        detailDiv.style.display = 'block';
        contentDiv.innerHTML = `
            <div class="row g-2">
                <div class="col-4">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Mois</small>
                        <div class="fw-bold fs-5 text-warning">${label}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Montant</small>
                        <div class="fw-bold fs-5 text-warning">${new Intl.NumberFormat('fr-FR').format(value)} USD</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Transactions</small>
                        <div class="fw-bold fs-5 text-warning">${transactions}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Contribution</small>
                        <div class="fw-bold text-warning">${percentage}%</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Cumul annuel</small>
                        <div class="fw-bold text-warning">${new Intl.NumberFormat('fr-FR').format(total)} USD</div>
                    </div>
                </div>
                ${transactions > 0 ? `
                <div class="col-12">
                    <div class="p-2 rounded bg-light text-center">
                        <small class="text-muted">📊 ${transactions} transaction(s) enregistrée(s) pour ce mois</small>
                    </div>
                </div>
                ` : `
                <div class="col-12">
                    <div class="p-2 rounded bg-warning bg-opacity-10 text-center">
                        <small class="text-warning">⚠️ Aucune transaction enregistrée pour ce mois</small>
                    </div>
                </div>
                `}
            </div>
        `;
    }

    function showInvestissementsDetails(label, value, details) {
        const detailDiv = document.getElementById('investissementsDetails');
        const contentDiv = document.getElementById('investissementsDetailContent');
        if (!detailDiv || !contentDiv) return;
        
        const total = Object.values(investData).reduce((a, b) => a + b, 0);
        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
        
        detailDiv.style.display = 'block';
        contentDiv.innerHTML = `
            <div class="row g-2">
                <div class="col-3">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Type</small>
                        <div class="fw-bold fs-5 text-info">${label}</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Montant</small>
                        <div class="fw-bold fs-5 text-info">${new Intl.NumberFormat('fr-FR').format(value)} USD</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Projets</small>
                        <div class="fw-bold fs-5 text-info">${details?.total_projets || 0}</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Répartition</small>
                        <div class="fw-bold fs-5 text-info">${percentage}%</div>
                    </div>
                </div>
            </div>
        `;
    }

    function showIndicateursDetails(key, data) {
        const detailDiv = document.getElementById('indicateursDetails');
        const contentDiv = document.getElementById('indicateursDetailContent');
        if (!detailDiv || !contentDiv || !data) return;
        
        detailDiv.style.display = 'block';
        const statusColor = data?.valeur >= data?.seuil_min && data?.valeur <= data?.seuil_max ? '#0e9f6e' : '#e02424';
        const statusText = data?.valeur >= data?.seuil_min && data?.valeur <= data?.seuil_max ? '✅ Dans les normes' : '⚠️ Hors normes';
        
        contentDiv.innerHTML = `
            <div class="row g-2">
                <div class="col-3">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Indicateur</small>
                        <div class="fw-bold fs-5" style="color: ${data.couleur}">${data.nom}</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Valeur</small>
                        <div class="fw-bold fs-5" style="color: ${data.couleur}">${new Intl.NumberFormat('fr-FR').format(data.valeur)} ${data.unite}</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Seuil</small>
                        <div class="fw-bold">${data.seuil_min ? new Intl.NumberFormat('fr-FR').format(data.seuil_min) : '-'} - ${data.seuil_max ? new Intl.NumberFormat('fr-FR').format(data.seuil_max) : '-'} ${data.unite}</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-2 rounded bg-white shadow-sm text-center">
                        <small class="text-muted d-block">Statut</small>
                        <div class="fw-bold fs-5" style="color: ${statusColor}">${statusText}</div>
                    </div>
                </div>
                ${data.formule ? `
                <div class="col-12">
                    <div class="p-2 rounded bg-light text-center">
                        <small class="text-muted"><i class="fas fa-calculator me-1"></i>Formule : ${data.formule}</small>
                    </div>
                </div>
                ` : ''}
                ${data.description ? `
                <div class="col-12">
                    <div class="p-2 rounded bg-light text-center">
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>${data.description}</small>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
    }
});

// ============================================
// FONCTIONS D'ACTION
// ============================================

function generateDecisions() {
    Swal.fire({
        title: '🤖 Génération de décisions',
        text: 'Le système va analyser les données et générer des décisions intelligentes',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#0e9f6e',
        confirmButtonText: '🚀 Générer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: '⏳ Génération en cours...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            window.location.href = "{{ route('direction.decisions.generate') }}";
        }
    });
}

function exportDecisions(format) {
    Swal.fire({
        title: `📤 Export ${format.toUpperCase()}`,
        text: 'Préparation de l\'export...',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Exporter',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ route('direction.decisions.export') }}?format=" + format;
        }
    });
}

function exportAll() {
    window.location.href = "{{ route('direction.decisions.export') }}?format=pdf&all=true";
}

function refreshData() {
    Swal.fire({ title: '🔄 Actualisation', text: 'Les données sont en cours de mise à jour...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
    setTimeout(() => { window.location.reload(); }, 1500);
}

function applyFilters() {
    const year = document.getElementById('yearFilter')?.value || '{{ $year }}';
    const status = document.getElementById('statusFilter')?.value || '';
    const viewType = document.getElementById('viewType')?.value || 'all';
    const search = document.getElementById('searchInput')?.value || '';
    
    let url = "{{ route('direction.decisions.index') }}?";
    const params = [];
    params.push('year=' + year);
    if (status) params.push('status=' + status);
    if (viewType !== 'all') params.push('view=' + viewType);
    if (search) params.push('search=' + encodeURIComponent(search));
    url += params.join('&');
    window.location.href = url;
}

document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') applyFilters();
});

document.getElementById('yearFilter')?.addEventListener('change', function() {
    applyFilters();
});
</script>
@endpush

@push('styles')
<style>
    .card-header-custom {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: default;
        border-radius: 16px;
        position: relative;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        letter-spacing: -0.5px;
        line-height: 1.2;
        position: relative;
        z-index: 1;
    }
    .stat-label {
        font-size: 0.75rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        z-index: 1;
    }
    .stat-trend {
        font-size: 0.65rem;
        opacity: 0.7;
        margin-top: 2px;
        position: relative;
        z-index: 1;
    }
    
    .chart-container {
        min-height: 200px;
        position: relative;
    }
    
    .text-purple { color: #7e3af2; }
    
    @media (max-width: 768px) {
        .stat-number { font-size: 1.8rem !important; }
        .chart-container { min-height: 220px; }
    }
    
    @media (min-width: 992px) {
        .stat-number { font-size: 3rem !important; }
        .chart-container { min-height: 300px; }
    }
</style>
@endpush
@endsection