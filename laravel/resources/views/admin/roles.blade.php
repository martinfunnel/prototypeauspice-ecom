@extends('components.admin-shell')

@section('title', 'Rôles & Permissions')

@section('content')


        @if(session('success'))
            <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-destructive/10 border border-destructive/20 text-destructive p-4 rounded-lg mb-6">{{ session('error') }}</div>
        @endif

        @if(isset($role))
            {{-- Edit form --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 mb-8">
                <h2 class="font-display text-lg font-bold text-foreground mb-4">Modifier le rôle : {{ $role->name }}</h2>
                <form action="/admin/roles/{{ $role->id }}" method="POST" class="space-y-4">
                    @csrf @method('PATCH')
                    <label class="block">
                        <span class="block text-sm font-semibold text-foreground mb-1.5">Nom du rôle</span>
                        <input type="text" name="name" value="{{ $role->name }}" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
                    </label>
                    <div>
                        <span class="block text-sm font-semibold text-foreground mb-2">Permissions</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($permissions as $group => $groupPermissions)
                                <div class="bg-muted/50 rounded-lg p-4">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2">{{ $group }}</h3>
                                    <div class="space-y-1.5">
                                        @foreach($groupPermissions as $perm)
                                            <label class="flex items-center gap-2 text-sm text-foreground">
                                                <input type="checkbox" name="permission_ids[]" value="{{ $perm->id }}" {{ $role->permissions->contains($perm->id) ? 'checked' : '' }}>
                                                {{ $perm->name }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground">Mettre à jour</button>
                        <a href="/admin/roles" class="rounded-lg border border-border px-5 py-2.5 text-sm font-medium text-foreground hover:bg-muted transition">Annuler</a>
                    </div>
                </form>
            </div>
        @else
            {{-- Create form --}}
            <div class="bg-card rounded-xl shadow-card border border-border p-6 mb-8">
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
                    <button type="submit" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground">Créer le rôle</button>
                </form>
            </div>
        @endif

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
                        <tr class="border-b border-border hover:bg-muted transition role-row">
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
                                <a href="/admin/roles/{{ $r->id }}/edit" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Modifier">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                </a>
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
</script>
@endsection
