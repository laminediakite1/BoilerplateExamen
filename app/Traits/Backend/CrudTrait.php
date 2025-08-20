<?php

namespace App\Traits\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

trait CrudTrait
{
    use SearchTrait, FilterTrait, PaginationTrait;

    protected $entityConfig;
    protected $model;
    protected $entityName;

    /**
     * Initialiser le trait CRUD
     */
    protected function initializeCrudTrait()
    {
        $this->loadEntityConfig();
    }

    /**
     * Charger la configuration de l'entité
     */
    protected function loadEntityConfig()
    {
        // Déterminer le nom de l'entité depuis le contrôleur
        $controllerName = class_basename(static::class);
        $this->entityName = strtolower(str_replace('Controller', '', $controllerName));
        
        // Charger la configuration
        $configPath = "entities.{$this->entityName}";
        
        if (!config()->has($configPath)) {
            throw new \Exception("Configuration not found for entity: {$this->entityName}");
        }
        
        $this->entityConfig = config($configPath);
        $this->model = app($this->entityConfig['model']);
    }

    /**
     * Afficher la liste des entités
     */
    public function index(Request $request)
    {
        $this->initializeCrudTrait();
        
        // Vérifier les permissions
        $this->checkPermission('view');
        
        // Construire la requête
        $query = $this->model->newQuery();
        
        // Appliquer les relations
        if (!empty($this->entityConfig['relations'])) {
            $query->with($this->entityConfig['relations']);
        }
        
        // Appliquer la recherche
        $query = $this->applySearch($query, $request);
        
        // Appliquer les filtres
        $query = $this->applyFilters($query, $request);
        
        // Appliquer le tri par défaut
        $defaultSort = $this->entityConfig['features']['default_sort'] ?? 'created_at';
        $defaultOrder = $this->entityConfig['features']['default_order'] ?? 'desc';
        
        if ($request->has('sort')) {
            $sort = $request->get('sort');
            $order = $request->get('order', 'asc');
            $query->orderBy($sort, $order);
        } else {
            $query->orderBy($defaultSort, $defaultOrder);
        }
        
        // Pagination
        $perPage = $this->entityConfig['features']['pagination'] ?? 15;
        $items = $query->paginate($perPage);
        
        // Obtenir les options pour les filtres
        $filterOptions = $this->getFilterOptions();
        
        return view("admin.{$this->entityName}.index", compact('items', 'filterOptions'))
            ->with('config', $this->entityConfig)
            ->with('entityName', $this->entityName);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $this->initializeCrudTrait();
        $this->checkPermission('create');
        
        // Obtenir les options pour les champs select
        $options = $this->getFormOptions();
        
        return view("admin.{$this->entityName}.create")
            ->with('config', $this->entityConfig)
            ->with('options', $options)
            ->with('entityName', $this->entityName);
    }

    /**
     * Sauvegarder une nouvelle entité
     */
    public function store(Request $request)
    {
        $this->initializeCrudTrait();
        $this->checkPermission('create');
        
        // Validation
        $rules = $this->getValidationRules();
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Préparer les données
            $data = $this->prepareData($request, 'create');
            
            // Créer l'entité
            $item = $this->model->create($data);
            
            // Traiter les relations many-to-many
            $this->handleRelations($item, $request);
            
            DB::commit();
            
            return redirect()->route("admin.{$this->entityName}.index")
                ->with('success', 'Élément créé avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher une entité
     */
    public function show($id)
    {
        $this->initializeCrudTrait();
        $this->checkPermission('view');
        
        $query = $this->model->newQuery();
        
        if (!empty($this->entityConfig['relations'])) {
            $query->with($this->entityConfig['relations']);
        }
        
        $item = $query->findOrFail($id);
        
        return view("admin.{$this->entityName}.show", compact('item'))
            ->with('config', $this->entityConfig)
            ->with('entityName', $this->entityName);
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $this->initializeCrudTrait();
        $this->checkPermission('edit');
        
        $query = $this->model->newQuery();
        
        if (!empty($this->entityConfig['relations'])) {
            $query->with($this->entityConfig['relations']);
        }
        
        $item = $query->findOrFail($id);
        
        // Obtenir les options pour les champs select
        $options = $this->getFormOptions();
        
        return view("admin.{$this->entityName}.edit", compact('item'))
            ->with('config', $this->entityConfig)
            ->with('options', $options)
            ->with('entityName', $this->entityName);
    }

    /**
     * Mettre à jour une entité
     */
    public function update(Request $request, $id)
    {
        $this->initializeCrudTrait();
        $this->checkPermission('edit');
        
        $item = $this->model->findOrFail($id);
        
        // Validation avec règles de mise à jour
        $rules = $this->getValidationRules(true, $id);
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Préparer les données
            $data = $this->prepareData($request, 'update');
            
            // Mettre à jour l'entité
            $item->update($data);
            
            // Traiter les relations many-to-many
            $this->handleRelations($item, $request);
            
            DB::commit();
            
            return redirect()->route("admin.{$this->entityName}.index")
                ->with('success', 'Élément mis à jour avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer une entité
     */
    public function destroy($id)
    {
        $this->initializeCrudTrait();
        $this->checkPermission('delete');
        
        try {
            $item = $this->model->findOrFail($id);
            $item->delete();
            
            return redirect()->route("admin.{$this->entityName}.index")
                ->with('success', 'Élément supprimé avec succès.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Vérifier les permissions
     */
    protected function checkPermission(string $action)
    {
        if (!isset($this->entityConfig['permissions'][$action])) {
            return;
        }
        
        $permission = $this->entityConfig['permissions'][$action];
        
        if (!auth()->user()->hasPermission($permission)) {
            abort(403, 'Action non autorisée.');
        }
    }

    /**
     * Obtenir les règles de validation
     */
    protected function getValidationRules(bool $isUpdate = false, $id = null): array
    {
        $rules = [];
        
        foreach ($this->entityConfig['fields'] as $field => $settings) {
            if (isset($settings['validation'])) {
                $rule = $settings['validation'];
                
                // Modifier les règles unique pour les mises à jour
                if ($isUpdate && $id && strpos($rule, 'unique:') !== false) {
                    $tableName = $this->model->getTable();
                    $rule = str_replace('unique:', "unique:{$tableName},{$field},{$id},", $rule);
                }
                
                $rules[$field] = $rule;
            }
        }
        
        return $rules;
    }

    /**
     * Préparer les données avant sauvegarde
     */
    protected function prepareData(Request $request, string $action): array
    {
        $data = [];
        
        foreach ($this->entityConfig['fields'] as $field => $settings) {
            if (in_array($field, $this->entityConfig['display'][$action === 'create' ? 'form' : 'form'])) {
                if ($request->has($field)) {
                    $value = $request->get($field);
                    
                    // Traitement spécial selon le type
                    switch ($settings['type'] ?? 'text') {
                        case 'password':
                            if (!empty($value)) {
                                $data[$field] = bcrypt($value);
                            }
                            break;
                        case 'checkbox':
                            $data[$field] = $request->boolean($field);
                            break;
                        case 'file':
                            // Traitement des fichiers géré séparément
                            break;
                        default:
                            $data[$field] = $value;
                            break;
                    }
                }
            }
        }
        
        return $data;
    }

    /**
     * Traiter les relations many-to-many
     */
    protected function handleRelations($item, Request $request)
    {
        // À implémenter selon les besoins spécifiques
    }

    /**
     * Obtenir les options pour les champs de formulaire
     */
    protected function getFormOptions(): array
    {
        $options = [];
        
        foreach ($this->entityConfig['fields'] as $field => $settings) {
            if ($settings['type'] === 'select' && isset($settings['relation'])) {
                // Charger les options depuis une relation
                $relationModel = $settings['relation'];
                $options[$field] = app("App\\Models\\" . ucfirst($relationModel))->all();
            } elseif (isset($settings['options'])) {
                // Options définies dans la configuration
                $options[$field] = $settings['options'];
            }
        }
        
        return $options;
    }
}