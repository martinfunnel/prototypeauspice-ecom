<?php

namespace Database\Seeders;

use App\Models\PromoBanner;
use Illuminate\Database\Seeder;

class PromoBannerSeeder extends Seeder
{
    public function run(): void
    {
        PromoBanner::create([
            'key' => 'catalogue',
            'title' => 'Cacao à la cannelle de Ceylan',
            'subtitle' => 'Notre produit phare bio — profitez de l\'offre du moment',
            'cta_label' => 'Découvrir l\'offre',
            'cta_url' => '/produit/cacaocelyan',
            'is_active' => true,
        ]);
    }
}
