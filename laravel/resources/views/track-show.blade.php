@extends('components.layout')

@section('title', 'Commande ' . $order->order_number . ' — Suivi')

@php
$steps = [
    ['key' => 'pending',    'label' => 'En attente',      'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['key' => 'confirmed',  'label' => 'Confirmée',       'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['key' => 'processing', 'label' => 'En préparation',  'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
    ['key' => 'shipped',    'label' => 'Expédiée',        'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
    ['key' => 'delivered',  'label' => 'Livrée',          'icon' => 'M5 13l4 4L19 7'],
];

$statusOrder = ['pending','confirmed','processing','shipped','delivered'];
$currentIndex = array_search($order->status, $statusOrder);
$isCancelled = $order->status === 'cancelled';
@endphp

@section('content')
<section class="mx-auto max-w-3xl px-4 py-10">
    <a href="/suivi" class="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Retour au suivi
    </a>

    <h1 class="font-display text-3xl font-bold">Commande {{ $order->order_number }}</h1>
    <p class="mt-1 text-sm text-muted-foreground">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>

    {{-- Récapitulatif --}}
    <div class="mt-6 rounded-2xl border border-border bg-card p-5 shadow-card">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="text-sm text-muted-foreground">Statut actuel</p>
                <p class="font-display text-xl font-bold text-primary">{{ $order->statusLabel() }}</p>
            </div>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium
                @if($order->status == 'delivered') bg-green-100 text-green-800
                @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                @elseif($order->status == 'pending') bg-yellow-100 text-yellow-800
                @elseif($order->status == 'confirmed') bg-blue-100 text-blue-800
                @elseif($order->status == 'processing') bg-purple-100 text-purple-800
                @elseif($order->status == 'shipped') bg-indigo-100 text-indigo-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ $order->statusLabel() }}
            </span>
        </div>
        <div class="mt-4 grid gap-4 border-t border-border pt-4 sm:grid-cols-2">
            <div>
                <p class="text-xs text-muted-foreground">Client</p>
                <p class="text-sm font-semibold">{{ $order->customer_name }}</p>
                <p class="text-sm text-muted-foreground">{{ $order->customer_phone }}</p>
            </div>
            <div>
                <p class="text-xs text-muted-foreground">Livraison</p>
                <p class="text-sm font-semibold">{{ $order->commune_name }}</p>
                <p class="text-sm text-muted-foreground">{{ $order->address }}</p>
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="mt-6 rounded-2xl border border-border bg-card p-5 shadow-card">
        <h2 class="font-display text-lg font-bold">Étapes de la commande</h2>

        @if($isCancelled)
            <div class="mt-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4">
                <svg class="h-5 w-5 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="text-sm font-semibold text-red-800">Commande annulée</p>
                    <p class="text-sm text-red-700">Cette commande a été annulée et ne sera pas traitée.</p>
                </div>
            </div>
        @else
            <div class="mt-4 relative">
                <div class="absolute left-[19px] top-4 bottom-4 w-0.5 bg-border"></div>
                <ul class="space-y-6">
                    @foreach($steps as $index => $step)
                        @php
                            $isDone = $currentIndex !== false && $index <= $currentIndex;
                            $isCurrent = $currentIndex !== false && $index === $currentIndex;
                        @endphp
                        <li class="relative flex items-start gap-4">
                            <div class="relative z-10 grid h-10 w-10 shrink-0 place-items-center rounded-full border-2 {{ $isDone ? 'border-accent bg-accent text-accent-foreground' : 'border-border bg-background text-muted-foreground' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/>
                                </svg>
                            </div>
                            <div class="pt-1.5">
                                <p class="text-sm font-semibold {{ $isDone ? 'text-foreground' : 'text-muted-foreground' }}">{{ $step['label'] }}</p>
                                @if($isCurrent)
                                    <p class="text-xs text-accent">Étape en cours</p>
                                @elseif($isDone)
                                    <p class="text-xs text-muted-foreground">Terminé</p>
                                @else
                                    <p class="text-xs text-muted-foreground">En attente</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Articles --}}
    <div class="mt-6 rounded-2xl border border-border bg-card p-5 shadow-card">
        <h2 class="font-display text-lg font-bold">Articles commandés</h2>
        <ul class="mt-3 divide-y divide-border">
            @foreach($order->items as $item)
                <li class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <span class="grid h-8 w-8 place-items-center rounded-md bg-muted text-sm font-bold text-muted-foreground">{{ $item->quantity }}</span>
                        <span class="text-sm text-foreground">{{ $item->product_name }}</span>
                    </div>
                    <span class="text-sm font-semibold text-primary">{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</span>
                </li>
            @endforeach
        </ul>
        <div class="mt-3 flex items-center justify-between border-t border-border pt-3 text-sm">
            <span class="text-muted-foreground">Sous-total</span>
            <span class="font-semibold">{{ number_format($order->subtotal, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="flex items-center justify-between text-sm">
            <span class="text-muted-foreground">Livraison ({{ $order->commune_name }})</span>
            <span class="font-semibold">{{ number_format($order->delivery_fee, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="mt-2 flex items-center justify-between border-t border-border pt-3">
            <span class="font-display text-base font-bold">Total</span>
            <span class="font-display text-xl font-bold text-primary">{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>
</section>
@endsection
