@extends('components.admin-shell')

@section('title', 'Codes pays (téléphones)')

@section('content')
{{-- Stats --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Codes pays</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ $codes->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Actifs</p>
        <p class="mt-1 font-display text-2xl font-bold text-success">{{ $codes->where('is_active', true)->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Inactifs</p>
        <p class="mt-1 font-display text-2xl font-bold text-muted-foreground">{{ $codes->where('is_active', false)->count() }}</p>
    </div>
</div>

{{-- Create panel --}}
<div class="mb-4">
    <button type="button" onclick="toggleCreatePanel()" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Nouveau code pays
    </button>
</div>

<div id="create-panel" class="rounded-2xl border border-border bg-card p-6 shadow-card" style="display:none;">
    <h2 class="mb-4 font-display text-lg font-bold">Nouveau code pays</h2>
    <form action="/admin/country-codes" method="POST" class="grid gap-4 sm:grid-cols-3">
        @csrf
        <label class="block">
            <span class="mb-1 block text-xs font-semibold">Nom du pays *</span>
            <input type="text" name="name" required placeholder="Ex. Togo" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold">Code indicatif *</span>
            <input type="text" name="code" required placeholder="+228" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold">ISO (3 lettres) *</span>
            <input type="text" name="iso" required maxlength="3" placeholder="TGO" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold">Chiffres attendus *</span>
            <input type="number" name="digits" required min="1" max="20" placeholder="8" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold">Format d'affichage *</span>
            <input type="text" name="format" required placeholder="XX XX XX XX" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold">Regex validation *</span>
            <input type="text" name="pattern" required placeholder="/^\d{8}$/" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold">Ordre</span>
            <input type="number" name="sort_order" value="0" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
        </label>
        <div class="flex items-end gap-2">
            <button type="submit" class="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">Ajouter</button>
            <button type="button" onclick="toggleCreatePanel()" class="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted transition">Annuler</button>
        </div>
    </form>
</div>

@if($codes->isEmpty())
    <div class="rounded-2xl border border-dashed border-border p-10 text-center text-sm text-muted-foreground">
        Aucun code pays configuré.
    </div>
@else
    <div class="overflow-x-auto rounded-2xl border border-border bg-card shadow-card">
        <table class="w-full text-sm">
            <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                <tr>
                    <th class="px-4 py-3 text-left">Pays</th>
                    <th class="px-4 py-3 text-center">Code</th>
                    <th class="px-4 py-3 text-center">ISO</th>
                    <th class="px-4 py-3 text-center">Chiffres</th>
                    <th class="px-4 py-3 text-center">Format</th>
                    <th class="px-4 py-3 text-center">Regex</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($codes as $c)
                <tr class="border-t border-border">
                    <td class="px-4 py-3 font-medium">{{ $c->name }}</td>
                    <td class="px-4 py-3 text-center font-mono">{{ $c->code }}</td>
                    <td class="px-4 py-3 text-center font-mono text-xs">{{ $c->iso }}</td>
                    <td class="px-4 py-3 text-center">{{ $c->digits }}</td>
                    <td class="px-4 py-3 text-center font-mono text-xs">{{ $c->format }}</td>
                    <td class="px-4 py-3 text-center font-mono text-xs">{{ $c->pattern }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $c->is_active ? 'bg-success/10 text-success' : 'bg-muted text-muted-foreground' }}">
                            {{ $c->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" onclick="openEditPanel('{{ $c->id }}')" class="grid h-8 w-8 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Modifier">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            </button>
                            <form action="/admin/country-codes/{{ $c->id }}/toggle" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="grid h-8 w-8 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="{{ $c->is_active ? 'Désactiver' : 'Activer' }}">
                                    @if($c->is_active)
                                        <svg class="h-4 w-4 text-warning" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                                    @else
                                        <svg class="h-4 w-4 text-success" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    @endif
                                </button>
                            </form>
                            <form action="/admin/country-codes/{{ $c->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce code pays ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="grid h-8 w-8 place-items-center rounded-lg text-destructive hover:bg-destructive/10 transition" title="Supprimer">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                {{-- Edit inline panel --}}
                <tr id="edit-panel-{{ $c->id }}" class="edit-panel border-t border-border" style="display:none;">
                    <td colspan="8" class="px-4 py-4 bg-muted/20">
                        <form action="/admin/country-codes/{{ $c->id }}" method="POST" class="grid gap-3 sm:grid-cols-3 lg:grid-cols-4">
                            @csrf @method('PATCH')
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold">Nom du pays *</span>
                                <input type="text" name="name" value="{{ $c->name }}" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold">Code indicatif *</span>
                                <input type="text" name="code" value="{{ $c->code }}" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold">ISO *</span>
                                <input type="text" name="iso" value="{{ $c->iso }}" required maxlength="3" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold">Chiffres *</span>
                                <input type="number" name="digits" value="{{ $c->digits }}" required min="1" max="20" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold">Format *</span>
                                <input type="text" name="format" value="{{ $c->format }}" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold">Regex *</span>
                                <input type="text" name="pattern" value="{{ $c->pattern }}" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold">Ordre</span>
                                <input type="number" name="sort_order" value="{{ $c->sort_order }}" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="is_active" value="1" {{ $c->is_active ? 'checked' : '' }} class="rounded border-border">
                                Actif
                            </label>
                            <div class="flex items-end gap-2 sm:col-span-3 lg:col-span-4">
                                <button type="submit" class="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">Enregistrer</button>
                                <button type="button" onclick="closeEditPanel('{{ $c->id }}')" class="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted transition">Annuler</button>
                            </div>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<script>
function toggleCreatePanel() {
    const el = document.getElementById('create-panel');
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
</script>
@endsection
