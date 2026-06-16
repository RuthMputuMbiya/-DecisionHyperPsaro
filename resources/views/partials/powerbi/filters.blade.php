{{-- resources/views/partials/powerbi/filters.blade.php --}}
@props([
    'filters' => [],
    'applyButton' => true,
    'resetButton' => true,
    'onApply' => null
])

<div class="filters-container" style="background: white; border-radius: 16px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    <div class="filters-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h4 style="font-size: 14px; font-weight: 600; margin: 0;">
            <i class="fas fa-filter" style="margin-right: 8px; color: #1a56db;"></i> Filtres
        </h4>
        <div style="display: flex; gap: 10px;">
            @if($resetButton)
                <button class="btn-reset-filters" onclick="resetFilters()" style="background: none; border: none; color: #6c757d; cursor: pointer; font-size: 13px;">
                    <i class="fas fa-undo-alt"></i> Réinitialiser
                </button>
            @endif
        </div>
    </div>
    
    <div class="filters-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        @foreach($filters as $filter)
            <div class="filter-item">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">
                    <i class="{{ $filter['icon'] ?? 'fas fa-filter' }}" style="margin-right: 5px; font-size: 11px;"></i> {{ $filter['label'] }}
                </label>
                
                @if($filter['type'] === 'select')
                    <select class="filter-select" data-filter="{{ $filter['name'] }}" 
                            style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px;">
                        <option value="">Tous</option>
                        @foreach($filter['options'] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    
                @elseif($filter['type'] === 'date')
                    <input type="date" class="filter-date" data-filter="{{ $filter['name'] }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px;">
                    
                @elseif($filter['type'] === 'daterange')
                    <div style="display: flex; gap: 8px;">
                        <input type="date" class="filter-date-start" data-filter="{{ $filter['name'] }}_start" 
                               placeholder="Début" style="flex: 1; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px;">
                        <input type="date" class="filter-date-end" data-filter="{{ $filter['name'] }}_end" 
                               placeholder="Fin" style="flex: 1; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px;">
                    </div>
                    
                @elseif($filter['type'] === 'search')
                    <input type="text" class="filter-search" data-filter="{{ $filter['name'] }}" 
                           placeholder="{{ $filter['placeholder'] ?? 'Rechercher...' }}"
                           style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px;">
                @endif
            </div>
        @endforeach
    </div>
    
    @if($applyButton)
        <div class="filters-actions" style="margin-top: 20px; text-align: right;">
            <button class="btn-apply-filters" onclick="applyFilters()" 
                    style="background: linear-gradient(135deg, #1a56db, #7e3af2); color: white; border: none; padding: 8px 24px; border-radius: 10px; cursor: pointer; font-weight: 600;">
                <i class="fas fa-check"></i> Appliquer les filtres
            </button>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function getFilterValues() {
        const filters = {};
        
        // Récupérer les select
        document.querySelectorAll('.filter-select').forEach(select => {
            if (select.value) {
                filters[select.dataset.filter] = select.value;
            }
        });
        
        // Récupérer les dates
        document.querySelectorAll('.filter-date').forEach(date => {
            if (date.value) {
                filters[date.dataset.filter] = date.value;
            }
        });
        
        // Récupérer les plages de dates
        document.querySelectorAll('.filter-date-start').forEach(start => {
            const name = start.dataset.filter.replace('_start', '');
            const end = document.querySelector(`.filter-date-end[data-filter="${name}_end"]`);
            if (start.value || end?.value) {
                filters[name] = {
                    start: start.value,
                    end: end?.value || null
                };
            }
        });
        
        // Récupérer les recherches
        document.querySelectorAll('.filter-search').forEach(search => {
            if (search.value) {
                filters[search.dataset.filter] = search.value;
            }
        });
        
        return filters;
    }
    
    function applyFilters() {
        const filters = getFilterValues();
        
        // Stocker dans sessionStorage
        sessionStorage.setItem('appliedFilters', JSON.stringify(filters));
        
        // Émettre un événement
        window.dispatchEvent(new CustomEvent('filters-applied', { detail: filters }));
        
        // Appeler le callback si fourni
        @if($onApply)
            {{ $onApply }}(filters);
        @endif
        
        // Recharger la page avec les filtres
        const url = new URL(window.location.href);
        url.searchParams.set('filters', JSON.stringify(filters));
        window.location.href = url.toString();
    }
    
    function resetFilters() {
        document.querySelectorAll('.filter-select, .filter-date, .filter-search').forEach(input => {
            input.value = '';
        });
        
        document.querySelectorAll('.filter-date-start, .filter-date-end').forEach(input => {
            input.value = '';
        });
        
        applyFilters();
    }
    
    // Charger les filtres depuis l'URL
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const filtersParam = urlParams.get('filters');
        
        if (filtersParam) {
            try {
                const filters = JSON.parse(filtersParam);
                // Appliquer les filtres aux champs
                Object.keys(filters).forEach(key => {
                    const value = filters[key];
                    const input = document.querySelector(`[data-filter="${key}"]`);
                    if (input) input.value = value;
                });
            } catch(e) {}
        }
    });
</script>
@endpush