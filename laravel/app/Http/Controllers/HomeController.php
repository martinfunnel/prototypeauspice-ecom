<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\PromoBanner;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->get();
        $products = Product::active()->orderBy('is_popular', 'desc')->limit(8)->get();
        $featured = Product::active()->where('slug', 'cacaocelyan')->first();
        $testimonials = Testimonial::active()->limit(6)->get();

        return view('home', compact('categories', 'products', 'featured', 'testimonials'));
    }
}
