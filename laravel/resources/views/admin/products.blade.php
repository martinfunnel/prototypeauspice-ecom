@extends('components.layout')

@section('title', 'Admin — Produits')

@section('content')
<section class="py-8 bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Produits</h1>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-3">Nom</th>
                        <th class="text-left p-3">Catégorie</th>
                        <th class="text-right p-3">Prix</th>
                        <th class="text-right p-3">Stock</th>
                        <th class="text-center p-3">Actif</th>
                        <th class="text-left p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium">{{ $product->name }}</td>
                            <td class="p-3">{{ $product->category?->name ?? '—' }}</td>
                            <td class="p-3 text-right">{{ number_format($product->price, 0, ',', ' ') }} FCFA</td>
                            <td class="p-3 text-right">{{ $product->stock }}</td>
                            <td class="p-3 text-center">{{ $product->is_active ? 'Oui' : 'Non' }}</td>
                            <td class="p-3">
                                <form action="/admin/products/{{ $product->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce produit ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Supprimer</button>
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
