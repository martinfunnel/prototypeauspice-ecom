@extends('components.layout')

@section('title', 'Définir le mot de passe — Santé Ivoire')

@section('content')
<section class="mx-auto max-w-md px-4 py-16">
    <div class="rounded-2xl border border-border bg-card p-8 shadow-card">
        <h1 class="font-display text-2xl font-bold">Définir votre mot de passe</h1>
        <p class="mt-2 text-sm text-muted-foreground">Choisissez un mot de passe sécurisé pour votre compte.</p>

        @if(session('status'))
            <div class="mt-4 rounded-lg bg-success/10 p-3 text-sm text-success">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="mt-4 rounded-lg bg-destructive/10 p-3 text-sm text-destructive">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="/password/reset" method="POST" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold">Nouveau mot de passe</span>
                <input type="password" name="password" required autofocus class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
            </label>

            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold">Confirmer le mot de passe</span>
                <input type="password" name="password_confirmation" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
            </label>

            <button type="submit" class="w-full rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:opacity-90">
                Enregistrer le mot de passe
            </button>
        </form>
    </div>
</section>
@endsection
