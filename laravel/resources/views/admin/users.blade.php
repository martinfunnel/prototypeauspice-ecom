@extends('components.layout')

@section('title', 'Admin — Utilisateurs')

@section('content')
<section class="py-8 bg-background min-h-screen">
    <div class="max-w-5xl mx-auto px-4">
        <h1 class="font-display text-3xl font-bold text-foreground mb-8">Utilisateurs</h1>

        @if(session('success'))
            <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-destructive/10 border border-destructive/20 text-destructive p-4 rounded-lg mb-6">{{ session('error') }}</div>
        @endif

        @if(isset($user))
            {{-- Edit form --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 mb-8">
                <h2 class="font-display text-lg font-bold text-foreground mb-4">Modifier l'utilisateur</h2>
                <form action="/admin/users/{{ $user->id }}" method="POST" class="space-y-4">
                    @csrf @method('PATCH')
                    <label class="block">
                        <span class="block text-sm font-semibold text-foreground mb-1.5">Nom</span>
                        <input type="text" name="name" value="{{ $user->name }}" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                    </label>
                    <label class="block">
                        <span class="block text-sm font-semibold text-foreground mb-1.5">Email</span>
                        <input type="email" name="email" value="{{ $user->email }}" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                    </label>
                    <label class="block">
                        <span class="block text-sm font-semibold text-foreground mb-1.5">Mot de passe (laisser vide pour ne pas changer)</span>
                        <input type="password" name="password" class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                    </label>
                    <label class="block">
                        <span class="block text-sm font-semibold text-foreground mb-1.5">Rôles</span>
                        <div class="space-y-2 mt-2">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-2 text-sm text-foreground">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                    {{ $role->name }}
                                </label>
                            @endforeach
                        </div>
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground">Mettre à jour</button>
                        <a href="/admin/users" class="rounded-lg border border-border px-5 py-2.5 text-sm font-medium text-foreground hover:bg-muted transition">Annuler</a>
                    </div>
                </form>
            </div>
        @else
            {{-- Create form --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 mb-8">
                <h2 class="font-display text-lg font-bold text-foreground mb-4">Nouvel utilisateur</h2>
                <form action="/admin/users" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <input type="text" name="name" placeholder="Nom" required class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                    <input type="email" name="email" placeholder="Email" required class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                    <input type="password" name="password" placeholder="Mot de passe" required class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                    <select name="role_ids[]" multiple class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="md:col-start-1 rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground">Créer</button>
                </form>
            </div>
        @endif

        <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr>
                        <th class="text-left p-3 text-foreground font-semibold">Nom</th>
                        <th class="text-left p-3 text-foreground font-semibold">Email</th>
                        <th class="text-left p-3 text-foreground font-semibold">Rôles</th>
                        <th class="text-left p-3 text-foreground font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr class="border-b border-border hover:bg-muted transition">
                            <td class="p-3 font-medium text-foreground">{{ $u->name }}</td>
                            <td class="p-3 text-muted-foreground">{{ $u->email }}</td>
                            <td class="p-3">
                                @foreach($u->roles as $role)
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-muted text-muted-foreground mr-1">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="p-3 flex gap-2">
                                <a href="/admin/users/{{ $u->id }}/edit" class="text-sm text-accent hover:underline">Modifier</a>
                                <form action="/admin/users/{{ $u->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-destructive hover:opacity-70 text-xs">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $users->links() }}</div>
    </div>
</section>
@endsection
