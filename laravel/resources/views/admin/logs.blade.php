@extends('components.admin-shell')

@section('title', 'Journal d\'activité')

@section('content')
@php
$totalLogs = $logs->total();
@endphp

<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Entrées</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ number_format($totalLogs, 0, ',', ' ') }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Actions distinctes</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ $actions->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Aujourd'hui</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ \App\Models\ActivityLog::whereDate('created_at', today())->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Cette semaine</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ \App\Models\ActivityLog::where('created_at', '>=', now()->subDays(6))->count() }}</p>
    </div>
</div>

{{-- Filtres --}}
<form action="/admin/logs" method="GET" class="mb-4 flex flex-wrap items-center gap-2">
    <div class="relative flex-1 min-w-[200px]">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher action, description, modèle…" class="w-full rounded-lg border border-border bg-background pl-9 pr-3 py-2 text-sm outline-none focus:border-accent">
    </div>
    <select name="action" class="rounded-lg border border-border bg-background px-3 py-2 text-sm">
        <option value="">Toutes les actions</option>
        @foreach($actions as $a)
            <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
        @endforeach
    </select>
    <select name="user_id" class="rounded-lg border border-border bg-background px-3 py-2 text-sm">
        <option value="">Tous les utilisateurs</option>
        @foreach($users as $u)
            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name ?? $u->identifier }}</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-muted transition">Filtrer</button>
    <a href="/admin/logs" class="rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-muted transition">Réinitialiser</a>
</form>

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
            <li class="log-row grid grid-cols-1 gap-2 px-4 py-3 md:grid-cols-[140px_1fr_120px_100px_80px_100px] md:items-center text-sm {{ $isError ? 'bg-destructive/5' : '' }}">
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

    html += '<div class="mt-3"><span class="text-muted-foreground text-xs uppercase">Description</span><p class="text-sm text-foreground">' + data.description + '</p></div>';

    if (data.model_type) {
        html += '<div class="mt-2"><span class="text-muted-foreground text-xs uppercase">Modèle</span><p class="text-sm font-mono">' + data.model_type + (data.model_id ? ' #' + data.model_id : '') + '</p></div>';
    }

    if (data.metadata && data.metadata !== 'null') {
        html += '<div class="mt-3"><span class="text-muted-foreground text-xs uppercase">Métadonnées (mode dev)</span><pre class="mt-1 rounded-lg border border-border bg-black/5 p-3 text-[11px] font-mono text-foreground overflow-x-auto">' + data.metadata.replace(/</g, '&lt;') + '</pre></div>';
    }

    wrapper.innerHTML = html;
    li.after(wrapper);
}
</script>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
@endif
@endsection
