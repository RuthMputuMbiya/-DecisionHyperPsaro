{{-- resources/views/service/dashboard.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Dashboard - ' . $service->nom_service)
@section('page-title', 'Tableau de bord - ' . $service->nom_service)
@section('breadcrumb', 'Service / Dashboard')

@section('content')
<div class="container-fluid px-2 px-md-3 px-lg-4">

    {{-- EN-TÊTE AVEC SÉLECTEUR DE DEVISES --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="fas fa-building me-2" style="color: {{ $service->couleur ?? '#1B6EF3' }};"></i>
                {{ $service->nom_service }}
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i> {{ now()->translatedFormat('l d F Y') }} | 
                <i class="fas fa-chart-line ms-2 me-1"></i> Vue d'ensemble
                <span class="badge bg-success ms-2">
                    <i class="fas fa-sync-alt me-1 fa-spin"></i> Données en temps réel
                </span>
            </p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            {{-- Sélecteur de devise --}}
            <div class="currency-selector" style="display: inline-flex; gap: 3px; background: #f1f5f9; padding: 4px; border-radius: 40px;">
                <form action="{{ route('change-currency') }}" method="POST" class="d-flex gap-1 m-0">
                    @csrf
                    <button type="submit" name="devise" value="USD" class="currency-btn {{ session('devise', 'USD') == 'USD' ? 'active' : '' }}">
                        $ USD
                    </button>
                    <button type="submit" name="devise" value="CDF" class="currency-btn {{ session('devise') == 'CDF' ? 'active' : '' }}">
                        FC CDF
                    </button>
                    <button type="submit" name="devise" value="EUR" class="currency-btn {{ session('devise') == 'EUR' ? 'active' : '' }}">
                        € EUR
                    </button>
                </form>
            </div>
            <button class="btn btn-outline-primary btn-sm" onclick="window.location.href='{{ route('service.performances.index') }}'">
                <i class="fas fa-chart-simple me-1"></i>Performances
            </button>
        
            <button class="btn btn-outline-secondary btn-sm" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i>Actualiser
            </button>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background: linear-gradient(135deg, #1B6EF3, #1a56db); border-radius: 16px; padding: 18px 20px; color: white;">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-label" style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Chiffre d'affaires</div>
                        <div class="kpi-value" style="font-size: 26px; font-weight: 800;">
                            @if(($stats['ca_total'] ?? 0) > 0)
                                {{ number_format($stats['ca_total'], 0, ',', ' ') }}
                            @else
                                <span style="font-size: 14px; opacity: 0.7;">Aucune donnée</span>
                            @endif
                        </div>
                        <div class="kpi-unit" style="font-size: 10px; opacity: 0.8;">{{ $devise }}</div>
                    </div>
                    <i class="fas fa-chart-line fa-2x" style="opacity: 0.3;"></i>
                </div>
                @if(($stats['ca_total'] ?? 0) > 0)
                <div class="mt-2">
                    @if(($stats['croissance_mensuelle'] ?? 0) >= 0)
                        <span class="text-success"><i class="fas fa-arrow-up"></i> +{{ $stats['croissance_mensuelle'] }}%</span>
                    @else
                        <span class="text-danger"><i class="fas fa-arrow-down"></i> {{ $stats['croissance_mensuelle'] }}%</span>
                    @endif
                    
                </div>
                @endif
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background: linear-gradient(135deg, #0e9f6e, #0a7c5a); border-radius: 16px; padding: 18px 20px; color: white;">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-label" style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Marge brute</div>
                        <div class="kpi-value" style="font-size: 26px; font-weight: 800;">
                            @if(($stats['marge'] ?? 0) > 0)
                                {{ number_format($stats['marge'], 0, ',', ' ') }}
                            @else
                                <span style="font-size: 14px; opacity: 0.7;">Aucune donnée</span>
                            @endif
                        </div>
                        <div class="kpi-unit" style="font-size: 10px; opacity: 0.8;">{{ $devise }}</div>
                    </div>
                    <i class="fas fa-percent fa-2x" style="opacity: 0.3;"></i>
                </div>
                @if(($stats['marge'] ?? 0) > 0)
                <div class="mt-2">
                    <span style="font-size: 11px;">Taux: <strong>{{ $stats['taux_marge'] ?? 0 }}%</strong></span>
                </div>
                @endif
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background: linear-gradient(135deg, #f5a623, #e69500); border-radius: 16px; padding: 18px 20px; color: white;">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-label" style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Dépenses</div>
                        <div class="kpi-value" style="font-size: 26px; font-weight: 800;">
                            @if(($stats['depenses'] ?? 0) > 0)
                                {{ number_format($stats['depenses'], 0, ',', ' ') }}
                            @else
                                <span style="font-size: 14px; opacity: 0.7;">Aucune donnée</span>
                            @endif
                        </div>
                        <div class="kpi-unit" style="font-size: 10px; opacity: 0.8;">{{ $devise }}</div>
                    </div>
                    <i class="fas fa-receipt fa-2x" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background: linear-gradient(135deg, #7e3af2, #6b2fd9); border-radius: 16px; padding: 18px 20px; color: white;">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="kpi-label" style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Transactions</div>
                        <div class="kpi-value" style="font-size: 26px; font-weight: 800;">
                            @if(($stats['transactions'] ?? 0) > 0)
                                {{ number_format($stats['transactions']) }}
                            @else
                                <span style="font-size: 14px; opacity: 0.7;">Aucune donnée</span>
                            @endif
                        </div>
                        <div class="kpi-unit" style="font-size: 10px; opacity: 0.8;">cette année</div>
                    </div>
                    <i class="fas fa-exchange-alt fa-2x" style="opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- OBJECTIFS ET ALERTES --}}
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="powerbi-card">
                <div class="card-header-custom p-3 p-md-4 border-bottom">
                    <h5 class="mb-0 fs-6 fs-md-5">
                        <i class="fas fa-bullseye me-2" style="color: #f5a623;"></i>
                        Objectifs
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    @if(($objectifs['ca_annuel']['actuel'] ?? 0) > 0)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Chiffre d'affaires</span>
                            <span class="small fw-bold">{{ number_format($objectifs['ca_annuel']['actuel'] ?? 0, 0, ',', ' ') }} / {{ number_format($objectifs['ca_annuel']['objectif'] ?? 0, 0, ',', ' ') }} {{ $objectifs['ca_annuel']['unite'] ?? 'USD' }}</span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 10px; background: #e9ecef;">
                            <div class="progress-bar bg-primary" style="width: {{ min(($indicators['progression_objectif'] ?? 0), 100) }}%; border-radius: 10px;">
                                {{ min(($indicators['progression_objectif'] ?? 0), 100) }}%
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle fa-2x d-block mb-2"></i>
                        <p class="small mb-0">Aucune donnée disponible pour les objectifs</p>
                    </div>
                    @endif

                    @if(($indicators['budget_consomme'] ?? 0) > 0)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Budget consommé</span>
                            <span class="small fw-bold">{{ min(($indicators['budget_consomme'] ?? 0), 100) }}%</span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 10px; background: #e9ecef;">
                            <div class="progress-bar bg-success" style="width: {{ min(($indicators['budget_consomme'] ?? 0), 100) }}%; border-radius: 10px;">
                                {{ min(($indicators['budget_consomme'] ?? 0), 100) }}%
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(($indicators['performance_vs_moyenne'] ?? 0) > 0)
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Performance vs moyenne</span>
                            <span class="small fw-bold">{{ $indicators['performance_vs_moyenne'] ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 10px; background: #e9ecef;">
                            <div class="progress-bar bg-info" style="width: {{ min(($indicators['performance_vs_moyenne'] ?? 0), 100) }}%; border-radius: 10px;">
                                {{ min(($indicators['performance_vs_moyenne'] ?? 0), 100) }}%
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6">
            <div class="powerbi-card">
                <div class="card-header-custom p-3 p-md-4 border-bottom">
                    <h5 class="mb-0 fs-6 fs-md-5">
                        <i class="fas fa-exclamation-triangle me-2" style="color: #e02424;"></i>
                        Alertes
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    @forelse($alerts ?? [] as $alerte)
                    <div class="alert-item d-flex align-items-center gap-3 p-3 mb-2 rounded-3" style="background: {{ $alerte['type'] == 'warning' ? '#fef3c7' : ($alerte['type'] == 'danger' ? '#fee2e2' : '#d1fae5') }};">
                        <i class="fas {{ $alerte['icon'] ?? 'fa-circle' }} fa-lg" style="color: {{ $alerte['type'] == 'warning' ? '#f5a623' : ($alerte['type'] == 'danger' ? '#e02424' : '#0e9f6e') }};"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $alerte['title'] ?? 'Information' }}</div>
                            <div class="small text-muted">{{ $alerte['message'] ?? '' }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-check-circle fa-2x text-success d-block mb-2"></i>
                        <p class="small mb-0">Aucune alerte à signaler</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- DERNIÈRES ACTIVITÉS ET TOP PRODUITS --}}
    <div class="row g-2 g-md-3 g-lg-4">
        <div class="col-12 col-md-7">
            <div class="powerbi-card">
                <div class="card-header-custom p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fs-6 fs-md-5">
                        <i class="fas fa-clock me-2" style="color: #7e3af2;"></i>
                        Dernières activités
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-secondary btn-sm active" onclick="filterActivities('all')">Toutes</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="filterActivities('vente')">Ventes</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="filterActivities('achat')">Achats</button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="filterActivities('depense')">Dépenses</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-sm table-md" id="activitiesTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-2">Type</th>
                                    <th class="py-2">Référence</th>
                                    <th class="py-2">Montant</th>
                                    <th class="py-2 d-none d-sm-table-cell">Client/Fournisseur</th>
                                    <th class="py-2 d-none d-md-table-cell">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities ?? [] as $activite)
                                <tr data-type="{{ $activite['type'] }}">
                                    <td class="py-2">
                                        <span class="badge" style="background: {{ $activite['color'] }}20; color: {{ $activite['color'] }};">
                                            <i class="fas {{ $activite['icon'] }} me-1"></i> {{ ucfirst($activite['type']) }}
                                        </span>
                                    </td>
                                    <td class="py-2"><code class="fw-bold small">{{ $activite['reference'] }}</code></td>
                                    <td class="py-2 fw-bold">{{ number_format($activite['montant'] ?? 0, 0, ',', ' ') }} {{ $devise }}</td>
                                    <td class="py-2 d-none d-sm-table-cell">{{ $activite['client'] ?? $activite['fournisseur'] ?? $activite['categorie'] ?? '-' }}</td>
                                    <td class="py-2 d-none d-md-table-cell">{{ $activite['date']->translatedFormat('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-clock fa-2x text-muted d-block mb-2"></i>
                                        <p class="text-muted mb-0 small">Aucune activité récente</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-5">
            <div class="powerbi-card h-100">
                <div class="card-header-custom p-3 p-md-4 border-bottom">
                    <h5 class="mb-0 fs-6 fs-md-5">
                        <i class="fas fa-trophy me-2" style="color: #f5a623;"></i>
                        Top produits
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    @forelse($topProducts ?? [] as $index => $product)
                    <div class="product-item d-flex justify-content-between align-items-center p-2 p-md-3 mb-2 rounded-3" style="background: #f8fafc; transition: all 0.2s;">
                        <div class="d-flex align-items-center gap-2 gap-md-3">
                            <div class="rank" style="width: 28px; height: 28px; background: linear-gradient(135deg, #f5a623, #e69500); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <div class="fw-semibold small">{{ $product['name'] ?? 'Produit' }}</div>
                                <small class="text-muted">Ventes</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold small">{{ number_format($product['ventes'] ?? 0, 0, ',', ' ') }} {{ $devise }}</div>
                            <div class="small {{ str_contains($product['evolution'] ?? '+0%', '+') ? 'text-success' : 'text-danger' }}">
                                {{ $product['evolution'] ?? '0%' }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-box fa-2x d-block mb-2"></i>
                        <p class="small mb-0">Aucun produit disponible</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        
        // Mettre à jour les boutons actifs
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
    }
    
    function refreshData() {
        Swal.fire({
            title: 'Actualisation...',
            text: 'Les données sont en cours de mise à jour',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
</script>
@endpush

@push('styles')
<style>
    .powerbi-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.06);
        background: #fff;
        transition: transform 0.3s, box-shadow 0.3s;
        overflow: hidden;
    }
    .powerbi-card:hover {
        box-shadow: 0 8px 32px rgba(0,0,0,0.10);
        transform: translateY(-2px);
    }
    .card-header-custom {
        background: linear-gradient(135deg, #f8fafc, #f1f4f9);
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .kpi-card {
        transition: transform 0.3s, box-shadow 0.3s;
        cursor: default;
        position: relative;
        overflow: hidden;
        min-height: 110px;
    }
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }
    .currency-btn {
        transition: all 0.2s;
        cursor: pointer;
        background: none;
        border: none;
        padding: 5px 14px;
        border-radius: 40px;
        font-weight: 600;
        font-size: 11px;
        color: #64748b;
    }
    .currency-btn.active {
        background: #1B6EF3;
        color: white;
        box-shadow: 0 2px 8px rgba(27, 110, 243, 0.3);
    }
    .currency-btn:hover:not(.active) {
        background: #e2e8f0;
    }
    .product-item {
        transition: all 0.2s;
        cursor: default;
    }
    .product-item:hover {
        transform: translateX(5px);
        background: #f1f4f9 !important;
    }
    .alert-item {
        transition: all 0.2s;
    }
    .alert-item:hover {
        transform: translateX(5px);
    }
    .progress {
        border-radius: 10px;
        background: #e9ecef;
        overflow: hidden;
    }
    .progress-bar {
        border-radius: 10px;
        transition: width 1s ease;
        font-size: 10px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table th, .table td {
        white-space: nowrap;
        vertical-align: middle;
    }
    .badge {
        font-size: 0.7rem;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 500;
    }
    
    @media (max-width: 576px) {
        .kpi-value {
            font-size: 18px !important;
        }
        .kpi-label {
            font-size: 9px !important;
        }
        .currency-btn {
            font-size: 9px;
            padding: 3px 10px;
        }
        .progress-bar {
            font-size: 8px;
            min-width: 20px;
        }
    }
    @media (min-width: 768px) {
        .kpi-value {
            font-size: 26px !important;
        }
    }
    @media (min-width: 992px) {
        .kpi-value {
            font-size: 30px !important;
        }
    }
</style>
@endpush
@endsection