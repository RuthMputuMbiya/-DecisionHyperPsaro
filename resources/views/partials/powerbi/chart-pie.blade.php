{{-- resources/views/partials/powerbi/chart-pie.blade.php --}}
@props([
    'id' => null,
    'title' => 'Répartition',
    'subtitle' => null,
    'labels' => [],
    'data' => [],
    'colors' => ['#1a56db', '#0e9f6e', '#f5a623', '#e02424', '#7e3af2', '#3abff8', '#f97316', '#8b5cf6'],
    'height' => 350,
    'showPercentage' => true,
    'showLegend' => true,
    'doughnut' => false,
    'cutout' => '50%',
    'downloadable' => true
])

@php
    $chartId = $id ?? ($doughnut ? 'donut-chart-' : 'pie-chart-') . uniqid();
    $total = array_sum($data);
@endphp

<div class="chart-pie-card" style="background: white; border-radius: 20px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    
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
        @if($downloadable)
            <div class="chart-actions" style="display: flex; gap: 8px;">
                <button class="chart-action-btn" onclick="downloadChart('{{ $chartId }}')" style="background: #f8fafc; border: none; padding: 8px; border-radius: 8px; cursor: pointer;">
                    <i class="fas fa-download" style="color: #6c757d;"></i>
                </button>
            </div>
        @endif
    </div>
    
    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
        {{-- Canvas --}}
        <div class="chart-canvas-container" style="flex: 1; min-width: 250px; height: {{ $height }}px;">
            <canvas id="{{ $chartId }}" style="width: 100%; height: 100%;"></canvas>
        </div>
        
        {{-- Légende détaillée --}}
        @if($showLegend)
            <div class="chart-legend-detailed" style="flex: 1; min-width: 200px; max-height: {{ $height }}px; overflow-y: auto;">
                <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0;">
                    Détail des parts
                </div>
                @foreach($labels as $index => $label)
                    @php
                        $value = $data[$index] ?? 0;
                        $percentage = $total > 0 ? round(($value / $total) * 100, 1) : 0;
                        $color = $colors[$index % count($colors)];
                    @endphp
                    <div class="legend-item-detailed" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="width: 12px; height: 12px; background: {{ $color }}; border-radius: 3px;"></span>
                            <span style="font-size: 13px; color: #475569;">{{ $label }}</span>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: #1e293b;">{{ number_format($value, 0, ',', ' ') }}</div>
                            @if($showPercentage)
                                <div style="font-size: 11px; color: #94a3b8;">{{ $percentage }}%</div>
                            @endif
                        </div>
                    </div>
                @endforeach
                
                {{-- Total --}}
                <div style="margin-top: 15px; padding-top: 10px; border-top: 2px solid #e2e8f0; display: flex; justify-content: space-between;">
                    <span style="font-weight: 700; color: #1e293b;">Total</span>
                    <span style="font-weight: 800; color: #1a56db;">{{ number_format($total, 0, ',', ' ') }}</span>
                </div>
            </div>
        @endif
    </div>
    
    {{-- Centre du donut (valeur centrale) --}}
    @if($doughnut && isset($centerValue))
        <div id="center-text-{{ $chartId }}" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none;">
            <div style="font-size: 28px; font-weight: 800; color: #1a56db;">{{ $centerValue['value'] ?? '' }}</div>
            <div style="font-size: 11px; color: #6c757d;">{{ $centerValue['label'] ?? 'Total' }}</div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    let pieChart{{ Str::studly($chartId) }} = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
        
        const config = {
            type: '{{ $doughnut ? "doughnut" : "pie" }}',
            data: {
                labels: @json($labels),
                datasets: [{
                    data: @json($data),
                    backgroundColor: @json($colors),
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '{{ $doughnut ? $cutout : "0%" }}',
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
                }
            }
        };
        
        pieChart{{ Str::studly($chartId) }} = new Chart(ctx, config);
    });
    
    function downloadChart(chartId) {
        const canvas = document.getElementById(chartId);
        const link = document.createElement('a');
        link.download = `chart-${chartId}.png`;
        link.href = canvas.toDataURL();
        link.click();
    }
</script>
@endpush