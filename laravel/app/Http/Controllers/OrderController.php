<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\CountryCode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use App\Services\TelegramService;
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
        $countryCodes = CountryCode::active()->orderBy('sort_order')->get();
        return view('order', compact('cart', 'communes', 'countryCodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'country_code_id' => 'required|exists:country_codes,id',
            'customer_phone' => 'required|string|max:30',
            'commune_id' => 'required|exists:communes,id',
            'address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Validation du format téléphone selon le pays
        $countryCode = CountryCode::findOrFail($validated['country_code_id']);
        $rawPhone = preg_replace('/\D/', '', $validated['customer_phone']);
        if (!$countryCode->validateRaw($rawPhone)) {
            return back()->withErrors(['customer_phone' => "Numéro invalide pour {$countryCode->name}. Format attendu : {$countryCode->code} {$countryCode->format} ({$countryCode->digits} chiffres)"])->withInput();
        }

        $fullPhone = $countryCode->code . ' ' . $countryCode->formatNumber($rawPhone);

        $cart = CartService::items();
        if (empty($cart['items'])) {
            return redirect()->route('cart')->with('error', 'Panier vide');
        }

        $commune = Commune::findOrFail($request->commune_id);

        $order = Order::create([
            'order_number' => 'CMD-' . now()->format('ymd') . '-' . strtoupper(Str::random(5)),
            'customer_name' => $request->customer_name,
            'customer_phone' => $fullPhone,
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

            // Décrémenter le stock
            $product = Product::find($item['product']->id);
            if ($product) {
                $product->decrement('stock', $item['quantity']);
            }
        }

        CartService::clear();

        // Notifications Telegram
        $tg = new TelegramService();
        $tg->notifyClient($order);
        $tg->notifyManagers($order);

        return redirect()->route('track')
            ->with('success', 'Commande passée avec succès ! Numéro : ' . $order->order_number);
    }

    public function storeDirect(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'country_code_id' => 'required|exists:country_codes,id',
            'customer_phone' => 'required|string|max:30',
            'commune_id' => 'required|exists:communes,id',
            'address' => 'required|string|max:500',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $countryCode = CountryCode::findOrFail($validated['country_code_id']);
        $rawPhone = preg_replace('/\D/', '', $validated['customer_phone']);
        if (!$countryCode->validateRaw($rawPhone)) {
            return back()->withErrors(['customer_phone' => "Numéro invalide pour {$countryCode->name}. Format attendu : {$countryCode->code} {$countryCode->format} ({$countryCode->digits} chiffres)"])->withInput();
        }

        $fullPhone = $countryCode->code . ' ' . $countryCode->formatNumber($rawPhone);

        $product = Product::findOrFail($request->product_id);
        $commune = Commune::findOrFail($request->commune_id);
        $subtotal = $product->displayPrice() * $request->quantity;

        $order = Order::create([
            'order_number' => 'CMD-' . now()->format('ymd') . '-' . strtoupper(Str::random(5)),
            'customer_name' => $request->customer_name,
            'customer_phone' => $fullPhone,
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

        // Décrémenter le stock
        $product->decrement('stock', $request->quantity);

        // Notifications Telegram
        $tg = new TelegramService();
        $tg->notifyClient($order);
        $tg->notifyManagers($order);

        return redirect()->route('track')
            ->with('success', 'Commande envoyée ! Numéro : ' . $order->order_number);
    }
}
