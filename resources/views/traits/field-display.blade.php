@php
    // Obtenir la valeur du champ
    if (strpos($field, '.') !== false) {
        // Champ relationnel (ex: category.name)
        $parts = explode('.', $field);
        $value = $item;
        foreach ($parts as $part) {
            $value = $value?->{$part};
        }
    } else {
        $value = $item->{$field};
    }
    
    // Configuration du champ
    $fieldConfig = $config['fields'][$field] ?? [];
    $type = $fieldConfig['type'] ?? 'text';
@endphp

@switch($type)
    @case('boolean')
    @case('checkbox')
        @if($value)
            <span class="badge bg-success">
                <i class="fas fa-check me-1"></i>
                Oui
            </span>
        @else
            <span class="badge bg-secondary">
                <i class="fas fa-times me-1"></i>
                Non
            </span>
        @endif
        @break
        
    @case('select')
        @if(isset($fieldConfig['options'][$value]))
            {{ $fieldConfig['options'][$value] }}
        @else
            {{ $value }}
        @endif
        @break
        
    @case('date')
        @if($value)
            <span title="{{ $value->format('d/m/Y H:i:s') }}">
                {{ $value->format('d/m/Y') }}
            </span>
        @else
            <span class="text-muted">-</span>
        @endif
        @break
        
    @case('datetime')
        @if($value)
            <span title="{{ $value->format('d/m/Y H:i:s') }}">
                {{ $value->format('d/m/Y H:i') }}
            </span>
        @else
            <span class="text-muted">-</span>
        @endif
        @break
        
    @case('file')
    @case('image')
        @if($value)
            @if(in_array(strtolower(pathinfo($value, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                <img src="{{ Storage::url($value) }}" 
                     alt="Image" 
                     class="img-thumbnail" 
                     style="max-width: 50px; max-height: 50px;">
            @else
                <a href="{{ Storage::url($value) }}" target="_blank" class="btn btn-sm btn-outline-info">
                    <i class="fas fa-file me-1"></i>
                    Fichier
                </a>
            @endif
        @else
            <span class="text-muted">-</span>
        @endif
        @break
        
    @case('email')
        @if($value)
            <a href="mailto:{{ $value }}">{{ $value }}</a>
        @else
            <span class="text-muted">-</span>
        @endif
        @break
        
    @case('url')
        @if($value)
            <a href="{{ $value }}" target="_blank">{{ Str::limit($value, 30) }}</a>
        @else
            <span class="text-muted">-</span>
        @endif
        @break
        
    @case('number')
    @case('decimal')
        @if(is_numeric($value))
            {{ number_format($value, 2) }}
        @else
            {{ $value ?? '-' }}
        @endif
        @break
        
    @case('currency')
        @if(is_numeric($value))
            {{ number_format($value, 2) }} €
        @else
            {{ $value ?? '-' }}
        @endif
        @break
        
    @case('textarea')
        @if($value)
            <span title="{{ $value }}">
                {{ Str::limit($value, 50) }}
            </span>
        @else
            <span class="text-muted">-</span>
        @endif
        @break
        
    @default
        @if($field === 'status' && isset($fieldConfig['options']))
            @php
                $statusClass = match($value) {
                    'active', 'actif' => 'success',
                    'inactive', 'inactif' => 'secondary',
                    'draft', 'brouillon' => 'warning',
                    'pending', 'en_attente' => 'info',
                    default => 'secondary'
                };
            @endphp
            <span class="badge bg-{{ $statusClass }}">
                {{ $fieldConfig['options'][$value] ?? ucfirst($value) }}
            </span>
        @elseif($field === 'role_name' || strpos($field, 'role') !== false)
            @if($value)
                <span class="badge bg-primary">{{ $value }}</span>
            @else
                <span class="text-muted">-</span>
            @endif
        @elseif(is_string($value) && strlen($value) > 50)
            <span title="{{ $value }}">
                {{ Str::limit($value, 50) }}
            </span>
        @else
            {{ $value ?? '-' }}
        @endif
        @break
@endswitch