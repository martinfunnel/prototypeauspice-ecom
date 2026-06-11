@extends('components.layout')

@section('title', 'Suivi de commande — Auspice Market')

@section('content')
<section class="py-12 bg-white min-h-[60vh]">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Suivre ma commande</h1>

        <form action="/suivi" method="POST" class="mb-10 max-w-md">
            @csrf
            <label class="block text-sm font-medium text-gray-700 mb-2">Numéro de téléphone utilisé lors de la commande</label>
            <div class="flex gap-2">
                <input type="tel" name="phone" required value="{{ old('phone') }}" placeholder="+225 07 XX XX XX XX" class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <button type="submit" class="bg-emerald-900 text-white px-6 py-2 rounded-lg hover:bg-emerald-800 transition">Rechercher</button>
            </div>
        </form>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(isset($orders))
            @if($orders->count())
                <div class="space-y-4">
                    @foreach($orders as $order)
                        <div class="border border-gray-200 rounded-xl p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-sm text-gray-500">Commande {{ $order->order_number }}</p>
                                    <p class="font-semibold text-gray-900">{{ $order->customer_name }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    @if($order->status == 'delivered') bg-green-100 text-green-800
                                    @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ $order->statusLabel() }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 mb-4">
                                <p>{{ $order->commune_name }} — {{ $order->address }}</p>
                                <p class="mt-1">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                            <div class="border-t border-gray-100 pt-4">
                                @foreach($order->items as $item)
                                    <div class="flex justify-between text-sm py-1">
                                        <span>{{ $item->product_name }} x{{ $item->quantity }}</span>
                                        <span>{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</span>
                                    </div>
                                @endforeach
                                <div class="flex justify-between font-bold mt-2 pt-2 border-t border-gray-100">
                                    <span>Total</span>
                                    <span>{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    Aucune commande trouvée pour ce numéro.
                </div>
            @endif
        @endif
    </div>
</section>
@endsection
