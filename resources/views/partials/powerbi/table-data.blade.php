{{-- resources/views/partials/powerbi/table-data.blade.php --}}
@props([
    'headers' => [],
    'rows' => [],
    'title' => null,
    'searchable' => false,
    'sortable' => false,
    'paginate' => false,
    'perPage' => 10,
    'actions' => false,
    'emptyMessage' => 'Aucune donnée disponible'
])

<div class="table-container" style="background: white; border-radius: 20px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
    @if($title)
        <div class="table-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
            <h3 class="table-title" style="font-size: 16px; font-weight: 600; margin: 0; padding-left: 12px; border-left: 3px solid #1a56db;">
                {{ $title }}
            </h3>
            @if($searchable)
                <div class="table-search">
                    <div class="input-group" style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="text" id="tableSearch" class="form-control" placeholder="Rechercher..." 
                               style="padding: 8px 12px 8px 35px; border-radius: 12px; border: 1px solid #e2e8f0; width: 250px;">
                    </div>
                </div>
            @endif
        </div>
    @endif
    
    <div class="table-responsive" style="overflow-x: auto;">
        <table class="powerbi-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-radius: 12px;">
                    @foreach($headers as $header)
                        <th style="padding: 14px 16px; text-align: left; font-size: 13px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; {{ $sortable ? 'cursor: pointer;' : '' }}"
                            @if($sortable) onclick="sortTable(this)" @endif>
                            {{ $header }}
                            @if($sortable)
                                <i class="fas fa-sort" style="margin-left: 5px; font-size: 11px;"></i>
                            @endif
                        </th>
                    @endforeach
                    @if($actions)
                        <th style="padding: 14px 16px; text-align: center; font-size: 13px; font-weight: 600; color: #475569;">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($rows as $row)
                    <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;" class="table-row">
                        @foreach($row as $cell)
                            <td style="padding: 14px 16px; font-size: 14px; color: #1e293b;">{!! $cell !!}</td>
                        @endforeach
                        @if($actions)
                            <td style="padding: 14px 16px; text-align: center;">
                                <div class="table-actions" style="display: flex; gap: 8px; justify-content: center;">
                                    {!! $actions !!}
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) + ($actions ? 1 : 0) }}" style="padding: 40px; text-align: center; color: #94a3b8;">
                            <i class="fas fa-database" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($paginate)
        <div class="table-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
            <div class="table-info" style="font-size: 13px; color: #6c757d;">
                Affichage de <span id="startCount">1</span> à <span id="endCount">{{ min($perPage, count($rows)) }}</span> sur <span id="totalCount">{{ count($rows) }}</span> entrées
            </div>
            <div class="table-pagination" style="display: flex; gap: 5px;">
                <button class="page-btn" onclick="previousPage()" style="padding: 6px 12px; border: 1px solid #e2e8f0; background: white; border-radius: 8px; cursor: pointer;">Précédent</button>
                <button class="page-btn" onclick="nextPage()" style="padding: 6px 12px; border: 1px solid #e2e8f0; background: white; border-radius: 8px; cursor: pointer;">Suivant</button>
            </div>
        </div>
    @endif
</div>

<style>
    .powerbi-table tbody tr:hover {
        background: rgba(26, 86, 219, 0.05);
        cursor: pointer;
    }
    
    .page-btn:hover {
        background: #f8fafc;
        border-color: #1a56db;
        color: #1a56db;
    }
</style>

@push('scripts')
<script>
    // Recherche dans le tableau
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.table-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
    
    // Tri du tableau
    let sortDirection = {};
    
    function sortTable(header) {
        const column = header.cellIndex;
        const table = header.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        sortDirection[column] = sortDirection[column] === 'asc' ? 'desc' : 'asc';
        
        rows.sort((a, b) => {
            const aValue = a.cells[column].textContent;
            const bValue = b.cells[column].textContent;
            
            if (sortDirection[column] === 'asc') {
                return aValue.localeCompare(bValue);
            } else {
                return bValue.localeCompare(aValue);
            }
        });
        
        rows.forEach(row => tbody.appendChild(row));
    }
    
    // Pagination
    let currentPage = 1;
    const rowsPerPage = {{ $perPage }};
    
    function updatePagination() {
        const rows = document.querySelectorAll('.table-row');
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        rows.forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });
        
        document.getElementById('startCount').textContent = start + 1;
        document.getElementById('endCount').textContent = Math.min(end, rows.length);
    }
    
    function previousPage() {
        if (currentPage > 1) {
            currentPage--;
            updatePagination();
        }
    }
    
    function nextPage() {
        const rows = document.querySelectorAll('.table-row');
        if (currentPage * rowsPerPage < rows.length) {
            currentPage++;
            updatePagination();
        }
    }
</script>
@endpush