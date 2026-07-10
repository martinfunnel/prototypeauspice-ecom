<?php

namespace Database\Seeders;

use App\Models\CountryCode;
use Illuminate\Database\Seeder;

class CountryCodeSeeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            ['name' => 'Côte d\'Ivoire',   'code' => '+225', 'iso' => 'CIV', 'flag_url' => 'https://flagcdn.com/w40/ci.png',  'digits' => 10, 'format' => 'XX XX XX XX XX', 'pattern' => '/^\d{10}$/', 'is_active' => true,  'sort_order' => 1],
            ['name' => 'Togo',              'code' => '+228', 'iso' => 'TGO', 'flag_url' => 'https://flagcdn.com/w40/tg.png',  'digits' => 8,  'format' => 'XX XX XX XX',    'pattern' => '/^\d{8}$/',  'is_active' => false, 'sort_order' => 2],
            ['name' => 'Bénin',             'code' => '+229', 'iso' => 'BEN', 'flag_url' => 'https://flagcdn.com/w40/bj.png',  'digits' => 8,  'format' => 'XX XX XX XX',    'pattern' => '/^\d{8}$/',  'is_active' => false, 'sort_order' => 3],
            ['name' => 'Ghana',             'code' => '+233', 'iso' => 'GHA', 'flag_url' => 'https://flagcdn.com/w40/gh.png',  'digits' => 9,  'format' => 'XX XXX XXXX',    'pattern' => '/^\d{9}$/',  'is_active' => false, 'sort_order' => 4],
            ['name' => 'Nigeria',           'code' => '+234', 'iso' => 'NGA', 'flag_url' => 'https://flagcdn.com/w40/ng.png',  'digits' => 10, 'format' => 'XXX XXX XXXX',   'pattern' => '/^\d{10}$/', 'is_active' => false, 'sort_order' => 5],
            ['name' => 'Sénégal',           'code' => '+221', 'iso' => 'SEN', 'flag_url' => 'https://flagcdn.com/w40/sn.png',  'digits' => 9,  'format' => 'XX XXX XX XX',   'pattern' => '/^\d{9}$/',  'is_active' => false, 'sort_order' => 6],
            ['name' => 'Burkina Faso',      'code' => '+226', 'iso' => 'BFA', 'flag_url' => 'https://flagcdn.com/w40/bf.png',  'digits' => 8,  'format' => 'XX XX XX XX',    'pattern' => '/^\d{8}$/',  'is_active' => false, 'sort_order' => 7],
            ['name' => 'Mali',              'code' => '+223', 'iso' => 'MLI', 'flag_url' => 'https://flagcdn.com/w40/ml.png',  'digits' => 8,  'format' => 'XX XX XX XX',    'pattern' => '/^\d{8}$/',  'is_active' => false, 'sort_order' => 8],
            ['name' => 'Niger',             'code' => '+227', 'iso' => 'NER', 'flag_url' => 'https://flagcdn.com/w40/ne.png',  'digits' => 8,  'format' => 'XX XX XX XX',    'pattern' => '/^\d{8}$/',  'is_active' => false, 'sort_order' => 9],
            ['name' => 'Cameroun',          'code' => '+237', 'iso' => 'CMR', 'flag_url' => 'https://flagcdn.com/w40/cm.png',  'digits' => 9,  'format' => 'XX XX XX XX',    'pattern' => '/^\d{9}$/',  'is_active' => false, 'sort_order' => 10],
            ['name' => 'Gabon',             'code' => '+241', 'iso' => 'GAB', 'flag_url' => 'https://flagcdn.com/w40/ga.png',  'digits' => 8,  'format' => 'XX XX XX XX',    'pattern' => '/^\d{8}$/',  'is_active' => false, 'sort_order' => 11],
            ['name' => 'Congo',             'code' => '+242', 'iso' => 'COG', 'flag_url' => 'https://flagcdn.com/w40/cg.png',  'digits' => 9,  'format' => 'XX XXX XX XX',   'pattern' => '/^\d{9}$/',  'is_active' => false, 'sort_order' => 12],
            ['name' => 'RDC',               'code' => '+243', 'iso' => 'COD', 'flag_url' => 'https://flagcdn.com/w40/cd.png',  'digits' => 9,  'format' => 'XX XXX XX XX',   'pattern' => '/^\d{9}$/',  'is_active' => false, 'sort_order' => 13],
            ['name' => 'Guinée',            'code' => '+224', 'iso' => 'GIN', 'flag_url' => 'https://flagcdn.com/w40/gn.png',  'digits' => 9,  'format' => 'XX XXX XX XX',   'pattern' => '/^\d{9}$/',  'is_active' => false, 'sort_order' => 14],
            ['name' => 'Guinée-Bissau',     'code' => '+245', 'iso' => 'GNB', 'flag_url' => 'https://flagcdn.com/w40/gw.png',  'digits' => 9,  'format' => 'XX XXX XXXX',    'pattern' => '/^\d{9}$/',  'is_active' => false, 'sort_order' => 15],
            ['name' => 'Liberia',           'code' => '+231', 'iso' => 'LBR', 'flag_url' => 'https://flagcdn.com/w40/lr.png',  'digits' => 9,  'format' => 'XX XXX XXXX',    'pattern' => '/^\d{9}$/',  'is_active' => false, 'sort_order' => 16],
            ['name' => 'Sierra Leone',      'code' => '+232', 'iso' => 'SLE', 'flag_url' => 'https://flagcdn.com/w40/sl.png',  'digits' => 8,  'format' => 'XX XXXXXX',      'pattern' => '/^\d{8}$/',  'is_active' => false, 'sort_order' => 17],
            ['name' => 'Tchad',             'code' => '+235', 'iso' => 'TCD', 'flag_url' => 'https://flagcdn.com/w40/td.png',  'digits' => 8,  'format' => 'XX XX XX XX',    'pattern' => '/^\d{8}$/',  'is_active' => false, 'sort_order' => 18],
            ['name' => 'Centrafrique',      'code' => '+236', 'iso' => 'CAF', 'flag_url' => 'https://flagcdn.com/w40/cf.png',  'digits' => 8,  'format' => 'XX XX XX XX',    'pattern' => '/^\d{8}$/',  'is_active' => false, 'sort_order' => 19],
            ['name' => 'Guinée Équatoriale','code' => '+240', 'iso' => 'GNQ', 'flag_url' => 'https://flagcdn.com/w40/gq.png',  'digits' => 9,  'format' => 'XX XXX XXXX',    'pattern' => '/^\d{9}$/',  'is_active' => false, 'sort_order' => 20],
            ['name' => 'Sao Tomé',          'code' => '+239', 'iso' => 'STP', 'flag_url' => 'https://flagcdn.com/w40/st.png',  'digits' => 7,  'format' => 'XX XX XX X',     'pattern' => '/^\d{7}$/',  'is_active' => false, 'sort_order' => 21],
            ['name' => 'Mauritanie',        'code' => '+222', 'iso' => 'MRT', 'flag_url' => 'https://flagcdn.com/w40/mr.png',  'digits' => 8,  'format' => 'XX XX XX XX',    'pattern' => '/^\d{8}$/',  'is_active' => false, 'sort_order' => 22],
            ['name' => 'Gambie',            'code' => '+220', 'iso' => 'GMB', 'flag_url' => 'https://flagcdn.com/w40/gm.png',  'digits' => 7,  'format' => 'XXX XXXX',       'pattern' => '/^\d{7}$/',  'is_active' => false, 'sort_order' => 23],
            ['name' => 'Cap-Vert',          'code' => '+238', 'iso' => 'CPV', 'flag_url' => 'https://flagcdn.com/w40/cv.png',  'digits' => 7,  'format' => 'XXX XX XX',      'pattern' => '/^\d{7}$/',  'is_active' => false, 'sort_order' => 24],
        ];

        foreach ($codes as $c) {
            CountryCode::updateOrCreate(['iso' => $c['iso']], $c);
        }
    }
}
