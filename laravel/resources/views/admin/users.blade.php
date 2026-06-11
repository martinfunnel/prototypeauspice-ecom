@extends('components.admin-shell')

@section('title', 'Utilisateurs')

@section('content')


        @if(session('success'))
            <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-destructive/10 border border-destructive/20 text-destructive p-4 rounded-lg mb-6">{{ session('error') }}</div>
        @endif

        {{-- Affichage des credentials nouvellement créés --}}
        @if($newCredentials)
            <div class="bg-accent/10 border border-accent/20 rounded-xl p-6 mb-8" id="credentials-box">
                <h2 class="font-display text-lg font-bold text-accent mb-2">Identifiants du nouveau compte</h2>
                <p class="text-sm text-muted-foreground mb-4">Copiez ces informations maintenant. Le mot de passe ne sera plus affiché.</p>
                <div class="space-y-2 font-mono text-sm">
                    <div class="flex items-center gap-2">
                        <span class="text-muted-foreground w-24">Identifiant :</span>
                        <code class="bg-background border border-border rounded px-2 py-1 text-foreground">{{ $newCredentials['identifier'] }}</code>
                        <button onclick="navigator.clipboard.writeText('{{ $newCredentials['identifier'] }}')" class="text-xs text-accent hover:underline">Copier</button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-muted-foreground w-24">Mot de passe :</span>
                        <code class="bg-background border border-border rounded px-2 py-1 text-foreground">{{ $newCredentials['password'] }}</code>
                        <button onclick="navigator.clipboard.writeText('{{ $newCredentials['password'] }}')" class="text-xs text-accent hover:underline">Copier</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Formulaire de création --}}
        <div class="bg-card rounded-xl shadow-card border border-border p-6 mb-8">
            <h2 class="font-display text-lg font-bold text-foreground mb-4">Créer un compte staff</h2>
            <form action="/admin/users" method="POST" class="flex flex-wrap items-end gap-4">
                @csrf
                <label class="block flex-1 min-w-[200px]">
                    <span class="block text-sm font-semibold text-foreground mb-1.5">Nom complet (optionnel)</span>
                    <input type="text" name="full_name" placeholder="Prénom Nom" class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                </label>
                <label class="block min-w-[180px]">
                    <span class="block text-sm font-semibold text-foreground mb-1.5">Rôle</span>
                    <select name="role" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                        <option value="admin">Administrateur</option>
                        <option value="vendeur">Vendeur</option>
                        <option value="comptable">Comptable</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </label>
                <button type="submit" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground shadow-accent transition hover:scale-[1.02]">
                    Générer le compte
                </button>
            </form>
        </div>

        {{-- Liste des utilisateurs --}}
        <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr>
                        <th class="text-left p-3 text-foreground font-semibold">Identifiant</th>
                        <th class="text-left p-3 text-foreground font-semibold">Nom</th>
                        <th class="text-left p-3 text-foreground font-semibold">Email</th>
                        <th class="text-left p-3 text-foreground font-semibold">Rôles</th>
                        <th class="text-left p-3 text-foreground font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr class="border-b border-border hover:bg-muted transition">
                            <td class="p-3 font-mono text-foreground">{{ $u->identifier ?? '—' }}</td>
                            <td class="p-3 font-medium text-foreground">{{ $u->full_name ?? $u->name }}</td>
                            <td class="p-3 text-muted-foreground text-xs">{{ $u->email }}</td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($u->userRoles as $ur)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-muted text-muted-foreground">{{ $ur->role }}</span>
                                    @endforeach
                                </div>
                                {{-- Ajouter/retirer rôles --}}
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($availableRoles as $role)
                                        @if(!$u->userRoles->contains('role', $role))
                                            <form action="/admin/users/{{ $u->id }}/role" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="role" value="{{ $role }}">
                                                <input type="hidden" name="action" value="add">
                                                <button type="submit" class="text-[10px] rounded-full border border-border px-2 py-0.5 text-muted-foreground hover:bg-muted transition">+ {{ $role }}</button>
                                            </form>
                                        @else
                                            <form action="/admin/users/{{ $u->id }}/role" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="role" value="{{ $role }}">
                                                <input type="hidden" name="action" value="remove">
                                                <button type="submit" class="text-[10px] rounded-full bg-accent/10 px-2 py-0.5 text-accent hover:opacity-70 transition" onclick="return confirm('Retirer le rôle {{ $role }} ?')">{{ $role }} &times;</button>
                                            </form>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="p-3">
                                <form action="/admin/users/{{ $u->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-destructive hover:opacity-70 text-xs transition">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $users->links() }}</div>
@endsection
