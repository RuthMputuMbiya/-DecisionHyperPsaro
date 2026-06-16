{{-- resources/views/partials/powerbi/chart-card.blade.php --}}
@props([
    'title' => 'Graphique',
    'subtitle' => null,
    'height' => 300,
    'chartId' => null,
    'chartType' => 'line',
    'chartData' => [],
    'chartOptions' => [],
    'downloadable' => false,
    'fullscreen' => false
])

@php
    $chartId = $chartId ?? 'chart-' . uniqid();
@endphp

<div class="chart-card" style="background: white; border-radius: 20px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <div class="chart-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 class="chart-title" style="font-size: 16px; font-weight: 600; margin: 0; padding-left: 12px; border-left: 3px solid #1a56db;">
                {{ $title }}
            </h3>
            @if($subtitle)
                <p style="font-size: 12px; color: #6c757d; margin: 5px 0 0 15px;">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="chart-actions" style="display: flex; gap: 8px;">
            @if($downloadable)
                <button class="btn-chart-action" onclick="downloadChart('{{ $chartId }}')" title="Télécharger" style="background: none; border: none; cursor: pointer; padding: 6px; border-radius: 8px; color: #6c757d;">
                    <i class="fas fa-download"></i>
                </button>
            @endif
            @if($fullscreen)
                <button class="btn-chart-action" onclick="fullscreenChart('{{ $chartId }}')" title="Plein écran" style="background: none; border: none; cursor: pointer; padding: 6px; border-radius: 8px; color: #6c757d;">
                    <i class="fas fa-expand"></i>
                </button>
            @endif
            <button class="btn-chart-action" onclick="refreshChart('{{ $chartId }}')" title="Rafraîchir" style="background: none; border: none; cursor: pointer; padding: 6px; border-radius: 8px; color: #6c757d;">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    <div class="chart-body">
        <canvas id="{{ $chartId }}" style="width: 100%; height: {{ $height }}px;"></canvas>
    </div>
</div>

@push('scripts')
<script>
    // Initialisation du graphique
    let chart{{ Str::studly($chartId) }} = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
        chart{{ Str::studly($chartId) }} = new Chart(ctx, {
            type: '{{ $chartType }}',
            data: @json($chartData),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                ...@json($chartOptions)
            }
        });
    });
    
    function refreshChart(chartId) {
        if (window['chart' + chartId.charAt(0).toUpperCase() + chartId.slice(1)]) {
            window['chart' + chartId.charAt(0).toUpperCase() + chartId.slice(1)].update();
        }
    }
    
    function downloadChart(chartId) {
        const canvas = document.getElementById(chartId);
        const link = document.createElement('a');
        link.download = `chart-${chartId}.png`;
        link.href = canvas.toDataURL();
        link.click();
    }
    
    function fullscreenChart(chartId) {
        const canvas = document.getElementById(chartId);
        if (canvas.requestFullscreen) {
            canvas.requestFullscreen();
        }
    }
</script>
@endpush