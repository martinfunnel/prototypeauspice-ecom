@extends('components.admin-shell')

@section('title', 'Mon profil')

@section('content')
@if(session('success'))
    <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
@endif

<div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
    {{-- Informations personnelles --}}
    <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
        <div class="mb-4 flex items-center gap-3">
            <div class="grid h-12 w-12 place-items-center rounded-xl bg-accent/10 text-accent">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
                <h2 class="font-display text-lg font-bold">Informations personnelles</h2>
                <p class="text-xs text-muted-foreground">Mettez à jour vos informations de profil.</p>
            </div>
        </div>

        <form action="/admin/profil" method="POST" class="space-y-4">
            @csrf @method('PATCH')
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Nom complet</span>
                    <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Nom d'affichage</span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                </label>
            </div>
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold">Email</span>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
            </label>
            <div class="rounded-lg bg-muted/40 p-3">
                <div class="flex items-center gap-2 text-sm">
                    <svg class="h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="m5 5 2.83 2.83"/><path d="m19 19 2.83-2.83"/><path d="M2 12h4"/><path d="m16.24 17.24 2.83 2.83"/><path d="M4.93 19.07l2.83-2.83"/><path d="m18.36 5.64 2.83 2.83"/><circle cx="12" cy="12" r="3"/></svg>
                    <span class="text-muted-foreground">Identifiant :</span>
                    <code class="font-mono font-semibold">{{ $user->identifier ?? '—' }}</code>
                </div>
                <div class="mt-1 flex flex-wrap gap-1">
                    @foreach($user->userRoles as $role)
                        <span class="inline-flex items-center rounded-full bg-accent/10 px-2 py-0.5 text-xs font-semibold text-accent">{{ $role->role }}</span>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:opacity-90">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Enregistrer les modifications
            </button>
        </form>
    </div>

    {{-- Sécurité --}}
    <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
        <div class="mb-4 flex items-center gap-3">
            <div class="grid h-12 w-12 place-items-center rounded-xl bg-destructive/10 text-destructive">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="8" x="3" y="3" rx="1"/><path d="M11 21h8a2 2 0 0 0 2-2v-8"/><path d="m21 3-9 9"/></svg>
            </div>
            <div>
                <h2 class="font-display text-lg font-bold">Sécurité</h2>
                <p class="text-xs text-muted-foreground">Changez votre mot de passe.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-lg bg-destructive/10 p-3 text-sm text-destructive">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="/admin/profil/password" method="POST" class="space-y-4">
            @csrf
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold">Mot de passe actuel</span>
                <input type="password" name="current_password" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold">Nouveau mot de passe</span>
                <input type="password" name="password" required minlength="8" class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold">Confirmer le nouveau mot de passe</span>
                <input type="password" name="password_confirmation" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
            </label>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-destructive px-5 py-2.5 text-sm font-semibold text-destructive-foreground transition hover:opacity-90">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                Changer le mot de passe
            </button>
        </form>
    </div>

    {{-- Session --}}
    <div class="rounded-2xl border border-border bg-card p-6 shadow-card lg:col-span-2">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-warning/10 text-warning">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                </div>
                <div>
                    <h2 class="font-display text-lg font-bold">Session</h2>
                    <p class="text-xs text-muted-foreground">Déconnectez-vous de votre compte sur cet appareil.</p>
                </div>
            </div>
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-border bg-background px-5 py-2.5 text-sm font-semibold transition hover:bg-muted">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
