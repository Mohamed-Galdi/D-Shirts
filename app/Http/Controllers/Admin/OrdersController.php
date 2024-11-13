<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::when($request->filter && $request->filter != 'all', function ($query) use ($request) {
            $query->where('status', $request->filter);
        })
        ->select('id', 'customer_id', 'status', 'tracking_number', 'created_at')
            ->with('customer')
            ->with([
                'tshirts.images' => function ($query) {
                    $query->where('order', 1);
                },
            ])
            ->paginate(10)
            ->withQueryString()
            ->through(function ($order) {
                return [
                    ...$order->toArray(),
                    'created_at' => $order->created_at->format('M d, Y H:i'),
                    'total_tshirts' => $order->getTotalTshirts(),
                    'total_amount' => $order->getTotalAmount(),
                ];
            });
        $currentFilter = $request->filter ?? 'all';
        return inertia('Admin/Orders', ['orders' => $orders, 'currentFilter' => $currentFilter]);
    }

    public function update(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required',
            'tracking_number' => 'min:8',
        ]);
        $order = Order::findOrFail($orderId);
        $order->status = $request->status['value'];
        $order->tracking_number = $request->tracking_number;
        $order->save();

        return redirect()->route('orders');
    }
}
