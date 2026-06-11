@extends('components.layout')

@section('title', 'Suivre ma commande — Santé Ivoire')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="font-display text-3xl font-bold">Suivre ma commande</h1>
    <p class="mt-1 text-sm text-muted-foreground">Entrez le numéro de commande reçu (ex : CMD-260611-01006).</p>

    <form action="/suivi" method="POST" class="mt-6 flex gap-2">
        @csrf
        <input type="text" name="order_number" required value="{{ old('order_number') }}" placeholder="CMD-260611-01006" class="flex-1 rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
        <button type="submit" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground transition hover:scale-[1.02]">Rechercher</button>
    </form>

    @if(session('success'))
        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(isset($orders))
        @if($orders->count())
            <ul class="mt-8 space-y-4">
                @foreach($orders as $order)
                    <li class="rounded-2xl border border-border bg-card p-5 shadow-card">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="font-display text-lg font-bold text-primary">{{ $order->order_number }}</p>
                                <p class="text-xs text-muted-foreground">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                @if($order->status == 'delivered') bg-green-100 text-green-800
                                @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                                @elseif($order->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->status == 'confirmed') bg-blue-100 text-blue-800
                                @elseif($order->status == 'processing') bg-purple-100 text-purple-800
                                @elseif($order->status == 'shipped') bg-indigo-100 text-indigo-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $order->statusLabel() }}
                            </span>
                        </div>
                        <ul class="mt-3 space-y-1 text-sm text-foreground/80">
                            @foreach($order->items as $item)
                                <li>• {{ $item->quantity }}× {{ $item->product_name }}</li>
                            @endforeach
                        </ul>
                        <div class="mt-3 flex items-center justify-between border-t border-border pt-3 text-sm">
                            <span class="text-muted-foreground">{{ $order->commune_name }} — Livraison {{ number_format($order->delivery_fee, 0, ',', ' ') }} FCFA</span>
                            <span class="font-bold text-primary">{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="mt-8 rounded-2xl border border-dashed border-border p-12 text-center text-sm text-muted-foreground">
                Aucune commande trouvée pour ce numéro de commande.
            </div>
        @endif
    @endif
</section>
@endsection
