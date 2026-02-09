<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    /**
     * List semua order (untuk dashboard kasir)
     */
    public function index()
    {
        $orders = Order::with(['items.menu', 'table'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'stats' => [
                'total' => $orders->count(),
                'pending' => $orders->where('status', 'pending')->count(),
                'confirmed' => $orders->where('status', 'confirmed')->count(),
                'processing' => $orders->where('status', 'processing')->count(),
                'completed' => $orders->where('status', 'completed')->count(),
                'cancelled' => $orders->where('status', 'cancelled')->count(),
            ],
            'orders' => $orders
        ]);
    }

    /**
     * Detail order
     */
    public function show($id)
    {
        $order = Order::with(['items.menu', 'table'])->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        return response()->json($order);
    }

    /**
     * Update status order (INTI LOGIC KASIR)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:confirmed,processing,completed,cancelled'
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing'],
            'processing' => ['completed'],
        ];

        if (
            !isset($allowedTransitions[$order->status]) ||
            !in_array($request->status, $allowedTransitions[$order->status])
        ) {
            return response()->json([
                'message' => 'Perubahan status tidak valid'
            ], 400);
        }

        $order->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Status pesanan diperbarui',
            'status' => $order->status
        ]);
    }

    public function updatePayment(Request $request, Order $order)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,transfer'
        ]);

        if ($order->payment_status === 'paid') {
            return response()->json([
                'message' => 'Pesanan sudah dibayar'
            ], 400);
        }

        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $request->payment_method
        ]);

        return response()->json([
            'message' => 'Pembayaran berhasil dikonfirmasi',
            'payment_status' => $order->payment_status
        ]);
    }
}
