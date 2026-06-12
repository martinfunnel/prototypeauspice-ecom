@extends('components.admin-shell')

@section('title', 'Tableau de bord')

@section('content')
@if(session('success'))
    <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
@endif

@php
$statCards = [
    ['label' => 'CA total', 'value' => number_format($stats['total_revenue'], 0, ',', ' ') . ' FCFA', 'tone' => 'success', 'icon' => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>'],
    ['label' => 'Commandes', 'value' => (string)$stats['total_orders'], 'tone' => 'primary', 'icon' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>'],
    ['label' => 'En attente', 'value' => (string)$stats['pending_orders'], 'tone' => 'warning', 'icon' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
    ['label' => 'Livrées', 'value' => (string)$stats['delivered_orders'], 'tone' => 'success', 'icon' => '<path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/>'],
];
$statCards2 = [
    ['label' => 'Produits actifs', 'value' => $stats['products_active'] . ' / ' . $stats['products_total'], 'tone' => 'primary', 'icon' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>'],
    ['label' => 'Stock faible', 'value' => (string)$stats['low_stock'], 'tone' => 'warning', 'icon' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>'],
    ['label' => 'Communes livrées', 'value' => (string)$stats['communes_active'], 'tone' => 'accent', 'icon' => '<path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/>'],
];
$tones = [
    'primary' => 'bg-primary/10 text-primary',
    'accent' => 'bg-accent/15 text-accent',
    'success' => 'bg-success/15 text-success',
    'warning' => 'bg-warning/15 text-warning-foreground',
];
@endphp

<div class="space-y-6">
    {{-- Ligne 1 : 4 stat cards --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($statCards as $s)
        <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wider text-muted-foreground">{{ $s['label'] }}</div>
                    <div class="mt-1 font-display text-2xl font-bold">{{ $s['value'] }}</div>
                </div>
                <div class="grid h-10 w-10 place-items-center rounded-xl {{ $tones[$s['tone']] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $s['icon'] !!}</svg>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Ligne 2 : 2 stat cards --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($statCards2 as $s)
        <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wider text-muted-foreground">{{ $s['label'] }}</div>
                    <div class="mt-1 font-display text-2xl font-bold">{{ $s['value'] }}</div>
                </div>
                <div class="grid h-10 w-10 place-items-center rounded-xl {{ $tones[$s['tone']] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $s['icon'] !!}</svg>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Graphique ventes 7 jours (barres CSS) --}}
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-display text-base font-bold">Activité 7 derniers jours</h2>
            <span class="text-xs text-muted-foreground">CA & nombre de commandes</span>
        </div>
        @php $maxRev = $salesByDay->max('revenue') ?: 1; @endphp
        <div class="flex items-end gap-2 h-48">
            @foreach($salesByDay as $day)
                @php $h = min(100, ($day->revenue / $maxRev) * 100); @endphp
                <div class="flex-1 flex flex-col items-center gap-1 group relative" title="{{ number_format($day->revenue, 0, ',', ' ') }} FCFA — {{ $day->count }} commande{{ $day->count > 1 ? 's' : '' }}">
                    <div class="w-full rounded-t-md bg-accent/30 relative transition-all group-hover:bg-accent/50" style="height: {{ max($h, 4) }}%;">
                        <div class="absolute bottom-0 left-0 right-0 rounded-t-md bg-accent" style="height: 100%;"></div>
                        @if($day->count > 0)
                        <div class="absolute -top-5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition text-[10px] font-bold whitespace-nowrap bg-card border border-border rounded px-1 shadow">
                            {{ number_format($day->revenue, 0, ',', ' ') }} F
                        </div>
                        @endif
                    </div>
                    <span class="text-[10px] text-muted-foreground">{{ \Carbon\Carbon::parse($day->date)->isoFormat('ddd') }}</span>
                    <span class="text-[10px] font-semibold">{{ $day->count }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-2 flex items-center justify-center gap-4 text-[10px] text-muted-foreground">
            <span class="flex items-center gap-1"><span class="inline-block h-2 w-2 rounded-sm bg-accent"></span> CA (FCFA)</span>
            <span class="flex items-center gap-1"><span class="inline-block h-2 w-2 rounded-sm border border-border"></span> Nb commandes (chiffre sous barre)</span>
        </div>
    </div>

</div>
@endsection
