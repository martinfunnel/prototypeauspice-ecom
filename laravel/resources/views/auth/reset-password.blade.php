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
                <div class="relative">
                    <input id="password" type="password" name="password" required autofocus class="w-full rounded-lg border border-border bg-background px-4 py-2.5 pr-10 text-sm outline-none focus:border-accent">
                    <button type="button" onclick="togglePw('password', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground" title="Afficher / Masquer">
                        <svg class="h-4 w-4 eye-open" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="h-4 w-4 eye-closed hidden" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                    </button>
                </div>
            </label>

            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold">Confirmer le mot de passe</span>
                <div class="relative">
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 pr-10 text-sm outline-none focus:border-accent">
                    <button type="button" onclick="togglePw('password_confirmation', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground" title="Afficher / Masquer">
                        <svg class="h-4 w-4 eye-open" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="h-4 w-4 eye-closed hidden" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                    </button>
                </div>
            </label>

            <button type="submit" class="w-full rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:opacity-90">
                Enregistrer le mot de passe
            </button>
        </form>
    </div>
</section>

<script>
function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    const openIcon = btn.querySelector('.eye-open');
    const closedIcon = btn.querySelector('.eye-closed');
    if (input.type === 'password') {
        input.type = 'text';
        openIcon.classList.add('hidden');
        closedIcon.classList.remove('hidden');
    } else {
        input.type = 'password';
        openIcon.classList.remove('hidden');
        closedIcon.classList.add('hidden');
    }
}
</script>
@endsection
