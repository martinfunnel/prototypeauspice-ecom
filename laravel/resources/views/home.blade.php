@extends('components.layout')

@section('title', 'Auspice Market — Produits naturels & bio')

@section('content')

{{-- Hero --}}
<section class="bg-emerald-900 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">Des produits naturels pour votre bien-être</h1>
        <p class="text-xl text-emerald-200 mb-8 max-w-2xl mx-auto">Découvrez notre sélection de compléments alimentaires, soins du corps et produits bio, livrés partout en Côte d'Ivoire.</p>
        <a href="/catalogue" class="inline-block bg-white text-emerald-900 px-8 py-3 rounded-lg font-semibold hover:bg-emerald-100 transition">
            Voir le catalogue
        </a>
    </div>
</section>

{{-- Catégories --}}
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12 text-gray-900">Nos rayons</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            @foreach($categories as $category)
                <a href="/catalogue?category={{ $category->slug }}" class="block p-6 bg-gray-50 rounded-xl hover:bg-emerald-50 hover:shadow-lg transition text-center">
                    <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                    <p class="text-sm text-gray-500 mt-2">{{ $category->description }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Produits populaires --}}
@if($popularProducts->count())
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12 text-gray-900">Produits populaires</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach($popularProducts as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="/catalogue" class="inline-block border-2 border-emerald-900 text-emerald-900 px-8 py-3 rounded-lg font-semibold hover:bg-emerald-900 hover:text-white transition">
                Voir tout le catalogue
            </a>
        </div>
    </div>
</section>
@endif

{{-- Bannière promo --}}
@if($banner)
<section class="py-16 bg-emerald-800 text-white">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">{{ $banner->title }}</h2>
        <p class="text-xl text-emerald-200 mb-8">{{ $banner->subtitle }}</p>
        <a href="{{ $banner->cta_url }}" class="inline-block bg-white text-emerald-900 px-8 py-3 rounded-lg font-semibold hover:bg-emerald-100 transition">
            {{ $banner->cta_label }}
        </a>
    </div>
</section>
@endif

{{-- Témoignages --}}
@if($testimonials->count())
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12 text-gray-900">Ce que disent nos clients</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @foreach($testimonials as $testimonial)
                <div class="p-6 bg-gray-50 rounded-xl">
                    <div class="flex items-center mb-4">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <p class="text-gray-700 mb-4">"{{ $testimonial->content }}"</p>
                    <div>
                        <p class="font-semibold text-gray-900">{{ $testimonial->author_name }}</p>
                        <p class="text-sm text-gray-500">{{ $testimonial->role }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
