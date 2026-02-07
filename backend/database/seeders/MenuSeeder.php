<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            [
                'name' => 'Espresso',
                'price' => 20000,
                'description' => 'Kopi hitam kuat',
                'is_active' => true,
            ],
            [
                'name' => 'Cappuccino',
                'price' => 25000,
                'description' => 'Espresso + susu',
                'is_active' => true,
            ],
            [
                'name' => 'Latte',
                'price' => 27000,
                'description' => 'Susu dominan',
                'is_active' => true,
            ],
            [
                'name' => 'Americano',
                'price' => 22000,
                'description' => 'Espresso + air',
                'is_active' => true,
            ],
        ];

        foreach ($menus as $menu) {
            Menu::firstOrCreate(
                ['name' => $menu['name']],
                $menu
            );
        }
    }
}
