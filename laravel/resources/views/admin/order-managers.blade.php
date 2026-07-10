@extends('components.admin-shell')

@section('title', 'Gérants de commandes — Telegram')

@section('content')
{{-- Stats cards --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Gérants</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ $managers->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Actifs</p>
        <p class="mt-1 font-display text-2xl font-bold text-success">{{ $managers->where('is_active', true)->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Inactifs</p>
        <p class="mt-1 font-display text-2xl font-bold text-muted-foreground">{{ $managers->where('is_active', false)->count() }}</p>
    </div>
</div>

{{-- Create button --}}
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="relative w-full sm:max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" id="search-managers" oninput="filterManagers()" placeholder="Rechercher un gérant…" class="w-full rounded-lg border border-border bg-card pl-9 pr-3 py-2 text-sm shadow-sm outline-none focus:border-accent">
    </div>
    @canDo('order_telegram_notification')
    <button type="button" onclick="toggleCreateForm()" class="inline-flex items-center gap-2 rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-accent-foreground shadow-accent transition hover:opacity-90">
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        Nouveau gérant
    </button>
    @endcanDo
</div>

@canDo('order_telegram_notification')
<div id="create-form-panel" class="mb-4 rounded-2xl border border-border bg-card p-4 shadow-card" style="display:none;">
    <h3 class="font-display text-sm font-bold mb-3">Nouveau gérant de commande</h3>
    <form action="/admin/order-managers" method="POST" class="space-y-3">
        @csrf
        <div class="grid gap-3 sm:grid-cols-2">
            <label class="block">
                <span class="mb-1 block text-xs font-semibold">Nom *</span>
                <input type="text" name="name" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold">Chat ID Telegram *</span>
                <input type="text" name="phone" required placeholder="123456789" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">Ajouter</button>
            <button type="button" onclick="toggleCreateForm()" class="rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium transition hover:bg-muted">Annuler</button>
        </div>
    </form>
</div>
@endcanDo

{{-- Tutoriel Chat ID --}}
<div class="mb-4 rounded-2xl border border-accent/30 bg-accent/5 p-4">
    <h3 class="flex items-center gap-2 font-display text-sm font-bold text-accent">
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        Comment obtenir le Chat ID Telegram ?
    </h3>
    <ol class="mt-3 space-y-1.5 text-sm text-foreground/80">
        <li><strong>1.</strong> Ouvre Telegram et cherche le bot <code class="rounded bg-muted px-1.5 py-0.5 text-xs font-mono">@userinfobot</code></li>
        <li><strong>2.</strong> Envoie-lui n'importe quel message (ex: <code class="rounded bg-muted px-1.5 py-0.5 text-xs font-mono">/start</code>)</li>
        <li><strong>3.</strong> Il te répond avec ton <strong>Id</strong> (ex: <code class="rounded bg-muted px-1.5 py-0.5 text-xs font-mono">123456789</code>)</li>
        <li><strong>4.</strong> Copie ce numéro et colle-le dans le champ « Chat ID Telegram »</li>
    </ol>
    <p class="mt-2 text-xs text-muted-foreground">
         Chaque gérant doit aussi envoyer au moins un message à ton bot (ex: <code class="rounded bg-muted px-1 py-0.5 font-mono">/start</code>) avant de pouvoir recevoir les notifications.
    </p>
</div>

{{-- Table --}}
<div class="overflow-x-auto rounded-2xl border border-border bg-card shadow-card">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-border bg-muted/50 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                <th class="px-4 py-3">Nom</th>
                <th class="px-4 py-3">Chat ID Telegram</th>
                <th class="px-4 py-3">Statut</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody id="managers-table-body" class="divide-y divide-border">
            @forelse($managers as $manager)
            <tr class="manager-row transition hover:bg-muted/30">
                <td class="px-4 py-3 font-medium">{{ $manager->name }}</td>
                <td class="px-4 py-3">{{ $manager->phone }}</td>
                <td class="px-4 py-3">
                    @if($manager->is_active)
                        <span class="inline-flex items-center gap-1 rounded-full bg-success/10 px-2 py-0.5 text-xs font-semibold text-success">
                            <span class="h-1.5 w-1.5 rounded-full bg-success"></span> Actif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-0.5 text-xs font-semibold text-muted-foreground">
                            <span class="h-1.5 w-1.5 rounded-full bg-muted-foreground"></span> Inactif
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    @canDo('order_telegram_notification')
                    <button type="button" onclick="openEditPanel('{{ $manager->id }}')" class="mr-1 inline-flex items-center justify-center h-8 w-8 rounded-lg text-foreground/70 hover:bg-muted transition" title="Modifier">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                    </button>
                    <form action="/admin/order-managers/{{ $manager->id }}/toggle" method="POST" class="inline">
                        @csrf
                        @if($manager->is_active)
                        <button type="submit" class="mr-1 inline-flex items-center justify-center h-8 w-8 rounded-lg text-warning hover:bg-warning/10 transition" title="Suspendre" onclick="return confirm('Suspendre ce gérant ?')">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                        </button>
                        @else
                        <button type="submit" class="mr-1 inline-flex items-center justify-center h-8 w-8 rounded-lg text-success hover:bg-success/10 transition" title="Activer" onclick="return confirm('Réactiver ce gérant ?')">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>
                        </button>
                        @endif
                    </form>
                    <form action="/admin/order-managers/{{ $manager->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce gérant ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-destructive hover:bg-destructive/10 transition" title="Supprimer">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        </button>
                    </form>
                    @endcanDo
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-muted-foreground">
                    Aucun gérant de commande configuré.
                    <br>Les gérants reçoivent une notification Telegram à chaque nouvelle commande.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Edit panels --}}
@canDo('order_telegram_notification')
@foreach($managers as $manager)
<div id="edit-panel-{{ $manager->id }}" class="edit-panel mb-4 rounded-2xl border border-border bg-card p-4 shadow-card" style="display:none;">
    <h3 class="font-display text-sm font-bold mb-3">Modifier le gérant</h3>
    <form action="/admin/order-managers/{{ $manager->id }}" method="POST" class="space-y-3">
        @csrf @method('PATCH')
        <div class="grid gap-3 sm:grid-cols-3">
            <label class="block">
                <span class="mb-1 block text-xs font-semibold">Nom *</span>
                <input type="text" name="name" value="{{ $manager->name }}" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold">Chat ID Telegram *</span>
                <input type="text" name="phone" value="{{ $manager->phone }}" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block flex items-center gap-2 pt-6">
                <input type="checkbox" name="is_active" value="1" {{ $manager->is_active ? 'checked' : '' }} class="h-4 w-4 rounded border-border text-accent focus:ring-accent">
                <span class="text-sm">Actif</span>
            </label>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">Enregistrer</button>
            <button type="button" onclick="closeEditPanel('{{ $manager->id }}')" class="rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium transition hover:bg-muted">Annuler</button>
        </div>
    </form>
</div>
@endforeach
@endcanDo

<script>
function toggleCreateForm() {
    const el = document.getElementById('create-form-panel');
    if (el.style.display === 'none') {
        window.accordionOpen(el);
    } else {
        window.accordionClose(el);
    }
}

function openEditPanel(id) {
    document.querySelectorAll('.edit-panel').forEach(p => p.style.display = 'none');
    const el = document.getElementById('edit-panel-' + id);
    if (el) window.accordionOpen(el);
}

function closeEditPanel(id) {
    const el = document.getElementById('edit-panel-' + id);
    if (el && el.style.display !== 'none') window.accordionClose(el);
}

function filterManagers() {
    const q = document.getElementById('search-managers').value.toLowerCase();
    document.querySelectorAll('.manager-row').forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
}
</script>
@endsection
