<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Users
            ['name' => 'users.view', 'display_name' => 'Voir les utilisateurs', 'group' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Créer des utilisateurs', 'group' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Modifier les utilisateurs', 'group' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Supprimer les utilisateurs', 'group' => 'users'],

            // Categories
            ['name' => 'categories.view', 'display_name' => 'Voir les catégories', 'group' => 'categories'],
            ['name' => 'categories.create', 'display_name' => 'Créer des catégories', 'group' => 'categories'],
            ['name' => 'categories.edit', 'display_name' => 'Modifier les catégories', 'group' => 'categories'],
            ['name' => 'categories.delete', 'display_name' => 'Supprimer les catégories', 'group' => 'categories'],

            // Products
            ['name' => 'products.view', 'display_name' => 'Voir les produits', 'group' => 'products'],
            ['name' => 'products.create', 'display_name' => 'Créer des produits', 'group' => 'products'],
            ['name' => 'products.edit', 'display_name' => 'Modifier les produits', 'group' => 'products'],
            ['name' => 'products.delete', 'display_name' => 'Supprimer les produits', 'group' => 'products'],

            // Dashboard
            ['name' => 'dashboard.view', 'display_name' => 'Voir le tableau de bord', 'group' => 'dashboard'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Attribuer toutes les permissions au rôle admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $allPermissions = Permission::all();
            $adminRole->permissions()->sync($allPermissions->pluck('id'));
        }

        // Attribuer certaines permissions au rôle manager
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $managerPermissions = Permission::whereIn('group', ['categories', 'products', 'dashboard'])
                ->whereIn('name', [
                    'categories.view', 'categories.create', 'categories.edit',
                    'products.view', 'products.create', 'products.edit',
                    'dashboard.view'
                ])->get();
            $managerRole->permissions()->sync($managerPermissions->pluck('id'));
        }

        // Le rôle user n'a aucune permission administrative par défaut
    }
}