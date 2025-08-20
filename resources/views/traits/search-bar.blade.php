<div class="row mb-3">
    <div class="col-md-6">
        <div class="input-group">
            <input type="text" 
                   class="form-control" 
                   placeholder="Rechercher..." 
                   id="searchInput" 
                   value="{{ request('search') }}"
                   data-entity="{{ $entityName }}">
            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                <i class="fas fa-search"></i>
            </button>
            @if(request('search'))
                <button class="btn btn-outline-danger" type="button" id="clearSearchBtn" title="Effacer la recherche">
                    <i class="fas fa-times"></i>
                </button>
            @endif
        </div>
        @if(request('search'))
            <small class="text-muted mt-1 d-block">
                <i class="fas fa-info-circle me-1"></i>
                Recherche : "{{ request('search') }}"
            </small>
        @endif
    </div>
    
    <div class="col-md-6">
        <div class="d-flex justify-content-end">
            @if($config['features']['export'] ?? false)
                <div class="btn-group me-2" role="group">
                    <button type="button" class="btn btn-outline-success dropdown-toggle" 
                            data-bs-toggle="dropdown" title="Exporter">
                        <i class="fas fa-download me-1"></i>
                        Exporter
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="#" onclick="exportData('excel')">
                                <i class="fas fa-file-excel me-2 text-success"></i>
                                Excel
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="exportData('pdf')">
                                <i class="fas fa-file-pdf me-2 text-danger"></i>
                                PDF
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="exportData('csv')">
                                <i class="fas fa-file-csv me-2 text-info"></i>
                                CSV
                            </a>
                        </li>
                    </ul>
                </div>
            @endif
            
            <button type="button" class="btn btn-outline-secondary me-2" 
                    data-bs-toggle="modal" data-bs-target="#filtersModal" title="Filtres">
                <i class="fas fa-filter me-1"></i>
                Filtres
                @if(request()->has('filters'))
                    <span class="badge bg-primary ms-1">{{ count(array_filter(request('filters', []))) }}</span>
                @endif
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Recherche
document.getElementById('searchBtn')?.addEventListener('click', function() {
    performSearch();
});

document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        performSearch();
    }
});

document.getElementById('clearSearchBtn')?.addEventListener('click', function() {
    document.getElementById('searchInput').value = '';
    performSearch();
});

function performSearch() {
    const search = document.getElementById('searchInput').value;
    const url = new URL(window.location);
    
    if (search.trim()) {
        url.searchParams.set('search', search.trim());
    } else {
        url.searchParams.delete('search');
    }
    
    url.searchParams.delete('page'); // Reset pagination
    window.location = url;
}

// Export
function exportData(format) {
    const url = new URL(window.location);
    url.pathname = url.pathname + '/export';
    url.searchParams.set('format', format);
    
    window.open(url, '_blank');
}
</script>
@endpush