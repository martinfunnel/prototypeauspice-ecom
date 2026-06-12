@extends('components.admin-shell')

@section('title', 'Commandes')

@section('content')
@php
$statusBadge = [
    'pending' => 'bg-warning/15 text-warning-foreground border-warning/30',
    'confirmed' => 'bg-teal/15 text-teal border-teal/30',
    'processing' => 'bg-teal/15 text-teal border-teal/30',
    'shipped' => 'bg-primary/10 text-primary border-primary/30',
    'delivered' => 'bg-success/15 text-success border-success/30',
    'cancelled' => 'bg-destructive/15 text-destructive border-destructive/30',
];
$totalRevenue = $orders->sum('total');
$pendingRevenue = $orders->where('status', 'pending')->sum('total');
@endphp

{{-- Stats cards --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Commandes</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ $orders->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">En attente</p>
        <p class="mt-1 font-display text-2xl font-bold text-warning">{{ $orders->where('status', 'pending')->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Livrées</p>
        <p class="mt-1 font-display text-2xl font-bold text-success">{{ $orders->where('status', 'delivered')->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">CA total</p>
        <p class="mt-1 font-display text-2xl font-bold text-accent">{{ number_format($totalRevenue, 0, ',', ' ') }} FCFA</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">En attente (CA)</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ number_format($pendingRevenue, 0, ',', ' ') }} FCFA</p>
    </div>
</div>

{{-- Barre recherche + filtre --}}
<form action="/admin/orders" method="GET" class="mb-4 flex flex-wrap items-center gap-2">
    <div class="relative flex-1 min-w-[200px]">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="N° commande, client, téléphone…" class="w-full rounded-lg border border-border bg-background pl-9 pr-3 py-2 text-sm outline-none focus:border-accent">
    </div>
    <select name="status" class="rounded-lg border border-border bg-background px-3 py-2 text-sm">
        <option value="all">Tous les statuts</option>
        @foreach($statuses as $key => $label)
            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-muted transition">Filtrer</button>
    <a href="/admin/orders" class="rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-muted transition">Réinitialiser</a>
</form>

@if($orders->isEmpty())
    <div class="rounded-xl border border-dashed border-border bg-card p-10 text-center text-sm text-muted-foreground">
        Aucune commande.
    </div>
@else
    <div class="space-y-3">
        @foreach($orders as $o)
            @php
                $waPhone = preg_replace('/[^0-9]/', '', $o->customer_phone);
                $waMsg = urlencode("Bonjour {$o->customer_name}, concernant votre commande {$o->order_number}…");
            @endphp
            <details id="order-{{ $o->id }}" class="group rounded-2xl border border-border bg-card shadow-card">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-sm font-bold text-primary">{{ $o->order_number }}</span>
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge[$o->status] ?? 'bg-muted text-muted-foreground border-border' }}">
                                {{ $o->statusLabel() }}
                            </span>
                            <span class="text-xs text-muted-foreground">{{ $o->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="mt-1 truncate text-sm">
                            <span class="font-semibold">{{ $o->customer_name }}</span>
                            <span class="text-muted-foreground"> · {{ $o->customer_phone }} · {{ $o->commune_name }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <div class="font-display text-base font-bold">{{ number_format($o->total, 0, ',', ' ') }} FCFA</div>
                            <div class="text-[10px] uppercase text-muted-foreground">{{ $o->items->count() }} article(s)</div>
                        </div>
                        <button type="button" onclick="event.preventDefault(); const d=document.getElementById('order-{{ $o->id }}'); d.open=!d.open;" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Voir">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </summary>

                <div class="grid gap-4 border-t border-border p-4 md:grid-cols-2">
                    {{-- Colonne 1 --}}
                    <div>
                        <div class="text-xs font-semibold uppercase text-muted-foreground">Adresse</div>
                        <p class="mt-1 text-sm">{{ $o->address }}</p>
                        @if($o->notes)
                            <p class="mt-2 rounded bg-muted/60 p-2 text-xs italic">{{ $o->notes }}</p>
                        @endif

                        <div class="mt-4 text-xs font-semibold uppercase text-muted-foreground">Articles</div>
                        <ul class="mt-1 space-y-1 text-sm">
                            @foreach($o->items as $item)
                                <li class="flex justify-between gap-2">
                                    <span>{{ $item->quantity }}× {{ $item->product_name }}</span>
                                    <span class="font-mono text-muted-foreground">{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-3 border-t border-border pt-2 text-sm">
                            <div class="flex justify-between text-muted-foreground">
                                <span>Sous-total</span>
                                <span>{{ number_format($o->subtotal, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between text-muted-foreground">
                                <span>Livraison</span>
                                <span>{{ number_format($o->delivery_fee, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="mt-1 flex justify-between font-bold">
                                <span>Total</span>
                                <span>{{ number_format($o->total, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>
                    </div>

                    {{-- Colonne 2 --}}
                    <div class="space-y-3">
                        <div>
                            <div class="text-xs font-semibold uppercase text-muted-foreground">Statut</div>
                            <form action="/admin/orders/{{ $o->id }}/status" method="POST" class="mt-1">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm cursor-pointer">
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ $o->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" onclick="printInvoice({{ json_encode([
                                'order_number' => $o->order_number,
                                'created_at' => $o->created_at->format('d/m/Y H:i'),
                                'customer_name' => $o->customer_name,
                                'customer_phone' => $o->customer_phone,
                                'address' => $o->address,
                                'commune_name' => $o->commune_name,
                                'notes' => $o->notes,
                                'subtotal' => number_format($o->subtotal, 0, ',', ' '),
                                'delivery_fee' => number_format($o->delivery_fee, 0, ',', ' '),
                                'total' => number_format($o->total, 0, ',', ' '),
                                'items' => $o->items->map(fn($item) => [
                                    'name' => $item->product_name,
                                    'qty' => $item->quantity,
                                    'price' => number_format($item->price, 0, ',', ' '),
                                    'subtotal' => number_format($item->subtotal, 0, ',', ' '),
                                ])->toArray(),
                            ]) }})" class="flex items-center justify-center gap-2 rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-muted transition">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                                Imprimer
                            </button>
                            <a href="https://wa.me/{{ $waPhone }}?text={{ $waMsg }}" target="_blank" class="flex items-center justify-center gap-2 rounded-lg bg-success/15 px-3 py-2 text-sm font-medium text-success hover:bg-success/25 transition">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                                Client
                            </a>
                            <a href="https://wa.me/?text={{ urlencode("Commande {$o->order_number} - {$o->customer_name} - Total: " . number_format($o->total, 0, ',', ' ') . ' FCFA') }}" target="_blank" class="flex items-center justify-center gap-2 rounded-lg bg-accent/15 px-3 py-2 text-sm font-medium text-accent hover:bg-accent/25 transition">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                                Récap
                            </a>
                            @canDo('delete_orders')
                            <form action="/admin/orders/{{ $o->id }}" method="POST" onsubmit="return confirm('Supprimer cette commande ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium text-destructive hover:bg-muted transition">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    Supprimer
                                </button>
                            </form>
                            @endcanDo
                        </div>
                    </div>
                </div>
            </details>
        @endforeach
    </div>
@endif
@endsection
