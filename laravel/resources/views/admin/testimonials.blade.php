@extends('components.layout')

@section('title', 'Admin — Témoignages')

@section('content')
<section class="py-8 bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Témoignages clients</h1>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr><th class="text-left p-3">Auteur</th><th class="text-left p-3">Rôle</th><th class="text-right p-3">Note</th><th class="text-center p-3">Actif</th><th class="text-left p-3">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($testimonials as $t)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium">{{ $t->author_name }}</td>
                            <td class="p-3">{{ $t->role ?? '—' }}</td>
                            <td class="p-3 text-right">{{ $t->rating }}/5</td>
                            <td class="p-3 text-center">{{ $t->is_active ? 'Oui' : 'Non' }}</td>
                            <td class="p-3">
                                <form action="/admin/testimonials/{{ $t->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ?')">
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
