<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = CartService::items();
        return view('cart', compact('cart'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        CartService::add($request->product_id, $request->quantity);

        return redirect()->back()->with('success', 'Produit ajouté au panier');
    }

    public function remove(Request $request)
    {
        $request->validate(['product_id' => 'required']);
        CartService::remove($request->product_id);
        return redirect()->route('cart');
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'quantity' => 'required|integer|min:0',
        ]);

        CartService::update($request->product_id, $request->quantity);
        return redirect()->route('cart');
    }
}
