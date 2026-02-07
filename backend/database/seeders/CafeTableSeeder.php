<?php

namespace Database\Seeders;

use App\Models\CafeTable;
use Illuminate\Database\Seeder;

class CafeTableSeeder extends Seeder
{
    public function run(): void
    {
        $tables = ['A1', 'A2', 'A3', 'B1', 'B2'];

        foreach ($tables as $table) {
            CafeTable::firstOrCreate([
                'table_number' => $table
            ]);
        }
    }
}
