@extends('components.admin-shell')

@section('title', 'Catégories')

@section('content')
@if(session('success'))
    <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
@endif

{{-- Bouton Nouvelle catégorie --}}
<div class="mb-4 flex justify-end">
    <button type="button" onclick="document.getElementById('category-form').classList.toggle('hidden'); document.getElementById('form-title').textContent = 'Nouvelle catégorie'; document.getElementById('category-form-tag').action = '/admin/categories'; document.getElementById('method-override').value = ''; document.getElementById('f-image-preview').innerHTML = ''; document.getElementById('f-existing_image').value = '';" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Nouvelle catégorie
    </button>
</div>

@if($categories->isEmpty())
    <div class="rounded-xl border border-dashed border-border bg-card p-10 text-center text-sm text-muted-foreground">
        Aucune catégorie. Créez votre première catégorie.
    </div>
@else
    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-card">
        <div class="hidden md:grid grid-cols-[1fr_120px_80px_100px] gap-3 border-b border-border bg-muted/40 px-4 py-2 text-xs font-semibold uppercase text-muted-foreground">
            <div>Catégorie</div>
            <div>Slug</div>
            <div class="text-right">Ordre</div>
            <div class="text-right">Actions</div>
        </div>
        <ul class="divide-y divide-border">
            @foreach($categories as $cat)
            <li class="grid grid-cols-1 gap-3 px-4 py-3 md:grid-cols-[1fr_120px_80px_100px] md:items-center">
                <div class="flex items-center gap-3 min-w-0">
                    @if($cat->image_url)
                        <img src="{{ $cat->image_url }}" alt="" class="h-12 w-12 flex-shrink-0 rounded-lg object-cover">
                    @else
                        <div class="h-12 w-12 flex-shrink-0 rounded-lg bg-muted"></div>
                    @endif
                    <div class="min-w-0">
                        <div class="truncate font-semibold">{{ $cat->name }}</div>
                        @if($cat->description)
                            <div class="truncate text-xs text-muted-foreground">{{ Str::limit($cat->description, 60) }}</div>
                        @endif
                    </div>
                </div>
                <div class="font-mono text-sm text-muted-foreground">/{{ $cat->slug }}</div>
                <div class="text-right text-sm">{{ $cat->sort_order }}</div>
                <div class="flex items-center justify-end gap-1">
                    @canDo('edit_categories')
                    <button type="button" onclick="fillForm({{ json_encode([
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'description' => $cat->description ?? '',
                        'sort_order' => $cat->sort_order,
                        'image_url' => $cat->image_url ?? '',
                    ]) }})" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                    </button>
                    @endcanDo
                    @canDo('delete_categories')
                    <form action="/admin/categories/{{ $cat->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer &quot;{{ $cat->name }}&quot; ?')">
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

{{-- Formulaire création / édition --}}
<div id="category-form" class="hidden mt-6 rounded-2xl border border-border bg-card shadow-card p-5">
    <h2 id="form-title" class="font-display text-lg font-bold mb-4">Nouvelle catégorie</h2>
    <form id="category-form-tag" action="/admin/categories" method="POST" enctype="multipart/form-data" class="space-y-3">
        @csrf
        <input type="hidden" name="_method" id="method-override" value="">
        <input type="hidden" name="existing_image" id="f-existing_image" value="">

        <div class="grid gap-3 sm:grid-cols-2">
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Nom *</span>
                <input type="text" name="name" id="f-name" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Slug</span>
                <input type="text" name="slug" id="f-slug" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
        </div>

        <label class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Description</span>
            <textarea name="description" id="f-description" rows="3" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></textarea>
        </label>

        <div class="grid gap-3 sm:grid-cols-2">
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Ordre d'affichage *</span>
                <input type="number" name="sort_order" id="f-sort_order" required min="0" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <div class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Image (JPEG/PNG)</span>
                <input type="file" name="image" id="f-image" accept="image/jpeg,image/png" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
                <div id="f-image-preview" class="mt-3 flex flex-wrap gap-2"></div>
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-border pt-4 mt-4">
            <button type="button" onclick="document.getElementById('category-form').classList.add('hidden');" class="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted transition">Annuler</button>
            <button type="submit" class="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">Enregistrer</button>
        </div>
    </form>
</div>

<script>
function fillForm(data) {
    const form = document.getElementById('category-form');
    form.classList.remove('hidden');
    document.getElementById('form-title').textContent = 'Modifier la catégorie';
    document.getElementById('category-form-tag').action = '/admin/categories/' + data.id;
    document.getElementById('method-override').value = 'PATCH';

    document.getElementById('f-name').value = data.name;
    document.getElementById('f-slug').value = data.slug;
    document.getElementById('f-description').value = data.description;
    document.getElementById('f-sort_order').value = data.sort_order;
    document.getElementById('f-existing_image').value = data.image_url;

    const preview = document.getElementById('f-image-preview');
    preview.innerHTML = '';
    if (data.image_url) {
        preview.innerHTML = `<div class="relative"><img src="${data.image_url}" alt="" class="h-20 w-20 rounded-lg object-cover border border-border"><button type="button" onclick="removeImage()" class="absolute -right-1.5 -top-1.5 grid h-5 w-5 place-items-center rounded-full bg-destructive text-white text-xs font-bold">×</button></div>`;
    }

    form.scrollIntoView({ behavior: 'smooth' });
}

function removeImage() {
    document.getElementById('f-existing_image').value = '';
    document.getElementById('f-image-preview').innerHTML = '';
    // Ajouter un champ hidden pour signaler la suppression
    const form = document.getElementById('category-form-tag');
    let existing = form.querySelector('input[name="remove_image"]');
    if (!existing) {
        existing = document.createElement('input');
        existing.type = 'hidden';
        existing.name = 'remove_image';
        existing.value = '1';
        form.appendChild(existing);
    }
}
</script>
@endsection
