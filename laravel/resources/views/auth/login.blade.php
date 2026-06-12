@extends('components.layout')

@section('hide-nav')
@endsection

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
                    <div class="relative">
                        <input type="password" id="login-password" name="password" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 pr-10 text-sm shadow-sm outline-none focus:border-accent">
                        <button type="button" onclick="togglePassword()" class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground" title="Afficher / Masquer">
                            <svg id="eye-icon" class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eye-off-icon" class="h-4 w-4 hidden" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7c.78 0 1.53-.09 2.24-.26"/><path d="M2 2l20 20"/></svg>
                        </button>
                    </div>
                </label>
                <button type="submit" class="w-full rounded-xl bg-accent px-6 py-3 text-sm font-bold text-accent-foreground shadow-accent transition hover:scale-[1.02]">
                    Se connecter
                </button>
            </form>

            <script>
            function togglePassword() {
                const input = document.getElementById('login-password');
                const eye = document.getElementById('eye-icon');
                const eyeOff = document.getElementById('eye-off-icon');
                if (input.type === 'password') {
                    input.type = 'text';
                    eye.classList.add('hidden');
                    eyeOff.classList.remove('hidden');
                } else {
                    input.type = 'password';
                    eye.classList.remove('hidden');
                    eyeOff.classList.add('hidden');
                }
            }
            </script>
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
