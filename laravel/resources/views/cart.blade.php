@extends('components.layout')

@section('title', 'Mon panier — Auspice Market')

@section('content')
<section class="py-12 bg-white min-h-[60vh]">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Mon panier</h1>

        @if(empty($cart['items']))
            <div class="text-center py-16">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <p class="text-gray-500 text-lg mb-6">Votre panier est vide</p>
                <a href="/catalogue" class="inline-block bg-emerald-900 text-white px-8 py-3 rounded-lg hover:bg-emerald-800 transition">Découvrir le catalogue</a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($cart['items'] as $item)
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                        <img src="{{ $item['product']->images[0] ?? '' }}" alt="" class="w-20 h-20 object-cover rounded-lg bg-gray-200">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ $item['product']->name }}</h3>
                            <p class="text-sm text-gray-500">{{ number_format($item['price'], 0, ',', ' ') }} FCFA / unité</p>
                        </div>
                        <form action="/panier/maj" method="POST" class="flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                            <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="0" class="w-16 border border-gray-300 rounded px-2 py-1 text-center">
                            <button type="submit" class="text-sm text-emerald-700 hover:underline">Mettre à jour</button>
                        </form>
                        <div class="text-right min-w-[120px]">
                            <p class="font-semibold text-gray-900">{{ number_format($item['subtotal'], 0, ',', ' ') }} FCFA</p>
                        </div>
                        <form action="/panier/supprimer" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 p-6 bg-gray-50 rounded-xl">
                <div class="flex justify-between items-center mb-6">
                    <span class="text-lg text-gray-600">Sous-total</span>
                    <span class="text-2xl font-bold text-gray-900">{{ number_format($cart['total'], 0, ',', ' ') }} FCFA</span>
                </div>
                <a href="/commande" class="block w-full bg-emerald-900 text-white text-center px-6 py-3 rounded-lg font-semibold hover:bg-emerald-800 transition">
                    Passer la commande
                </a>
            </div>
        @endif
    </div>
</section>
@endsection
