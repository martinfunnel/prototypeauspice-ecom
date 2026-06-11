@extends('components.layout')

@section('title', 'Accès refusé')

@section('content')
<section class="py-16 min-h-[70vh] flex items-center">
    <div class="max-w-md mx-auto px-4 w-full text-center">
        <div class="bg-card border border-border p-8 rounded-2xl shadow-card">
            <h1 class="font-display text-2xl font-bold text-foreground mb-4">Accès refusé</h1>
            <p class="text-muted-foreground mb-6">Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
            <a href="/" class="inline-flex rounded-lg bg-accent px-6 py-3 text-sm font-bold text-accent-foreground shadow-accent transition hover:scale-[1.02]">Retour à l'accueil</a>
        </div>
    </div>
</section>
@endsection
