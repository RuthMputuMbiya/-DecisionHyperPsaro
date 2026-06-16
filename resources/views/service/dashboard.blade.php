{{-- resources/views/service/dashboard.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Dashboard - ' . $service->nom_service)
@section('page-title', 'Tableau de bord - ' . $service->nom_service)
@section('breadcrumb', 'Service / Dashboard')

@section('content')
<div class="container-fluid px-0">

    {{-- En-tête avec sélecteur de devise --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-building me-2" style="color: {{ $service->couleur ?? '#1B6EF3' }};"></i>
                {{ $service->nom_service }}
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-chart-line ms-2 me-1"></i> Vue d'ensemble
            </p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            {{-- Sélecteur de devise --}}
            <div class="currency-selector" style="display: inline-flex; gap: 5px; background: #f1f5f9; padding: 5px; border-radius: 40px;">
                <form action="{{ route('change-currency') }}" method="POST" class="d-flex gap-1 m-0">
                    @csrf
                    <button type="submit" name="devise" value="USD" class="currency-btn {{ session('devise', 'USD') == 'USD' ? 'active' : '' }}" style="padding: 5px 15px; border-radius: 40px; font-weight: 600; font-size: 12px;">
                        $ USD
                    </button>
                    <button type="submit" name="devise" value="CDF" class="currency-btn {{ session('devise') == 'CDF' ? 'active' : '' }}" style="padding: 5px 15px; border-radius: 40px; font-weight: 600; font-size: 12px;">
                        FC CDF
                    </button>
                    <button type="submit" name="devise" value="EUR" class="currency-btn {{ session('devise') == 'EUR' ? 'active' : '' }}" style="padding: 5px 15px; border-radius: 40px; font-weight: 600; font-size: 12px;">
                        € EUR
                    </button>
                </form>
            </div>
            <button class="btn btn-outline-primary" onclick="window.location.href='{{ route('service.performances.index') }}'">
                <i class="fas fa-chart-simple me-2"></i>Performances
            </button>
            <button class="btn btn-outline-success" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
        </div>
    </div>

    {{-- KPIs avec devise dynamique --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); border-radius: 20px; padding: 20px; color: white;">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-label" style="font-size: 12px; opacity: 0.8;">CHIFFRE D'AFFAIRES</div>
                        <div class="kpi-value" style="font-size: 28px; font-weight: 800;">{{ number_format($stats['ca_total'], 0, ',', ' ') }}</div>
                        <div class="kpi-unit" style="font-size: 11px;">
                            @if(session('devise', 'USD') == 'USD') USD
                            @elseif(session('devise') == 'CDF') CDF
                            @else EUR @endif
                        </div>
                    </div>
                    <i class="fas fa-chart-line fa-2x" style="opacity: 0.5;"></i>
                </div>
                <div class="mt-2">
                    @if($stats['croissance_mensuelle'] >= 0)
                        <span class="text-success"><i class="fas fa-arrow-up"></i> +{{ $stats['croissance_mensuelle'] }}%</span>
                    @else
                        <span class="text-danger"><i class="fas fa-arrow-down"></i> {{ $stats['croissance_mensuelle'] }}%</span>
                    @endif
                    <span style="font-size: 11px;"> vs mois dernier</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); border-radius: 20px; padding: 20px; color: white;">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-label" style="font-size: 12px; opacity: 0.8;">MARGE</div>
                        <div class="kpi-value" style="font-size: 28px; font-weight: 800;">{{ number_format($stats['marge'], 0, ',', ' ') }}</div>
                        <div class="kpi-unit" style="font-size: 11px;">
                            @if(session('devise', 'USD') == 'USD') USD
                            @elseif(session('devise') == 'CDF') CDF
                            @else EUR @endif
                        </div>
                    </div>
                    <i class="fas fa-percent fa-2x" style="opacity: 0.5;"></i>
                </div>
                <div class="mt-2">
                    <span>Taux: {{ $stats['taux_marge'] }}%</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card" style="background: linear-gradient(135deg, #f5a623, #e69500); border-radius: 20px; padding: 20px; color: white;">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-label" style="font-size: 12px; opacity: 0.8;">DÉPENSES</div>
                        <div class="kpi-value" style="font-size: 28px; font-weight: 800;">{{ number_format($stats['depenses'], 0, ',', ' ') }}</div>
                        <div class="kpi-unit" style="font-size: 11px;">
                            @if(session('devise', 'USD') == 'USD') USD
                            @elseif(session('devise') == 'CDF') CDF
                            @else EUR @endif
                        </div>
                    </div>
                    <i class="fas fa-receipt fa-2x" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); border-radius: 20px; padding: 20px; color: white;">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-label" style="font-size: 12px; opacity: 0.8;">TRANSACTIONS</div>
                        <div class="kpi-value" style="font-size: 28px; font-weight: 800;">{{ number_format($stats['transactions']) }}</div>
                        <div class="kpi-unit" style="font-size: 11px;">cette année</div>
                    </div>
                    <i class="fas fa-exchange-alt fa-2x" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Objectifs et Alertes --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-bullseye me-2" style="color: #f5a623;"></i>Objectifs</h5>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Chiffre d'affaires</span>
                        <span class="small fw-bold">{{ number_format($objectifs['ca_annuel']['actuel'], 0, ',', ' ') }} / {{ number_format($objectifs['ca_annuel']['objectif'], 0, ',', ' ') }} {{ $objectifs['ca_annuel']['unite'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: {{ min($indicators['progression_objectif'], 100) }}%"></div>
                    </div>
                    <div class="text-end small mt-1">{{ $indicators['progression_objectif'] }}%</div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Budget consommé</span>
                        <span class="small fw-bold">{{ $indicators['budget_consomme'] }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ min($indicators['budget_consomme'], 100) }}%"></div>
                    </div>
                </div>
                
                <div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">Performance vs moyenne</span>
                        <span class="small fw-bold">{{ $indicators['performance_vs_moyenne'] }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: {{ min($indicators['performance_vs_moyenne'], 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6">
            @if(count($alerts) > 0)
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-exclamation-triangle me-2" style="color: #e02424;"></i>Alertes</h5>
                @foreach($alerts as $alerte)
                <div class="alert-item d-flex align-items-center gap-3 p-3 mb-2 rounded" style="background: {{ $alerte['type'] == 'warning' ? '#fef3c7' : '#fee2e2' }};">
                    <i class="fas {{ $alerte['icon'] }} fa-lg" style="color: {{ $alerte['type'] == 'warning' ? '#f5a623' : '#e02424' }};"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $alerte['title'] }}</div>
                        <div class="small">{{ $alerte['message'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Dernières activités --}}
    <div class="row g-4">
        <div class="col-xl-7">
            <div class="powerbi-card">
                <div class="card-header-custom d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-clock me-2" style="color: #7e3af2;"></i>Dernières activités</h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary active" onclick="showAllActivities()">Toutes</button>
                        <button class="btn btn-outline-secondary" onclick="filterActivities('vente')">Ventes</button>
                        <button class="btn btn-outline-secondary" onclick="filterActivities('achat')">Achats</button>
                        <button class="btn btn-outline-secondary" onclick="filterActivities('depense')">Dépenses</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="activitiesTable">
                        <thead class="table-light">
                            <tr><th>Type</th><th>Référence</th><th>Montant</th><th>Client/Fournisseur</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivities as $activite)
                            <tr data-type="{{ $activite['type'] }}">
                                <td>
                                    <span class="badge" style="background: {{ $activite['color'] }}20; color: {{ $activite['color'] }};">
                                        <i class="fas {{ $activite['icon'] }} me-1"></i> {{ ucfirst($activite['type']) }}
                                    </span>
                                </td>
                                <td><code>{{ $activite['reference'] }}</code></td>
                                <td>
                                    <span class="fw-bold">{{ number_format($activite['montant'], 0, ',', ' ') }} {{ $devise }}</span>
                                </td>
                                <td>{{ $activite['client'] ?? $activite['fournisseur'] ?? $activite['categorie'] ?? '-' }}</td>
                                <td>{{ $activite['date']->translatedFormat('d/m/Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-xl-5">
            <div class="powerbi-card">
                <h5 class="mb-3"><i class="fas fa-trophy me-2" style="color: #f5a623;"></i>Top 5 produits</h5>
                <div class="top-products">
                    @foreach($topProducts as $index => $product)
                    <div class="product-item d-flex justify-content-between align-items-center p-3 mb-2 rounded" style="background: #f8fafc;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rank" style="width: 28px; height: 28px; background: linear-gradient(135deg, #f5a623, #e69500); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $product['name'] }}</div>
                                <small class="text-muted">Ventes</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">{{ number_format($product['ventes'], 0, ',', ' ') }} {{ $devise }}</div>
                            <div class="small {{ str_contains($product['evolution'], '+') ? 'text-success' : 'text-danger' }}">
                                {{ $product['evolution'] }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function filterActivities(type) {
        const rows = document.querySelectorAll('#activitiesTable tbody tr');
        rows.forEach(row => {
            if (type === 'all' || row.dataset.type === type) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    function showAllActivities() {
        filterActivities('all');
    }
</script>
@endpush

<style>
    .kpi-card {
        transition: transform 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
    }
    .product-item, .alert-item {
        transition: transform 0.2s;
    }
    .product-item:hover, .alert-item:hover {
        transform: translateX(5px);
    }
    .currency-btn {
        transition: all 0.2s;
        cursor: pointer;
        background: none;
        border: none;
    }
    .currency-btn.active {
        background: #1B6EF3;
        color: white;
    }
    .currency-btn:hover:not(.active) {
        background: #e2e8f0;
    }
</style>
@endsection