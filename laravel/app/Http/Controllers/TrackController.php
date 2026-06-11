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

        session(['last_phone' => $request->phone]);

        $orders = Order::where('customer_phone', $request->phone)
            ->with('items')
            ->orderByDesc('created_at')
            ->get();

        return view('track', compact('orders'));
    }
}
