{{-- resources/views/partials/modals/export-modal.blade.php --}}
@props([
    'id' => 'exportModal',
    'title' => 'Exporter les données',
    'exportUrl' => null,
    'formats' => ['pdf', 'excel', 'csv', 'print']
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
            
            {{-- Header --}}
            <div class="modal-header" style="background: linear-gradient(135deg, #1a56db, #7e3af2); border-bottom: none; padding: 24px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="modal-icon" style="width: 44px; height: 44px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-download" style="color: white; font-size: 22px;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" style="color: white; font-weight: 700; margin: 0;">{{ $title }}</h5>
                        <p style="color: rgba(255,255,255,0.8); font-size: 12px; margin: 5px 0 0 0;">Choisissez le format d'exportation</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- Body --}}
            <div class="modal-body" style="padding: 24px;">
                <form id="exportForm" action="{{ $exportUrl }}" method="POST">
                    @csrf
                    
                    {{-- Options de format --}}
                    <div class="export-formats" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 25px;">
                        @if(in_array('pdf', $formats))
                            <label class="format-option" style="display: flex; align-items: center; gap: 15px; padding: 15px; border: 2px solid #e2e8f0; border-radius: 16px; cursor: pointer; transition: all 0.2s;">
                                <input type="radio" name="format" value="pdf" style="width: 18px; height: 18px;" checked>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 40px; height: 40px; background: #fee2e2; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-file-pdf" style="color: #e02424; font-size: 20px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #1e293b;">PDF</div>
                                        <div style="font-size: 11px; color: #6c757d;">Document professionnel</div>
                                    </div>
                                </div>
                            </label>
                        @endif
                        
                        @if(in_array('excel', $formats))
                            <label class="format-option" style="display: flex; align-items: center; gap: 15px; padding: 15px; border: 2px solid #e2e8f0; border-radius: 16px; cursor: pointer; transition: all 0.2s;">
                                <input type="radio" name="format" value="excel" style="width: 18px; height: 18px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 40px; height: 40px; background: #e0f2fe; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-file-excel" style="color: #0e9f6e; font-size: 20px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #1e293b;">Excel</div>
                                        <div style="font-size: 11px; color: #6c757d;">Tableau analysable</div>
                                    </div>
                                </div>
                            </label>
                        @endif
                        
                        @if(in_array('csv', $formats))
                            <label class="format-option" style="display: flex; align-items: center; gap: 15px; padding: 15px; border: 2px solid #e2e8f0; border-radius: 16px; cursor: pointer; transition: all 0.2s;">
                                <input type="radio" name="format" value="csv" style="width: 18px; height: 18px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 40px; height: 40px; background: #fef3c7; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-file-csv" style="color: #f5a623; font-size: 20px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #1e293b;">CSV</div>
                                        <div style="font-size: 11px; color: #6c757d;">Compatible universel</div>
                                    </div>
                                </div>
                            </label>
                        @endif
                        
                        @if(in_array('print', $formats))
                            <label class="format-option" style="display: flex; align-items: center; gap: 15px; padding: 15px; border: 2px solid #e2e8f0; border-radius: 16px; cursor: pointer; transition: all 0.2s;">
                                <input type="radio" name="format" value="print" style="width: 18px; height: 18px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 40px; height: 40px; background: #f3e8ff; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-print" style="color: #7e3af2; font-size: 20px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #1e293b;">Impression</div>
                                        <div style="font-size: 11px; color: #6c757d;">Version papier</div>
                                    </div>
                                </div>
                            </label>
                        @endif
                    </div>
                    
                    {{-- Options supplémentaires --}}
                    <div style="margin-bottom: 20px;">
                        <h6 style="font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 15px;">Options d'export</h6>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="include_charts" value="1" checked style="width: 16px; height: 16px;">
                                <span style="font-size: 13px; color: #475569;">Inclure les graphiques</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="include_summary" value="1" checked style="width: 16px; height: 16px;">
                                <span style="font-size: 13px; color: #475569;">Inclure le résumé</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="landscape" value="1" style="width: 16px; height: 16px;">
                                <span style="font-size: 13px; color: #475569;">Format paysage</span>
                            </label>
                        </div>
                    </div>
                    
                    {{-- Période --}}
                    <div style="margin-bottom: 20px;">
                        <h6 style="font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 10px;">Période</h6>
                        <div style="display: flex; gap: 10px;">
                            <select name="period" class="form-control" style="border-radius: 12px; border: 1px solid #e2e8f0; padding: 10px;">
                                <option value="current_month">Mois en cours</option>
                                <option value="last_month">Mois dernier</option>
                                <option value="current_quarter">Trimestre en cours</option>
                                <option value="current_year">Année en cours</option>
                                <option value="last_year">Année dernière</option>
                                <option value="custom">Personnalisé</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            
            {{-- Footer --}}
            <div class="modal-footer" style="border-top: none; padding: 0 24px 24px 24px; gap: 12px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background: #f1f5f9; color: #475569; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="button" class="btn-export-submit" style="background: linear-gradient(135deg, #1a56db, #7e3af2); color: white; border: none; padding: 12px 32px; border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-download"></i> Exporter
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .format-option:hover {
        border-color: #1a56db !important;
        background: #f8fafc;
    }
    .format-option:has(input:checked) {
        border-color: #1a56db !important;
        background: rgba(26,86,219,0.05);
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const exportBtn = document.querySelector('#{{ $id }} .btn-export-submit');
        const exportForm = document.querySelector('#exportForm');
        
        if (exportBtn && exportForm) {
            exportBtn.addEventListener('click', function() {
                const formData = new FormData(exportForm);
                const format = formData.get('format');
                
                // Afficher le loading
                exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Export en cours...';
                exportBtn.disabled = true;
                
                if (format === 'print') {
                    window.print();
                    exportBtn.innerHTML = '<i class="fas fa-download"></i> Exporter';
                    exportBtn.disabled = false;
                    const modal = bootstrap.Modal.getInstance(document.getElementById('{{ $id }}'));
                    if (modal) modal.hide();
                } else {
                    // Soumettre le formulaire
                    exportForm.submit();
                    
                    setTimeout(() => {
                        exportBtn.innerHTML = '<i class="fas fa-download"></i> Exporter';
                        exportBtn.disabled = false;
                    }, 2000);
                }
            });
        }
    });
    
    // Fonction globale pour ouvrir le modal d'export
    window.showExportModal = function(url) {
        const form = document.querySelector('#exportForm');
        if (form) {
            form.action = url;
        }
        const modal = document.getElementById('{{ $id }}');
        if (modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    };
</script>
@endpush