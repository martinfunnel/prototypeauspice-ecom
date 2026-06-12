<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function index()
    {
        return view('track');
    }

    public function search(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string|max:50',
        ]);

        $orders = Order::where('order_number', $request->order_number)
            ->with('items')
            ->orderByDesc('created_at')
            ->get();

        return view('track', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items');
        return view('track-show', compact('order'));
    }
}
