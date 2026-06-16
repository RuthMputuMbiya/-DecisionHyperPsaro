{{-- resources/views/partials/modals/delete-modal.blade.php --}}
@props([
    'id' => 'deleteModal',
    'title' => 'Confirmation de suppression',
    'message' => 'Êtes-vous sûr de vouloir supprimer cet élément ?',
    'itemName' => null,
    'itemId' => null,
    'deleteUrl' => null,
    'confirmText' => 'Supprimer',
    'cancelText' => 'Annuler'
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
            
            {{-- Header --}}
            <div class="modal-header" style="border-bottom: none; padding: 24px 24px 0 24px;">
                <div class="modal-icon" style="width: 48px; height: 48px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                    <i class="fas fa-trash-alt" style="color: #e02424; font-size: 22px;"></i>
                </div>
                <h5 class="modal-title" style="font-weight: 700; color: #1e293b;">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- Body --}}
            <div class="modal-body" style="padding: 20px 24px;">
                <p style="color: #475569; margin-bottom: 10px; font-size: 15px;">
                    {{ $message }}
                </p>
                @if($itemName)
                    <div class="alert alert-warning" style="background: #fef3c7; border: none; border-radius: 12px; padding: 12px; margin-top: 15px;">
                        <i class="fas fa-exclamation-triangle" style="color: #f5a623; margin-right: 8px;"></i>
                        <strong>{{ $itemName }}</strong> - Cette action est irréversible.
                    </div>
                @endif
            </div>
            
            {{-- Footer --}}
            <div class="modal-footer" style="border-top: none; padding: 0 24px 24px 24px; gap: 12px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background: #f1f5f9; color: #475569; border: none; padding: 10px 24px; border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-times"></i> {{ $cancelText }}
                </button>
                <button type="button" class="btn-delete-confirm" data-url="{{ $deleteUrl }}" data-id="{{ $itemId }}" style="background: #e02424; color: white; border: none; padding: 10px 24px; border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-trash-alt"></i> {{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Gestionnaire global pour les modals de suppression
    document.addEventListener('DOMContentLoaded', function() {
        // Configuration du modal de suppression
        const deleteModal = document.getElementById('{{ $id }}');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const url = button.getAttribute('data-url');
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                
                const confirmBtn = deleteModal.querySelector('.btn-delete-confirm');
                if (confirmBtn) {
                    confirmBtn.setAttribute('data-url', url);
                    confirmBtn.setAttribute('data-id', id);
                    
                    if (name) {
                        const alertDiv = deleteModal.querySelector('.alert-warning strong');
                        if (alertDiv) alertDiv.textContent = name;
                    }
                }
            });
            
            // Action de confirmation
            const confirmBtn = deleteModal.querySelector('.btn-delete-confirm');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function() {
                    const url = this.getAttribute('data-url');
                    const id = this.getAttribute('data-id');
                    
                    if (url) {
                        // Afficher un loading
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Suppression...';
                        this.disabled = true;
                        
                        // Envoyer la requête
                        fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Fermer le modal
                                const modal = bootstrap.Modal.getInstance(deleteModal);
                                modal.hide();
                                
                                // Afficher le toast de succès
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Supprimé !',
                                    text: data.message || 'L\'élément a été supprimé avec succès.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                
                                // Recharger la page ou supprimer la ligne
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erreur',
                                    text: data.message || 'Une erreur est survenue.'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: 'Impossible de supprimer l\'élément.'
                            });
                        })
                        .finally(() => {
                            this.innerHTML = '<i class="fas fa-trash-alt"></i> Supprimer';
                            this.disabled = false;
                        });
                    }
                });
            }
        }
    });
    
    // Fonction globale pour ouvrir le modal de suppression
    window.showDeleteModal = function(url, id, name) {
        const modal = document.getElementById('{{ $id }}');
        if (modal) {
            const confirmBtn = modal.querySelector('.btn-delete-confirm');
            if (confirmBtn) {
                confirmBtn.setAttribute('data-url', url);
                confirmBtn.setAttribute('data-id', id);
            }
            if (name) {
                const alertDiv = modal.querySelector('.alert-warning strong');
                if (alertDiv) alertDiv.textContent = name;
            }
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    };
</script>
@endpush