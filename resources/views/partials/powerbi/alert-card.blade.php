{{-- resources/views/partials/powerbi/alert-card.blade.php --}}
@props([
    'type' => 'info', // success, danger, warning, info
    'title' => null,
    'message' => null,
    'icon' => null,
    'dismissible' => false,
    'animated' => true
])

@php
    $colors = [
        'success' => ['bg' => '#0e9f6e', 'light' => '#0e9f6e15', 'border' => '#0e9f6e', 'icon' => 'fa-check-circle'],
        'danger' => ['bg' => '#e02424', 'light' => '#e0242415', 'border' => '#e02424', 'icon' => 'fa-exclamation-triangle'],
        'warning' => ['bg' => '#f5a623', 'light' => '#f5a62315', 'border' => '#f5a623', 'icon' => 'fa-exclamation-circle'],
        'info' => ['bg' => '#3abff8', 'light' => '#3abff815', 'border' => '#3abff8', 'icon' => 'fa-info-circle'],
    ];
    
    $color = $colors[$type] ?? $colors['info'];
    $icon = $icon ?? $color['icon'];
@endphp

<div class="alert-card" 
     style="background: {{ $color['light'] }}; border-left: 4px solid {{ $color['border'] }}; border-radius: 12px; padding: 16px; margin-bottom: 16px; {{ $animated ? 'animation: slideIn 0.3s ease-out;' : '' }}">
    <div class="d-flex align-items-start gap-3">
        <div class="alert-icon" style="color: {{ $color['bg'] }}; font-size: 20px;">
            <i class="fas {{ $icon }}"></i>
        </div>
        <div class="alert-content flex-grow-1">
            @if($title)
                <h5 style="color: {{ $color['bg'] }}; margin: 0 0 5px 0; font-weight: 600;">{{ $title }}</h5>
            @endif
            <p style="margin: 0; color: #1e293b;">{{ $message ?? $slot }}</p>
        </div>
        @if($dismissible)
            <button type="button" class="alert-close" style="background: none; border: none; cursor: pointer; color: #94a3b8;" onclick="this.closest('.alert-card').remove()">
                <i class="fas fa-times"></i>
            </button>
        @endif
    </div>
</div>

<style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
</style>