{{-- resources/views/partials/powerbi/kpi-card.blade.php --}}
@props([
    'title' => 'KPI',
    'value' => '0',
    'unit' => '',
    'trend' => null,
    'trendValue' => null,
    'icon' => 'fas fa-chart-line',
    'color' => '#1a56db',
    'bgColor' => 'white',
    'link' => null,
    'target' => null
])

@php
    $trendClass = 'trend-up';
    $trendIcon = 'fa-arrow-up';
    if ($trend === 'down') {
        $trendClass = 'trend-down';
        $trendIcon = 'fa-arrow-down';
    } elseif ($trend === 'stable') {
        $trendClass = 'trend-stable';
        $trendIcon = 'fa-minus';
    }
@endphp

<div class="kpi-card" 
     style="background: {{ $bgColor }}; border-radius: 20px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-left: 4px solid {{ $color }}; transition: all 0.3s; cursor: {{ $link ? 'pointer' : 'default' }};"
     @if($link) onclick="window.location='{{ $link }}'" @endif
     @if($target) data-target="{{ $target }}" @endif>
    
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div class="kpi-title" style="font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #6c757d; margin-bottom: 8px;">
                <i class="{{ $icon }}" style="margin-right: 5px;"></i> {{ $title }}
            </div>
            <div class="kpi-value" style="font-size: 32px; font-weight: 800; color: #1a202c; margin-bottom: 8px;">
                {{ $value }} <span style="font-size: 14px; font-weight: 400; color: #6c757d;">{{ $unit }}</span>
            </div>
            @if($trendValue)
                <div class="kpi-trend {{ $trendClass }}" style="font-size: 12px; display: flex; align-items: center; gap: 5px;">
                    <i class="fas {{ $trendIcon }}"></i>
                    <span>{{ $trendValue }}</span>
                    <span style="color: #6c757d;">vs période précédente</span>
                </div>
            @endif
        </div>
        <div class="kpi-icon" style="width: 48px; height: 48px; background: linear-gradient(135deg, {{ $color }}20, {{ $color }}05); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
            <i class="{{ $icon }}" style="font-size: 24px; color: {{ $color }};"></i>
        </div>
    </div>
</div>

<style>
    .trend-up { color: #0e9f6e; }
    .trend-down { color: #e02424; }
    .trend-stable { color: #f5a623; }
    
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
</style>