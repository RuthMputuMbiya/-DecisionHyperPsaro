{{-- resources/views/partials/powerbi/alerts.blade.php --}}
@props([
    'type' => 'info', // success, danger, warning, info
    'title' => null,
    'message' => null,
    'icon' => null,
    'dismissible' => true,
    'autoClose' => false,
    'autoCloseDelay' => 5000
])

@php
    $alertConfigs = [
        'success' => [
            'bg' => '#0e9f6e15',
            'border' => '#0e9f6e',
            'icon' => 'fa-check-circle',
            'iconColor' => '#0e9f6e',
            'text' => '#0e9f6e'
        ],
        'danger' => [
            'bg' => '#e0242415',
            'border' => '#e02424',
            'icon' => 'fa-exclamation-triangle',
            'iconColor' => '#e02424',
            'text' => '#e02424'
        ],
        'warning' => [
            'bg' => '#f5a62315',
            'border' => '#f5a623',
            'icon' => 'fa-exclamation-circle',
            'iconColor' => '#f5a623',
            'text' => '#f5a623'
        ],
        'info' => [
            'bg' => '#3abff815',
            'border' => '#3abff8',
            'icon' => 'fa-info-circle',
            'iconColor' => '#3abff8',
            'text' => '#3abff8'
        ]
    ];
    
    $config = $alertConfigs[$type] ?? $alertConfigs['info'];
    $icon = $icon ?? $config['icon'];
@endphp

<div class="powerbi-alert powerbi-alert-{{ $type }}" 
     style="background: {{ $config['bg'] }}; border-left: 4px solid {{ $config['border'] }}; border-radius: 12px; padding: 15px 20px; margin-bottom: 20px; position: relative; overflow: hidden; animation: slideInRight 0.3s ease-out;">
    
    <div style="display: flex; align-items: flex-start; gap: 12px;">
        <div class="alert-icon" style="color: {{ $config['iconColor'] }}; font-size: 18px;">
            <i class="fas {{ $icon }}"></i>
        </div>
        
        <div class="alert-content" style="flex: 1;">
            @if($title)
                <h5 style="color: {{ $config['text'] }}; font-weight: 700; margin: 0 0 5px 0; font-size: 14px;">
                    {{ $title }}
                </h5>
            @endif
            <p style="margin: 0; color: #1e293b; font-size: 13px; line-height: 1.5;">
                {{ $message ?? $slot }}
            </p>
        </div>
        
        @if($dismissible)
            <button type="button" class="alert-close" onclick="this.closest('.powerbi-alert').remove()" 
                    style="background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px; transition: color 0.2s;">
                <i class="fas fa-times"></i>
            </button>
        @endif
    </div>
    
    @if($autoClose)
        <div class="alert-progress" style="position: absolute; bottom: 0; left: 0; height: 2px; background: {{ $config['border'] }}; width: 100%; animation: progressBar {{ $autoCloseDelay }}ms linear forwards;"></div>
    @endif
</div>

<style>
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes progressBar {
        from { width: 100%; }
        to { width: 0%; }
    }
    
    .alert-close:hover {
        color: #1e293b !important;
    }
    
    .powerbi-alert {
        transition: all 0.3s;
    }
    
    .powerbi-alert:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>

@if($autoClose)
<script>
    setTimeout(() => {
        const alert = document.querySelector('.powerbi-alert');
        if (alert) alert.remove();
    }, {{ $autoCloseDelay }});
</script>
@endif