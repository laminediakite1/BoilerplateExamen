<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $userRole = Role::where('name', 'user')->first();

        // Administrateur
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $admin->roles()->sync([$adminRole->id]);

        // Gestionnaire
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Gestionnaire',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $manager->roles()->sync([$managerRole->id]);

        // Utilisateur standard
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Utilisateur',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $user->roles()->sync([$userRole->id]);

        // Créer quelques utilisateurs supplémentaires
        for ($i = 1; $i <= 10; $i++) {
            $newUser = User::firstOrCreate(
                ['email' => "user{$i}@example.com"],
                [
                    'name' => "Utilisateur {$i}",
                    'password' => Hash::make('password'),
                    'status' => rand(0, 1) ? 'active' : 'inactive',
                ]
            );
            $newUser->roles()->sync([$userRole->id]);
        }
    }
}