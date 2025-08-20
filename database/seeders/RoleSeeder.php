<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrateur',
                'description' => 'Accès complet au système',
                'is_active' => true,
            ],
            [
                'name' => 'manager',
                'display_name' => 'Gestionnaire',
                'description' => 'Gestion des contenus et utilisateurs',
                'is_active' => true,
            ],
            [
                'name' => 'user',
                'display_name' => 'Utilisateur',
                'description' => 'Accès utilisateur standard',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}