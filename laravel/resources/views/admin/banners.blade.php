@extends('components.layout')

@section('title', 'Admin — Bannières')

@section('content')
<section class="py-8 bg-background min-h-screen">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="font-display text-3xl font-bold text-foreground mb-8">Bannières promotionnelles</h1>

        @if(session('success'))
            <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <div class="space-y-4">
            @foreach($banners as $banner)
                <div class="bg-card p-6 rounded-xl shadow-card">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-muted-foreground">Clé : {{ $banner->key }}</p>
                            <h3 class="text-lg font-semibold text-foreground">{{ $banner->title ?? 'Sans titre' }}</h3>
                            <p class="text-muted-foreground">{{ $banner->subtitle ?? '—' }}</p>
                            <p class="text-sm text-muted-foreground mt-2">CTA : {{ $banner->cta_label }} → {{ $banner->cta_url }}</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $banner->is_active ? 'bg-success/15 text-success' : 'bg-background text-muted-foreground' }}">
                            {{ $banner->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
