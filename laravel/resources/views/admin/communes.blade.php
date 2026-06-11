@extends('components.layout')

@section('title', 'Admin — Communes')

@section('content')
<section class="py-8 bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Communes de livraison</h1>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr><th class="text-left p-3">Nom</th><th class="text-left p-3">Zone</th><th class="text-right p-3">Frais</th><th class="text-right p-3">Délai</th><th class="text-center p-3">Actif</th><th class="text-left p-3">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($communes as $commune)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium">{{ $commune->name }}</td>
                            <td class="p-3">{{ $commune->zone }}</td>
                            <td class="p-3 text-right">{{ number_format($commune->delivery_fee, 0, ',', ' ') }} FCFA</td>
                            <td class="p-3 text-right">{{ $commune->delivery_days }} j</td>
                            <td class="p-3 text-center">{{ $commune->is_active ? 'Oui' : 'Non' }}</td>
                            <td class="p-3">
                                <form action="/admin/communes/{{ $commune->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
