<?php

return [
    'model' => \App\Models\Category::class,
    'middleware' => ['auth', 'role:admin'],
    'fields' => [
        'name' => [
            'type' => 'text',
            'label' => 'Nom',
            'required' => true,
            'searchable' => true,
            'sortable' => true,
            'validation' => 'required|string|max:255'
        ],
        'slug' => [
            'type' => 'text',
            'label' => 'Slug',
            'searchable' => true,
            'validation' => 'sometimes|string|max:255|unique:categories,slug'
        ],
        'description' => [
            'type' => 'textarea',
            'label' => 'Description',
            'searchable' => true,
            'validation' => 'nullable|string'
        ],
        'image' => [
            'type' => 'file',
            'label' => 'Image',
            'validation' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ],
        'parent_id' => [
            'type' => 'select',
            'label' => 'Catégorie parent',
            'relation' => 'categories',
            'filterable' => true,
            'validation' => 'nullable|exists:categories,id'
        ],
        'is_active' => [
            'type' => 'checkbox',
            'label' => 'Actif',
            'filterable' => true,
            'default' => true,
            'validation' => 'boolean'
        ],
        'sort_order' => [
            'type' => 'number',
            'label' => 'Ordre de tri',
            'default' => 0,
            'validation' => 'integer|min:0'
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
    'relations' => ['parent', 'children'],
    'display' => [
        'list' => ['name', 'parent.name', 'is_active', 'sort_order', 'created_at'],
        'form' => ['name', 'slug', 'description', 'image', 'parent_id', 'is_active', 'sort_order'],
        'show' => ['name', 'slug', 'description', 'parent.name', 'is_active', 'sort_order', 'created_at', 'updated_at'],
        'export' => ['name', 'parent.name', 'description', 'is_active', 'sort_order', 'created_at']
    ],
    'permissions' => [
        'view' => 'categories.view',
        'create' => 'categories.create',
        'edit' => 'categories.edit',
        'delete' => 'categories.delete'
    ],
    'features' => [
        'search' => true,
        'filters' => true,
        'export' => true,
        'bulk_actions' => ['delete', 'activate', 'deactivate'],
        'soft_deletes' => true,
        'pagination' => 15,
        'default_sort' => 'sort_order',
        'default_order' => 'asc'
    ]
];