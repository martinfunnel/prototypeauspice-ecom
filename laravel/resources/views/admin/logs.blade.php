@extends('components.admin-shell')

@section('title', 'Journal d\'activité')

@section('content')
@php
$totalLogs = $logs->total();
@endphp

<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Entrées</p>
        <p id="stat-total" class="mt-1 font-display text-2xl font-bold">{{ number_format($totalLogs, 0, ',', ' ') }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Actions distinctes</p>
        <p id="stat-actions" class="mt-1 font-display text-2xl font-bold">{{ $actions->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Aujourd'hui</p>
        <p id="stat-today" class="mt-1 font-display text-2xl font-bold">{{ \App\Models\ActivityLog::whereDate('created_at', today())->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Cette semaine</p>
        <p id="stat-week" class="mt-1 font-display text-2xl font-bold">{{ \App\Models\ActivityLog::where('created_at', '>=', now()->subDays(6))->count() }}</p>
    </div>
</div>

{{-- Filtres live + auto-refresh --}}
<div class="mb-4 flex flex-wrap items-center gap-2">
    <div class="relative flex-1 min-w-[200px]">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" id="live-search" placeholder="Rechercher action, description, modèle…" class="w-full rounded-lg border border-border bg-background pl-9 pr-3 py-2 text-sm outline-none focus:border-accent">
    </div>
    <select id="live-action" class="rounded-lg border border-border bg-background px-3 py-2 text-sm">
        <option value="">Toutes les actions</option>
        @foreach($actions as $a)
            <option value="{{ $a }}">{{ $a }}</option>
        @endforeach
    </select>
    <select id="live-user" class="rounded-lg border border-border bg-background px-3 py-2 text-sm">
        <option value="">Tous les utilisateurs</option>
        @foreach($users as $u)
            <option value="{{ $u->id }}">{{ $u->name ?? $u->identifier }}</option>
        @endforeach
    </select>
    <button type="button" id="live-reset" onclick="resetFilters()" class="rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-muted transition">Réinitialiser</button>

    <button type="button" id="auto-refresh-toggle" onclick="toggleAutoRefresh()" class="ml-auto flex items-center gap-1.5 rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium transition" title="Auto-actualisation">
        <span id="refresh-indicator" class="inline-block h-2 w-2 rounded-full bg-success"></span>
        <span id="refresh-label">Auto ON</span>
    </button>
</div>

@if($logs->isEmpty())
    <div class="rounded-xl border border-dashed border-border bg-card p-10 text-center text-sm text-muted-foreground">
        Aucune entrée dans le journal d'activité.
    </div>
@else
    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-card">
        <div class="hidden md:grid grid-cols-[140px_1fr_120px_100px_80px_100px] gap-3 border-b border-border bg-muted/40 px-4 py-2 text-xs font-semibold uppercase text-muted-foreground">
            <div>Date</div>
            <div>Action / Description</div>
            <div>Modèle</div>
            <div class="text-center">IP</div>
            <div class="text-center">Voir</div>
            <div class="text-right">Utilisateur</div>
        </div>
        <ul class="divide-y divide-border">
            @foreach($logs as $log)
            @php
                $isError = in_array($log->action, ['unauthorized_access', 'validation_error', 'error']);
                $metadata = $log->metadata ? json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null;
            @endphp
            <li class="log-row grid grid-cols-1 gap-2 px-4 py-3 md:grid-cols-[140px_1fr_120px_100px_80px_100px] md:items-center text-sm {{ $isError ? 'bg-destructive/5' : '' }}"
                data-log-id="{{ $log->id }}"
                data-action="{{ $log->action }}"
                data-user-id="{{ $log->user_id }}"
                data-text="{{ strtolower($log->action . ' ' . ($log->description ?? '') . ' ' . ($log->model_type ?? '')) }}">
                <div class="text-xs text-muted-foreground">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                <div class="min-w-0">
                    <div class="text-xs font-semibold uppercase {{ $isError ? 'text-destructive' : 'text-accent' }}">{{ $log->action }}</div>
                    <div class="text-muted-foreground truncate">{{ $log->description ?? '—' }}</div>
                </div>
                <div class="text-xs text-muted-foreground truncate">
                    @if($log->model_type)
                        {{ class_basename($log->model_type) }}
                        @if($log->model_id)
                            <span class="font-mono">#{{ Str::limit($log->model_id, 8, '') }}</span>
                        @endif
                    @else
                        —
                    @endif
                </div>
                <div class="text-xs text-muted-foreground font-mono text-center">{{ $log->ip_address ?? '—' }}</div>
                <div class="flex justify-center">
                    <button type="button" onclick="showLogDetail(this, {{ json_encode([
                        'action' => $log->action,
                        'description' => $log->description ?? '—',
                        'model_type' => $log->model_type ? class_basename($log->model_type) : null,
                        'model_id' => $log->model_id,
                        'ip' => $log->ip_address,
                        'metadata' => $metadata,
                        'user_name' => $log->user?->name ?? $log->user?->identifier ?? 'Système',
                        'user_id' => $log->user_id,
                        'created_at' => $log->created_at->format('d/m/Y H:i:s'),
                    ]) }})" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Voir détails">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <div class="text-right text-xs">
                    @if($log->user)
                        <span class="font-medium">{{ $log->user->name ?? $log->user->identifier }}</span>
                    @else
                        <span class="text-muted-foreground">Système</span>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>

<script>
const csrf = '{{ csrf_token() }}';
const errorActions = ['unauthorized_access', 'validation_error', 'error'];

function showLogDetail(btn, data) {
    const li = btn.closest('li');
    let panel = li.nextElementSibling;
    if (panel && panel.classList.contains('detail-panel')) {
        panel.remove();
        return;
    }
    document.querySelectorAll('.detail-panel').forEach(el => el.remove());

    const wrapper = document.createElement('li');
    wrapper.className = 'detail-panel col-span-full px-4 py-4 border-t border-border bg-muted/20';

    let html = '<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">';
    html += '<div><span class="text-muted-foreground text-xs uppercase">Action</span><p class="font-semibold">' + data.action + '</p></div>';
    html += '<div><span class="text-muted-foreground text-xs uppercase">Utilisateur</span><p class="font-semibold">' + data.user_name + '</p></div>';
    html += '<div><span class="text-muted-foreground text-xs uppercase">Date</span><p class="font-semibold">' + data.created_at + '</p></div>';
    html += '<div><span class="text-muted-foreground text-xs uppercase">IP</span><p class="font-mono font-semibold">' + (data.ip ?? '—') + '</p></div>';
    html += '</div>';

    html += '<div class="mt-3"><span class="text-muted-foreground text-xs uppercase">Description</span><p class="text-sm text-foreground">' + escapeHtml(data.description) + '</p></div>';

    if (data.model_type) {
        html += '<div class="mt-2"><span class="text-muted-foreground text-xs uppercase">Modèle</span><p class="text-sm font-mono">' + data.model_type + (data.model_id ? ' #' + data.model_id : '') + '</p></div>';
    }

    if (data.metadata && data.metadata !== 'null') {
        html += '<div class="mt-3"><span class="text-muted-foreground text-xs uppercase">Métadonnées (mode dev)</span><pre class="mt-1 rounded-lg border border-border bg-black/5 p-3 text-[11px] font-mono text-foreground overflow-x-auto">' + escapeHtml(data.metadata) + '</pre></div>';
    }

    wrapper.innerHTML = html;
    li.after(wrapper);
}

function escapeHtml(text) {
    if (!text) return '';
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

/* ---- Live filtering ---- */
const searchInput = document.getElementById('live-search');
const actionSelect = document.getElementById('live-action');
const userSelect = document.getElementById('live-user');

function filterLogs() {
    const q = searchInput.value.toLowerCase().trim();
    const action = actionSelect.value;
    const userId = userSelect.value;
    const rows = document.querySelectorAll('.log-row');
    let visible = 0;
    rows.forEach(row => {
        const text = row.dataset.text;
        const rowAction = row.dataset.action;
        const rowUser = row.dataset.userId;
        const matchText = !q || text.includes(q);
        const matchAction = !action || rowAction === action;
        const matchUser = !userId || rowUser === userId;
        if (matchText && matchAction && matchUser) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });
}

searchInput.addEventListener('input', filterLogs);
actionSelect.addEventListener('change', filterLogs);
userSelect.addEventListener('change', filterLogs);

function resetFilters() {
    searchInput.value = '';
    actionSelect.value = '';
    userSelect.value = '';
    filterLogs();
}

/* ---- Auto-refresh ---- */
let autoRefreshOn = true;
let refreshTimer = null;
const knownIds = new Set();

// Record initial IDs
document.querySelectorAll('.log-row').forEach(row => knownIds.add(parseInt(row.dataset.logId)));

function toggleAutoRefresh() {
    autoRefreshOn = !autoRefreshOn;
    document.getElementById('refresh-indicator').className = 'inline-block h-2 w-2 rounded-full ' + (autoRefreshOn ? 'bg-success' : 'bg-muted');
    document.getElementById('refresh-label').textContent = autoRefreshOn ? 'Auto ON' : 'Auto OFF';
    if (autoRefreshOn) startPolling(); else stopPolling();
}

function stopPolling() {
    if (refreshTimer) clearInterval(refreshTimer);
    refreshTimer = null;
}

function startPolling() {
    stopPolling();
    refreshTimer = setInterval(pollNewLogs, 10000);
}

function buildLogRowHtml(log) {
    const isError = errorActions.includes(log.action);
    const metadata = log.metadata ? JSON.stringify(log.metadata, null, 2) : null;
    const userName = log.user?.name ?? log.user?.identifier ?? 'Système';
    const createdAt = log.created_at;
    const timeLabel = createdAt ? createdAt.substring(0, 16).replace('T', ' ') : '';

    let modelHtml = '—';
    if (log.model_type) {
        const base = log.model_type.split('\\').pop();
        modelHtml = base + (log.model_id ? ' <span class="font-mono">#' + (log.model_id.length > 8 ? log.model_id.substring(0,8) : log.model_id) + '</span>' : '');
    }

    const detailData = JSON.stringify({
        action: log.action,
        description: log.description ?? '—',
        model_type: log.model_type ? log.model_type.split('\\').pop() : null,
        model_id: log.model_id,
        ip: log.ip_address,
        metadata: metadata,
        user_name: userName,
        user_id: log.user_id,
        created_at: log.created_at ? log.created_at.replace('T', ' ').substring(0, 19) : ''
    });

    return '<li class="log-row grid grid-cols-1 gap-2 px-4 py-3 md:grid-cols-[140px_1fr_120px_100px_80px_100px] md:items-center text-sm ' + (isError ? 'bg-destructive/5' : '') + '"'
        + ' data-log-id="' + log.id + '"'
        + ' data-action="' + log.action + '"'
        + ' data-user-id="' + (log.user_id ?? '') + '"'
        + ' data-text="' + (log.action + ' ' + (log.description ?? '') + ' ' + (log.model_type ?? '')).toLowerCase().replace(/"/g, '&quot;') + '">'
        + '<div class="text-xs text-muted-foreground">' + timeLabel + '</div>'
        + '<div class="min-w-0">'
        + '<div class="text-xs font-semibold uppercase ' + (isError ? 'text-destructive' : 'text-accent') + '">' + log.action + '</div>'
        + '<div class="text-muted-foreground truncate">' + escapeHtml(log.description ?? '—') + '</div>'
        + '</div>'
        + '<div class="text-xs text-muted-foreground truncate">' + modelHtml + '</div>'
        + '<div class="text-xs text-muted-foreground font-mono text-center">' + (log.ip_address ?? '—') + '</div>'
        + '<div class="flex justify-center">'
        + '<button type="button" onclick="showLogDetail(this, ' + detailData.replace(/"/g, '&quot;') + ')" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Voir détails">'
        + '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>'
        + '</button></div>'
        + '<div class="text-right text-xs"><span class="font-medium">' + escapeHtml(userName) + '</span></div>'
        + '</li>';
}

function pollNewLogs() {
    if (!autoRefreshOn) return;
    fetch('/admin/logs?format=json')
        .then(r => r.json())
        .then(data => {
            if (!data.logs || !data.logs.length) return;

            // Update stats
            document.getElementById('stat-total').textContent = data.total.toLocaleString('fr-FR');
            document.getElementById('stat-today').textContent = data.today.toLocaleString('fr-FR');
            document.getElementById('stat-week').textContent = data.this_week.toLocaleString('fr-FR');

            const firstRow = document.querySelector('.log-row');
            const list = firstRow ? firstRow.parentElement : null;
            let inserted = 0;
            const fragment = document.createElement('div');

            data.logs.forEach(log => {
                const id = parseInt(log.id);
                if (!knownIds.has(id)) {
                    knownIds.add(id);
                    fragment.insertAdjacentHTML('afterbegin', buildLogRowHtml(log));
                    inserted++;
                }
            });

            if (inserted > 0 && list) {
                const rows = Array.from(fragment.children);
                rows.reverse().forEach(row => list.insertBefore(row, list.firstChild));
                // Re-apply current filter
                filterLogs();
                // Show toast
                const toast = document.createElement('div');
                toast.className = 'fixed bottom-4 right-4 bg-accent text-accent-foreground px-4 py-2 rounded-lg shadow-lg text-sm font-medium z-50 transition-opacity';
                toast.textContent = inserted + ' nouvelle(s) entrée(s)';
                document.body.appendChild(toast);
                setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 500); }, 3000);
            }
        })
        .catch(() => {});
}

startPolling();
</script>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
@endif
@endsection
