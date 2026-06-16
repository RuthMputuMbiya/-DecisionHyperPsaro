{{-- resources/views/partials/powerbi/chart-donut.blade.php --}}
@props([
    'id' => null,
    'title' => 'Distribution',
    'subtitle' => null,
    'labels' => [],
    'data' => [],
    'colors' => ['#1a56db', '#0e9f6e', '#f5a623', '#e02424', '#7e3af2', '#3abff8', '#f97316', '#8b5cf6'],
    'height' => 350,
    'cutout' => '65%',
    'centerValue' => null,
    'centerLabel' => 'Total',
    'showLegend' => true,
    'animation' => true
])

@php
    $chartId = $id ?? 'donut-chart-' . uniqid();
    $total = array_sum($data);
    $centerValue = $centerValue ?? $total;
@endphp

<div class="chart-donut-card" style="background: white; border-radius: 20px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    
    {{-- En-tête --}}
    <div class="chart-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h3 class="chart-title" style="font-size: 16px; font-weight: 700; margin: 0; padding-left: 12px; border-left: 3px solid #1a56db;">
                <i class="fas fa-chart-pie" style="margin-right: 8px; color: #1a56db;"></i> {{ $title }}
            </h3>
            @if($subtitle)
                <p style="font-size: 12px; color: #6c757d; margin: 5px 0 0 15px;">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    
    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 30px;">
        {{-- Donut Chart --}}
        <div class="donut-container" style="position: relative; width: 300px; height: 300px; margin: 0 auto;">
            <canvas id="{{ $chartId }}" style="width: 100%; height: 100%;"></canvas>
            
            {{-- Centre du donut --}}
            <div class="donut-center" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                <div style="font-size: 36px; font-weight: 800; background: linear-gradient(135deg, #1a56db, #7e3af2); -webkit-background-clip: text; background-clip: text; color: transparent;">
                    {{ is_numeric($centerValue) ? number_format($centerValue, 0, ',', ' ') : $centerValue }}
                </div>
                <div style="font-size: 12px; color: #6c757d;">{{ $centerLabel }}</div>
            </div>
        </div>
        
        {{-- Légende interactive --}}
        @if($showLegend)
            <div class="donut-legend" style="flex: 1; min-width: 200px;">
                @foreach($labels as $index => $label)
                    @php
                        $value = $data[$index] ?? 0;
                        $percentage = $total > 0 ? round(($value / $total) * 100, 1) : 0;
                        $color = $colors[$index % count($colors)];
                    @endphp
                    <div class="legend-item" data-index="{{ $index }}" 
                         style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; margin: 4px 0; border-radius: 10px; cursor: pointer; transition: all 0.2s;"
                         onmouseover="highlightSegment({{ $index }}, true)"
                         onmouseout="highlightSegment({{ $index }}, false)">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="width: 12px; height: 12px; background: {{ $color }}; border-radius: 50%;"></span>
                            <span style="font-size: 13px; font-weight: 500; color: #1e293b;">{{ $label }}</span>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: #1a56db;">{{ number_format($value, 0, ',', ' ') }}</div>
                            <div style="font-size: 11px; color: #94a3b8;">{{ $percentage }}%</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    let donutChart{{ Str::studly($chartId) }} = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
        
        donutChart{{ Str::studly($chartId) }} = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($labels),
                datasets: [{
                    data: @json($data),
                    backgroundColor: @json($colors),
                    borderWidth: 0,
                    hoverOffset: 15,
                    cutout: '{{ $cutout }}'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${new Intl.NumberFormat('fr-FR').format(value)} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateScale: {{ $animation ? 'true' : 'false' }},
                    animateRotate: {{ $animation ? 'true' : 'false' }}
                }
            }
        });
    });
    
    function highlightSegment(index, highlight) {
        if (donutChart{{ Str::studly($chartId) }}) {
            const meta = donutChart{{ Str::studly($chartId) }}.getDatasetMeta(0);
            meta.data[index].options = {
                offset: highlight ? 15 : 0
            };
            donutChart{{ Str::studly($chartId) }}.update();
            
            // Mettre à jour le style de la légende
            const legendItem = document.querySelector(`.legend-item[data-index="${index}"]`);
            if (legendItem) {
                if (highlight) {
                    legendItem.style.background = '#f8fafc';
                    legendItem.style.transform = 'translateX(5px)';
                } else {
                    legendItem.style.background = 'transparent';
                    legendItem.style.transform = 'translateX(0)';
                }
            }
        }
    }
</script>
@endpush