<?php

namespace Database\Seeders;

use App\Models\Commune;
use Illuminate\Database\Seeder;

class CommuneSeeder extends Seeder
{
    public function run(): void
    {
        $communes = [
            ['name' => 'Cocody', 'zone' => 'Abidjan', 'delivery_fee' => 1500, 'delivery_days' => 1],
            ['name' => 'Plateau', 'zone' => 'Abidjan', 'delivery_fee' => 1500, 'delivery_days' => 1],
            ['name' => 'Marcory', 'zone' => 'Abidjan', 'delivery_fee' => 1500, 'delivery_days' => 1],
            ['name' => 'Treichville', 'zone' => 'Abidjan', 'delivery_fee' => 1500, 'delivery_days' => 1],
            ['name' => 'Yopougon', 'zone' => 'Abidjan', 'delivery_fee' => 2000, 'delivery_days' => 1],
            ['name' => 'Abobo', 'zone' => 'Abidjan', 'delivery_fee' => 2000, 'delivery_days' => 1],
            ['name' => 'Adjamé', 'zone' => 'Abidjan', 'delivery_fee' => 1500, 'delivery_days' => 1],
            ['name' => 'Koumassi', 'zone' => 'Abidjan', 'delivery_fee' => 1500, 'delivery_days' => 1],
            ['name' => 'Port-Bouët', 'zone' => 'Abidjan', 'delivery_fee' => 2000, 'delivery_days' => 2],
            ['name' => 'Attécoubé', 'zone' => 'Abidjan', 'delivery_fee' => 2000, 'delivery_days' => 2],
            ['name' => 'Bingerville', 'zone' => 'Abidjan périphérie', 'delivery_fee' => 2500, 'delivery_days' => 2],
            ['name' => 'Songon', 'zone' => 'Abidjan périphérie', 'delivery_fee' => 2500, 'delivery_days' => 2],
            ['name' => 'Anyama', 'zone' => 'Abidjan périphérie', 'delivery_fee' => 2500, 'delivery_days' => 2],
            ['name' => 'Bouaké', 'zone' => 'Intérieur', 'delivery_fee' => 3500, 'delivery_days' => 3],
            ['name' => 'Yamoussoukro', 'zone' => 'Intérieur', 'delivery_fee' => 3500, 'delivery_days' => 3],
            ['name' => 'San-Pédro', 'zone' => 'Intérieur', 'delivery_fee' => 4000, 'delivery_days' => 4],
            ['name' => 'Daloa', 'zone' => 'Intérieur', 'delivery_fee' => 4000, 'delivery_days' => 4],
            ['name' => 'Korhogo', 'zone' => 'Intérieur', 'delivery_fee' => 4500, 'delivery_days' => 5],
        ];

        foreach ($communes as $commune) {
            Commune::create($commune);
        }
    }
}
