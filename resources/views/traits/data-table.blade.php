<!-- Tableau responsive avec actions -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Liste des {{ ucfirst($entityName) }}
            <span class="badge bg-secondary ms-2">{{ $items->total() }}</span>
        </h5>
        
        @if($config['permissions']['create'] ?? false)
            <a href="{{ route("admin.{$entityName}.create") }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Ajouter
            </a>
        @endif
    </div>
    
    <div class="card-body p-0">
        @if($items->count() > 0)
            <!-- Actions en masse -->
            @if(!empty($config['features']['bulk_actions']))
                <div class="bg-light p-3 border-bottom" id="bulkActions" style="display: none;">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <span id="selectedCount">0</span> élément(s) sélectionné(s)
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-end">
                                <select id="bulkActionSelect" class="form-select me-2" style="width: auto;">
                                    <option value="">Choisir une action...</option>
                                    @foreach($config['features']['bulk_actions'] as $action)
                                        <option value="{{ $action }}">
                                            @switch($action)
                                                @case('delete')
                                                    Supprimer
                                                    @break
                                                @case('activate')
                                                    Activer
                                                    @break
                                                @case('deactivate')
                                                    Désactiver
                                                    @break
                                                @case('restore')
                                                    Restaurer
                                                    @break
                                                @default
                                                    {{ ucfirst($action) }}
                                            @endswitch
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-warning me-2" onclick="executeBulkAction()">
                                    Exécuter
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="clearSelection()">
                                    Annuler
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            @if(!empty($config['features']['bulk_actions']))
                                <th width="40">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </div>
                                </th>
                            @endif
                            
                            @foreach($config['display']['list'] as $field)
                                @php
                                    $fieldConfig = null;
                                    $label = ucfirst($field);
                                    
                                    // Gérer les champs relationnels (ex: category.name)
                                    if (strpos($field, '.') !== false) {
                                        $parts = explode('.', $field, 2);
                                        $relationField = $parts[0];
                                        $relationColumn = $parts[1];
                                        
                                        if (isset($config['fields'][$relationField])) {
                                            $relationConfig = $config['fields'][$relationField];
                                            $label = $relationConfig['label'] ?? ucfirst($relationField);
                                        }
                                    } else {
                                        $fieldConfig = $config['fields'][$field] ?? null;
                                        $label = $fieldConfig['label'] ?? ucfirst($field);
                                    }
                                    
                                    $sortable = $fieldConfig['sortable'] ?? false;
                                @endphp
                                
                                <th>
                                    @if($sortable)
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => $field, 'order' => request('sort') === $field && request('order') === 'asc' ? 'desc' : 'asc']) }}" 
                                           class="text-decoration-none text-dark">
                                            {{ $label }}
                                            @if(request('sort') === $field)
                                                <i class="fas fa-sort-{{ request('order') === 'desc' ? 'down' : 'up' }} ms-1"></i>
                                            @else
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            @endif
                                        </a>
                                    @else
                                        {{ $label }}
                                    @endif
                                </th>
                            @endforeach
                            
                            <th width="150" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                @if(!empty($config['features']['bulk_actions']))
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input item-checkbox" 
                                                   value="{{ $item->id }}">
                                        </div>
                                    </td>
                                @endif
                                
                                @foreach($config['display']['list'] as $field)
                                    <td>
                                        @include('traits.field-display', ['item' => $item, 'field' => $field, 'config' => $config])
                                    </td>
                                @endforeach
                                
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        @if($config['permissions']['view'] ?? false)
                                            <a href="{{ route("admin.{$entityName}.show", $item->id) }}" 
                                               class="btn btn-sm btn-outline-info" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                        
                                        @if($config['permissions']['edit'] ?? false)
                                            <a href="{{ route("admin.{$entityName}.edit", $item->id) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        
                                        @if($config['permissions']['delete'] ?? false)
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Supprimer"
                                                    onclick="confirmDelete('{{ route("admin.{$entityName}.destroy", $item->id) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Aucun élément trouvé</p>
                @if($config['permissions']['create'] ?? false)
                    <a href="{{ route("admin.{$entityName}.create") }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-1"></i>
                        Créer le premier élément
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cet élément ?</p>
                <p class="text-muted small">Cette action ne peut pas être annulée.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedItems = [];

// Gestion de la sélection
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelection();
});

document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelection);
});

function updateSelection() {
    selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'))
                         .map(cb => cb.value);
    
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selectedItems.length > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = selectedItems.length;
    } else {
        bulkActions.style.display = 'none';
    }
}

function clearSelection() {
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateSelection();
}

// Exécution des actions en masse
function executeBulkAction() {
    const action = document.getElementById('bulkActionSelect').value;
    
    if (!action) {
        alert('Veuillez sélectionner une action.');
        return;
    }
    
    if (selectedItems.length === 0) {
        alert('Veuillez sélectionner au moins un élément.');
        return;
    }
    
    let confirmMessage = `Êtes-vous sûr de vouloir ${action} ${selectedItems.length} élément(s) ?`;
    
    if (action === 'delete') {
        confirmMessage = `Êtes-vous sûr de vouloir supprimer ${selectedItems.length} élément(s) ?\nCette action ne peut pas être annulée.`;
    }
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Exécuter l'action via AJAX
    fetch(`{{ route("admin.{$entityName}.index") }}/bulk-action`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: action,
            ids: selectedItems
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur lors de l\'exécution de l\'action.');
        console.error(error);
    });
}

// Confirmation de suppression individuelle
function confirmDelete(url) {
    document.getElementById('deleteForm').action = url;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush