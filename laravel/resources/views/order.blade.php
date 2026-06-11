@extends('components.layout')

@section('title', 'Finaliser ma commande — Santé Ivoire')

@section('content')
<section class="mx-auto max-w-5xl px-4 py-10">
    <h1 class="font-display text-3xl font-bold">Finaliser ma commande</h1>
    <p class="mt-1 text-sm text-success">💵 Paiement à la livraison disponible</p>

    @if(empty($cart['items']))
        <div class="mt-16 text-center">
            <p class="text-muted-foreground">Votre panier est vide.</p>
            <a href="/catalogue" class="mt-4 inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 transition">Voir le catalogue</a>
        </div>
    @else
        <div class="mt-8 grid gap-8 md:grid-cols-[1.5fr_1fr]">
            <form action="/commande" method="POST" class="space-y-4">
                @csrf
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Nom complet *</span>
                    <input type="text" name="customer_name" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Numéro de téléphone *</span>
                    <input type="tel" name="customer_phone" required placeholder="+225 07 00 00 00 00" class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Commune / lieu de livraison *</span>
                    <select name="commune_id" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                        <option value="">— Sélectionner —</option>
                        @foreach($communes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->zone }}) — {{ number_format($c->delivery_fee, 0, ',', ' ') }} FCFA</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Adresse précise *</span>
                    <textarea name="address" required rows="2" placeholder="Quartier, rue, point de repère..." class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent"></textarea>
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Notes (optionnel)</span>
                    <textarea name="notes" rows="2" placeholder="Instructions particulières..." class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent"></textarea>
                </label>
                <button type="submit" class="w-full rounded-xl bg-accent px-6 py-4 text-base font-bold text-accent-foreground shadow-accent transition hover:scale-[1.02]">
                    Confirmer la commande ({{ number_format($cart['total'], 0, ',', ' ') }} FCFA)
                </button>
            </form>

            <aside class="h-fit rounded-2xl border border-border bg-card p-5 shadow-card">
                <h3 class="font-display text-lg font-bold">Récapitulatif</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach($cart['items'] as $item)
                        <li class="flex justify-between">
                            <span class="text-foreground/80">{{ $item['quantity'] }}× {{ $item['product']->name }}</span>
                            <span class="font-semibold">{{ number_format($item['subtotal'], 0, ',', ' ') }} FCFA</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 space-y-1 border-t border-border pt-3 text-sm">
                    <div class="flex justify-between text-foreground/80"><span>Sous-total</span><span>{{ number_format($cart['total'], 0, ',', ' ') }} FCFA</span></div>
                    <div class="flex justify-between text-foreground/80"><span>Livraison</span><span>—</span></div>
                    <div class="flex justify-between text-base font-bold text-primary"><span>Total</span><span>{{ number_format($cart['total'], 0, ',', ' ') }} FCFA</span></div>
                </div>
            </aside>
        </div>
    @endif
</section>
@endsection
