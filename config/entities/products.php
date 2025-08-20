<?php

return [
    'model' => \App\Models\Product::class,
    'middleware' => ['auth', 'role:admin'],
    'fields' => [
        'name' => [
            'type' => 'text',
            'label' => 'Nom du produit',
            'required' => true,
            'searchable' => true,
            'sortable' => true,
            'validation' => 'required|string|max:255'
        ],
        'slug' => [
            'type' => 'text',
            'label' => 'Slug',
            'searchable' => true,
            'validation' => 'sometimes|string|max:255|unique:products,slug'
        ],
        'description' => [
            'type' => 'textarea',
            'label' => 'Description',
            'searchable' => true,
            'validation' => 'nullable|string'
        ],
        'short_description' => [
            'type' => 'textarea',
            'label' => 'Description courte',
            'validation' => 'nullable|string|max:500'
        ],
        'sku' => [
            'type' => 'text',
            'label' => 'SKU',
            'searchable' => true,
            'sortable' => true,
            'validation' => 'required|string|max:100|unique:products,sku'
        ],
        'price' => [
            'type' => 'number',
            'label' => 'Prix',
            'required' => true,
            'filterable' => true,
            'sortable' => true,
            'validation' => 'required|numeric|min:0'
        ],
        'sale_price' => [
            'type' => 'number',
            'label' => 'Prix de vente',
            'validation' => 'nullable|numeric|min:0'
        ],
        'stock_quantity' => [
            'type' => 'number',
            'label' => 'Quantité en stock',
            'filterable' => true,
            'sortable' => true,
            'default' => 0,
            'validation' => 'integer|min:0'
        ],
        'manage_stock' => [
            'type' => 'checkbox',
            'label' => 'Gérer le stock',
            'default' => true,
            'validation' => 'boolean'
        ],
        'status' => [
            'type' => 'select',
            'label' => 'Statut',
            'options' => [
                'draft' => 'Brouillon',
                'active' => 'Actif',
                'inactive' => 'Inactif'
            ],
            'filterable' => true,
            'default' => 'draft',
            'validation' => 'required|in:draft,active,inactive'
        ],
        'category_id' => [
            'type' => 'select',
            'label' => 'Catégorie',
            'relation' => 'categories',
            'required' => true,
            'filterable' => true,
            'validation' => 'required|exists:categories,id'
        ],
        'image' => [
            'type' => 'file',
            'label' => 'Image principale',
            'validation' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ],
        'created_at' => [
            'type' => 'datetime',
            'label' => 'Créé le',
            'sortable' => true
        ],
        'updated_at' => [
            'type' => 'datetime',
            'label' => 'Modifié le',
            'sortable' => true
        ]
    ],
    'relations' => ['category'],
    'search' => [
        'relations' => [
            'category' => ['name']
        ]
    ],
    'display' => [
        'list' => ['name', 'sku', 'category.name', 'price', 'stock_quantity', 'status', 'created_at'],
        'form' => ['name', 'slug', 'description', 'short_description', 'sku', 'price', 'sale_price', 'category_id', 'stock_quantity', 'manage_stock', 'status', 'image'],
        'show' => ['name', 'slug', 'description', 'sku', 'price', 'sale_price', 'category.name', 'stock_quantity', 'status', 'created_at', 'updated_at'],
        'export' => ['name', 'sku', 'category.name', 'price', 'sale_price', 'stock_quantity', 'status', 'created_at']
    ],
    'permissions' => [
        'view' => 'products.view',
        'create' => 'products.create',
        'edit' => 'products.edit',
        'delete' => 'products.delete'
    ],
    'features' => [
        'search' => true,
        'filters' => true,
        'export' => true,
        'bulk_actions' => ['delete', 'activate', 'deactivate'],
        'soft_deletes' => true,
        'pagination' => 15,
        'default_sort' => 'created_at',
        'default_order' => 'desc'
    ]
];