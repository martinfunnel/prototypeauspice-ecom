@extends('components.admin-shell')

@section('title', 'Communes')

@section('content')
@if(session('success'))
    <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
@endif

{{-- Stats cards --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Communes</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ $communes->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Actives</p>
        <p class="mt-1 font-display text-2xl font-bold text-success">{{ $communes->where('is_active', true)->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Zones</p>
        <p class="mt-1 font-display text-2xl font-bold text-accent">{{ $communes->pluck('zone')->unique()->count() }}</p>
    </div>
</div>

{{-- Search --}}
<div class="mb-4">
    <div class="relative w-full sm:max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" id="search-communes" oninput="filterCommunes()" placeholder="Rechercher une commune…" class="w-full rounded-lg border border-border bg-card pl-9 pr-3 py-2 text-sm shadow-sm outline-none focus:border-accent">
    </div>
</div>

@if($communes->isEmpty())
    <div class="rounded-2xl border border-dashed border-border p-12 text-center text-sm text-muted-foreground">
        Aucune commune.
    </div>
@else
    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-card">
        <div class="hidden md:grid grid-cols-[1fr_120px_100px_80px_80px_100px] gap-3 border-b border-border bg-muted/40 px-4 py-2 text-xs font-semibold uppercase text-muted-foreground">
            <div>Commune</div>
            <div>Zone</div>
            <div class="text-right">Frais</div>
            <div class="text-right">Délai</div>
            <div class="text-center">Statut</div>
            <div class="text-right">Actions</div>
        </div>
        <ul class="divide-y divide-border">
            @foreach($communes as $c)
            <li class="commune-row grid grid-cols-1 gap-2 px-4 py-3 md:grid-cols-[1fr_120px_100px_80px_80px_100px] md:items-center">
                <div class="font-medium">{{ $c->name }}</div>
                <div class="text-sm text-muted-foreground">{{ $c->zone }}</div>
                <div class="text-right text-sm font-mono">{{ number_format($c->delivery_fee, 0, ',', ' ') }} FCFA</div>
                <div class="text-right text-sm">{{ $c->delivery_days }} j</div>
                <div class="flex justify-center">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $c->is_active ? 'bg-success/10 text-success' : 'bg-muted text-muted-foreground' }}">
                        {{ $c->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex items-center justify-end gap-1">
                    @canDo('edit_communes')
                    <button type="button" onclick="fillForm({{ json_encode([
                        'id' => $c->id,
                        'name' => $c->name,
                        'zone' => $c->zone,
                        'delivery_fee' => $c->delivery_fee,
                        'delivery_days' => $c->delivery_days,
                        'is_active' => $c->is_active,
                    ]) }})" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                    </button>
                    @endcanDo
                    @canDo('delete_communes')
                    <form action="/admin/communes/{{ $c->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer &quot;{{ $c->name }}&quot; ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg text-destructive hover:bg-destructive/10 transition">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        </button>
                    </form>
                    @endcanDo
                </div>
            </li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Inline form (hidden by default) --}}
<div id="commune-form" class="hidden mt-6 rounded-2xl border border-border bg-card shadow-card p-5">
    <h2 id="form-title" class="font-display text-lg font-bold mb-4">Nouvelle commune</h2>
    <form id="commune-form-tag" action="/admin/communes" method="POST" class="space-y-3">
        @csrf
        <input type="hidden" name="_method" id="method-override" value="">
        <div class="grid gap-3 sm:grid-cols-2">
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Nom *</span>
                <input type="text" name="name" id="f-name" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Zone *</span>
                <input type="text" name="zone" id="f-zone" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
        </div>
        <div class="grid gap-3 sm:grid-cols-3">
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Frais livraison (FCFA) *</span>
                <input type="number" name="delivery_fee" id="f-delivery_fee" required min="0" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Délai (jours) *</span>
                <input type="number" name="delivery_days" id="f-delivery_days" required min="0" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="flex items-center gap-2 pt-6 text-sm">
                <input type="checkbox" name="is_active" id="f-is_active" value="1" checked class="rounded border-border">
                Active
            </label>
        </div>
        <div class="flex justify-end gap-2 border-t border-border pt-4 mt-4">
            <button type="button" onclick="document.getElementById('commune-form').classList.add('hidden');" class="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted transition">Annuler</button>
            <button type="submit" class="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">Enregistrer</button>
        </div>
    </form>
</div>

<script>
function filterCommunes() {
    const q = document.getElementById('search-communes').value.toLowerCase();
    const items = document.querySelectorAll('.commune-row');
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(q) ? '' : 'none';
    });
}

function fillForm(data) {
    const form = document.getElementById('commune-form');
    form.classList.remove('hidden');
    document.getElementById('form-title').textContent = 'Modifier la commune';
    document.getElementById('commune-form-tag').action = '/admin/communes/' + data.id;
    document.getElementById('method-override').value = 'PATCH';

    document.getElementById('f-name').value = data.name;
    document.getElementById('f-zone').value = data.zone;
    document.getElementById('f-delivery_fee').value = data.delivery_fee;
    document.getElementById('f-delivery_days').value = data.delivery_days;
    document.getElementById('f-is_active').checked = data.is_active;

    form.scrollIntoView({ behavior: 'smooth' });
}
</script>
@endsection
