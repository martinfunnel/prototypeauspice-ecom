<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\Product;
use App\Models\Testimonial;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::active()->where('slug', $slug)->firstOrFail();
        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();
        $communes = Commune::active()->orderBy('zone')->orderBy('name')->get();
        $testimonials = Testimonial::orderBy('sort_order')->limit(6)->get();

        return view('product', compact('product', 'related', 'communes', 'testimonials'));
    }
}
