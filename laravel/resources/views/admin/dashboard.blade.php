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
    ['label' => 'Produits', 'value' => (string)$stats['total_products'], 'tone' => 'primary', 'icon' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>'],
    ['label' => 'Clients', 'value' => (string)$stats['total_customers'], 'tone' => 'accent', 'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
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
        @if($salesByDay->count())
            @php $maxRev = $salesByDay->max('revenue') ?: 1; @endphp
            <div class="flex items-end gap-2 h-48">
                @foreach($salesByDay as $day)
                    @php $h = min(100, ($day->revenue / $maxRev) * 100); @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full rounded-t-md bg-accent/50 relative" style="height: {{ $h }}%;">
                            <div class="absolute bottom-0 left-0 right-0 rounded-t-md bg-accent" style="height: 60%;"></div>
                        </div>
                        <span class="text-[10px] text-muted-foreground">{{ \Carbon\Carbon::parse($day->date)->isoFormat('ddd') }}</span>
                        <span class="text-[10px] font-semibold">{{ number_format($day->revenue/1000, 0) }}k</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-muted-foreground">Aucune vente sur les 7 derniers jours.</p>
        @endif
    </div>

    {{-- Commandes récentes --}}
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <h2 class="font-display text-base font-bold mb-3">Commandes récentes</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr>
                        <th class="text-left p-2 text-xs font-semibold text-muted-foreground">N°</th>
                        <th class="text-left p-2 text-xs font-semibold text-muted-foreground">Client</th>
                        <th class="text-right p-2 text-xs font-semibold text-muted-foreground">Total</th>
                        <th class="text-left p-2 text-xs font-semibold text-muted-foreground">Statut</th>
                        <th class="text-left p-2 text-xs font-semibold text-muted-foreground">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                        <tr class="border-b border-border hover:bg-muted transition">
                            <td class="p-2 font-medium">{{ $order->order_number }}</td>
                            <td class="p-2">{{ $order->customer_name }}</td>
                            <td class="p-2 text-right">{{ number_format($order->total, 0, ',', ' ') }} FCFA</td>
                            <td class="p-2">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium
                                    @if($order->status == 'delivered') bg-success/15 text-success
                                    @elseif($order->status == 'cancelled') bg-destructive/10 text-destructive
                                    @else bg-warning/15 text-warning-foreground
                                    @endif">
                                    {{ $order->statusLabel() }}
                                </span>
                            </td>
                            <td class="p-2 text-muted-foreground text-xs">{{ $order->created_at->format('d/m H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
