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
            'phone' => 'required|string|max:20',
        ]);

        $orders = Order::where('customer_phone', $request->phone)
            ->with('items')
            ->orderByDesc('created_at')
            ->get();

        return view('track', compact('orders'));
    }
}
