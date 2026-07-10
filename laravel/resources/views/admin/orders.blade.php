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
    {{-- Select all bar --}}
    <div class="mb-3 flex items-center gap-3 rounded-xl border border-border bg-card px-4 py-2.5 shadow-card">
        <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
            <input type="checkbox" id="select-all-orders" class="h-4 w-4 rounded border-border text-accent focus:ring-accent cursor-pointer">
            Tout sélectionner
        </label>
        <span id="selected-count" class="text-xs text-muted-foreground">0 sélectionnée(s)</span>
    </div>

    <div class="space-y-3">
        @foreach($orders as $o)
            @php
                $waPhone = preg_replace('/[^0-9]/', '', $o->customer_phone);
                $waMsg = urlencode("Bonjour {$o->customer_name}, concernant votre commande {$o->order_number}…");
            @endphp
            <details id="order-{{ $o->id }}" class="group rounded-2xl border border-border bg-card shadow-card">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-4">
                    <div class="flex min-w-0 flex-1 items-start gap-3">
                        <input type="checkbox" class="order-checkbox h-4 w-4 mt-0.5 shrink-0 rounded border-border text-accent focus:ring-accent cursor-pointer" value="{{ $o->id }}" onchange="updateSelectedCount()" onclick="event.stopPropagation()">
                        <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-sm font-bold text-primary">{{ $o->order_number }}</span>
                            <span id="status-badge-{{ $o->id }}" class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge[$o->status] ?? 'bg-muted text-muted-foreground border-border' }}">
                                {{ $o->statusLabel() }}
                            </span>
                            <span class="text-xs text-muted-foreground">{{ $o->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="mt-1 truncate text-sm">
                            <span class="font-semibold">{{ $o->customer_name }}</span>
                            <span class="text-muted-foreground"> · {{ $o->customer_phone }} · {{ $o->commune_name }}</span>
                        </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <div class="font-display text-base font-bold">{{ number_format($o->total, 0, ',', ' ') }} FCFA</div>
                            <div class="text-[10px] uppercase text-muted-foreground">{{ $o->items->count() }} article(s)</div>
                        </div>
                        <button type="button" onclick="event.stopPropagation(); const d=document.getElementById('order-{{ $o->id }}'); d.open=!d.open; return false;" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Voir">
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
                            <select id="status-select-{{ $o->id }}" data-order="{{ $o->id }}" data-old="{{ $o->status }}" onchange="updateOrderStatus(this)" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm cursor-pointer">
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ $o->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
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

{{-- Bulk action bar --}}
<div id="bulk-action-bar" class="fixed bottom-0 left-0 right-0 z-50 translate-y-full transition-transform duration-300">
    <div class="mx-auto mb-4 max-w-2xl px-4">
        <div class="flex items-center gap-3 rounded-2xl border border-accent/30 bg-card p-4 shadow-lg">
            <span id="bulk-count" class="text-sm font-bold text-foreground">0 commande(s)</span>
            <div id="bulk-divider" class="h-6 w-px bg-border"></div>
            <button type="button" id="bulk-print-btn" class="hidden md:flex items-center gap-2 rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-muted transition">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                Imprimer les tickets
            </button>
            <div id="bulk-status-zone" class="flex items-center gap-2">
                <select id="bulk-status-select" class="rounded-lg border border-border bg-background px-3 py-2 text-sm cursor-pointer">
                    <option value="">Changer le statut…</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <button type="button" id="bulk-status-btn" class="rounded-lg bg-accent px-3 py-2 text-sm font-bold text-accent-foreground transition hover:brightness-110">
                    Appliquer
                </button>
            </div>
            <div id="bulk-confirm-zone" class="hidden items-center gap-2">
                <span id="bulk-confirm-text" class="text-sm font-medium text-foreground"></span>
                <button type="button" id="bulk-confirm-yes" class="rounded-lg bg-accent px-3 py-2 text-sm font-bold text-accent-foreground transition hover:brightness-110">
                    Confirmer
                </button>
                <button type="button" id="bulk-confirm-no" class="rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-muted transition">
                    Annuler
                </button>
            </div>
            <button type="button" id="bulk-clear-btn" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-destructive font-bold hover:bg-destructive/10 transition" aria-label="Fermer">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </div>
</div>

<script>
const orderStatusBadges = @json($statusBadge);
const orderStatusLabels = @json($statuses);
const orderPrintData = {};

async function updateOrderStatus(select) {
    const orderId = select.dataset.order;
    const oldStatus = select.dataset.old;
    const newStatus = select.value;
    if (oldStatus === newStatus) return;

    try {
        const res = await fetch('/admin/orders/' + orderId + '/status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'X-HTTP-Method-Override': 'PATCH'
            },
            body: JSON.stringify({ status: newStatus })
        });
        const data = await res.json();
        if (data.success) {
            select.dataset.old = newStatus;
            const badge = document.getElementById('status-badge-' + orderId);
            if (badge) {
                badge.className = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold ' + (orderStatusBadges[newStatus] || 'bg-muted text-muted-foreground border-border');
                badge.textContent = orderStatusLabels[newStatus] || newStatus;
            }
            showToast('Statut changé en ' + (orderStatusLabels[newStatus] || newStatus));
        } else {
            select.value = oldStatus;
            showToast('Erreur lors du changement de statut', 'error');
        }
    } catch (e) {
        select.value = oldStatus;
        showToast('Erreur réseau', 'error');
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg text-sm font-medium z-50 transition-opacity ' + (type === 'error' ? 'bg-destructive text-white' : 'bg-accent text-accent-foreground');
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
}

function printInvoice(data) {
    orderPrintData[data.order_number] = data;
    const itemsHtml = data.items.map(function(item) {
        return '<div style="display:flex;justify-content:space-between;font-size:10px;"><span>' + item.qty + 'x ' + item.name + '</span><span>' + item.subtotal + ' FCFA</span></div>';
    }).join('');

    var sc = '<' + '/script>';
    var html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Ticket ' + data.order_number + '</title>' +
    '<style>' +
    'body{font-family:"Courier New",monospace;font-size:10px;line-height:1.3;color:#000;margin:0;padding:8px;}' +
    '.ticket{max-width:80mm;margin:0 auto;}' +
    '.center{text-align:center;}' +
    '.bold{font-weight:bold;}' +
    '.dashed{border-top:1px dashed #000;margin:6px 0;padding-top:4px;}' +
    '.right{text-align:right;}' +
    '.total{font-size:12px;font-weight:bold;}' +
    '</style></head><body>' +
    '<div class="ticket">' +
    '<div class="center bold" style="font-size:12px;">AUSPICE MARKET</div>' +
    '<div class="center">Bien-être bio & naturel</div>' +
    '<div class="center dashed">---</div>' +
    '<div><span class="bold">N° :</span> ' + data.order_number + '</div>' +
    '<div><span class="bold">Date :</span> ' + data.created_at + '</div>' +
    '<div class="dashed"></div>' +
    '<div><span class="bold">Client :</span> ' + data.customer_name + '</div>' +
    '<div><span class="bold">Tél :</span> ' + data.customer_phone + '</div>' +
    '<div><span class="bold">Adresse :</span> ' + data.commune_name + ' — ' + data.address + '</div>' +
    '<div class="dashed"></div>' +
    '<div class="center bold" style="font-size:10px;">ARTICLES</div>' +
    itemsHtml +
    '<div class="dashed"></div>' +
    '<div class="right">Sous-total : ' + data.subtotal + ' FCFA</div>' +
    '<div class="right">Livraison : ' + data.delivery_fee + ' FCFA</div>' +
    '<div class="right total">TOTAL : ' + data.total + ' FCFA</div>' +
    '<div class="dashed"></div>' +
    '<div class="center">---</div>' +
    '<div class="center">Merci pour votre confiance !</div>' +
    '<div class="center">Auspice Market</div>' +
    '<div class="center">---</div>' +
    '</div>' +
    sc + '>window.onload=function(){window.print();setTimeout(function(){window.close();},500);}' + sc + '>' +
    '</body></html>';

    var w = window.open('', '_blank', 'width=400,height=600');
    if (w) {
        w.document.open();
        w.document.write(html);
        w.document.close();
    } else {
        showToast('Veuillez autoriser les popups pour imprimer', 'error');
    }
}
</script>
<script>
// Ouvrir automatiquement la commande si on arrive via #order-{id}
(function() {
    if (location.hash) {
        var el = document.querySelector(location.hash);
        if (el && el.tagName === 'DETAILS') {
            el.open = true;
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
})();

// ===== Bulk actions =====
function getSelectedIds() {
    return Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
}

function updateSelectedCount() {
    const ids = getSelectedIds();
    const count = ids.length;
    document.getElementById('selected-count').textContent = count + ' sélectionnée(s)';
    document.getElementById('bulk-count').textContent = count + ' commande(s)';
    const bar = document.getElementById('bulk-action-bar');
    if (count > 0) {
        bar.classList.remove('translate-y-full');
    } else {
        bar.classList.add('translate-y-full');
    }
    const selectAll = document.getElementById('select-all-orders');
    const allCheckboxes = document.querySelectorAll('.order-checkbox');
    if (selectAll && allCheckboxes.length > 0) {
        selectAll.checked = count === allCheckboxes.length;
    }
}

function clearSelection() {
    document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('select-all-orders');
    if (selectAll) selectAll.checked = false;
    const statusSelect = document.getElementById('bulk-status-select');
    if (statusSelect) statusSelect.value = '';
    updateSelectedCount();
}

document.getElementById('select-all-orders')?.addEventListener('change', function() {
    document.querySelectorAll('.order-checkbox').forEach(cb => {
        cb.checked = this.checked;
    });
    updateSelectedCount();
});

document.getElementById('bulk-clear-btn')?.addEventListener('click', function() {
    document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('select-all-orders').checked = false;
    document.getElementById('bulk-status-select').value = '';
    updateSelectedCount();
});

document.getElementById('bulk-print-btn')?.addEventListener('click', function() {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    const checked = Array.from(document.querySelectorAll('.order-checkbox:checked'));
    const printDataList = [];
    checked.forEach(cb => {
        const details = cb.closest('details');
        if (details) {
            const btn = details.querySelector('button[onclick^="printInvoice"]');
            if (btn) {
                const match = btn.getAttribute('onclick').match(/printInvoice\((.+)\)/);
                if (match) {
                    try {
                        printDataList.push(JSON.parse(match[1]));
                    } catch(e) {}
                }
            }
        }
    });
    if (printDataList.length === 0) {
        showToast('Aucun ticket à imprimer', 'error');
        return;
    }
    bulkPrintInvoices(printDataList);
    clearSelection();
});

let pendingBulkStatus = null;

document.getElementById('bulk-status-btn')?.addEventListener('click', function() {
    const ids = getSelectedIds();
    const newStatus = document.getElementById('bulk-status-select').value;
    if (ids.length === 0 || !newStatus) {
        showToast('Sélectionnez des commandes et un statut', 'error');
        return;
    }
    pendingBulkStatus = { ids: ids, status: newStatus };
    document.getElementById('bulk-confirm-text').textContent = ids.length + ' commande(s) → ' + (orderStatusLabels[newStatus] || newStatus) + ' ?';
    ['bulk-count','bulk-divider','bulk-print-btn','bulk-status-zone'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
    });
    document.getElementById('bulk-confirm-zone').classList.remove('hidden');
    document.getElementById('bulk-confirm-zone').classList.add('flex');
});

document.getElementById('bulk-confirm-no')?.addEventListener('click', function() {
    pendingBulkStatus = null;
    document.getElementById('bulk-confirm-zone').classList.add('hidden');
    document.getElementById('bulk-confirm-zone').classList.remove('flex');
    ['bulk-count','bulk-divider','bulk-print-btn','bulk-status-zone'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) { el.classList.remove('hidden'); if (id === 'bulk-print-btn') el.classList.add('flex'); else el.classList.add('flex'); }
    });
});

document.getElementById('bulk-confirm-yes')?.addEventListener('click', async function() {
    if (!pendingBulkStatus) return;
    const ids = pendingBulkStatus.ids;
    const newStatus = pendingBulkStatus.status;
    pendingBulkStatus = null;
    document.getElementById('bulk-confirm-zone').classList.add('hidden');
    document.getElementById('bulk-confirm-zone').classList.remove('flex');
    ['bulk-count','bulk-divider','bulk-print-btn','bulk-status-zone'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) { el.classList.remove('hidden'); el.classList.add('flex'); }
    });
    try {
        const res = await fetch('/admin/orders/bulk-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ ids: ids, status: newStatus })
        });
        const data = await res.json();
        if (data.success) {
            ids.forEach(id => {
                const select = document.getElementById('status-select-' + id);
                if (select) { select.value = newStatus; select.dataset.old = newStatus; }
                const badge = document.getElementById('status-badge-' + id);
                if (badge) {
                    badge.className = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold ' + (orderStatusBadges[newStatus] || 'bg-muted text-muted-foreground border-border');
                    badge.textContent = orderStatusLabels[newStatus] || newStatus;
                }
            });
            showToast(data.updated + ' commande(s) mise(s) à jour');
            document.getElementById('bulk-status-select').value = '';
            clearSelection();
        } else {
            showToast('Erreur lors de la mise à jour', 'error');
        }
    } catch (e) {
        showToast('Erreur réseau', 'error');
    }
});

function bulkPrintInvoices(dataList) {
    var sc = '<' + '/script>';
    var ticketsHtml = dataList.map(function(data) {
        var itemsHtml = data.items.map(function(item) {
            return '<div style="display:flex;justify-content:space-between;font-size:10px;"><span>' + item.qty + 'x ' + item.name + '</span><span>' + item.subtotal + ' FCFA</span></div>';
        }).join('');
        return '<div class="ticket" style="margin-bottom:20px;border-bottom:2px dashed #000;padding-bottom:10px;">' +
            '<div class="center bold" style="font-size:12px;">AUSPICE MARKET</div>' +
            '<div class="center">Bien-être bio & naturel</div>' +
            '<div class="center dashed">---</div>' +
            '<div><span class="bold">N° :</span> ' + data.order_number + '</div>' +
            '<div><span class="bold">Date :</span> ' + data.created_at + '</div>' +
            '<div class="dashed"></div>' +
            '<div><span class="bold">Client :</span> ' + data.customer_name + '</div>' +
            '<div><span class="bold">Tél :</span> ' + data.customer_phone + '</div>' +
            '<div><span class="bold">Adresse :</span> ' + data.commune_name + ' — ' + data.address + '</div>' +
            '<div class="dashed"></div>' +
            '<div class="center bold" style="font-size:10px;">ARTICLES</div>' +
            itemsHtml +
            '<div class="dashed"></div>' +
            '<div class="right">Sous-total : ' + data.subtotal + ' FCFA</div>' +
            '<div class="right">Livraison : ' + data.delivery_fee + ' FCFA</div>' +
            '<div class="right total">TOTAL : ' + data.total + ' FCFA</div>' +
            '<div class="dashed"></div>' +
            '<div class="center">Merci pour votre confiance !</div>' +
            '<div class="center">Auspice Market</div>' +
            '<div class="center">---</div>' +
            '</div>';
    }).join('');

    var html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Tickets (' + dataList.length + ')</title>' +
    '<style>' +
    'body{font-family:"Courier New",monospace;font-size:10px;line-height:1.3;color:#000;margin:0;padding:8px;}' +
    '.ticket{max-width:80mm;margin:0 auto;}' +
    '.center{text-align:center;}' +
    '.bold{font-weight:bold;}' +
    '.dashed{border-top:1px dashed #000;margin:6px 0;padding-top:4px;}' +
    '.right{text-align:right;}' +
    '.total{font-size:12px;font-weight:bold;}' +
    '.toolbar{position:sticky;top:0;background:#fff;border-bottom:1px solid #ccc;padding:8px;text-align:center;z-index:10;}' +
    '.toolbar button{font-family:sans-serif;font-size:14px;padding:8px 20px;cursor:pointer;background:#2e7d4a;color:#fff;border:none;border-radius:6px;font-weight:bold;}' +
    '.toolbar button:hover{filter:brightness(1.1);}' +
    '@media print{.toolbar{display:none;}.ticket{page-break-after:always;}}' +
    '</style></head><body>' +
    '<div class="toolbar"><button onclick="window.print()">🖨 Imprimer tous les tickets (' + dataList.length + ')</button></div>' +
    ticketsHtml +
    '</body></html>';

    var w = window.open('', '_blank', 'width=400,height=600');
    if (w) {
        w.document.open();
        w.document.write(html);
        w.document.close();
    } else {
        showToast('Veuillez autoriser les popups pour imprimer', 'error');
    }
}
</script>
@endsection
