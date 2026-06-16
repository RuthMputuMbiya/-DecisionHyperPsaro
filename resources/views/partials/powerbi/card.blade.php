{{-- resources/views/partials/powerbi/card.blade.php --}}
@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'color' => '#1a56db',
    'bgColor' => 'white',
    'padding' => '20px',
    'shadow' => true,
    'hoverable' => true,
    'border' => false,
    'borderColor' => '#e2e8f0',
    'headerAction' => null,
    'footer' => null,
    'loading' => false
])

<div class="powerbi-card" 
     style="background: {{ $bgColor }}; border-radius: 20px; padding: {{ $padding }}; margin-bottom: 24px; 
            {{ $shadow ? 'box-shadow: 0 1px 3px rgba(0,0,0,0.05);' : '' }}
            {{ $border ? 'border: 1px solid ' . $borderColor . ';' : '' }}
            transition: all 0.3s ease;">
    
    {{-- Header --}}
    @if($title || $headerAction)
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                @if($icon)
                    <div class="card-icon" style="width: 40px; height: 40px; background: linear-gradient(135deg, {{ $color }}20, {{ $color }}05); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="{{ $icon }}" style="color: {{ $color }}; font-size: 20px;"></i>
                    </div>
                @endif
                <div>
                    <h3 class="card-title" style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0; {{ $icon ? '' : 'padding-left: 12px; border-left: 3px solid ' . $color }}">
                        {{ $title }}
                    </h3>
                    @if($subtitle)
                        <p class="card-subtitle" style="font-size: 12px; color: #6c757d; margin: 5px 0 0 0;">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            @if($headerAction)
                <div class="card-header-action">
                    {{ $headerAction }}
                </div>
            @endif
        </div>
    @endif
    
    {{-- Body --}}
    <div class="card-body" style="position: relative;">
        @if($loading)
            <div class="card-loading" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; border-radius: 16px; z-index: 10;">
                <div class="spinner" style="width: 40px; height: 40px; border: 3px solid #e2e8f0; border-top-color: {{ $color }}; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
            </div>
        @endif
        {{ $slot }}
    </div>
    
    {{-- Footer --}}
    @if($footer)
        <div class="card-footer" style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
            {{ $footer }}
        </div>
    @endif
</div>

<style>
    .powerbi-card {
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    @if($hoverable)
    .powerbi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }
    @endif
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>