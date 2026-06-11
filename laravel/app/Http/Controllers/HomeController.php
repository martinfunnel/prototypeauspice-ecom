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
        $popularProducts = Product::active()->popular()->limit(8)->get();
        $testimonials = Testimonial::active()->limit(6)->get();
        $banner = PromoBanner::active()->where('key', 'catalogue')->first();

        return view('home', compact('categories', 'popularProducts', 'testimonials', 'banner'));
    }
}
