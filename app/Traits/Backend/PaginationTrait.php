<?php

namespace App\Traits\Backend;

trait PaginationTrait
{
    /**
     * Obtenir le nombre d'éléments par page
     */
    protected function getPerPageCount(): int
    {
        return $this->entityConfig['features']['pagination'] ?? 15;
    }

    /**
     * Obtenir les options de pagination
     */
    protected function getPerPageOptions(): array
    {
        return [
            10 => '10 par page',
            15 => '15 par page',
            25 => '25 par page',
            50 => '50 par page',
            100 => '100 par page'
        ];
    }

    /**
     * Obtenir les informations de pagination
     */
    protected function getPaginationInfo($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}