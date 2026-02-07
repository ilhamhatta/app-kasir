<?php

namespace App\Http\Controllers\Api;

use App\Models\CafeTable;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CafeTableController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'table_number' => 'required|unique:cafe_tables,table_number'
        ]);

        $table = CafeTable::create([
            'table_number' => $request->table_number
        ]);

        $url = config('app.frontend_url') . '/menu/' . $table->table_number;

        $qrPath = 'qrcodes/table_' . $table->table_number . '.svg';

        Storage::disk('public')->put(
            $qrPath,
            QrCode::format('svg')
                ->size(300)
                ->generate($url)
        );

        $table->update([
            'qr_code' => $qrPath
        ]);

        return response()->json([
            'table' => $table,
            'qr_url' => asset('storage/' . $qrPath)
        ]);
    }
}
