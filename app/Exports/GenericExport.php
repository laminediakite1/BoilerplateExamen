<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class GenericExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    protected $columns;

    public function __construct(Collection $data, array $columns)
    {
        $this->data = $data;
        $this->columns = $columns;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return array_values($this->columns);
    }

    public function map($item): array
    {
        $row = [];
        
        foreach (array_keys($this->columns) as $field) {
            if (strpos($field, '.') !== false) {
                // Champ relationnel
                $parts = explode('.', $field);
                $value = $item;
                
                foreach ($parts as $part) {
                    $value = $value?->{$part};
                }
                
                $row[] = $value;
            } else {
                $row[] = $item->{$field};
            }
        }
        
        return $row;
    }
}