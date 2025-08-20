<?php

namespace App\Traits\Backend;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\GenericExport;
use Barryvdh\DomPDF\Facade\Pdf;

trait ExportTrait
{
    /**
     * Exporter les données
     */
    public function export(Request $request, string $format = 'excel')
    {
        $this->initializeCrudTrait();
        $this->checkPermission('view');
        
        // Vérifier si l'export est activé
        if (!($this->entityConfig['features']['export'] ?? false)) {
            abort(403, 'Export non autorisé pour cette entité.');
        }
        
        // Construire la requête avec les mêmes filtres que l'index
        $query = $this->buildExportQuery($request);
        
        // Obtenir les données
        $data = $query->get();
        
        // Nom du fichier
        $filename = $this->generateExportFilename($format);
        
        switch ($format) {
            case 'excel':
                return $this->exportToExcel($data, $filename);
                
            case 'pdf':
                return $this->exportToPdf($data, $filename);
                
            case 'csv':
                return $this->exportToCsv($data, $filename);
                
            default:
                abort(400, 'Format d\'export non supporté.');
        }
    }

    /**
     * Construire la requête pour l'export
     */
    protected function buildExportQuery(Request $request): Builder
    {
        $query = $this->model->newQuery();
        
        // Appliquer les relations
        if (!empty($this->entityConfig['relations'])) {
            $query->with($this->entityConfig['relations']);
        }
        
        // Appliquer la recherche
        $query = $this->applySearch($query, $request);
        
        // Appliquer les filtres
        $query = $this->applyFilters($query, $request);
        
        // Appliquer le tri
        $defaultSort = $this->entityConfig['features']['default_sort'] ?? 'created_at';
        $defaultOrder = $this->entityConfig['features']['default_order'] ?? 'desc';
        
        if ($request->has('sort')) {
            $sort = $request->get('sort');
            $order = $request->get('order', 'asc');
            $query->orderBy($sort, $order);
        } else {
            $query->orderBy($defaultSort, $defaultOrder);
        }
        
        return $query;
    }

    /**
     * Exporter vers Excel
     */
    protected function exportToExcel($data, string $filename)
    {
        $export = new GenericExport($data, $this->getExportColumns());
        
        return Excel::download($export, $filename . '.xlsx');
    }

    /**
     * Exporter vers PDF
     */
    protected function exportToPdf($data, string $filename)
    {
        $columns = $this->getExportColumns();
        
        $pdf = PDF::loadView('admin.exports.pdf', [
            'data' => $data,
            'columns' => $columns,
            'title' => ucfirst($this->entityName),
            'entityConfig' => $this->entityConfig
        ]);
        
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Exporter vers CSV
     */
    protected function exportToCsv($data, string $filename)
    {
        $columns = $this->getExportColumns();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];
        
        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, array_values($columns));
            
            // Données
            foreach ($data as $item) {
                $row = [];
                foreach (array_keys($columns) as $field) {
                    $row[] = $this->getFieldValue($item, $field);
                }
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Obtenir les colonnes à exporter
     */
    protected function getExportColumns(): array
    {
        $columns = [];
        $exportFields = $this->entityConfig['display']['export'] ?? $this->entityConfig['display']['list'];
        
        foreach ($exportFields as $field) {
            $fieldConfig = $this->entityConfig['fields'][$field] ?? null;
            $label = $fieldConfig['label'] ?? ucfirst($field);
            $columns[$field] = $label;
        }
        
        return $columns;
    }

    /**
     * Obtenir la valeur d'un champ pour l'export
     */
    protected function getFieldValue($item, string $field)
    {
        if (strpos($field, '.') !== false) {
            // Champ relationnel
            $parts = explode('.', $field);
            $value = $item;
            
            foreach ($parts as $part) {
                $value = $value?->{$part};
            }
            
            return $value;
        }
        
        $value = $item->{$field};
        
        // Formatage selon le type de champ
        $fieldConfig = $this->entityConfig['fields'][$field] ?? null;
        $type = $fieldConfig['type'] ?? 'text';
        
        switch ($type) {
            case 'boolean':
            case 'checkbox':
                return $value ? 'Oui' : 'Non';
                
            case 'date':
                return $value ? $value->format('d/m/Y') : '';
                
            case 'datetime':
                return $value ? $value->format('d/m/Y H:i') : '';
                
            case 'select':
                if (isset($fieldConfig['options'][$value])) {
                    return $fieldConfig['options'][$value];
                }
                return $value;
                
            default:
                return $value;
        }
    }

    /**
     * Générer le nom du fichier d'export
     */
    protected function generateExportFilename(string $format): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        return ucfirst($this->entityName) . '_export_' . $timestamp;
    }
}