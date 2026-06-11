@extends('components.layout')

@section('title', $product->name . ' — Santé Ivoire')

@section('content')
<section class="mx-auto px-4 py-10 max-w-7xl">

    {{-- Section produit : images + détails --}}
    <div class="grid gap-8 md:grid-cols-2">
        {{-- Images --}}
        <div>
            <div class="aspect-square overflow-hidden rounded-2xl bg-muted">
                @if(!empty($product->images[0]))
                    <img id="main-image" src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                @else
                    <div class="grid h-full place-items-center text-7xl">📦</div>
                @endif
            </div>
            @if(count($product->images) > 1)
                <div class="mt-3 flex gap-2 overflow-x-auto">
                    @foreach($product->images as $i => $src)
                        <button type="button" onclick="document.getElementById('main-image').src='{{ $src }}'" class="thumb-btn h-16 w-16 shrink-0 overflow-hidden rounded-md border-2 {{ $i === 0 ? 'border-accent' : 'border-transparent' }} transition hover:border-accent">
                            <img src="{{ $src }}" alt="" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Détails --}}
        <div>
            <h1 class="font-display text-3xl font-bold md:text-4xl">{{ $product->name }}</h1>
            <div class="mt-4 flex items-baseline gap-3">
                <span class="font-display text-3xl font-bold text-primary">{{ number_format($product->displayPrice(), 0, ',', ' ') }} FCFA</span>
                @if($product->hasPromo())
                    <span class="text-base text-muted-foreground line-through">{{ number_format($product->price, 0, ',', ' ') }} FCFA</span>
                @endif
            </div>
            <p class="mt-2 inline-block rounded-full bg-success/15 px-3 py-1 text-xs font-semibold text-success">💵 Paiement à la livraison disponible</p>
            @if($product->short_description)
                <p class="mt-5 text-base text-foreground/80">{{ $product->short_description }}</p>
            @endif

            @if(!empty($product->benefits))
                <ul class="mt-5 space-y-2">
                    @foreach(array_slice($product->benefits, 0, 4) as $benefit)
                        <li class="flex items-start gap-2 text-sm">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ $benefit }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- Quantité + Panier --}}
            <form action="/panier/ajouter" method="POST" class="mt-6 flex items-center gap-3">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <span class="text-sm font-semibold">Quantité :</span>
                <div class="flex items-center rounded-lg border border-border">
                    <button type="button" onclick="let q=document.getElementById('qty'); q.value=Math.max(1,parseInt(q.value)-1)" class="grid h-11 w-11 place-items-center hover:bg-muted transition"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg></button>
                    <input type="number" id="qty" name="quantity" value="1" min="1" class="w-10 text-center font-semibold bg-transparent border-none focus:ring-0 p-0">
                    <button type="button" onclick="let q=document.getElementById('qty'); q.value=parseInt(q.value)+1" class="grid h-11 w-11 place-items-center hover:bg-muted transition"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg></button>
                </div>
                <button type="submit" class="ml-auto rounded-xl bg-accent px-6 py-3 text-sm font-bold text-accent-foreground shadow-accent transition hover:scale-105">Ajouter au panier</button>
            </form>
        </div>
    </div>

    {{-- Section commande directe --}}
    <div class="mt-12 rounded-2xl border border-border bg-card p-6 shadow-card md:p-8">
        <h2 class="font-display text-2xl font-bold">Commander ce produit</h2>
        <p class="mt-1 text-sm text-success">💵 Paiement à la livraison</p>

        <div class="mt-6 grid gap-8 md:grid-cols-[1.5fr_1fr]">
            <form action="/commande-directe" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1" id="direct-qty">

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
                    <select name="commune_id" required id="commune-select" class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                        <option value="">— Sélectionner —</option>
                        @foreach($communes as $c)
                            <option value="{{ $c->id }}" data-fee="{{ $c->delivery_fee }}">{{ $c->name }} ({{ $c->zone }}) — {{ number_format($c->delivery_fee, 0, ',', ' ') }} FCFA</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Adresse précise *</span>
                    <textarea name="address" required rows="2" placeholder="Quartier, rue, point de repère..." class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent"></textarea>
                </label>
                <button type="submit" class="w-full rounded-xl bg-accent px-6 py-4 text-base font-bold text-accent-foreground shadow-accent transition hover:scale-[1.02]">
                    Confirmer la commande
                </button>
            </form>

            {{-- Récapitulatif --}}
            <aside class="h-fit rounded-2xl border border-border bg-background p-5">
                <h3 class="font-display text-lg font-bold">Récapitulatif</h3>
                <div class="mt-4 flex justify-between text-sm">
                    <span class="text-foreground/80"><span id="recap-qty">1</span>× {{ $product->name }}</span>
                    <span class="font-semibold" id="recap-subtotal">{{ number_format($product->displayPrice(), 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="mt-4 space-y-1 border-t border-border pt-3 text-sm">
                    <div class="flex justify-between text-foreground/80"><span>Sous-total</span><span id="recap-subtotal2">{{ number_format($product->displayPrice(), 0, ',', ' ') }} FCFA</span></div>
                    <div class="flex justify-between text-foreground/80"><span>Livraison</span><span id="recap-fee">—</span></div>
                    <div class="flex justify-between text-base font-bold text-primary"><span>Total</span><span id="recap-total">{{ number_format($product->displayPrice(), 0, ',', ' ') }} FCFA</span></div>
                </div>
            </aside>
        </div>
    </div>

    {{-- Pourquoi choisir ce produit --}}
    @if(!empty($product->benefits))
    <section class="mt-16">
        <h2 class="font-display text-2xl font-bold md:text-3xl">Pourquoi choisir ce produit ?</h2>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($product->benefits as $benefit)
                <div class="flex items-start gap-3 rounded-2xl border border-border bg-card p-5 shadow-card">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-success/15 text-success">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-sm font-medium text-foreground/90">{{ $benefit }}</p>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Détails du produit --}}
    @if($product->description || !empty($product->detail_images))
    <section class="mt-16">
        <h2 class="font-display text-2xl font-bold md:text-3xl">Détails du produit</h2>
        <div class="mt-5 rounded-2xl border border-border bg-card p-6 md:p-8">
            @if($product->description)
                <p class="whitespace-pre-line text-base leading-relaxed text-foreground/80">{{ $product->description }}</p>
            @endif
            @if(!empty($product->detail_images))
                <div class="grid gap-4 {{ $product->description ? 'mt-6' : '' }} sm:grid-cols-2">
                    @foreach($product->detail_images as $src)
                        <div class="overflow-hidden rounded-xl bg-muted">
                            <img src="{{ $src }}" alt="{{ $product->name }} détail" class="h-full w-full object-cover" loading="lazy">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
    @endif

    {{-- En images --}}
    @if(count($product->images) > 1)
    <section class="mt-16">
        <h2 class="font-display text-2xl font-bold md:text-3xl">En images</h2>
        <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
            @foreach(array_slice($product->images, 1) as $src)
                <div class="aspect-square overflow-hidden rounded-xl bg-muted">
                    <img src="{{ $src }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Réassurance --}}
    <section class="mt-16 grid gap-4 sm:grid-cols-3">
        <div class="flex items-start gap-3 rounded-2xl border border-border bg-background p-5">
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-primary/10 text-primary">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
            </div>
            <div>
                <p class="font-semibold">Livraison rapide</p>
                <p class="text-sm text-muted-foreground">Partout à Abidjan et environs</p>
            </div>
        </div>
        <div class="flex items-start gap-3 rounded-2xl border border-border bg-background p-5">
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-primary/10 text-primary">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div>
                <p class="font-semibold">Paiement à la livraison</p>
                <p class="text-sm text-muted-foreground">Payez seulement à la réception</p>
            </div>
        </div>
        <div class="flex items-start gap-3 rounded-2xl border border-border bg-background p-5">
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-primary/10 text-primary">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <p class="font-semibold">Produits vérifiés</p>
                <p class="text-sm text-muted-foreground">Qualité contrôlée à chaque commande</p>
            </div>
        </div>
    </section>

    {{-- Produits similaires --}}
    @if($related->count())
    <div class="mt-16">
        <h2 class="font-display text-2xl font-bold">Produits similaires</h2>
        <div class="mt-5 grid grid-cols-2 gap-4 md:grid-cols-4">
            @foreach($related as $item)
                @include('components.product-card', ['product' => $item])
            @endforeach
        </div>
    </div>
    @endif

</section>

<script>
(function() {
    const qtyInput = document.getElementById('qty');
    const directQty = document.getElementById('direct-qty');
    const communeSelect = document.getElementById('commune-select');
    const price = {{ $product->displayPrice() }};

    function updateRecap() {
        const qty = parseInt(qtyInput.value) || 1;
        directQty.value = qty;
        const subtotal = price * qty;
        const feeOption = communeSelect.options[communeSelect.selectedIndex];
        const fee = feeOption.dataset.fee ? parseInt(feeOption.dataset.fee) : 0;
        const total = subtotal + fee;

        document.getElementById('recap-qty').textContent = qty;
        document.getElementById('recap-subtotal').textContent = subtotal.toLocaleString('fr-FR') + ' FCFA';
        document.getElementById('recap-subtotal2').textContent = subtotal.toLocaleString('fr-FR') + ' FCFA';
        document.getElementById('recap-fee').textContent = fee ? fee.toLocaleString('fr-FR') + ' FCFA' : '—';
        document.getElementById('recap-total').textContent = total.toLocaleString('fr-FR') + ' FCFA';
    }

    qtyInput.addEventListener('change', updateRecap);
    qtyInput.addEventListener('input', updateRecap);
    communeSelect.addEventListener('change', updateRecap);
    updateRecap();
})();
</script>
@endsection
