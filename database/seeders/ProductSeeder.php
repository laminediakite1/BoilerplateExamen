<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            return;
        }

        $products = [
            ['name' => 'iPhone 15 Pro', 'price' => 1229.00, 'sale_price' => 1199.00, 'stock' => 50],
            ['name' => 'Samsung Galaxy S24', 'price' => 899.00, 'stock' => 30],
            ['name' => 'MacBook Pro 14"', 'price' => 2499.00, 'stock' => 20],
            ['name' => 'Dell XPS 13', 'price' => 1299.00, 'sale_price' => 1199.00, 'stock' => 15],
            ['name' => 'Sony WH-1000XM5', 'price' => 399.00, 'stock' => 75],
            ['name' => 'T-shirt Premium', 'price' => 29.99, 'sale_price' => 24.99, 'stock' => 100],
            ['name' => 'Jeans Slim Fit', 'price' => 79.99, 'stock' => 80],
            ['name' => 'Chaise de Bureau', 'price' => 199.00, 'stock' => 25],
            ['name' => 'Table Basse', 'price' => 159.00, 'sale_price' => 129.00, 'stock' => 10],
            ['name' => 'Raquette de Tennis', 'price' => 149.00, 'stock' => 35],
        ];

        foreach ($products as $productData) {
            $category = $categories->random();
            
            Product::firstOrCreate(
                ['sku' => 'PRD-' . strtoupper(Str::random(8))],
                [
                    'name' => $productData['name'],
                    'slug' => Str::slug($productData['name']),
                    'description' => 'Description détaillée du produit ' . $productData['name'],
                    'short_description' => 'Description courte du produit ' . $productData['name'],
                    'price' => $productData['price'],
                    'sale_price' => $productData['sale_price'] ?? null,
                    'stock_quantity' => $productData['stock'],
                    'manage_stock' => true,
                    'status' => ['active', 'inactive', 'draft'][array_rand(['active', 'inactive', 'draft'])],
                    'category_id' => $category->id,
                ]
            );
        }

        // Créer quelques produits supplémentaires
        for ($i = 1; $i <= 20; $i++) {
            $category = $categories->random();
            
            Product::create([
                'name' => 'Produit Test ' . $i,
                'slug' => Str::slug('Produit Test ' . $i),
                'description' => 'Description du produit test numéro ' . $i,
                'short_description' => 'Description courte du produit ' . $i,
                'sku' => 'TST-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'price' => rand(10, 500),
                'sale_price' => rand(0, 1) ? rand(10, 400) : null,
                'stock_quantity' => rand(0, 100),
                'manage_stock' => true,
                'status' => ['active', 'inactive', 'draft'][array_rand(['active', 'inactive', 'draft'])],
                'category_id' => $category->id,
            ]);
        }
    }
}