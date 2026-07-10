@extends('components.layout')

@section('title', 'Auspice Market — Bien-être bio & naturel')

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden gradient-hero text-primary-foreground">
    <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-accent/30 blur-3xl"></div>
    <div class="absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-teal/20 blur-3xl"></div>
    <div class="relative mx-auto grid gap-10 px-4 py-16 md:grid-cols-2 md:py-24 max-w-7xl">
        <div class="flex flex-col justify-center">
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-primary-foreground/20 bg-primary-foreground/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider">
                <svg class="h-3.5 w-3.5 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                Auspice SARL · Bio & livraison Côte d'Ivoire
            </span>
            <h1 class="mt-5 font-display text-4xl font-bold leading-tight md:text-6xl text-balance">
                Le bien-être bio,<br><span class="text-accent">enraciné dans la nature.</span>
            </h1>
            <p class="mt-4 max-w-lg text-base text-primary-foreground/80 md:text-lg">
                Auspice Market vous propose des compléments alimentaires et produits de santé 100% issus de l'agriculture biologique. Découvrez notre best-seller : le <strong class="text-accent">cacao à la cannelle de Ceylan</strong>.
            </p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a href="/produit/cacaocelyan" class="inline-flex items-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-semibold text-accent-foreground shadow-accent transition hover:scale-105">
                    Découvrir le cacao Ceylan
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="/catalogue" class="inline-flex items-center gap-2 rounded-xl border border-primary-foreground/30 px-6 py-3 text-sm font-semibold text-primary-foreground hover:bg-primary-foreground/10 transition">
                    Voir la boutique
                </a>
            </div>
            <div class="mt-8 grid grid-cols-3 gap-3 text-xs">
                <div class="flex items-center gap-2 text-primary-foreground/80">
                    <svg class="h-4 w-4 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    Livraison 24-48h
                </div>
                <div class="flex items-center gap-2 text-primary-foreground/80">
                    <svg class="h-4 w-4 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Paiement à la livraison
                </div>
                <div class="flex items-center gap-2 text-primary-foreground/80">
                    <svg class="h-4 w-4 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Certifié bio
                </div>
            </div>
        </div>
        <div class="relative hidden md:block">
            @if($featured && !empty($featured->images[0]))
                <a href="/produit/{{ $featured->slug }}" class="group relative mx-auto block aspect-square w-full max-w-md rounded-3xl bg-gradient-to-br from-accent/40 to-teal/30 p-6 shadow-elevated transition hover:scale-[1.02]">
                    <img src="{{ $featured->images[0] }}" alt="{{ $featured->name }} — Auspice Market" class="h-full w-full object-contain drop-shadow-2xl">
                    <span class="absolute left-4 top-4 inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1 text-xs font-bold text-accent-foreground shadow-accent">
                        <svg class="h-3 w-3 fill-current" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        Best-seller bio
                    </span>
                </a>
            @endif
        </div>
    </div>
</section>

{{-- FEATURED PRODUCT SPOTLIGHT --}}
@if($featured)
<section class="mx-auto px-4 py-12 md:py-16 max-w-7xl">
    <div class="overflow-hidden rounded-3xl border border-border bg-card shadow-elevated">
        <div class="grid gap-0 md:grid-cols-2">
            <div class="relative flex items-center justify-center bg-gradient-to-br from-accent/20 via-background to-teal/10 p-8 md:p-12">
                <span class="absolute left-6 top-6 inline-flex items-center gap-1.5 rounded-full bg-accent px-3 py-1 text-xs font-bold text-accent-foreground shadow-accent">
                    <svg class="h-3 w-3 fill-current" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Produit phare
                </span>
                @if(!empty($featured->images[0]))
                    <img src="{{ $featured->images[0] }}" alt="{{ $featured->name }}" class="max-h-[420px] w-auto object-contain drop-shadow-2xl">
                @endif
            </div>
            <div class="flex flex-col justify-center p-8 md:p-12">
                <span class="text-xs font-semibold uppercase tracking-wider text-accent">Auspice Market · Best-seller bio</span>
                <h2 class="mt-3 font-display text-3xl font-bold leading-tight md:text-4xl">
                    Cacao brut à la <span class="text-accent">cannelle de Ceylan</span>
                </h2>
                <p class="mt-3 text-base text-foreground/80">
                    {{ $featured->short_description ?? "Une poudre 100% naturelle, riche en antioxydants, pour un cacao chaud onctueux et plein de bienfaits." }}
                </p>
                <ul class="mt-5 space-y-2">
                    @foreach(array_slice($featured->benefits ?? ["100% naturel et bio", "Riche en antioxydants", "Cannelle de Ceylan authentique", "Sans sucre ajouté"], 0, 4) as $benefit)
                        <li class="flex items-start gap-2 text-sm">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ $benefit }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6 flex items-baseline gap-3">
                    <span class="font-display text-3xl font-bold text-primary">{{ number_format($featured->displayPrice(), 0, ',', ' ') }} FCFA</span>
                    @if($featured->hasPromo())
                        <span class="text-base text-muted-foreground line-through">{{ number_format($featured->price, 0, ',', ' ') }} FCFA</span>
                    @endif
                </div>
                <a href="/produit/{{ $featured->slug }}" class="mt-6 inline-flex w-fit items-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-bold text-accent-foreground shadow-accent transition hover:scale-105">
                    Commander maintenant
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <p class="mt-3 text-xs text-success">💵 Paiement à la livraison disponible</p>
            </div>
        </div>
    </div>
</section>
@endif

{{-- CATEGORIES --}}
<section class="mx-auto px-4 py-12 md:py-16 max-w-7xl">
    <div class="mb-6 flex items-end justify-between">
        <h2 class="font-display text-2xl font-bold md:text-3xl">Catégories</h2>
        <a href="/catalogue" class="text-sm font-semibold text-accent hover:underline">Tout voir →</a>
    </div>
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5">
        @foreach($categories as $category)
            <a href="/catalogue?category={{ $category->slug }}" class="group rounded-xl border border-border bg-card p-5 shadow-card transition hover:-translate-y-0.5 hover:border-accent hover:shadow-elevated">
                <div class="text-3xl">🌿</div>
                <h3 class="mt-3 font-display text-sm font-bold">{{ $category->name }}</h3>
                <p class="mt-1 line-clamp-2 text-xs text-muted-foreground">{{ $category->description }}</p>
            </a>
        @endforeach
    </div>
</section>

{{-- FEATURED PRODUCTS --}}
<section class="mx-auto px-4 pb-16 max-w-7xl">
    <div class="mb-6 flex items-end justify-between">
        <div>
            <h2 class="font-display text-2xl font-bold md:text-3xl">Nos produits bio populaires</h2>
            <p class="mt-1 text-sm text-muted-foreground">Sélectionnés par Auspice SARL pour leur pureté et leur efficacité.</p>
        </div>
        <a href="/catalogue" class="text-sm font-semibold text-accent hover:underline">Tout voir →</a>
    </div>
    @if($products->count() === 0)
        <div class="rounded-2xl border border-dashed border-border bg-muted/30 p-12 text-center">
            <p class="text-sm text-muted-foreground">Aucun produit pour le moment. L'administrateur peut en ajouter depuis le dashboard.</p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
            @foreach($products as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>
    @endif
</section>

{{-- TESTIMONIALS --}}
@include('components.testimonials-carousel', ['testimonials' => $testimonials])

{{-- CTA --}}
<section class="mx-auto px-4 pb-16 max-w-7xl">
    <div class="overflow-hidden rounded-3xl gradient-accent p-10 text-accent-foreground shadow-accent md:p-14">
        <div class="grid items-center gap-6 md:grid-cols-[1fr_auto]">
            <div>
                <h3 class="font-display text-3xl font-bold">Commandez en 2 minutes</h3>
                <p class="mt-2 max-w-xl text-accent-foreground/90">Pas de carte bancaire. Vous payez le livreur en espèces à la réception, partout en Côte d'Ivoire.</p>
            </div>
            <a href="/produit/cacaocelyan" class="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-primary-foreground hover:opacity-90 transition">
                Commander le cacao Ceylan
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>
</section>

@endsection
