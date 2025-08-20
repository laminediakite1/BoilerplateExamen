<?php

namespace App\Traits\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait BulkActionTrait
{
    /**
     * Exécuter une action en masse
     */
    public function bulkAction(Request $request)
    {
        $this->initializeCrudTrait();
        
        $action = $request->get('action');
        $ids = $request->get('ids', []);
        
        if (empty($action) || empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Action ou IDs manquants.'
            ]);
        }
        
        // Vérifier si l'action est autorisée
        $allowedActions = $this->entityConfig['features']['bulk_actions'] ?? [];
        
        if (!in_array($action, $allowedActions)) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée.'
            ]);
        }
        
        try {
            DB::beginTransaction();
            
            $result = $this->executeBulkAction($action, $ids);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'affected' => $result['affected']
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'exécution : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Exécuter l'action en masse spécifique
     */
    protected function executeBulkAction(string $action, array $ids): array
    {
        switch ($action) {
            case 'delete':
                return $this->bulkDelete($ids);
                
            case 'activate':
                return $this->bulkActivate($ids);
                
            case 'deactivate':
                return $this->bulkDeactivate($ids);
                
            case 'restore':
                return $this->bulkRestore($ids);
                
            default:
                throw new \Exception("Action non supportée : {$action}");
        }
    }

    /**
     * Suppression en masse
     */
    protected function bulkDelete(array $ids): array
    {
        $this->checkPermission('delete');
        
        $affected = $this->model->whereIn('id', $ids)->delete();
        
        return [
            'affected' => $affected,
            'message' => "{$affected} élément(s) supprimé(s) avec succès."
        ];
    }

    /**
     * Activation en masse
     */
    protected function bulkActivate(array $ids): array
    {
        $this->checkPermission('edit');
        
        $statusField = $this->getStatusField();
        
        if (!$statusField) {
            throw new \Exception('Aucun champ de statut défini pour cette entité.');
        }
        
        $affected = $this->model->whereIn('id', $ids)->update([
            $statusField => $this->getActiveStatusValue()
        ]);
        
        return [
            'affected' => $affected,
            'message' => "{$affected} élément(s) activé(s) avec succès."
        ];
    }

    /**
     * Désactivation en masse
     */
    protected function bulkDeactivate(array $ids): array
    {
        $this->checkPermission('edit');
        
        $statusField = $this->getStatusField();
        
        if (!$statusField) {
            throw new \Exception('Aucun champ de statut défini pour cette entité.');
        }
        
        $affected = $this->model->whereIn('id', $ids)->update([
            $statusField => $this->getInactiveStatusValue()
        ]);
        
        return [
            'affected' => $affected,
            'message' => "{$affected} élément(s) désactivé(s) avec succès."
        ];
    }

    /**
     * Restauration en masse (pour soft deletes)
     */
    protected function bulkRestore(array $ids): array
    {
        $this->checkPermission('edit');
        
        if (!method_exists($this->model, 'restore')) {
            throw new \Exception('Cette entité ne supporte pas la restauration.');
        }
        
        $affected = $this->model->onlyTrashed()->whereIn('id', $ids)->restore();
        
        return [
            'affected' => $affected,
            'message' => "{$affected} élément(s) restauré(s) avec succès."
        ];
    }

    /**
     * Obtenir le champ de statut
     */
    protected function getStatusField(): ?string
    {
        // Rechercher un champ de statut dans la configuration
        foreach ($this->entityConfig['fields'] as $field => $config) {
            if (in_array($field, ['status', 'is_active', 'active']) && 
                isset($config['options'])) {
                return $field;
            }
        }
        
        return null;
    }

    /**
     * Obtenir la valeur pour "actif"
     */
    protected function getActiveStatusValue()
    {
        $statusField = $this->getStatusField();
        
        if (!$statusField) {
            return null;
        }
        
        $options = $this->entityConfig['fields'][$statusField]['options'] ?? [];
        
        // Rechercher les valeurs communes pour "actif"
        $activeKeys = ['active', 'actif', '1', 1, true];
        
        foreach ($activeKeys as $key) {
            if (isset($options[$key])) {
                return $key;
            }
        }
        
        // Prendre la première option par défaut
        return array_key_first($options);
    }

    /**
     * Obtenir la valeur pour "inactif"
     */
    protected function getInactiveStatusValue()
    {
        $statusField = $this->getStatusField();
        
        if (!$statusField) {
            return null;
        }
        
        $options = $this->entityConfig['fields'][$statusField]['options'] ?? [];
        
        // Rechercher les valeurs communes pour "inactif"
        $inactiveKeys = ['inactive', 'inactif', '0', 0, false];
        
        foreach ($inactiveKeys as $key) {
            if (isset($options[$key])) {
                return $key;
            }
        }
        
        // Prendre la dernière option par défaut
        return array_key_last($options);
    }

    /**
     * Obtenir les actions disponibles pour une entité
     */
    protected function getAvailableBulkActions(): array
    {
        $actions = [];
        $allowedActions = $this->entityConfig['features']['bulk_actions'] ?? [];
        
        $actionLabels = [
            'delete' => 'Supprimer',
            'activate' => 'Activer',
            'deactivate' => 'Désactiver',
            'restore' => 'Restaurer',
        ];
        
        foreach ($allowedActions as $action) {
            if (isset($actionLabels[$action])) {
                $actions[$action] = $actionLabels[$action];
            }
        }
        
        return $actions;
    }
}