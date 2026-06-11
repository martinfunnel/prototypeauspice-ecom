<a href="/produit/{{ $product->slug }}" class="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-card transition hover:-translate-y-0.5 hover:shadow-elevated">
    <div class="relative aspect-square overflow-hidden bg-muted">
        @if(!empty($product->images[0]))
            <img src="{{ $product->images[0] }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition group-hover:scale-105">
        @else
            <div class="grid h-full w-full place-items-center text-muted-foreground">📦</div>
        @endif
        @if($product->hasPromo())
            <span class="absolute left-3 top-3 rounded-full bg-accent px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-accent-foreground shadow-accent">Promo</span>
        @endif
        @if($product->is_popular)
            <span class="absolute right-3 top-3 rounded-full bg-primary px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-primary-foreground">★ Populaire</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col gap-2 p-4">
        <h3 class="line-clamp-2 font-display text-base font-semibold leading-tight text-foreground">{{ $product->name }}</h3>
        @if($product->short_description)
            <p class="line-clamp-2 text-xs text-muted-foreground">{{ $product->short_description }}</p>
        @endif
        <div class="mt-auto flex items-baseline gap-2 pt-2">
            <span class="font-display text-lg font-bold text-primary">{{ number_format($product->displayPrice(), 0, ',', ' ') }} FCFA</span>
            @if($product->hasPromo())
                <span class="text-xs text-muted-foreground line-through">{{ number_format($product->price, 0, ',', ' ') }} FCFA</span>
            @endif
        </div>
    </div>
</a>
