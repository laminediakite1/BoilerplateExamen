<?php

namespace App\Traits\Backend;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait SearchTrait
{
    /**
     * Appliquer la recherche à la requête
     */
    protected function applySearch(Builder $query, Request $request): Builder
    {
        $search = $request->get('search');
        
        if (empty($search)) {
            return $query;
        }
        
        // Obtenir les champs recherchables
        $searchableFields = $this->getSearchableFields();
        
        if (empty($searchableFields)) {
            return $query;
        }
        
        $query->where(function ($q) use ($search, $searchableFields) {
            foreach ($searchableFields as $field) {
                if (strpos($field, '.') !== false) {
                    // Recherche dans une relation
                    $this->addRelationSearch($q, $field, $search);
                } else {
                    // Recherche dans un champ de la table principale
                    $q->orWhere($field, 'LIKE', "%{$search}%");
                }
            }
        });
        
        return $query;
    }

    /**
     * Ajouter une recherche dans une relation
     */
    protected function addRelationSearch(Builder $query, string $field, string $search): void
    {
        $parts = explode('.', $field, 2);
        $relation = $parts[0];
        $column = $parts[1];
        
        $query->orWhereHas($relation, function ($q) use ($column, $search) {
            $q->where($column, 'LIKE', "%{$search}%");
        });
    }

    /**
     * Obtenir les champs recherchables depuis la configuration
     */
    protected function getSearchableFields(): array
    {
        $searchable = [];
        
        foreach ($this->entityConfig['fields'] as $field => $settings) {
            if ($settings['searchable'] ?? false) {
                $searchable[] = $field;
            }
        }
        
        // Ajouter les champs de recherche des relations
        if (!empty($this->entityConfig['search']['relations'])) {
            foreach ($this->entityConfig['search']['relations'] as $relation => $fields) {
                foreach ($fields as $field) {
                    $searchable[] = "{$relation}.{$field}";
                }
            }
        }
        
        return $searchable;
    }

    /**
     * Obtenir les statistiques de recherche
     */
    protected function getSearchStats(Builder $query, string $search): array
    {
        if (empty($search)) {
            return [
                'total' => $query->count(),
                'search_term' => null,
                'found' => null
            ];
        }
        
        $originalQuery = clone $query;
        $totalCount = $originalQuery->count();
        $searchCount = $query->count();
        
        return [
            'total' => $totalCount,
            'search_term' => $search,
            'found' => $searchCount
        ];
    }

    /**
     * Suggérer des termes de recherche similaires
     */
    protected function getSearchSuggestions(string $search): array
    {
        // Implémenter la logique de suggestions si nécessaire
        // Par exemple, rechercher des termes similaires dans la base
        return [];
    }

    /**
     * Sauvegarder le terme de recherche dans la session
     */
    protected function saveSearchToSession(string $search): void
    {
        $sessionKey = "search.{$this->entityName}";
        session([$sessionKey => $search]);
    }

    /**
     * Récupérer le dernier terme de recherche depuis la session
     */
    protected function getSearchFromSession(): ?string
    {
        $sessionKey = "search.{$this->entityName}";
        return session($sessionKey);
    }

    /**
     * Effacer le terme de recherche de la session
     */
    protected function clearSearchFromSession(): void
    {
        $sessionKey = "search.{$this->entityName}";
        session()->forget($sessionKey);
    }
}