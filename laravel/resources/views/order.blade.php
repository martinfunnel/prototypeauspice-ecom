@extends('components.layout')

@section('title', 'Passer commande — Auspice Market')

@section('content')
<section class="py-12 bg-white min-h-[60vh]">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Passer commande</h1>

        <div class="grid md:grid-cols-2 gap-12">
            {{-- Formulaire --}}
            <div>
                <form action="/commande" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                        <input type="text" name="customer_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone (WhatsApp)</label>
                        <input type="tel" name="customer_phone" required placeholder="+225 07 XX XX XX XX" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Commune de livraison</label>
                        <select name="commune_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Choisir une commune</option>
                            @foreach($communes as $commune)
                                <option value="{{ $commune->id }}">{{ $commune->name }} — {{ number_format($commune->delivery_fee, 0, ',', ' ') }} FCFA ({{ $commune->delivery_days }} jour(s))</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse précise</label>
                        <textarea name="address" required rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Quartier, rue, point de repère..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optionnel)</label>
                        <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-emerald-900 text-white px-6 py-3 rounded-lg font-semibold hover:bg-emerald-800 transition">
                        Confirmer la commande
                    </button>
                </form>
            </div>

            {{-- Récapitulatif --}}
            <div class="bg-gray-50 p-6 rounded-xl h-fit">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Récapitulatif</h2>
                <div class="space-y-4">
                    @foreach($cart['items'] as $item)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ $item['product']->name }} x{{ $item['quantity'] }}</span>
                            <span class="font-medium">{{ number_format($item['subtotal'], 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-gray-200 mt-4 pt-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Sous-total</span>
                        <span>{{ number_format($cart['total'], 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex justify-between mb-4">
                        <span class="text-gray-600">Livraison</span>
                        <span class="text-gray-500">À calculer</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total</span>
                        <span>{{ number_format($cart['total'], 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
