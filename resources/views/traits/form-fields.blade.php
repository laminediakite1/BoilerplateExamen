@foreach($config['display']['form'] as $field)
    @php
        $fieldConfig = $config['fields'][$field] ?? [];
        $type = $fieldConfig['type'] ?? 'text';
        $label = $fieldConfig['label'] ?? ucfirst($field);
        $required = $fieldConfig['required'] ?? false;
        $value = old($field, $item?->{$field} ?? $fieldConfig['default'] ?? '');
        
        // Pour les mots de passe, ne pas pré-remplir
        if ($type === 'password') {
            $value = '';
        }
    @endphp
    
    <div class="mb-3">
        <label for="{{ $field }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
        
        @switch($type)
            @case('text')
            @case('email')
            @case('url')
            @case('number')
                <input type="{{ $type }}" 
                       name="{{ $field }}" 
                       id="{{ $field }}" 
                       class="form-control @error($field) is-invalid @enderror"
                       value="{{ $value }}"
                       @if($required) required @endif
                       @if($type === 'number') step="0.01" min="0" @endif>
                @break
                
            @case('password')
                <input type="password" 
                       name="{{ $field }}" 
                       id="{{ $field }}" 
                       class="form-control @error($field) is-invalid @enderror"
                       @if($required && !isset($item)) required @endif>
                @if(isset($item))
                    <div class="form-text">Laissez vide pour conserver le mot de passe actuel</div>
                @endif
                @break
                
            @case('textarea')
                <textarea name="{{ $field }}" 
                          id="{{ $field }}" 
                          class="form-control @error($field) is-invalid @enderror" 
                          rows="4"
                          @if($required) required @endif>{{ $value }}</textarea>
                @break
                
            @case('select')
                <select name="{{ $field }}" 
                        id="{{ $field }}" 
                        class="form-select @error($field) is-invalid @enderror"
                        @if($required) required @endif>
                    @if(!$required)
                        <option value="">Sélectionnez une option</option>
                    @endif
                    
                    @if(isset($fieldConfig['relation']))
                        @foreach($options[$field] ?? [] as $option)
                            <option value="{{ $option->id }}" 
                                    {{ $value == $option->id ? 'selected' : '' }}>
                                {{ $option->name ?? $option->display_name ?? $option->title }}
                            </option>
                        @endforeach
                    @elseif(isset($fieldConfig['options']))
                        @foreach($fieldConfig['options'] as $optionValue => $optionLabel)
                            <option value="{{ $optionValue }}" 
                                    {{ $value == $optionValue ? 'selected' : '' }}>
                                {{ $optionLabel }}
                            </option>
                        @endforeach
                    @endif
                </select>
                @break
                
            @case('checkbox')
                <div class="form-check">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input type="checkbox" 
                           name="{{ $field }}" 
                           id="{{ $field }}" 
                           class="form-check-input @error($field) is-invalid @enderror"
                           value="1"
                           {{ $value ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $field }}">
                        {{ $label }}
                    </label>
                </div>
                @break
                
            @case('file')
            @case('image')
                <input type="file" 
                       name="{{ $field }}" 
                       id="{{ $field }}" 
                       class="form-control @error($field) is-invalid @enderror"
                       @if($type === 'image') accept="image/*" @endif
                       @if($required && !isset($item)) required @endif>
                       
                @if(isset($item) && $item->{$field})
                    <div class="mt-2">
                        <small class="text-muted">Fichier actuel :</small>
                        @if($type === 'image' && in_array(strtolower(pathinfo($item->{$field}, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                            <div class="mt-1">
                                <img src="{{ Storage::url($item->{$field}) }}" 
                                     alt="Image actuelle" 
                                     class="img-thumbnail" 
                                     style="max-width: 150px; max-height: 150px;">
                            </div>
                        @else
                            <div class="mt-1">
                                <a href="{{ Storage::url($item->{$field}) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-download me-1"></i>
                                    Télécharger le fichier actuel
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
                @break
                
            @case('date')
                <input type="date" 
                       name="{{ $field }}" 
                       id="{{ $field }}" 
                       class="form-control @error($field) is-invalid @enderror"
                       value="{{ $value instanceof \Carbon\Carbon ? $value->format('Y-m-d') : $value }}"
                       @if($required) required @endif>
                @break
                
            @case('datetime')
                <input type="datetime-local" 
                       name="{{ $field }}" 
                       id="{{ $field }}" 
                       class="form-control @error($field) is-invalid @enderror"
                       value="{{ $value instanceof \Carbon\Carbon ? $value->format('Y-m-d\TH:i') : $value }}"
                       @if($required) required @endif>
                @break
                
            @default
                <input type="text" 
                       name="{{ $field }}" 
                       id="{{ $field }}" 
                       class="form-control @error($field) is-invalid @enderror"
                       value="{{ $value }}"
                       @if($required) required @endif>
                @break
        @endswitch
        
        @error($field)
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
        
        @if(isset($fieldConfig['help']))
            <div class="form-text">{{ $fieldConfig['help'] }}</div>
        @endif
    </div>
@endforeach