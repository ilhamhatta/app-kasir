<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\Menu;

class MenuController extends Controller
{
    public function index($tableNumber)
    {
        $table = CafeTable::where('table_number', $tableNumber)->first();

        if (!$table) {
            return response()->json([
                'message' => 'Meja tidak ditemukan'
            ], 404);
        }

        $menus = Menu::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'table' => $table->table_number,
            'menus' => $menus
        ]);
    }
}
