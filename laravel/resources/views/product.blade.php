@extends('components.layout')

@section('title', $product->name . ' — Auspice Market')

@section('content')
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-12">
            {{-- Images --}}
            <div>
                @if(!empty($product->images[0]))
                    <img src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="w-full rounded-xl mb-4">
                @endif
                @if(count($product->images) > 1)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(array_slice($product->images, 1) as $img)
                            <img src="{{ $img }}" class="rounded-lg aspect-square object-cover">
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Détails --}}
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $product->name }}</h1>
                <p class="text-gray-600 mb-6">{{ $product->description }}</p>

                @if(!empty($product->benefits))
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 mb-2">Bénéfices</h3>
                        <ul class="space-y-1">
                            @foreach($product->benefits as $benefit)
                                <li class="flex items-center text-gray-600">
                                    <svg class="w-4 h-4 text-emerald-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    {{ $benefit }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-6">
                    @if($product->hasPromo())
                        <span class="text-3xl font-bold text-emerald-700">{{ number_format($product->displayPrice(), 0, ',', ' ') }} FCFA</span>
                        <span class="text-xl text-gray-400 line-through ml-3">{{ number_format($product->price, 0, ',', ' ') }} FCFA</span>
                    @else
                        <span class="text-3xl font-bold text-gray-900">{{ number_format($product->price, 0, ',', ' ') }} FCFA</span>
                    @endif
                </div>

                <form action="/panier/ajouter" method="POST" class="flex gap-4 mb-8">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="number" name="quantity" value="1" min="1" class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-center">
                    <button type="submit" class="flex-1 bg-emerald-900 text-white px-6 py-3 rounded-lg font-semibold hover:bg-emerald-800 transition">
                        Ajouter au panier
                    </button>
                </form>

                {{-- Commande directe --}}
                <a href="/commande?product_id={{ $product->id }}&quantity=1" class="block w-full text-center border-2 border-emerald-900 text-emerald-900 px-6 py-3 rounded-lg font-semibold hover:bg-emerald-900 hover:text-white transition">
                    Commander maintenant
                </a>
            </div>
        </div>

        {{-- Produits similaires --}}
        @if($related->count())
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Produits similaires</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($related as $item)
                    @include('components.product-card', ['product' => $item])
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
