@extends('components.admin-shell')

@section('title', 'Catégories')

@section('content')
@if(session('success'))
    <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
@endif

{{-- Stats cards --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Catégories</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ $categories->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Produits</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ $categories->sum(fn($c) => $c->products->count()) }}</p>
    </div>
</div>

{{-- Search + button --}}
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="relative w-full sm:max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" id="search-categories" oninput="filterCategories()" placeholder="Rechercher une catégorie…" class="w-full rounded-lg border border-border bg-card pl-9 pr-3 py-2 text-sm shadow-sm outline-none focus:border-accent">
    </div>
    <button type="button" onclick="toggleForm()" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Nouvelle catégorie
    </button>
</div>

{{-- Formulaire création / édition (AVANT la liste) --}}
<div id="category-form" class="hidden mb-6 rounded-2xl border border-border bg-card shadow-card p-5">
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
            <li class="category-row grid grid-cols-1 gap-3 px-4 py-3 md:grid-cols-[1fr_120px_80px_100px] md:items-center">
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
                    <button type="button" onclick="showDetail({{ json_encode([
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'description' => $cat->description ?? '',
                        'sort_order' => $cat->sort_order,
                        'image_url' => $cat->image_url ?? '',
                        'product_count' => $cat->products->count(),
                        'products' => $cat->products->map(fn($p) => [
                            'id' => $p->id,
                            'name' => $p->name,
                            'slug' => $p->slug,
                            'price' => $p->displayPrice(),
                            'stock' => $p->stock,
                            'is_active' => $p->is_active,
                            'image' => $p->images[0] ?? null,
                        ])->values(),
                    ]) }})" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Voir">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
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

{{-- Modal détail catégorie --}}
<div id="detail-modal" class="hidden fixed inset-0 z-50 flex items-start justify-center bg-black/50 p-4 pt-20" onclick="if(event.target===this) closeDetail()">
    <div class="w-full max-w-3xl rounded-2xl border border-border bg-card shadow-elevated p-6 max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 id="detail-title" class="font-display text-xl font-bold">Détail catégorie</h2>
            <button type="button" onclick="closeDetail()" class="grid h-8 w-8 place-items-center rounded-lg hover:bg-muted transition">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <div id="detail-content" class="space-y-5">
            {{-- Infos catégorie --}}
            <div class="flex items-start gap-4">
                <img id="detail-image" src="" alt="" class="h-20 w-20 flex-shrink-0 rounded-xl object-cover border border-border">
                <div>
                    <p class="font-semibold text-lg" id="detail-name"></p>
                    <p class="text-sm text-muted-foreground font-mono" id="detail-slug"></p>
                    <p class="text-sm text-muted-foreground mt-1" id="detail-desc"></p>
                    <div class="mt-2 flex gap-3 text-xs">
                        <span class="rounded-full bg-muted px-2.5 py-1">Ordre : <span id="detail-order"></span></span>
                        <span class="rounded-full bg-muted px-2.5 py-1">Produits : <span id="detail-count"></span></span>
                    </div>
                </div>
            </div>

            {{-- Produits --}}
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-3">Produits dans cette catégorie</h3>
                <div id="detail-products" class="overflow-hidden rounded-xl border border-border">
                    <div class="hidden md:grid grid-cols-[60px_1fr_100px_80px_80px] gap-3 border-b border-border bg-muted/40 px-3 py-2 text-xs font-semibold uppercase text-muted-foreground">
                        <div></div>
                        <div>Nom</div>
                        <div class="text-right">Prix</div>
                        <div class="text-right">Stock</div>
                        <div class="text-center">Statut</div>
                    </div>
                    <ul id="detail-products-list" class="divide-y divide-border"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showDetail(data) {
    document.getElementById('detail-modal').classList.remove('hidden');
    document.getElementById('detail-title').textContent = data.name;
    document.getElementById('detail-name').textContent = data.name;
    document.getElementById('detail-slug').textContent = '/' + data.slug;
    document.getElementById('detail-desc').textContent = data.description || 'Aucune description';
    document.getElementById('detail-order').textContent = data.sort_order;
    document.getElementById('detail-count').textContent = data.product_count;

    const img = document.getElementById('detail-image');
    if (data.image_url) { img.src = data.image_url; img.classList.remove('hidden'); }
    else { img.classList.add('hidden'); }

    const list = document.getElementById('detail-products-list');
    list.innerHTML = '';
    if (!data.products || data.products.length === 0) {
        list.innerHTML = '<li class="p-6 text-center text-sm text-muted-foreground">Aucun produit dans cette catégorie.</li>';
    } else {
        data.products.forEach(p => {
            const li = document.createElement('li');
            li.className = 'grid grid-cols-1 gap-2 px-3 py-2 md:grid-cols-[60px_1fr_100px_80px_80px] md:items-center';
            const statusBadge = p.is_active
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold uppercase text-green-800">Actif</span>'
                : '<span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold uppercase text-gray-800">Masqué</span>';
            li.innerHTML = `
                <div>${p.image ? `<img src="${p.image}" alt="" class="h-10 w-10 rounded-lg object-cover">` : '<div class="h-10 w-10 rounded-lg bg-muted"></div>'}</div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-medium">${p.name}</div>
                    <div class="truncate text-xs text-muted-foreground font-mono">/${p.slug}</div>
                </div>
                <div class="text-right text-sm font-semibold">${p.price.toLocaleString('fr-FR')} FCFA</div>
                <div class="text-right text-sm ${p.stock <= 3 ? 'text-destructive font-bold' : ''}">${p.stock}</div>
                <div class="flex justify-center">${statusBadge}</div>
            `;
            list.appendChild(li);
        });
    }
}

function closeDetail() {
    document.getElementById('detail-modal').classList.add('hidden');
}

function toggleForm() {
    const form = document.getElementById('category-form');
    const isHidden = form.classList.contains('hidden');
    if (isHidden) {
        form.classList.remove('hidden');
        document.getElementById('form-title').textContent = 'Nouvelle catégorie';
        document.getElementById('category-form-tag').action = '/admin/categories';
        document.getElementById('method-override').value = '';
        document.getElementById('f-image-preview').innerHTML = '';
        document.getElementById('f-existing_image').value = '';
        document.getElementById('f-name').value = '';
        document.getElementById('f-slug').value = '';
        document.getElementById('f-description').value = '';
        document.getElementById('f-sort_order').value = '';
    } else {
        form.classList.add('hidden');
    }
}

function filterCategories() {
    const q = document.getElementById('search-categories').value.toLowerCase();
    const items = document.querySelectorAll('.category-row');
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(q) ? '' : 'none';
    });
}

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
