<?php

namespace App\Http\Controllers\Api;

use App\Models\Menu;
use App\Models\Order;
use App\Models\CafeTable;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'table_number' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $table = CafeTable::where('table_number', $request->table_number)->first();

        if (!$table) {
            return response()->json([
                'message' => 'Meja tidak ditemukan'
            ], 404);
        }

        return DB::transaction(function () use ($request, $table) {

            $order = Order::create([
                'order_number' => 'ORD-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                'cafe_table_id' => $table->id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'total_price' => 0
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $menu = Menu::where('id', $item['menu_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (!$menu) {
                    return response()->json([
                        'message' => 'Menu tidak tersedia'
                    ], 400);
                }

                $subtotal = $menu->price * $item['qty'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                    'price' => $menu->price,
                    'qty' => $item['qty'],
                    'subtotal' => $subtotal
                ]);

                $total += $subtotal;
            }

            $order->update([
                'total_price' => $total
            ]);

            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total_price' => $order->total_price
            ], 201);
        });
    }

    public function show($orderNumber)
    {
        $order = Order::with('items.menu')
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'total_price' => $order->total_price,
            'items' => $order->items
        ]);
    }
}
