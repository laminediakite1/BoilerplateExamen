<?php

return [
    'model' => \App\Models\User::class,
    'middleware' => ['auth', 'role:admin'],
    'fields' => [
        'name' => [
            'type' => 'text',
            'label' => 'Nom complet',
            'required' => true,
            'searchable' => true,
            'sortable' => true,
            'validation' => 'required|string|max:255'
        ],
        'email' => [
            'type' => 'email',
            'label' => 'Email',
            'required' => true,
            'unique' => true,
            'searchable' => true,
            'sortable' => true,
            'validation' => 'required|email|unique:users,email'
        ],
        'password' => [
            'type' => 'password',
            'label' => 'Mot de passe',
            'required' => true,
            'validation' => 'required|string|min:8'
        ],
        'status' => [
            'type' => 'select',
            'label' => 'Statut',
            'options' => [
                'active' => 'Actif',
                'inactive' => 'Inactif'
            ],
            'filterable' => true,
            'default' => 'active',
            'validation' => 'required|in:active,inactive'
        ],
        'created_at' => [
            'type' => 'datetime',
            'label' => 'Créé le',
            'sortable' => true,
            'filterable' => true
        ],
        'updated_at' => [
            'type' => 'datetime',
            'label' => 'Modifié le',
            'sortable' => true
        ],
        'last_login_at' => [
            'type' => 'datetime',
            'label' => 'Dernière connexion',
            'sortable' => true
        ]
    ],
    'relations' => ['roles'],
    'search' => [
        'relations' => [
            'roles' => ['display_name']
        ]
    ],
    'display' => [
        'list' => ['name', 'email', 'role_name', 'status', 'created_at'],
        'form' => ['name', 'email', 'password', 'status'],
        'show' => ['name', 'email', 'role_name', 'status', 'created_at', 'updated_at', 'last_login_at'],
        'export' => ['name', 'email', 'role_name', 'status', 'created_at']
    ],
    'permissions' => [
        'view' => 'users.view',
        'create' => 'users.create',
        'edit' => 'users.edit',
        'delete' => 'users.delete'
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