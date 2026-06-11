@extends('components.layout')

@section('title', 'Inscription — Santé Ivoire')

@section('content')
<section class="py-16 min-h-[70vh] flex items-center">
    <div class="max-w-md mx-auto px-4 w-full">
        <div class="bg-card border border-border p-8 rounded-2xl shadow-card">
            <h1 class="font-display text-2xl font-bold text-foreground mb-6 text-center">Créer un compte admin</h1>

            @if($errors->any())
                <div class="bg-destructive/10 border border-destructive/20 text-destructive p-4 rounded-xl mb-6 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="/register" method="POST" class="space-y-5">
                @csrf
                <label class="block">
                    <span class="block text-sm font-semibold text-foreground mb-1.5">Nom complet</span>
                    <input type="text" name="name" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                </label>
                <label class="block">
                    <span class="block text-sm font-semibold text-foreground mb-1.5">Email</span>
                    <input type="email" name="email" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                </label>
                <label class="block">
                    <span class="block text-sm font-semibold text-foreground mb-1.5">Mot de passe</span>
                    <input type="password" name="password" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                </label>
                <label class="block">
                    <span class="block text-sm font-semibold text-foreground mb-1.5">Confirmer le mot de passe</span>
                    <input type="password" name="password_confirmation" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                </label>
                <button type="submit" class="w-full rounded-xl bg-accent px-6 py-3 text-sm font-bold text-accent-foreground shadow-accent transition hover:scale-[1.02]">
                    Créer le compte
                </button>
            </form>

            <p class="text-center text-sm text-muted-foreground mt-6">
                Déjà un compte ? <a href="/login" class="text-accent font-semibold hover:underline">Se connecter</a>
            </p>
        </div>
    </div>
</section>
@endsection
