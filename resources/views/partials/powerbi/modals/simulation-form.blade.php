{{-- resources/views/partials/modals/simulation-modal.blade.php --}}
@props([
    'id' => 'simulationModal',
    'title' => 'Simulation financière',
    'simulateUrl' => null,
    'parameters' => []
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
            
            {{-- Header --}}
            <div class="modal-header" style="background: linear-gradient(135deg, #1a56db, #7e3af2); border-bottom: none; padding: 24px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="modal-icon" style="width: 44px; height: 44px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-flask" style="color: white; font-size: 22px;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" style="color: white; font-weight: 700; margin: 0;">{{ $title }}</h5>
                        <p style="color: rgba(255,255,255,0.8); font-size: 12px; margin: 5px 0 0 0;">Modifiez les paramètres pour simuler l'impact financier</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- Body --}}
            <div class="modal-body" style="padding: 24px; max-height: 60vh; overflow-y: auto;">
                <form id="simulationForm">
                    @csrf
                    
                    {{-- Paramètres de simulation --}}
                    <div class="simulation-params" style="margin-bottom: 25px;">
                        <h6 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0;">
                            <i class="fas fa-sliders-h" style="margin-right: 8px; color: #1a56db;"></i> Paramètres
                        </h6>
                        
                        {{-- Variation du CA --}}
                        <div class="param-group" style="margin-bottom: 25px;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 10px;">
                                Variation du Chiffre d'Affaires (%)
                            </label>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <input type="range" name="ca_variation" class="param-slider" min="-50" max="50" value="0" step="1" 
                                       style="flex: 1; height: 6px; border-radius: 3px; background: #e2e8f0;" 
                                       oninput="this.nextElementSibling.value = this.value + '%'">
                                <output style="width: 60px; text-align: center; font-weight: 700; color: #1a56db;">0%</output>
                            </div>
                        </div>
                        
                        {{-- Variation des dépenses --}}
                        <div class="param-group" style="margin-bottom: 25px;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 10px;">
                                Variation des Dépenses (%)
                            </label>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <input type="range" name="depenses_variation" class="param-slider" min="-30" max="30" value="0" step="1" 
                                       style="flex: 1; height: 6px; border-radius: 3px; background: #e2e8f0;"
                                       oninput="this.nextElementSibling.value = this.value + '%'">
                                <output style="width: 60px; text-align: center; font-weight: 700; color: #1a56db;">0%</output>
                            </div>
                        </div>
                        
                        {{-- Nouvel investissement --}}
                        <div class="param-group" style="margin-bottom: 25px;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 10px;">
                                Montant d'investissement (USD)
                            </label>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <input type="range" name="investissement" class="param-slider" min="0" max="100000" value="0" step="5000" 
                                       style="flex: 1; height: 6px; border-radius: 3px; background: #e2e8f0;"
                                       oninput="this.nextElementSibling.value = this.value.toLocaleString() + ' USD'">
                                <output style="width: 100px; text-align: center; font-weight: 700; color: #1a56db;">0 USD</output>
                            </div>
                        </div>
                        
                        {{-- Période de simulation --}}
                        <div class="param-group" style="margin-bottom: 25px;">
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 10px;">
                                Période de simulation
                            </label>
                            <select name="periode" class="form-control" style="border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px;">
                                <option value="3">3 mois</option>
                                <option value="6" selected>6 mois</option>
                                <option value="12">12 mois</option>
                                <option value="24">24 mois</option>
                            </select>
                        </div>
                    </div>
                    
                    {{-- Résultats de simulation --}}
                    <div id="simulationResults" style="display: none; background: #f8fafc; border-radius: 16px; padding: 20px; margin-top: 20px;">
                        <h6 style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 15px;">
                            <i class="fas fa-chart-line" style="margin-right: 8px; color: #0e9f6e;"></i> Résultats de la simulation
                        </h6>
                        <div class="results-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                            <div class="result-item" style="background: white; border-radius: 12px; padding: 12px;">
                                <div style="font-size: 11px; color: #6c757d;">Nouveau CA</div>
                                <div class="result-ca" style="font-size: 20px; font-weight: 800; color: #1a56db;">0 USD</div>
                            </div>
                            <div class="result-item" style="background: white; border-radius: 12px; padding: 12px;">
                                <div style="font-size: 11px; color: #6c757d;">Nouvelle Marge</div>
                                <div class="result-marge" style="font-size: 20px; font-weight: 800; color: #0e9f6e;">0 USD</div>
                            </div>
                            <div class="result-item" style="background: white; border-radius: 12px; padding: 12px;">
                                <div style="font-size: 11px; color: #6c757d;">Nouvelle Trésorerie</div>
                                <div class="result-tresorerie" style="font-size: 20px; font-weight: 800; color: #f5a623;">0 USD</div>
                            </div>
                            <div class="result-item" style="background: white; border-radius: 12px; padding: 12px;">
                                <div style="font-size: 11px; color: #6c757d;">ROI estimé</div>
                                <div class="result-roi" style="font-size: 20px; font-weight: 800; color: #7e3af2;">0%</div>
                            </div>
                        </div>
                        <div class="impact-message" style="margin-top: 15px; padding: 12px; background: #e0f2fe; border-radius: 12px; text-align: center;">
                            <i class="fas fa-info-circle" style="color: #1a56db;"></i>
                            <span style="font-size: 13px;">Impact estimé sur la période sélectionnée</span>
                        </div>
                    </div>
                </form>
            </div>
            
            {{-- Footer --}}
            <div class="modal-footer" style="border-top: none; padding: 0 24px 24px 24px; gap: 12px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background: #f1f5f9; color: #475569; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-times"></i> Fermer
                </button>
                <button type="button" class="btn-simulate" style="background: linear-gradient(135deg, #1a56db, #7e3af2); color: white; border: none; padding: 12px 28px; border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-chart-line"></i> Lancer la simulation
                </button>
                <button type="button" class="btn-save-simulation" style="background: #0e9f6e; color: white; border: none; padding: 12px 28px; border-radius: 12px; font-weight: 600; display: none;">
                    <i class="fas fa-save"></i> Sauvegarder la simulation
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const simulateBtn = document.querySelector('#{{ $id }} .btn-simulate');
        const saveBtn = document.querySelector('#{{ $id }} .btn-save-simulation');
        const resultsDiv = document.getElementById('simulationResults');
        const form = document.getElementById('simulationForm');
        
        let currentResults = null;
        
        // Mise à jour des affichages des sliders
        document.querySelectorAll('.param-slider').forEach(slider => {
            slider.addEventListener('input', function() {
                const output = this.nextElementSibling;
                if (output) {
                    let value = this.value;
                    if (this.name === 'investissement') {
                        output.textContent = parseInt(value).toLocaleString() + ' USD';
                    } else {
                        output.textContent = value + '%';
                    }
                }
            });
        });
        
        // Simulation
        if (simulateBtn) {
            simulateBtn.addEventListener('click', function() {
                const formData = new FormData(form);
                const data = {};
                formData.forEach((value, key) => { data[key] = value; });
                
                // Afficher le loading
                simulateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calcul en cours...';
                simulateBtn.disabled = true;
                
                // Appel API de simulation
                fetch('{{ $simulateUrl }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    currentResults = result;
                    
                    // Afficher les résultats
                    resultsDiv.style.display = 'block';
                    
                    // Mettre à jour les valeurs
                    document.querySelector('#{{ $id }} .result-ca').textContent = result.ca?.toLocaleString() + ' USD' || '0 USD';
                    document.querySelector('#{{ $id }} .result-marge').textContent = result.marge?.toLocaleString() + ' USD' || '0 USD';
                    document.querySelector('#{{ $id }} .result-tresorerie').textContent = result.tresorerie?.toLocaleString() + ' USD' || '0 USD';
                    document.querySelector('#{{ $id }} .result-roi').textContent = (result.roi || 0) + '%';
                    
                    // Ajouter des animations
                    resultsDiv.style.animation = 'fadeIn 0.5s ease';
                    
                    // Afficher le bouton sauvegarder
                    saveBtn.style.display = 'inline-flex';
                    
                    // Afficher un message d'impact
                    const impactMsg = document.querySelector('#{{ $id }} .impact-message span');
                    if (result.impact && impactMsg) {
                        impactMsg.textContent = result.impact;
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Impossible de réaliser la simulation.'
                    });
                })
                .finally(() => {
                    simulateBtn.innerHTML = '<i class="fas fa-chart-line"></i> Lancer la simulation';
                    simulateBtn.disabled = false;
                });
            });
        }
        
        // Sauvegarder la simulation
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                const formData = new FormData(form);
                const data = {};
                formData.forEach((value, key) => { data[key] = value; });
                data.results = currentResults;
                
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sauvegarde...';
                saveBtn.disabled = true;
                
                fetch('{{ $simulateUrl }}/save', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Simulation sauvegardée',
                            text: 'Votre simulation a été enregistrée avec succès.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Fermer le modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('{{ $id }}'));
                        if (modal) modal.hide();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: result.message || 'Impossible de sauvegarder la simulation.'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Impossible de sauvegarder la simulation.'
                    });
                })
                .finally(() => {
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Sauvegarder la simulation';
                    saveBtn.disabled = false;
                });
            });
        }
    });
    
    // Animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
</script>
@endpush