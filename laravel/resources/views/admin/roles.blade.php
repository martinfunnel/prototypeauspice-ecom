@extends('components.admin-shell')

@section('title', 'Rôles & Permissions')

@section('content')


        @if(session('success'))
            <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-destructive/10 border border-destructive/20 text-destructive p-4 rounded-lg mb-6">{{ session('error') }}</div>
        @endif

        @if(isset($role))
            {{-- Edit form --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 mb-8">
                <h2 class="font-display text-lg font-bold text-foreground mb-4">Modifier le rôle : {{ $role->name }}</h2>
                <form action="/admin/roles/{{ $role->id }}" method="POST" class="space-y-4">
                    @csrf @method('PATCH')
                    <label class="block">
                        <span class="block text-sm font-semibold text-foreground mb-1.5">Nom du rôle</span>
                        <input type="text" name="name" value="{{ $role->name }}" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                    </label>
                    <div>
                        <span class="block text-sm font-semibold text-foreground mb-2">Permissions</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($permissions as $group => $groupPermissions)
                                <div class="bg-muted/50 rounded-lg p-4">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">{{ $group }}</h3>
                                    <div class="space-y-1.5">
                                        @foreach($groupPermissions as $perm)
                                            <label class="flex items-center gap-2 text-sm text-foreground">
                                                <input type="checkbox" name="permission_ids[]" value="{{ $perm->id }}" {{ $role->permissions->contains($perm->id) ? 'checked' : '' }}>
                                                {{ $perm->name }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground">Mettre à jour</button>
                        <a href="/admin/roles" class="rounded-lg border border-border px-5 py-2.5 text-sm font-medium text-foreground hover:bg-muted transition">Annuler</a>
                    </div>
                </form>
            </div>
        @else
            {{-- Create form --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 mb-8">
                <h2 class="font-display text-lg font-bold text-foreground mb-4">Nouveau rôle</h2>
                <form action="/admin/roles" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="key" placeholder="Clé (ex: manager)" required class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                        <input type="text" name="name" placeholder="Nom (ex: Manager)" required class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($permissions as $group => $groupPermissions)
                            <div class="bg-muted/50 rounded-lg p-4">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">{{ $group }}</h3>
                                <div class="space-y-1.5">
                                    @foreach($groupPermissions as $perm)
                                        <label class="flex items-center gap-2 text-sm text-foreground">
                                            <input type="checkbox" name="permission_ids[]" value="{{ $perm->id }}">
                                            {{ $perm->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground">Créer le rôle</button>
                </form>
            </div>
        @endif

        <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr>
                        <th class="text-left p-3 text-foreground font-semibold">Clé</th>
                        <th class="text-left p-3 text-foreground font-semibold">Nom</th>
                        <th class="text-left p-3 text-foreground font-semibold">Permissions</th>
                        <th class="text-left p-3 text-foreground font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $r)
                        <tr class="border-b border-border hover:bg-muted transition">
                            <td class="p-3 font-medium text-foreground">{{ $r->key }}</td>
                            <td class="p-3 text-foreground">{{ $r->name }}</td>
                            <td class="p-3 text-muted-foreground">{{ $r->permissions->count() }} permission(s)</td>
                            <td class="p-3 flex gap-2">
                                <a href="/admin/roles/{{ $r->id }}/edit" class="text-sm text-accent hover:underline">Modifier</a>
                                <form action="/admin/roles/{{ $r->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-destructive hover:opacity-70 text-xs">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
@endsection
