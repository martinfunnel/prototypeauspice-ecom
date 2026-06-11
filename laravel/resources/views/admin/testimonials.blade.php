@extends('components.admin-shell')

@section('title', 'Témoignages')

@section('content')


        @if(session('success'))
            <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <div class="bg-card rounded-xl shadow-card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr><th class="text-left p-3">Auteur</th><th class="text-left p-3">Rôle</th><th class="text-right p-3">Note</th><th class="text-center p-3">Actif</th><th class="text-left p-3">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($testimonials as $t)
                        <tr class="border-b hover:bg-muted">
                            <td class="p-3 font-medium">{{ $t->author_name }}</td>
                            <td class="p-3">{{ $t->role ?? '—' }}</td>
                            <td class="p-3 text-right">{{ $t->rating }}/5</td>
                            <td class="p-3 text-center">{{ $t->is_active ? 'Oui' : 'Non' }}</td>
                            <td class="p-3">
                                @canDo('delete_testimonials')
                                <form action="/admin/testimonials/{{ $t->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-destructive hover:opacity-70 text-xs">Supprimer</button>
                                </form>
                                @endcanDo
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
@endsection
