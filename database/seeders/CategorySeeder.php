<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Électronique', 'description' => 'Produits électroniques et high-tech'],
            ['name' => 'Vêtements', 'description' => 'Mode et vêtements'],
            ['name' => 'Maison & Jardin', 'description' => 'Articles pour la maison et le jardin'],
            ['name' => 'Sport & Loisirs', 'description' => 'Équipements sportifs et de loisirs'],
            ['name' => 'Livres', 'description' => 'Livres et publications'],
        ];

        foreach ($categories as $index => $categoryData) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryData['name'])],
                [
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );

            // Créer quelques sous-catégories
            if ($categoryData['name'] === 'Électronique') {
                $subcategories = ['Smartphones', 'Ordinateurs', 'TV & Audio'];
                foreach ($subcategories as $subIndex => $subcategoryName) {
                    Category::firstOrCreate(
                        ['slug' => Str::slug($subcategoryName)],
                        [
                            'name' => $subcategoryName,
                            'parent_id' => $category->id,
                            'is_active' => true,
                            'sort_order' => $subIndex + 1,
                        ]
                    );
                }
            }
        }
    }
}