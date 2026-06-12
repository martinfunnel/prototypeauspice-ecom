@extends('components.admin-shell')

@section('title', 'Rôles & Permissions')

@section('content')


        {{-- Stats cards --}}
        @php
            $totalRoles = $roles->count();
            $totalPerms = \App\Models\Permission::count();
            $systemRoles = $roles->filter(fn($r) => in_array($r->key, ['super_admin', 'admin', 'manager', 'viewer']))->count();
            $customRoles = $totalRoles - $systemRoles;
        @endphp
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Rôles</p>
                <p class="mt-1 font-display text-2xl font-bold">{{ $totalRoles }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Permissions</p>
                <p class="mt-1 font-display text-2xl font-bold">{{ $totalPerms }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Système</p>
                <p class="mt-1 font-display text-2xl font-bold">{{ $systemRoles }}</p>
            </div>
            <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Personnalisés</p>
                <p class="mt-1 font-display text-2xl font-bold">{{ $customRoles }}</p>
            </div>
        </div>

        {{-- Filter + Create button --}}
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" id="role-search" placeholder="Filtrer rôles…" class="w-full rounded-lg border border-border bg-background pl-9 pr-3 py-2 text-sm outline-none focus:border-accent">
            </div>
            <button type="button" id="btn-create-role" onclick="document.getElementById('role-create-form').classList.toggle('hidden'); this.classList.add('hidden');" class="rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-accent-foreground transition hover:opacity-90">
                + Créer un rôle
            </button>
        </div>
        {{-- Create form (hidden by default) --}}
        <div id="role-create-form" class="hidden bg-card rounded-xl shadow-card border border-border p-6 mb-8">
            <h2 class="font-display text-lg font-bold text-foreground mb-4">Nouveau rôle</h2>
            <form action="/admin/roles" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" name="key" placeholder="Clé (ex: manager)" required class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                    <input type="text" name="name" placeholder="Nom (ex: Manager)" required class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($permissions as $group => $groupPermissions)
                        <div class="bg-muted/50 rounded-lg p-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">{{ $group }}</h3>
                            <div class="space-y-1.5">
                                @foreach($groupPermissions as $perm)
                                    <label class="flex items-center gap-2 text-sm text-foreground">
                                        <input type="checkbox" name="permission_ids[]" value="{{ $perm->id }}">
                                        {{ $perm->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground">Créer le rôle</button>
                    <button type="button" onclick="document.getElementById('role-create-form').classList.add('hidden'); document.getElementById('btn-create-role').classList.remove('hidden');" class="rounded-lg border border-border px-5 py-2.5 text-sm font-medium text-foreground hover:bg-muted transition">Annuler</button>
                </div>
            </form>
        </div>

        <div class="bg-card rounded-xl shadow-card border border-border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr>
                        <th class="text-left p-3 text-foreground font-semibold">Clé</th>
                        <th class="text-left p-3 text-foreground font-semibold">Nom</th>
                        <th class="text-left p-3 text-foreground font-semibold">Permissions</th>
                        <th class="text-left p-3 text-foreground font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $r)
                        <tr class="border-b border-border hover:bg-muted transition role-row"
                            data-text="{{ strtolower($r->key . ' ' . $r->name) }}">
                            <td class="p-3 font-medium text-foreground">{{ $r->key }}</td>
                            <td class="p-3 text-foreground">{{ $r->name }}</td>
                            <td class="p-3 text-muted-foreground">{{ $r->permissions->count() }} permission(s)</td>
                            <td class="p-3 flex gap-1">
                                <button type="button" onclick="showRoleDetail(this, {{ json_encode([
                                    'id' => $r->id,
                                    'key' => $r->key,
                                    'name' => $r->name,
                                    'permissions' => $r->permissions->map(fn($p) => ['group' => $p->group, 'name' => $p->name])->groupBy('group')->toArray(),
                                ]) }})" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Voir">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button type="button" onclick="openEditRoleInline(this, {{ json_encode([
                                    'id' => $r->id,
                                    'key' => $r->key,
                                    'name' => $r->name,
                                    'permission_ids' => $r->permissions->pluck('id')->values()->toArray(),
                                ]) }})" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Modifier">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                </button>
                                <form action="/admin/roles/{{ $r->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer &quot;{{ $r->name }}&quot; ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg text-destructive hover:bg-destructive/10 transition" title="Supprimer">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

<script>
window.rolePermissions = @json($permissions->map(fn($group, $groupName) => $group->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values())->toArray());

function openEditRoleInline(btn, data) {
    const tr = btn.closest('tr');
    let panel = tr.nextElementSibling;
    if (panel && panel.classList.contains('edit-panel')) {
        panel.remove();
        return;
    }
    document.querySelectorAll('.edit-panel').forEach(el => el.remove());
    document.querySelectorAll('.detail-panel').forEach(el => el.remove());

    const checkedIds = new Set(data.permission_ids.map(Number));

    let permsHtml = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
    for (const [group, perms] of Object.entries(window.rolePermissions)) {
        permsHtml += '<div class="bg-muted/50 rounded-lg p-4">';
        permsHtml += '<h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">' + group + '</h4>';
        permsHtml += '<div class="space-y-1.5">';
        perms.forEach(p => {
            const isChecked = checkedIds.has(p.id) ? 'checked' : '';
            permsHtml += '<label class="flex items-center gap-2 text-sm text-foreground">';
            permsHtml += '<input type="checkbox" name="permission_ids[]" value="' + p.id + '" ' + isChecked + '>';
            permsHtml += p.name + '</label>';
        });
        permsHtml += '</div></div>';
    }
    permsHtml += '</div>';

    const wrapper = document.createElement('tr');
    wrapper.className = 'edit-panel';
    wrapper.innerHTML = '<td colspan="4" class="px-4 py-4 border-t border-border bg-muted/20">'
        + '<form action="/admin/roles/' + data.id + '" method="POST" class="space-y-4">'
        + '<input type="hidden" name="_token" value="{{ csrf_token() }}">'
        + '<input type="hidden" name="_method" value="PATCH">'
        + '<div class="flex items-center gap-2 mb-2">'
        + '<span class="font-display text-sm font-bold">Modifier : ' + data.name + '</span>'
        + '<span class="text-xs text-muted-foreground font-mono">' + data.key + '</span>'
        + '</div>'
        + '<label class="block">'
        + '<span class="block text-sm font-semibold text-foreground mb-1.5">Nom du rôle</span>'
        + '<input type="text" name="name" value="' + data.name.replace(/"/g, '&quot;') + '" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">'
        + '</label>'
        + '<div><span class="block text-sm font-semibold text-foreground mb-2">Permissions</span>' + permsHtml + '</div>'
        + '<div class="flex gap-2">'
        + '<button type="submit" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground">Mettre à jour</button>'
        + '<button type="button" onclick="this.closest(\\'.edit-panel\\').remove()" class="rounded-lg border border-border px-5 py-2.5 text-sm font-medium text-foreground hover:bg-muted transition">Annuler</button>'
        + '</div>'
        + '</form>'
        + '</td>';
    tr.after(wrapper);
}

function showRoleDetail(btn, data) {
    const tr = btn.closest('tr');
    let panel = tr.nextElementSibling;
    if (panel && panel.classList.contains('detail-panel')) {
        panel.remove();
        return;
    }
    document.querySelectorAll('.detail-panel').forEach(el => el.remove());

    let permsHtml = '';
    if (data.permissions && Object.keys(data.permissions).length) {
        permsHtml += '<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 mt-2">';
        for (const [group, perms] of Object.entries(data.permissions)) {
            permsHtml += '<div class="rounded-lg border border-border bg-muted/30 p-3">';
            permsHtml += '<h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-1">' + group + '</h4>';
            permsHtml += '<ul class="space-y-1">';
            perms.forEach(p => {
                permsHtml += '<li class="text-sm text-muted-foreground flex items-center gap-1"><svg class="h-3 w-3 text-accent shrink-0" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' + p.name + '</li>';
            });
            permsHtml += '</ul></div>';
        }
        permsHtml += '</div>';
    } else {
        permsHtml = '<p class="text-sm text-muted-foreground mt-2">Aucune permission assignée.</p>';
    }

    const wrapper = document.createElement('tr');
    wrapper.className = 'detail-panel';
    wrapper.innerHTML = '<td colspan="4" class="px-4 py-4 border-t border-border bg-muted/20">'
        + '<div class="flex items-center gap-2 mb-2">'
        + '<span class="font-display text-sm font-bold">' + data.name + '</span>'
        + '<span class="text-xs text-muted-foreground font-mono">' + data.key + '</span>'
        + '</div>'
        + permsHtml
        + '</td>';
    tr.after(wrapper);
}

/* ---- Live filter ---- */
document.getElementById('role-search').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.role-row').forEach(row => {
        const text = row.dataset.text;
        row.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
});
</script>
@endsection
