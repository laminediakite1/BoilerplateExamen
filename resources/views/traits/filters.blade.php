<!-- Modal des filtres -->
<div class="modal fade" id="filtersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-filter me-2"></i>
                    Filtres
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="filtersForm" method="GET">
                <div class="modal-body">
                    <div class="row">
                        @foreach($config['fields'] as $field => $fieldConfig)
                            @if($fieldConfig['filterable'] ?? false)
                                <div class="col-md-6 mb-3">
                                    <label for="filter_{{ $field }}" class="form-label">
                                        {{ $fieldConfig['label'] }}
                                    </label>
                                    
                                    @if($fieldConfig['type'] === 'select')
                                        <select name="filters[{{ $field }}]" 
                                                id="filter_{{ $field }}" 
                                                class="form-select">
                                            <option value="">Tous</option>
                                            @if(isset($filterOptions[$field]))
                                                @if(is_array($filterOptions[$field]) && isset($filterOptions[$field][0]) && is_object($filterOptions[$field][0]))
                                                    @foreach($filterOptions[$field] as $option)
                                                        <option value="{{ $option->id }}" 
                                                                {{ request("filters.{$field}") == $option->id ? 'selected' : '' }}>
                                                            {{ $option->name ?? $option->display_name ?? $option->title }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    @foreach($filterOptions[$field] as $value => $label)
                                                        <option value="{{ $value }}" 
                                                                {{ request("filters.{$field}") == $value ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </select>
                                        
                                    @elseif(in_array($fieldConfig['type'], ['boolean', 'checkbox']))
                                        <select name="filters[{{ $field }}]" 
                                                id="filter_{{ $field }}" 
                                                class="form-select">
                                            <option value="">Tous</option>
                                            <option value="1" {{ request("filters.{$field}") === '1' ? 'selected' : '' }}>
                                                Oui
                                            </option>
                                            <option value="0" {{ request("filters.{$field}") === '0' ? 'selected' : '' }}>
                                                Non
                                            </option>
                                        </select>
                                        
                                    @elseif($fieldConfig['type'] === 'date')
                                        <input type="date" 
                                               name="filters[{{ $field }}]" 
                                               id="filter_{{ $field }}" 
                                               class="form-control"
                                               value="{{ request("filters.{$field}") }}">
                                               
                                    @elseif($fieldConfig['type'] === 'number')
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="number" 
                                                       name="filters[{{ $field }}][min]" 
                                                       class="form-control" 
                                                       placeholder="Min"
                                                       value="{{ request("filters.{$field}.min") }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="number" 
                                                       name="filters[{{ $field }}][max]" 
                                                       class="form-control" 
                                                       placeholder="Max"
                                                       value="{{ request("filters.{$field}.max") }}">
                                            </div>
                                        </div>
                                        
                                    @else
                                        <input type="text" 
                                               name="filters[{{ $field }}]" 
                                               id="filter_{{ $field }}" 
                                               class="form-control"
                                               value="{{ request("filters.{$field}") }}">
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                    
                    @if(request()->has('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                        <i class="fas fa-eraser me-1"></i>
                        Effacer
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Fermer
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>
                        Appliquer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(request()->has('filters') && count(array_filter(request('filters', []))) > 0)
    <div class="mb-3">
        <div class="d-flex flex-wrap align-items-center">
            <span class="text-muted me-2">Filtres actifs :</span>
            @foreach(request('filters', []) as $field => $value)
                @if(!empty($value) || $value === '0')
                    @php
                        $fieldConfig = $config['fields'][$field] ?? null;
                        $label = $fieldConfig['label'] ?? $field;
                        
                        // Obtenir la valeur d'affichage
                        if ($fieldConfig['type'] === 'select' && isset($filterOptions[$field])) {
                            if (is_array($filterOptions[$field]) && isset($filterOptions[$field][0]) && is_object($filterOptions[$field][0])) {
                                $option = collect($filterOptions[$field])->firstWhere('id', $value);
                                $displayValue = $option->name ?? $option->display_name ?? $value;
                            } else {
                                $displayValue = $filterOptions[$field][$value] ?? $value;
                            }
                        } elseif (in_array($fieldConfig['type'], ['boolean', 'checkbox'])) {
                            $displayValue = $value ? 'Oui' : 'Non';
                        } elseif (is_array($value)) {
                            $parts = array_filter($value);
                            $displayValue = implode(' - ', $parts);
                        } else {
                            $displayValue = $value;
                        }
                    @endphp
                    
                    <span class="badge bg-primary me-2 mb-1">
                        {{ $label }}: {{ $displayValue }}
                        <a href="#" class="text-white ms-1" onclick="removeFilter('{{ $field }}')">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif
            @endforeach
        </div>
    </div>
@endif

@push('scripts')
<script>
function clearFilters() {
    const form = document.getElementById('filtersForm');
    const inputs = form.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        if (input.type === 'checkbox' || input.type === 'radio') {
            input.checked = false;
        } else {
            input.value = '';
        }
    });
    
    form.submit();
}

function removeFilter(field) {
    const url = new URL(window.location);
    const filters = new URLSearchParams(url.search);
    
    // Supprimer le filtre spécifique
    const keysToRemove = [];
    for (const [key] of filters) {
        if (key.startsWith(`filters[${field}]`)) {
            keysToRemove.push(key);
        }
    }
    
    keysToRemove.forEach(key => filters.delete(key));
    
    // Reconstruire l'URL
    url.search = filters.toString();
    window.location = url;
}
</script>
@endpush