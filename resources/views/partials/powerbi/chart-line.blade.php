{{-- resources/views/partials/powerbi/chart-line.blade.php --}}
@props([
    'id' => null,
    'title' => 'Évolution',
    'subtitle' => null,
    'labels' => [],
    'datasets' => [],
    'height' => 350,
    'showGrid' => true,
    'showLegend' => true,
    'smooth' => true,
    'fill' => false,
    'downloadable' => true,
    'colors' => ['#1a56db', '#0e9f6e', '#e02424', '#f5a623', '#7e3af2']
])

@php
    $chartId = $id ?? 'line-chart-' . uniqid();
    
    // Couleurs par défaut pour les datasets
    $defaultColors = [
        'border' => ['#1a56db', '#0e9f6e', '#e02424', '#f5a623', '#7e3af2', '#3abff8'],
        'background' => ['rgba(26,86,219,0.1)', 'rgba(14,159,110,0.1)', 'rgba(224,36,36,0.1)', 'rgba(245,166,35,0.1)', 'rgba(126,58,242,0.1)', 'rgba(58,191,248,0.1)']
    ];
@endphp

<div class="chart-line-card" style="background: white; border-radius: 20px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    
    {{-- En-tête --}}
    <div class="chart-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 class="chart-title" style="font-size: 16px; font-weight: 700; margin: 0; padding-left: 12px; border-left: 3px solid #1a56db;">
                <i class="fas fa-chart-line" style="margin-right: 8px; color: #1a56db;"></i> {{ $title }}
            </h3>
            @if($subtitle)
                <p style="font-size: 12px; color: #6c757d; margin: 5px 0 0 15px;">{{ $subtitle }}</p>
            @endif
        </div>
        @if($downloadable)
            <div class="chart-actions" style="display: flex; gap: 8px;">
                <button class="chart-action-btn" onclick="downloadChart('{{ $chartId }}')" title="Télécharger PNG" style="background: #f8fafc; border: none; padding: 8px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-download" style="color: #6c757d;"></i>
                </button>
                <button class="chart-action-btn" onclick="printChart('{{ $chartId }}')" title="Imprimer" style="background: #f8fafc; border: none; padding: 8px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-print" style="color: #6c757d;"></i>
                </button>
                <button class="chart-action-btn" onclick="toggleFullscreen('{{ $chartId }}')" title="Plein écran" style="background: #f8fafc; border: none; padding: 8px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-expand" style="color: #6c757d;"></i>
                </button>
            </div>
        @endif
    </div>
    
    {{-- Filtres rapides --}}
    <div class="chart-filters" style="display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
        <button class="filter-quick-btn" onclick="changeTimeRange('week')" style="padding: 4px 12px; background: none; border: 1px solid #e2e8f0; border-radius: 20px; font-size: 12px; cursor: pointer;">Semaine</button>
        <button class="filter-quick-btn" onclick="changeTimeRange('month')" style="padding: 4px 12px; background: none; border: 1px solid #e2e8f0; border-radius: 20px; font-size: 12px; cursor: pointer;">Mois</button>
        <button class="filter-quick-btn" onclick="changeTimeRange('quarter')" style="padding: 4px 12px; background: none; border: 1px solid #e2e8f0; border-radius: 20px; font-size: 12px; cursor: pointer;">Trimestre</button>
        <button class="filter-quick-btn" onclick="changeTimeRange('year')" style="padding: 4px 12px; background: none; border: 1px solid #e2e8f0; border-radius: 20px; font-size: 12px; cursor: pointer;">Année</button>
    </div>
    
    {{-- Canvas du graphique --}}
    <div class="chart-canvas-container" style="position: relative; height: {{ $height }}px;">
        <canvas id="{{ $chartId }}" style="width: 100%; height: 100%;"></canvas>
    </div>
    
    {{-- Légende personnalisée --}}
    @if($showLegend && count($datasets) > 0)
        <div class="chart-legend" style="display: flex; justify-content: center; gap: 20px; margin-top: 20px; flex-wrap: wrap;">
            @foreach($datasets as $index => $dataset)
                <div class="legend-item" style="display: flex; align-items: center; gap: 8px; cursor: pointer;" onclick="toggleDataset({{ $index }})">
                    <span style="width: 12px; height: 12px; background: {{ $dataset['borderColor'] ?? $defaultColors['border'][$index % count($defaultColors['border'])] }}; border-radius: 2px;"></span>
                    <span style="font-size: 12px; color: #475569;">{{ $dataset['label'] }}</span>
                </div>
            @endforeach
        </div>
    @endif
    
    {{-- Indicateurs de tendance --}}
    @if(isset($trend))
        <div class="chart-trend" style="margin-top: 15px; padding: 12px; background: #f8fafc; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; color: #475569;">Tendance globale</span>
            <div class="trend-value" style="display: flex; align-items: center; gap: 8px;">
                <i class="fas {{ $trend['icon'] ?? 'fa-arrow-up' }}" style="color: {{ $trend['color'] ?? '#0e9f6e' }};"></i>
                <span style="font-weight: 700; color: {{ $trend['color'] ?? '#0e9f6e' }};">{{ $trend['value'] ?? '+12.5%' }}</span>
                <span style="font-size: 12px; color: #6c757d;">{{ $trend['period'] ?? 'vs période précédente' }}</span>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    let lineChart{{ Str::studly($chartId) }} = null;
    let currentTimeRange = 'month';
    
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
        
        lineChart{{ Str::studly($chartId) }} = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($labels),
                datasets: @json($datasets)
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: {{ $showLegend ? 'true' : 'false' }},
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: '#1a56db',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                let value = context.raw;
                                return `${label}: ${new Intl.NumberFormat('fr-FR').format(value)} ${context.dataset.unit || ''}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: {{ $showGrid ? 'true' : 'false' }},
                            color: '#e2e8f0',
                            drawBorder: true
                        },
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value);
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                elements: {
                    line: {
                        tension: {{ $smooth ? '0.4' : '0' }},
                        borderWidth: 2
                    },
                    point: {
                        radius: 3,
                        hoverRadius: 5,
                        backgroundColor: 'white',
                        borderWidth: 2
                    }
                }
            }
        });
    });
    
    function downloadChart(chartId) {
        const canvas = document.getElementById(chartId);
        const link = document.createElement('a');
        link.download = `chart-${chartId}.png`;
        link.href = canvas.toDataURL();
        link.click();
    }
    
    function printChart(chartId) {
        const canvas = document.getElementById(chartId);
        const win = window.open();
        win.document.write('<img src="' + canvas.toDataURL() + '" style="width:100%"/>');
        win.print();
    }
    
    function toggleFullscreen(chartId) {
        const container = document.getElementById(chartId).parentElement;
        if (container.requestFullscreen) {
            container.requestFullscreen();
        }
    }
    
    function changeTimeRange(range) {
        currentTimeRange = range;
        // Mettre à jour les données via AJAX
        fetch(`/api/chart-data?range=${range}`)
            .then(response => response.json())
            .then(data => {
                if (lineChart{{ Str::studly($chartId) }}) {
                    lineChart{{ Str::studly($chartId) }}.data.labels = data.labels;
                    lineChart{{ Str::studly($chartId) }}.data.datasets = data.datasets;
                    lineChart{{ Str::studly($chartId) }}.update();
                }
            });
    }
    
    function toggleDataset(index) {
        const meta = lineChart{{ Str::studly($chartId) }}.getDatasetMeta(index);
        meta.hidden = !meta.hidden;
        lineChart{{ Str::studly($chartId) }}.update();
    }
</script>
@endpush