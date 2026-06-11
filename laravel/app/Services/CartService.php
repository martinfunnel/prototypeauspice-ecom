<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    const CART_KEY = 'cart';

    public static function get(): array
    {
        return Session::get(self::CART_KEY, []);
    }

    public static function add(string $productId, int $quantity = 1): void
    {
        $cart = self::get();

        if (isset($cart[$productId])) {
            $cart[$productId] += $quantity;
        } else {
            $cart[$productId] = $quantity;
        }

        Session::put(self::CART_KEY, $cart);
    }

    public static function remove(string $productId): void
    {
        $cart = self::get();
        unset($cart[$productId]);
        Session::put(self::CART_KEY, $cart);
    }

    public static function update(string $productId, int $quantity): void
    {
        $cart = self::get();
        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }
        Session::put(self::CART_KEY, $cart);
    }

    public static function clear(): void
    {
        Session::forget(self::CART_KEY);
    }

    public static function count(): int
    {
        return array_sum(self::get());
    }

    public static function items(): array
    {
        $cart = self::get();
        $items = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = Product::find($productId);
            if ($product) {
                $price = $product->displayPrice();
                $subtotal = $price * $quantity;
                $total += $subtotal;
                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }
        }

        return [
            'items' => $items,
            'total' => $total,
            'count' => self::count(),
        ];
    }
}
