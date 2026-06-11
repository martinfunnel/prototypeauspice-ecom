<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function create()
    {
        $cart = CartService::items();
        if (empty($cart['items'])) {
            return redirect()->route('cart')->with('error', 'Votre panier est vide');
        }

        $communes = Commune::active()->orderBy('zone')->orderBy('name')->get();
        return view('order', compact('cart', 'communes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'commune_id' => 'required|exists:communes,id',
            'address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cart = CartService::items();
        if (empty($cart['items'])) {
            return redirect()->route('cart')->with('error', 'Panier vide');
        }

        $commune = Commune::findOrFail($request->commune_id);

        $order = Order::create([
            'order_number' => 'CMD-' . now()->format('ymd') . '-' . strtoupper(Str::random(5)),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'commune_id' => $commune->id,
            'commune_name' => $commune->name,
            'address' => $request->address,
            'notes' => $request->notes,
            'subtotal' => $cart['total'],
            'delivery_fee' => $commune->delivery_fee,
            'total' => $cart['total'] + $commune->delivery_fee,
            'status' => 'pending',
        ]);

        foreach ($cart['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product']->id,
                'product_name' => $item['product']->name,
                'unit_price' => $item['price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        CartService::clear();

        return redirect()->route('track')
            ->with('success', 'Commande passée avec succès ! Numéro : ' . $order->order_number);
    }

    public function storeDirect(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'commune_id' => 'required|exists:communes,id',
            'address' => 'required|string|max:500',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $commune = Commune::findOrFail($request->commune_id);
        $subtotal = $product->displayPrice() * $request->quantity;

        $order = Order::create([
            'order_number' => 'CMD-' . now()->format('ymd') . '-' . strtoupper(Str::random(5)),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'commune_id' => $commune->id,
            'commune_name' => $commune->name,
            'address' => $request->address,
            'subtotal' => $subtotal,
            'delivery_fee' => $commune->delivery_fee,
            'total' => $subtotal + $commune->delivery_fee,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->displayPrice(),
            'quantity' => $request->quantity,
            'subtotal' => $subtotal,
        ]);

        // Notif WhatsApp admin (même logique que React notifyAdminInNewTab)
        $waMsg = urlencode(
            "📦 *Nouvelle commande*\n\n" .
            "N° : {$order->order_number}\n" .
            "Client : {$request->customer_name}\n" .
            "Tél : {$request->customer_phone}\n" .
            "Commune : {$commune->name}\n" .
            "Adresse : {$request->address}\n\n" .
            "Article : {$product->name} x {$request->quantity}\n" .
            "Sous-total : " . number_format($subtotal, 0, ',', ' ') . " FCFA\n" .
            "Livraison : " . number_format($commune->delivery_fee, 0, ',', ' ') . " FCFA\n" .
            "*Total : " . number_format($order->total, 0, ',', ' ') . " FCFA*"
        );
        session()->flash('wa_admin_url', "https://wa.me/?text={$waMsg}");

        return redirect()->route('track')
            ->with('success', 'Commande envoyée ! Numéro : ' . $order->order_number);
    }
}
