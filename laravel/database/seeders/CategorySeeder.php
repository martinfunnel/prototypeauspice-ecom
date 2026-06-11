<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Compléments alimentaires', 'slug' => 'complements', 'description' => 'Vitamines, minéraux et compléments pour votre santé', 'sort_order' => 1],
            ['name' => 'Soins du corps', 'slug' => 'soins-corps', 'description' => 'Produits naturels pour le bien-être quotidien', 'sort_order' => 2],
            ['name' => 'Énergie & Vitalité', 'slug' => 'energie', 'description' => 'Boosters d\'énergie et tonus', 'sort_order' => 3],
            ['name' => 'Beauté & Peau', 'slug' => 'beaute', 'description' => 'Soins naturels pour une peau éclatante', 'sort_order' => 4],
            ['name' => 'Minceur', 'slug' => 'minceur', 'description' => 'Solutions naturelles pour la silhouette', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
