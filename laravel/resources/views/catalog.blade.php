@extends('components.layout')

@section('title', 'Catalogue — Auspice Market')

@section('content')
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Notre catalogue</h1>

        {{-- Filtres --}}
        <form method="GET" action="/catalogue" class="mb-8 flex flex-col md:flex-row gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un produit..." class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            <select name="category" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-emerald-900 text-white px-6 py-2 rounded-lg hover:bg-emerald-800 transition">Filtrer</button>
            @if(request('search') || request('category'))
                <a href="/catalogue" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition text-center">Réinitialiser</a>
            @endif
        </form>

        {{-- Résultats --}}
        <p class="text-gray-600 mb-6">{{ $products->total() }} produit(s) trouvé(s)</p>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($products as $product)
                @include('components.product-card', ['product' => $product])
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    Aucun produit ne correspond à votre recherche.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-10">
            {{ $products->links() }}
        </div>
    </div>
</section>
@endsection
