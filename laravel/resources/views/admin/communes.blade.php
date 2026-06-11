@extends('components.admin-shell')

@section('title', 'Communes')

@section('content')


        @if(session('success'))
            <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <div class="bg-card rounded-xl shadow-card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr><th class="text-left p-3">Nom</th><th class="text-left p-3">Zone</th><th class="text-right p-3">Frais</th><th class="text-right p-3">Délai</th><th class="text-center p-3">Actif</th><th class="text-left p-3">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($communes as $commune)
                        <tr class="border-b hover:bg-muted">
                            <td class="p-3 font-medium">{{ $commune->name }}</td>
                            <td class="p-3">{{ $commune->zone }}</td>
                            <td class="p-3 text-right">{{ number_format($commune->delivery_fee, 0, ',', ' ') }} FCFA</td>
                            <td class="p-3 text-right">{{ $commune->delivery_days }} j</td>
                            <td class="p-3 text-center">{{ $commune->is_active ? 'Oui' : 'Non' }}</td>
                            <td class="p-3">
                                @canDo('delete_communes')
                                <form action="/admin/communes/{{ $commune->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ?')">
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
