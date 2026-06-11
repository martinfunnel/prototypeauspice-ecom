@extends('components.layout')

@section('title', 'Connexion — Santé Ivoire')

@section('content')
<section class="py-16 min-h-[70vh] flex items-center">
    <div class="max-w-md mx-auto px-4 w-full">
        <div class="bg-card border border-border p-8 rounded-2xl shadow-card">
            <h1 class="font-display text-2xl font-bold text-foreground mb-6 text-center">Connexion admin</h1>

            @if($errors->any())
                <div class="bg-destructive/10 border border-destructive/20 text-destructive p-4 rounded-xl mb-6 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="/login" method="POST" class="space-y-5">
                @csrf
                <label class="block">
                    <span class="block text-sm font-semibold text-foreground mb-1.5">Identifiant</span>
                    <input type="text" name="identifier" required placeholder="AUS-XXXXXX" class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                    <p class="mt-1 text-xs text-muted-foreground">Entrez AUS-XXXXXX ou l'email complet.</p>
                </label>
                <label class="block">
                    <span class="block text-sm font-semibold text-foreground mb-1.5">Mot de passe</span>
                    <input type="password" name="password" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                </label>
                <button type="submit" class="w-full rounded-xl bg-accent px-6 py-3 text-sm font-bold text-accent-foreground shadow-accent transition hover:scale-[1.02]">
                    Se connecter
                </button>
            </form>
        </div>

        @auth
            @php
                $hasAdmin = \App\Models\UserRole::whereIn('role', ['super_admin', 'admin'])->exists();
                $isAdmin = auth()->user()->hasAnyRole(['super_admin', 'admin', 'vendeur', 'comptable']);
            @endphp
            @if(!$isAdmin && !$hasAdmin)
                <div class="mt-6 bg-card border border-border p-6 rounded-2xl shadow-card text-center">
                    <p class="text-sm text-muted-foreground mb-4">Aucun administrateur n'est encore configuré.</p>
                    <form action="/admin/claim-first-admin" method="POST">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-primary px-6 py-3 text-sm font-bold text-primary-foreground transition hover:scale-[1.02]">
                            Devenir le premier administrateur
                        </button>
                    </form>
                </div>
            @elseif(!$isAdmin && $hasAdmin)
                <div class="mt-6 bg-card border border-border p-6 rounded-2xl shadow-card text-center">
                    <p class="text-sm text-muted-foreground">Vous n'avez pas les droits d'administration. Contactez un administrateur.</p>
                </div>
            @endif
        @endauth
    </div>
</section>
@endsection
