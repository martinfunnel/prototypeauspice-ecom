@extends('components.layout')

@section('title', 'Mon panier — Santé Ivoire')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="font-display text-3xl font-bold">Mon panier</h1>

    @if(empty($cart['items']))
        <div class="mt-8 rounded-2xl border border-dashed border-border p-12 text-center">
            <p class="text-muted-foreground">Votre panier est vide.</p>
            <a href="/catalogue" class="mt-4 inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 transition">Voir le catalogue</a>
        </div>
    @else
        <ul class="mt-6 divide-y divide-border rounded-2xl border border-border bg-card">
            @foreach($cart['items'] as $item)
                <li class="flex items-center gap-4 p-4">
                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-md bg-muted">
                        @if(!empty($item['product']->images[0]))
                            <img src="{{ $item['product']->images[0] }}" alt="" class="h-full w-full object-cover">
                        @else
                            <div class="grid h-full place-items-center text-2xl">📦</div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold truncate">{{ $item['product']->name }}</p>
                        <p class="text-sm text-muted-foreground">{{ number_format($item['price'], 0, ',', ' ') }} FCFA</p>
                    </div>
                    <div class="flex items-center rounded-lg border border-border">
                        <form action="/panier/maj" method="POST" class="contents">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                            <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                            <button type="submit" class="grid h-9 w-9 place-items-center hover:bg-muted transition {{ $item['quantity'] <= 1 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                            </button>
                        </form>
                        <span class="w-8 text-center text-sm font-semibold">{{ $item['quantity'] }}</span>
                        <form action="/panier/maj" method="POST" class="contents">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                            <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                            <button type="submit" class="grid h-9 w-9 place-items-center hover:bg-muted transition">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </form>
                    </div>
                    <form action="/panier/supprimer" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                        <button type="submit" class="text-destructive hover:opacity-70 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>

        <div class="mt-6 flex items-center justify-between rounded-2xl border border-border bg-card p-5">
            <span class="text-sm text-muted-foreground">Sous-total (livraison calculée à la commande)</span>
            <span class="font-display text-xl font-bold text-primary">{{ number_format($cart['total'], 0, ',', ' ') }} FCFA</span>
        </div>
        <a href="/commande" class="mt-4 block w-full rounded-xl bg-accent px-6 py-4 text-center text-base font-bold text-accent-foreground shadow-accent transition hover:scale-[1.02]">Passer la commande →</a>
    @endif
</section>
@endsection
