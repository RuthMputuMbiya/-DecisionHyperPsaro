{{-- resources/views/partials/powerbi/modal.blade.php --}}
@props([
    'id' => 'modal',
    'title' => 'Modal',
    'size' => 'md', // sm, md, lg, xl
    'closeable' => true,
    'footer' => true
])

@php
    $sizes = [
        'sm' => '400px',
        'md' => '600px',
        'lg' => '800px',
        'xl' => '90%'
    ];
    $width = $sizes[$size] ?? $sizes['md'];
@endphp

<div id="{{ $id }}" class="powerbi-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; align-items: center; justify-content: center;">
    <div class="modal-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);"></div>
    <div class="modal-container" style="position: relative; width: {{ $width }}; max-width: 95%; background: white; border-radius: 24px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); animation: modalFadeIn 0.3s ease;">
        
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #e2e8f0;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">{{ $title }}</h3>
            @if($closeable)
                <button class="modal-close" onclick="closeModal('{{ $id }}')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">
                    <i class="fas fa-times"></i>
                </button>
            @endif
        </div>
        
        <div class="modal-body" style="padding: 24px; max-height: 60vh; overflow-y: auto;">
            {{ $slot }}
        </div>
        
        @if($footer)
            <div class="modal-footer" style="padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 12px;">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>

<style>
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>

@push('scripts')
<script>
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
    
    // Fermer en cliquant sur l'overlay
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            const modal = e.target.closest('.powerbi-modal');
            if (modal) {
                closeModal(modal.id);
            }
        }
    });
</script>
@endpush