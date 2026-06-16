{{-- resources/views/finance/simulations/create.blade.php --}}
@extends('layouts.powerbi')

@section('title', 'Nouvelle simulation')
@section('page-title', 'Créer une simulation financière')
@section('breadcrumb', 'Finance / Simulations / Créer')

@section('content')
<div class="container-fluid px-0">

    <div class="row">
        <div class="col-lg-8">
            <div class="powerbi-card">
                <div class="card-header-custom p-4 border-bottom">
                    <h5 class="mb-0"><i class="fas fa-flask me-2" style="color: #7e3af2;"></i>Paramètres de simulation</h5>
                </div>
                <div class="card-body p-4">
                    <form id="simulationForm">
                        @csrf
                        
                        {{-- Informations générales --}}
                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nom de la simulation <span class="text-danger">*</span></label>
                                <input type="text" id="nom_simulation" class="form-control" placeholder="Ex: Simulation croissance 2025" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea id="description" class="form-control" rows="2" placeholder="Décrivez l'objectif de cette simulation..."></textarea>
                            </div>
                        </div>
                        
                        {{-- Paramètres financiers --}}
                        <h6 class="fw-semibold mb-3"><i class="fas fa-sliders-h me-2"></i>Paramètres financiers</h6>
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Variation du Chiffre d'Affaires (%)</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="range" id="ca_variation" class="form-range" min="-50" max="50" value="0" step="1">
                                    <span id="ca_variation_value" class="fw-bold text-primary" style="min-width: 60px;">0%</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Variation des Dépenses (%)</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="range" id="depenses_variation" class="form-range" min="-30" max="30" value="0" step="1">
                                    <span id="depenses_variation_value" class="fw-bold text-primary" style="min-width: 60px;">0%</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Investissement (USD)</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="range" id="investissement" class="form-range" min="0" max="500000" value="0" step="10000">
                                    <span id="investissement_value" class="fw-bold text-primary" style="min-width: 80px;">0 USD</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Période de simulation (mois)</label>
                                <select id="periode" class="form-select">
                                    <option value="3">3 mois</option>
                                    <option value="6" selected>6 mois</option>
                                    <option value="12">12 mois</option>
                                    <option value="24">24 mois</option>
                                    <option value="36">36 mois</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Service ciblé (optionnel)</label>
                                <select id="service_id" class="form-select">
                                    <option value="">Tous les services</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id_service }}">{{ $service->nom_service }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        {{-- Boutons --}}
                        <div class="d-flex gap-3 mt-4">
                            <button type="button" class="btn btn-primary" id="btnCalculer">
                                <i class="fas fa-calculator me-2"></i>Calculer
                            </button>
                            <button type="button" class="btn btn-success" id="btnSauvegarder" style="display: none;">
                                <i class="fas fa-save me-2"></i>Sauvegarder la simulation
                            </button>
                            <button type="button" class="btn btn-secondary" id="btnReset">
                                <i class="fas fa-undo me-2"></i>Réinitialiser
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            {{-- Données actuelles --}}
            <div class="powerbi-card mb-4">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Données actuelles</h6>
                </div>
                <div class="card-body p-4">
                    <div class="info-row d-flex justify-content-between py-2">
                        <span class="text-muted">Chiffre d'affaires</span>
                        <span class="fw-bold">{{ number_format($currentData['ca_actuel'], 0, ',', ' ') }} {{ $currentData['devise'] }}</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-2">
                        <span class="text-muted">Dépenses</span>
                        <span class="fw-bold">{{ number_format($currentData['depenses_actuelles'], 0, ',', ' ') }} {{ $currentData['devise'] }}</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-2">
                        <span class="text-muted">Marge</span>
                        <span class="fw-bold">{{ number_format($currentData['marge_actuelle'], 0, ',', ' ') }} {{ $currentData['devise'] }}</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-2">
                        <span class="text-muted">Taux de marge</span>
                        <span class="fw-bold">{{ $currentData['taux_marge_actuel'] }}%</span>
                    </div>
                </div>
            </div>
            
            {{-- Résultats --}}
            <div class="powerbi-card" id="resultatsCard" style="display: none;">
                <div class="card-header-custom p-3 border-bottom">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Résultats de la simulation</h6>
                </div>
                <div class="card-body p-4" id="resultatsContent">
                    <!-- Les résultats seront injectés ici -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Éléments DOM
    const caSlider = document.getElementById('ca_variation');
    const caValue = document.getElementById('ca_variation_value');
    const depensesSlider = document.getElementById('depenses_variation');
    const depensesValue = document.getElementById('depenses_variation_value');
    const investSlider = document.getElementById('investissement');
    const investValue = document.getElementById('investissement_value');
    const btnCalculer = document.getElementById('btnCalculer');
    const btnSauvegarder = document.getElementById('btnSauvegarder');
    const btnReset = document.getElementById('btnReset');
    const resultatsCard = document.getElementById('resultatsCard');
    const resultatsContent = document.getElementById('resultatsContent');
    
    let dernierResultat = null;
    
    // Mise à jour des affichages
    caSlider.addEventListener('input', function() {
        caValue.textContent = this.value + '%';
    });
    
    depensesSlider.addEventListener('input', function() {
        depensesValue.textContent = this.value + '%';
    });
    
    investSlider.addEventListener('input', function() {
        investValue.textContent = new Intl.NumberFormat('fr-FR').format(this.value) + ' USD';
    });
    
    // Calcul de la simulation
    btnCalculer.addEventListener('click', async function() {
        const params = {
            ca_variation: parseInt(caSlider.value),
            depenses_variation: parseInt(depensesSlider.value),
            investissement: parseInt(investSlider.value),
            periode: document.getElementById('periode').value,
            service_id: document.getElementById('service_id').value
        };
        
        btnCalculer.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Calcul en cours...';
        btnCalculer.disabled = true;
        
        try {
            const response = await fetch('{{ route("finance.simulations.calculate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(params)
            });
            
            const data = await response.json();
            
            if (data.success) {
                dernierResultat = data.resultats;
                afficherResultats(data.resultats);
                btnSauvegarder.style.display = 'block';
            } else {
                Swal.fire({ icon: 'error', title: 'Erreur', text: 'Erreur lors du calcul' });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Erreur', text: 'Impossible de contacter le serveur' });
        } finally {
            btnCalculer.innerHTML = '<i class="fas fa-calculator me-2"></i>Calculer';
            btnCalculer.disabled = false;
        }
    });
    
    // Afficher les résultats
    function afficherResultats(resultats) {
        const impactClass = resultats.impact_tresorerie >= 0 ? 'text-success' : 'text-danger';
        const impactIcon = resultats.impact_tresorerie >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
        
        let alertesHtml = '';
        if (resultats.alertes && resultats.alertes.length > 0) {
            alertesHtml = '<div class="mt-3 pt-3 border-top">';
            alertesHtml += '<small class="text-muted">⚠️ Alertes</small>';
            resultats.alertes.forEach(alerte => {
                const bgColor = alerte.type === 'warning' ? '#f5a62320' : '#1B6EF320';
                alertesHtml += `<div class="small p-2 mt-2 rounded" style="background: ${bgColor}">${alerte.message}</div>`;
            });
            alertesHtml += '</div>';
        }
        
        resultatsContent.innerHTML = `
            <div class="result-item text-center p-3 mb-3 rounded" style="background: #f8fafc;">
                <div class="small text-muted">Impact sur trésorerie</div>
                <div class="display-6 fw-bold ${impactClass}">
                    <i class="fas ${impactIcon}"></i> ${resultats.impact_tresorerie >= 0 ? '+' : ''}${new Intl.NumberFormat('fr-FR').format(resultats.impact_tresorerie)} ${resultats.devise}
                </div>
            </div>
            
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="p-2 rounded text-center" style="background: #f8fafc;">
                        <div class="small text-muted">Nouveau CA</div>
                        <div class="fw-bold">${new Intl.NumberFormat('fr-FR').format(resultats.ca_apres)} ${resultats.devise}</div>
                        <div class="small ${resultats.ca_apres >= resultats.ca_actuel ? 'text-success' : 'text-danger'}">
                            ${resultats.ca_apres >= resultats.ca_actuel ? '+' : ''}${((resultats.ca_apres - resultats.ca_actuel) / resultats.ca_actuel * 100).toFixed(1)}%
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded text-center" style="background: #f8fafc;">
                        <div class="small text-muted">Nouvelle marge</div>
                        <div class="fw-bold">${new Intl.NumberFormat('fr-FR').format(resultats.marge_apres)} ${resultats.devise}</div>
                        <div class="small text-success">+${resultats.taux_marge_apres}%</div>
                    </div>
                </div>
            </div>
            
            <div class="row g-2">
                <div class="col-6">
                    <div class="p-2 rounded text-center" style="background: #f8fafc;">
                        <div class="small text-muted">ROI estimé</div>
                        <div class="fw-bold text-info">${resultats.roi}%</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded text-center" style="background: #f8fafc;">
                        <div class="small text-muted">Retour invest.</div>
                        <div class="fw-bold text-info">${resultats.retour_investissement} mois</div>
                    </div>
                </div>
            </div>
            ${alertesHtml}
        `;
        
        resultatsCard.style.display = 'block';
        resultatsCard.scrollIntoView({ behavior: 'smooth' });
    }
    
    // Sauvegarder la simulation
    btnSauvegarder.addEventListener('click', async function() {
        const nom = document.getElementById('nom_simulation').value.trim();
        if (!nom) {
            Swal.fire({ icon: 'warning', title: 'Champ requis', text: 'Veuillez donner un nom à votre simulation' });
            return;
        }
        
        const params = {
            ca_variation: parseInt(caSlider.value),
            depenses_variation: parseInt(depensesSlider.value),
            investissement: parseInt(investSlider.value),
            periode: document.getElementById('periode').value,
            service_id: document.getElementById('service_id').value
        };
        
        btnSauvegarder.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sauvegarde...';
        btnSauvegarder.disabled = true;
        
        try {
            const response = await fetch('{{ route("finance.simulations.save") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    nom_simulation: nom,
                    description: document.getElementById('description').value,
                    parametres: params,
                    resultats: dernierResultat
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Simulation sauvegardée',
                    text: 'Redirection vers la page de détails...',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                setTimeout(() => {
                    window.location.href = '{{ url("finance/simulations") }}/' + data.simulation_id;
                }, 2000);
            } else {
                Swal.fire({ icon: 'error', title: 'Erreur', text: 'Erreur lors de la sauvegarde' });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Erreur', text: 'Impossible de contacter le serveur' });
        } finally {
            btnSauvegarder.innerHTML = '<i class="fas fa-save me-2"></i>Sauvegarder la simulation';
            btnSauvegarder.disabled = false;
        }
    });
    
    // Réinitialiser
    btnReset.addEventListener('click', function() {
        caSlider.value = 0;
        caValue.textContent = '0%';
        depensesSlider.value = 0;
        depensesValue.textContent = '0%';
        investSlider.value = 0;
        investValue.textContent = '0 USD';
        document.getElementById('periode').value = '6';
        document.getElementById('service_id').value = '';
        document.getElementById('nom_simulation').value = '';
        document.getElementById('description').value = '';
        resultatsCard.style.display = 'none';
        btnSauvegarder.style.display = 'none';
    });
</script>
@endpush
@endsection