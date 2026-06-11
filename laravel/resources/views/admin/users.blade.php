@extends('components.admin-shell')

@section('title', 'Gestion des utilisateurs')

@section('content')
@if(session('success'))
    <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-destructive/10 border border-destructive/20 text-destructive p-4 rounded-lg mb-6">{{ session('error') }}</div>
@endif

@php
$isSuper = auth()->user()->isSuperAdmin();
@endphp

{{-- Stats cards --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Utilisateurs</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ $users->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Super Admins</p>
        <p class="mt-1 font-display text-2xl font-bold text-accent">{{ $users->filter(fn($u) => $u->userRoles->contains('role', 'super_admin'))->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Admins</p>
        <p class="mt-1 font-display text-2xl font-bold text-primary">{{ $users->filter(fn($u) => $u->userRoles->contains('role', 'admin'))->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Vendeurs</p>
        <p class="mt-1 font-display text-2xl font-bold text-success">{{ $users->filter(fn($u) => $u->userRoles->contains('role', 'vendeur'))->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Comptables</p>
        <p class="mt-1 font-display text-2xl font-bold text-muted-foreground">{{ $users->filter(fn($u) => $u->userRoles->contains('role', 'comptable'))->count() }}</p>
    </div>
</div>

{{-- Search + actions --}}
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <form action="/admin/users" method="GET" class="w-full sm:max-w-sm">
        <input type="text" name="q" value="{{ $q }}" placeholder="Rechercher par identifiant ou nom…" class="w-full rounded-lg border border-border bg-card px-4 py-2 text-sm shadow-sm outline-none focus:border-accent">
    </form>
    <div class="flex items-center gap-3">
        <span class="text-xs text-muted-foreground">{{ $users->count() }} utilisateur(s)</span>
        @if($isSuper)
        <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
            Nouveau compte
        </button>
        @endif
    </div>
</div>

@if(!$isSuper)
    <p class="mb-3 rounded-lg border border-border bg-muted/40 p-3 text-xs text-muted-foreground">
        Seul un <strong>Super Administrateur</strong> peut créer des comptes et attribuer des rôles.
    </p>
@endif

@if($users->isEmpty())
    <div class="rounded-2xl border border-dashed border-border p-10 text-center text-sm text-muted-foreground">
        Aucun utilisateur trouvé.
    </div>
@else
    <div class="overflow-x-auto rounded-2xl border border-border bg-card shadow-card">
        <table class="w-full text-sm">
            <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                <tr>
                    <th class="px-4 py-3 text-left">Utilisateur</th>
                    @foreach($availableRoles as $role)
                    <th class="px-4 py-3 text-center">{{ ucfirst(str_replace('_', ' ', $role)) }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr class="border-t border-border">
                    <td class="px-4 py-3">
                        <div class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <div>
                                <div class="font-medium">{{ $u->full_name ?: '—' }}</div>
                                <div class="font-mono text-xs text-muted-foreground">{{ $u->identifier ?? $u->email }}</div>
                            </div>
                        </div>
                    </td>
                    @foreach($availableRoles as $role)
                    <td class="px-4 py-3 text-center">
                        @if($u->userRoles->contains('role', $role))
                            <form action="/admin/users/{{ $u->id }}/role" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="role" value="{{ $role }}">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" {{ !$isSuper ? 'disabled' : '' }} onclick="return confirm('Retirer le rôle {{ $role }} ?')" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition disabled:opacity-50 bg-accent text-accent-foreground">
                                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    Attribué
                                </button>
                            </form>
                        @else
                            <form action="/admin/users/{{ $u->id }}/role" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="role" value="{{ $role }}">
                                <input type="hidden" name="action" value="add">
                                <button type="submit" {{ !$isSuper ? 'disabled' : '' }} class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition disabled:opacity-50 border border-border bg-background text-foreground/70 hover:border-accent">
                                    {{ $isSuper ? 'Attribuer' : '—' }}
                                </button>
                            </form>
                        @endif
                    </td>
                    @endforeach
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            {{-- Voir --}}
                            <button type="button" onclick="showUserDetails('{{ $u->id }}')" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Voir">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            @if($isSuper && $u->id !== auth()->id())
                            {{-- Reset password --}}
                            <form action="/admin/users/{{ $u->id }}/reset-password" method="POST" class="inline" onsubmit="return confirm('Envoyer un email de réinitialisation à {{ $u->email }} ?')">
                                @csrf
                                <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Reset mot de passe">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="8" x="3" y="3" rx="1"/><path d="M11 21h8a2 2 0 0 0 2-2v-8"/><path d="m21 3-9 9"/></svg>
                                </button>
                            </form>
                            {{-- Supprimer --}}
                            <form action="/admin/users/{{ $u->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer définitivement ce compte ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg text-destructive hover:bg-destructive/10 transition" title="Supprimer">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Modal Création --}}
<div id="create-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) closeCreateModal()">
    <form action="/admin/users" method="POST" onclick="event.stopPropagation()" class="w-full max-w-md rounded-2xl border border-border bg-card p-6 shadow-card">
        @csrf
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-bold">Nouveau compte</h2>
            <button type="button" onclick="closeCreateModal()" class="rounded-md p-1 hover:bg-muted transition">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <p class="mb-4 text-xs text-muted-foreground">
            Le système génère un identifiant unique et envoie un email d'invitation avec un lien pour définir le mot de passe.
        </p>
        <div class="space-y-3">
            <label class="block">
                <span class="mb-1 block text-xs font-medium">Nom complet (optionnel)</span>
                <input type="text" name="full_name" placeholder="Ex. Jean Kouassi" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-medium">Email *</span>
                <input type="email" name="email" required placeholder="jean@exemple.com" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-medium">Rôle initial</span>
                <select name="role" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
                    @foreach($availableRoles as $role)
                    <option value="{{ $role }}">{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <button type="submit" class="mt-5 w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground transition hover:opacity-90">
            Créer le compte & envoyer l'invitation
        </button>
    </form>
</div>

{{-- Modal Détails utilisateur --}}
<div id="detail-modal" class="hidden fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4 pt-10" onclick="if(event.target===this) closeDetailModal()">
    <div class="w-full max-w-3xl rounded-2xl border border-border bg-card p-6 shadow-elevated">
        <div class="mb-4 flex items-center justify-between">
            <h2 id="detail-title" class="font-display text-xl font-bold">Détail utilisateur</h2>
            <button type="button" onclick="closeDetailModal()" class="rounded-md p-1 hover:bg-muted transition">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <div id="detail-content" class="space-y-5">
            {{-- Infos user --}}
            <div class="rounded-xl border border-border bg-muted/30 p-4">
                <div class="grid gap-2 text-sm">
                    <div class="flex gap-2"><span class="text-muted-foreground w-24">Identifiant :</span><span id="detail-identifier" class="font-mono font-semibold"></span></div>
                    <div class="flex gap-2"><span class="text-muted-foreground w-24">Nom :</span><span id="detail-name"></span></div>
                    <div class="flex gap-2"><span class="text-muted-foreground w-24">Email :</span><span id="detail-email"></span></div>
                    <div class="flex gap-2"><span class="text-muted-foreground w-24">Créé le :</span><span id="detail-created"></span></div>
                    <div class="flex gap-2"><span class="text-muted-foreground w-24">Rôles :</span><span id="detail-roles" class="flex flex-wrap gap-1"></span></div>
                </div>
            </div>

            {{-- Logs --}}
            <div>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Journal d'activité</h3>
                    <select id="log-filter" onchange="filterLogs()" class="rounded-lg border border-border bg-background px-3 py-1.5 text-xs outline-none">
                        <option value="">Toutes les actions</option>
                    </select>
                </div>
                <div id="logs-container" class="overflow-hidden rounded-xl border border-border">
                    <div class="hidden md:grid grid-cols-[140px_1fr_100px_120px] gap-3 border-b border-border bg-muted/40 px-3 py-2 text-xs font-semibold uppercase text-muted-foreground">
                        <div>Date</div>
                        <div>Action</div>
                        <div>IP</div>
                        <div>Détails</div>
                    </div>
                    <ul id="logs-list" class="divide-y divide-border"></ul>
                </div>
                <p id="logs-empty" class="hidden p-6 text-center text-sm text-muted-foreground">Aucune activité enregistrée.</p>
            </div>
        </div>
    </div>
</div>

<script>
let currentUserId = null;
let allLogs = [];
let logActions = [];

function openCreateModal() {
    document.getElementById('create-modal').classList.remove('hidden');
}
function closeCreateModal() {
    document.getElementById('create-modal').classList.add('hidden');
}
function closeDetailModal() {
    document.getElementById('detail-modal').classList.add('hidden');
}

async function showUserDetails(userId) {
    currentUserId = userId;
    const modal = document.getElementById('detail-modal');
    modal.classList.remove('hidden');

    // Reset content
    document.getElementById('detail-identifier').textContent = 'Chargement…';
    document.getElementById('logs-list').innerHTML = '';

    try {
        const res = await fetch('/admin/users/' + userId + '/details');
        const data = await res.json();

        document.getElementById('detail-title').textContent = data.user.name || 'Détail utilisateur';
        document.getElementById('detail-identifier').textContent = data.user.identifier;
        document.getElementById('detail-name').textContent = data.user.name || '—';
        document.getElementById('detail-email').textContent = data.user.email;
        document.getElementById('detail-created').textContent = data.user.created_at;

        const rolesContainer = document.getElementById('detail-roles');
        rolesContainer.innerHTML = '';
        data.user.roles.forEach(r => {
            const span = document.createElement('span');
            span.className = 'inline-flex items-center rounded-full bg-accent/10 px-2 py-0.5 text-xs font-semibold text-accent';
            span.textContent = r;
            rolesContainer.appendChild(span);
        });

        allLogs = data.logs;
        logActions = data.log_actions;
        renderLogs();
        populateLogFilter();
    } catch (e) {
        document.getElementById('detail-identifier').textContent = 'Erreur de chargement';
    }
}

function populateLogFilter() {
    const select = document.getElementById('log-filter');
    const current = select.value;
    select.innerHTML = '<option value="">Toutes les actions</option>';
    logActions.forEach(action => {
        const opt = document.createElement('option');
        opt.value = action;
        opt.textContent = action;
        select.appendChild(opt);
    });
    select.value = current;
}

function filterLogs() {
    renderLogs();
}

function renderLogs() {
    const filter = document.getElementById('log-filter').value;
    const list = document.getElementById('logs-list');
    const empty = document.getElementById('logs-empty');
    const container = document.getElementById('logs-container');

    const filtered = filter ? allLogs.filter(l => l.action === filter) : allLogs;

    if (filtered.length === 0) {
        list.innerHTML = '';
        container.classList.add('hidden');
        empty.classList.remove('hidden');
        return;
    }

    container.classList.remove('hidden');
    empty.classList.add('hidden');
    list.innerHTML = '';

    filtered.forEach(log => {
        const li = document.createElement('li');
        li.className = 'grid grid-cols-1 gap-1 px-3 py-2 md:grid-cols-[140px_1fr_100px_120px] md:items-center';
        li.innerHTML = `
            <div class="text-xs text-muted-foreground">${log.created_at}</div>
            <div class="text-xs font-semibold uppercase text-accent">${log.action}</div>
            <div class="text-xs text-muted-foreground font-mono">${log.ip || '—'}</div>
            <div class="text-xs text-muted-foreground">${log.description || '—'}</div>
        `;
        list.appendChild(li);
    });
}

// Escape to close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateModal();
        closeDetailModal();
    }
});
</script>
@endsection
