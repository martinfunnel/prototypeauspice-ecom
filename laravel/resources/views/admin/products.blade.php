@extends('components.layout')

@section('title', 'Admin — Produits')

@section('content')
<section class="py-8 bg-background min-h-screen">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h1 class="font-display text-3xl font-bold text-foreground">Produits</h1>
        </div>

        @if(session('success'))
            <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <div class="bg-card rounded-xl shadow-card overflow-hidden border border-border">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr>
                        <th class="text-left p-3 text-foreground font-semibold">Nom</th>
                        <th class="text-left p-3 text-foreground font-semibold">Catégorie</th>
                        <th class="text-right p-3 text-foreground font-semibold">Prix</th>
                        <th class="text-right p-3 text-foreground font-semibold">Stock</th>
                        <th class="text-center p-3 text-foreground font-semibold">Actif</th>
                        <th class="text-left p-3 text-foreground font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr class="border-b border-border hover:bg-muted transition">
                            <td class="p-3 font-medium text-foreground">{{ $product->name }}</td>
                            <td class="p-3 text-muted-foreground">{{ $product->category?->name ?? '—' }}</td>
                            <td class="p-3 text-right text-foreground">{{ number_format($product->price, 0, ',', ' ') }} FCFA</td>
                            <td class="p-3 text-right text-foreground">{{ $product->stock }}</td>
                            <td class="p-3 text-center text-foreground">{{ $product->is_active ? 'Oui' : 'Non' }}</td>
                            <td class="p-3">
                                <form action="/admin/products/{{ $product->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce produit ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-destructive hover:opacity-70 text-xs transition">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    </div>
</section>
@endsection
