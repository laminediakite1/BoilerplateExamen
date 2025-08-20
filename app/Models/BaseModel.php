<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    /**
     * Get the model's configuration
     */
    public static function getEntityConfig(): array
    {
        $entityName = strtolower(class_basename(static::class));
        $configPath = "entities.{$entityName}";
        
        if (!config()->has($configPath)) {
            throw new \Exception("Configuration not found for entity: {$entityName}");
        }
        
        return config($configPath);
    }

    /**
     * Get searchable fields from configuration
     */
    public function getSearchableFields(): array
    {
        $config = static::getEntityConfig();
        $searchable = [];
        
        foreach ($config['fields'] as $field => $settings) {
            if ($settings['searchable'] ?? false) {
                $searchable[] = $field;
            }
        }
        
        return $searchable;
    }

    /**
     * Get filterable fields from configuration
     */
    public function getFilterableFields(): array
    {
        $config = static::getEntityConfig();
        $filterable = [];
        
        foreach ($config['fields'] as $field => $settings) {
            if ($settings['filterable'] ?? false) {
                $filterable[$field] = $settings;
            }
        }
        
        return $filterable;
    }

    /**
     * Get field validation rules
     */
    public static function getValidationRules(bool $isUpdate = false): array
    {
        $config = static::getEntityConfig();
        $rules = [];
        
        foreach ($config['fields'] as $field => $settings) {
            if (isset($settings['validation'])) {
                $rule = $settings['validation'];
                
                // Modifier les règles unique pour les mises à jour
                if ($isUpdate && strpos($rule, 'unique:') !== false) {
                    $rule = str_replace('unique:', 'unique:', $rule);
                }
                
                $rules[$field] = $rule;
            }
        }
        
        return $rules;
    }
}