<?php

namespace App\Traits\Backend;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

trait FilterTrait
{
    /**
     * Appliquer les filtres à la requête
     */
    protected function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = $request->get('filters', []);
        
        if (empty($filters)) {
            return $query;
        }
        
        foreach ($filters as $field => $value) {
            if (empty($value) && $value !== '0') {
                continue;
            }
            
            $fieldConfig = $this->entityConfig['fields'][$field] ?? null;
            
            if (!$fieldConfig || !($fieldConfig['filterable'] ?? false)) {
                continue;
            }
            
            $this->applyFieldFilter($query, $field, $value, $fieldConfig);
        }
        
        return $query;
    }

    /**
     * Appliquer un filtre spécifique
     */
    protected function applyFieldFilter(Builder $query, string $field, $value, array $config): void
    {
        $type = $config['type'] ?? 'text';
        
        switch ($type) {
            case 'select':
                $this->applySelectFilter($query, $field, $value, $config);
                break;
                
            case 'date':
                $this->applyDateFilter($query, $field, $value);
                break;
                
            case 'daterange':
                $this->applyDateRangeFilter($query, $field, $value);
                break;
                
            case 'number':
                $this->applyNumberFilter($query, $field, $value);
                break;
                
            case 'boolean':
            case 'checkbox':
                $this->applyBooleanFilter($query, $field, $value);
                break;
                
            default:
                $this->applyTextFilter($query, $field, $value);
                break;
        }
    }

    /**
     * Appliquer un filtre de sélection
     */
    protected function applySelectFilter(Builder $query, string $field, $value, array $config): void
    {
        if (isset($config['relation'])) {
            // Filtre sur une relation
            $query->whereHas($config['relation'], function ($q) use ($value) {
                $q->where('id', $value);
            });
        } else {
            // Filtre simple
            $query->where($field, $value);
        }
    }

    /**
     * Appliquer un filtre de date
     */
    protected function applyDateFilter(Builder $query, string $field, $value): void
    {
        try {
            $date = Carbon::parse($value);
            $query->whereDate($field, $date->format('Y-m-d'));
        } catch (\Exception $e) {
            // Date invalide, ignorer le filtre
        }
    }

    /**
     * Appliquer un filtre de plage de dates
     */
    protected function applyDateRangeFilter(Builder $query, string $field, $value): void
    {
        if (!is_array($value) || count($value) !== 2) {
            return;
        }
        
        try {
            $startDate = Carbon::parse($value[0]);
            $endDate = Carbon::parse($value[1]);
            
            $query->whereBetween($field, [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ]);
        } catch (\Exception $e) {
            // Dates invalides, ignorer le filtre
        }
    }

    /**
     * Appliquer un filtre numérique
     */
    protected function applyNumberFilter(Builder $query, string $field, $value): void
    {
        if (is_array($value)) {
            // Plage numérique
            if (isset($value['min']) && is_numeric($value['min'])) {
                $query->where($field, '>=', $value['min']);
            }
            if (isset($value['max']) && is_numeric($value['max'])) {
                $query->where($field, '<=', $value['max']);
            }
        } elseif (is_numeric($value)) {
            // Valeur exacte
            $query->where($field, $value);
        }
    }

    /**
     * Appliquer un filtre booléen
     */
    protected function applyBooleanFilter(Builder $query, string $field, $value): void
    {
        $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $query->where($field, $boolValue);
    }

    /**
     * Appliquer un filtre texte
     */
    protected function applyTextFilter(Builder $query, string $field, $value): void
    {
        $query->where($field, 'LIKE', "%{$value}%");
    }

    /**
     * Obtenir les options pour les filtres
     */
    protected function getFilterOptions(): array
    {
        $options = [];
        
        foreach ($this->entityConfig['fields'] as $field => $config) {
            if (!($config['filterable'] ?? false)) {
                continue;
            }
            
            $type = $config['type'] ?? 'text';
            
            if ($type === 'select') {
                if (isset($config['relation'])) {
                    // Charger les options depuis une relation
                    $relationModel = "App\\Models\\" . ucfirst($config['relation']);
                    if (class_exists($relationModel)) {
                        $options[$field] = app($relationModel)->all();
                    }
                } elseif (isset($config['options'])) {
                    // Options définies dans la configuration
                    $options[$field] = $config['options'];
                }
            } elseif ($type === 'boolean' || $type === 'checkbox') {
                $options[$field] = [
                    '1' => 'Oui',
                    '0' => 'Non'
                ];
            }
        }
        
        return $options;
    }

    /**
     * Obtenir les filtres actifs
     */
    protected function getActiveFilters(Request $request): array
    {
        $filters = $request->get('filters', []);
        $active = [];
        
        foreach ($filters as $field => $value) {
            if (empty($value) && $value !== '0') {
                continue;
            }
            
            $fieldConfig = $this->entityConfig['fields'][$field] ?? null;
            
            if (!$fieldConfig || !($fieldConfig['filterable'] ?? false)) {
                continue;
            }
            
            $label = $fieldConfig['label'] ?? $field;
            $displayValue = $this->getFilterDisplayValue($field, $value, $fieldConfig);
            
            $active[$field] = [
                'label' => $label,
                'value' => $value,
                'display' => $displayValue
            ];
        }
        
        return $active;
    }

    /**
     * Obtenir la valeur d'affichage d'un filtre
     */
    protected function getFilterDisplayValue(string $field, $value, array $config): string
    {
        $type = $config['type'] ?? 'text';
        
        switch ($type) {
            case 'select':
                if (isset($config['options'][$value])) {
                    return $config['options'][$value];
                } elseif (isset($config['relation'])) {
                    $relationModel = "App\\Models\\" . ucfirst($config['relation']);
                    if (class_exists($relationModel)) {
                        $item = app($relationModel)->find($value);
                        return $item?->name ?? $value;
                    }
                }
                return $value;
                
            case 'boolean':
            case 'checkbox':
                return $value ? 'Oui' : 'Non';
                
            case 'date':
                try {
                    return Carbon::parse($value)->format('d/m/Y');
                } catch (\Exception $e) {
                    return $value;
                }
                
            default:
                return $value;
        }
    }

    /**
     * Sauvegarder les filtres dans la session
     */
    protected function saveFiltersToSession(array $filters): void
    {
        $sessionKey = "filters.{$this->entityName}";
        session([$sessionKey => $filters]);
    }

    /**
     * Récupérer les filtres depuis la session
     */
    protected function getFiltersFromSession(): array
    {
        $sessionKey = "filters.{$this->entityName}";
        return session($sessionKey, []);
    }

    /**
     * Effacer les filtres de la session
     */
    protected function clearFiltersFromSession(): void
    {
        $sessionKey = "filters.{$this->entityName}";
        session()->forget($sessionKey);
    }
}