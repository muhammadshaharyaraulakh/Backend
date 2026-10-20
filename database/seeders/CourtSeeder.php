<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourtSeeder extends Seeder
{
    public function run(): void
    {
        $courts = [
            ['name' => 'Supreme Court',         'short_name' => 'SC',  'hierarchy_level' => 1],
            ['name' => 'Federal Shariat Court', 'short_name' => 'FSC', 'hierarchy_level' => 2],
            ['name' => 'Lahore High Court',     'short_name' => 'LHC', 'hierarchy_level' => 3],
            ['name' => 'Sindh High Court',      'short_name' => 'SHC', 'hierarchy_level' => 3],
            ['name' => 'Peshawar High Court',   'short_name' => 'PHC', 'hierarchy_level' => 3],
            ['name' => 'Islamabad High Court',  'short_name' => 'IHC', 'hierarchy_level' => 3],
            ['name' => 'Balochistan High Court','short_name' => 'BHC', 'hierarchy_level' => 3],
        ];

        DB::table('courts')->insertOrIgnore($courts);
    }
}