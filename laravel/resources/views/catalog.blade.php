@extends('components.layout')

@section('title', 'Catalogue — Santé Ivoire')

@section('content')
<section class="mx-auto px-4 py-10 max-w-7xl">

    {{-- Promo Banner --}}
    @if($banner)
        <a href="{{ $banner->cta_url ?? '#' }}" class="mb-8 block">
            <div class="relative flex min-h-[180px] flex-col justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-primary via-primary to-accent/80 p-6 text-primary-foreground shadow-card md:min-h-[220px] md:p-10"
                @if($banner->image_url)
                    style="background-image: linear-gradient(90deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.25) 60%, rgba(0,0,0,0.1) 100%), url('{{ $banner->image_url }}'); background-size: cover; background-position: center;"
                @endif
            >
                <span class="inline-flex w-fit items-center gap-1 rounded-full bg-accent/90 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-accent-foreground">Offre spéciale</span>
                @if($banner->title)<h2 class="font-display text-2xl font-bold md:text-4xl">{{ $banner->title }}</h2>@endif
                @if($banner->subtitle)<p class="max-w-2xl text-sm opacity-95 md:text-base">{{ $banner->subtitle }}</p>@endif
                @if($banner->cta_label)<span class="mt-2 inline-flex w-fit items-center rounded-full bg-background px-5 py-2 text-sm font-bold text-primary shadow">{{ $banner->cta_label }} →</span>@endif
            </div>
        </a>
    @endif

    <h1 class="font-display text-3xl font-bold md:text-4xl">Catalogue</h1>
    <p class="mt-2 text-muted-foreground">{{ $products->total() }} produit(s)</p>

    {{-- Filtres --}}
    <form method="GET" action="/catalogue" class="mt-6 flex flex-col gap-4 md:flex-row md:items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un produit..." class="w-full rounded-lg border border-border bg-card px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent md:max-w-sm">
        <div class="flex flex-wrap gap-2">
            <a href="/catalogue{{ request('search') ? '?search=' . request('search') : '' }}" class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ !request('category') ? 'border-accent bg-accent text-accent-foreground' : 'border-border bg-card hover:border-accent' }}">Tous</a>
            @foreach($categories as $c)
                <a href="/catalogue?category={{ $c->slug }}{{ request('search') ? '&search=' . request('search') : '' }}" class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ request('category') == $c->slug ? 'border-accent bg-accent text-accent-foreground' : 'border-border bg-card hover:border-accent' }}">{{ $c->name }}</a>
            @endforeach
        </div>
    </form>

    @if($products->count() === 0)
        <div class="mt-12 rounded-2xl border border-dashed border-border p-12 text-center text-sm text-muted-foreground">
            Aucun produit trouvé.
        </div>
    @else
        <div class="mt-8 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
            @foreach($products as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>
    @endif

    @if($products->hasPages())
        <div class="mt-10">
            {{ $products->links() }}
        </div>
    @endif

</section>
@endsection
