@extends('components.admin-shell')

@section('title', 'Produits')

@section('content')
{{-- Stats cards --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Produits</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ $products->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Actifs</p>
        <p class="mt-1 font-display text-2xl font-bold text-success">{{ $products->where('is_active', true)->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Stock faible</p>
        <p class="mt-1 font-display text-2xl font-bold text-warning">{{ $products->where('stock', '<=', 3)->where('is_active', true)->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Mis en avant</p>
        <p class="mt-1 font-display text-2xl font-bold text-accent">{{ $products->where('is_popular', true)->count() }}</p>
    </div>
</div>

{{-- Search + button --}}
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="relative w-full sm:max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" id="search-products" oninput="filterProducts()" placeholder="Rechercher un produit…" class="w-full rounded-lg border border-border bg-card pl-9 pr-3 py-2 text-sm shadow-sm outline-none focus:border-accent">
    </div>
    <button type="button" onclick="document.getElementById('product-form').classList.toggle('hidden'); document.getElementById('form-title').textContent = 'Nouveau produit'; document.getElementById('product-form-tag').action = '/admin/products'; document.getElementById('method-override').value = '';" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Nouveau produit
    </button>
</div>

@if($products->isEmpty())
    <div class="rounded-xl border border-dashed border-border bg-card p-10 text-center text-sm text-muted-foreground">
        Aucun produit. Créez votre premier produit.
    </div>
@else
    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-card">
        {{-- Header desktop --}}
        <div class="hidden md:grid grid-cols-[1fr_110px_80px_80px_220px] gap-3 border-b border-border bg-muted/40 px-4 py-2 text-xs font-semibold uppercase text-muted-foreground">
            <div>Produit</div>
            <div>Prix</div>
            <div>Stock</div>
            <div>Statut</div>
            <div class="text-right">Actions</div>
        </div>
        <ul class="divide-y divide-border">
            @foreach($products as $p)
            <li class="product-row grid grid-cols-1 gap-3 px-4 py-3 md:grid-cols-[1fr_110px_80px_80px_220px] md:items-center">
                <div class="flex items-center gap-3 min-w-0">
                    @if(!empty($p->images[0]))
                        <img src="{{ $p->images[0] }}" alt="" class="h-12 w-12 flex-shrink-0 rounded-lg object-cover">
                    @else
                        <div class="h-12 w-12 flex-shrink-0 rounded-lg bg-muted"></div>
                    @endif
                    <div class="min-w-0">
                        <div class="truncate font-semibold">{{ $p->name }}</div>
                        <div class="truncate text-xs text-muted-foreground">/{{ $p->slug }}</div>
                    </div>
                </div>
                <div class="font-mono text-sm">
                    @if($p->promo_price && $p->promo_price < $p->price)
                        <span class="font-bold text-accent">{{ number_format($p->promo_price, 0, ',', ' ') }}</span>
                        <span class="ml-1 text-xs text-muted-foreground line-through">{{ number_format($p->price, 0, ',', ' ') }}</span>
                    @else
                        {{ number_format($p->price, 0, ',', ' ') }}
                    @endif
                </div>
                <div class="text-sm {{ $p->stock <= 3 ? 'text-warning-foreground font-semibold' : '' }}">
                    {{ $p->stock }}
                </div>
                <div>
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $p->is_active ? 'bg-success/15 text-success' : 'bg-muted text-muted-foreground' }}">
                        {{ $p->is_active ? 'Actif' : 'Masqué' }}
                    </span>
                    @if($p->is_popular)
                        <span class="ml-1 inline-flex rounded-full bg-accent/15 px-2 py-0.5 text-xs font-semibold text-accent">★</span>
                    @endif
                </div>
                <div class="flex items-center justify-end gap-1">
                    {{-- Voir --}}
                    <button type="button" onclick="showProductDetail(this, {{ json_encode([
                        'id' => $p->id,
                        'name' => $p->name,
                        'slug' => $p->slug,
                        'short_description' => $p->short_description ?? '',
                        'description' => $p->description ?? '',
                        'benefits' => $p->benefits ?? [],
                        'price' => $p->price,
                        'promo_price' => $p->promo_price,
                        'promo_ends_at' => $p->promo_ends_at?->format('d/m/Y H:i'),
                        'promo_ends_at_raw' => $p->promo_ends_at?->format('Y-m-d\TH:i') ?? '',
                        'stock' => $p->stock,
                        'category_name' => $p->category?->name ?? '—',
                        'category_id' => $p->category_id ?? '',
                        'images' => $p->images ?? [],
                        'detail_images' => $p->detail_images ?? [],
                        'is_active' => $p->is_active,
                        'is_popular' => $p->is_popular,
                    ]) }})" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Voir">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    @canDo('edit_products')
                    {{-- Edit --}}
                    <button type="button" onclick="fillForm(this, {{ json_encode([
                        'id' => $p->id,
                        'name' => $p->name,
                        'slug' => $p->slug,
                        'short_description' => $p->short_description ?? '',
                        'description' => $p->description ?? '',
                        'benefits' => implode("\n", $p->benefits ?? []),
                        'price' => $p->price,
                        'promo_price' => $p->promo_price ?? '',
                        'stock' => $p->stock,
                        'category_id' => $p->category_id ?? '',
                        'images' => implode("\n", $p->images ?? []),
                        'detail_images' => implode("\n", $p->detail_images ?? []),
                        'is_active' => $p->is_active,
                        'is_popular' => $p->is_popular,
                    ]) }})" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                    </button>
                    @endcanDo
                    @canDo('delete_products')
                    <form action="/admin/products/{{ $p->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer &quot;{{ $p->name }}&quot; ?')">
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
<div id="product-form" class="hidden mt-6 rounded-2xl border border-border bg-card shadow-card p-5">
    <h2 id="form-title" class="font-display text-lg font-bold mb-4">Nouveau produit</h2>
    <form id="product-form-tag" action="/admin/products" method="POST" class="space-y-3">
        @csrf
        <input type="hidden" name="_method" id="method-override" value="">

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
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Description courte</span>
            <input type="text" name="short_description" id="f-short_description" maxlength="300" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
        </label>

        <label class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Description complète</span>
            <textarea name="description" id="f-description" rows="4" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></textarea>
        </label>

        <label class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Avantages / bénéfices (un par ligne)</span>
            <textarea name="benefits" id="f-benefits" rows="4" placeholder="100% naturel&#10;Livraison rapide&#10;Garantie satisfait ou remboursé" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></textarea>
        </label>

        <div class="grid gap-3 sm:grid-cols-3">
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Prix (FCFA) *</span>
                <input type="number" name="price" id="f-price" required min="0" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Prix promo</span>
                <input type="number" name="promo_price" id="f-promo_price" min="0" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Stock *</span>
                <input type="number" name="stock" id="f-stock" required min="0" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
        </div>

        <label class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Catégorie</span>
            <select name="category_id" id="f-category_id" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
                <option value="">— Aucune —</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </label>

        {{-- Images du produit --}}
        <div class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Images du produit (JPEG/PNG)</span>
            <input type="file" name="product_images[]" id="f-product_images" accept="image/jpeg,image/png" multiple class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            <input type="hidden" name="existing_images" id="f-existing_images" value="">
            <div id="preview-product-images" class="mt-3 flex flex-wrap gap-2"></div>
        </div>

        {{-- Images détails --}}
        <div class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Images de la section « Détails » (JPEG/PNG)</span>
            <input type="file" name="detail_product_images[]" id="f-detail_product_images" accept="image/jpeg,image/png" multiple class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            <input type="hidden" name="existing_detail_images" id="f-existing_detail_images" value="">
            <div id="preview-detail-images" class="mt-3 flex flex-wrap gap-2"></div>
        </div>

        <div class="flex flex-wrap gap-4 pt-1">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" id="f-is_active" value="1" checked class="rounded border-border">
                Actif (visible)
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_popular" id="f-is_popular" value="1" class="rounded border-border">
                Mis en avant
            </label>
        </div>

        <div class="flex justify-end gap-2 border-t border-border pt-4 mt-4">
            <button type="button" onclick="document.getElementById('product-form').classList.add('hidden');" class="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted transition">Annuler</button>
            <button type="submit" class="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">Enregistrer</button>
        </div>
    </form>
</div>

<script>
function renderPreview(containerId, urls, inputId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    urls.forEach((url, idx) => {
        if (!url) return;
        const div = document.createElement('div');
        div.className = 'relative';
        div.innerHTML = `<img src="${url}" alt="" class="h-20 w-20 rounded-lg object-cover border border-border">
            <button type="button" onclick="removeImage('${containerId}', ${idx}, '${inputId}')" class="absolute -right-1.5 -top-1.5 grid h-5 w-5 place-items-center rounded-full bg-destructive text-white text-xs font-bold">×</button>`;
        container.appendChild(div);
    });
}

function removeImage(containerId, idx, inputId) {
    const input = document.getElementById(inputId);
    let urls = input.value.split('\n').filter(u => u.trim());
    urls.splice(idx, 1);
    input.value = urls.join('\n');
    renderPreview(containerId, urls, inputId);
}

function fillForm(data) {
    const form = document.getElementById('product-form');
    form.classList.remove('hidden');
    document.getElementById('form-title').textContent = 'Modifier le produit';
    document.getElementById('product-form-tag').action = '/admin/products/' + data.id;
    document.getElementById('method-override').value = 'PATCH';

    document.getElementById('f-name').value = data.name;
    document.getElementById('f-slug').value = data.slug;
    document.getElementById('f-short_description').value = data.short_description;
    document.getElementById('f-description').value = data.description;
    document.getElementById('f-benefits').value = data.benefits;
    document.getElementById('f-price').value = data.price;
    document.getElementById('f-promo_price').value = data.promo_price;
    document.getElementById('f-stock').value = data.stock;
    document.getElementById('f-category_id').value = data.category_id;

    // Images existantes (data.images peut être un tableau JS ou une chaîne séparée par \n)
    const existingImages = Array.isArray(data.images) ? data.images : (data.images ? data.images.split('\n').filter(u => u.trim()) : []);
    document.getElementById('f-existing_images').value = existingImages.join('\n');
    renderPreview('preview-product-images', existingImages, 'f-existing_images');

    const existingDetailImages = Array.isArray(data.detail_images) ? data.detail_images : (data.detail_images ? data.detail_images.split('\n').filter(u => u.trim()) : []);
    document.getElementById('f-existing_detail_images').value = existingDetailImages.join('\n');
    renderPreview('preview-detail-images', existingDetailImages, 'f-existing_detail_images');

    document.getElementById('f-is_active').checked = data.is_active;
    document.getElementById('f-is_popular').checked = data.is_popular;

    form.scrollIntoView({ behavior: 'smooth' });
}

function filterProducts() {
    const q = document.getElementById('search-products').value.toLowerCase();
    const items = document.querySelectorAll('.product-row');
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(q) ? '' : 'none';
    });
}

function showProductDetail(btn, data) {
    const li = btn.closest('li');
    let panel = li.nextElementSibling;
    if (panel && panel.classList.contains('detail-panel')) {
        panel.remove();
        return;
    }
    // close other panels
    document.querySelectorAll('.detail-panel, .edit-panel, .promo-panel').forEach(el => el.remove());

    const wrapper = document.createElement('div');
    wrapper.className = 'detail-panel col-span-full px-4 py-4 border-t border-border bg-muted/20';

    let html = '<div class="flex flex-wrap items-center gap-2 mb-4">';
    html += '<span class="font-display text-sm font-bold mr-auto">' + data.name + '</span>';
    html += '<form action="/admin/products/' + data.id + '/toggle" method="POST" class="inline">'
        + '<input type="hidden" name="_token" value="{{ csrf_token() }}">'
        + '<button type="submit" class="grid h-9 w-9 place-items-center rounded-lg ' + (data.is_active ? 'text-success hover:bg-success/10' : 'text-muted-foreground hover:bg-muted') + ' transition" title="' + (data.is_active ? 'Désactiver' : 'Activer') + '">' + (data.is_active
            ? '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.36 6.64A9 9 0 0 1 20.77 15"/><path d="M5.64 5.64A9 9 0 1 0 20.36 18.36"/><path d="m22 2-2 2"/></svg>'
            : '<svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" x2="12" y1="2" y2="12"/></svg>') + '</button></form>';
    html += '<form action="/admin/products/' + data.id + '/stock" method="POST" class="inline">'
        + '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="amount" value="-1">'
        + '<button type="submit" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Sortie -1"><svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg></button></form>';
    html += '<form action="/admin/products/' + data.id + '/stock" method="POST" class="inline">'
        + '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="amount" value="1">'
        + '<button type="submit" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Entrée +1"><svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg></button></form>';
    html += '<button type="button" onclick="openPromoInline(this, ' + JSON.stringify({id:data.id,name:data.name,promo_price:data.promo_price ?? '',promo_ends_at:data.promo_ends_at_raw ?? ''}).replace(/"/g,'&quot;') + ')" class="grid h-9 w-9 place-items-center rounded-lg ' + (data.promo_price ? 'text-accent hover:bg-accent/10' : 'text-foreground/70 hover:bg-muted') + ' transition" title="Promotion"><svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg></button>';
    html += '<button type="button" onclick="fillForm(this, ' + JSON.stringify({id:data.id,name:data.name,slug:data.slug,short_description:data.short_description ?? '',description:data.description ?? '',benefits:Array.isArray(data.benefits)?data.benefits.join("\\n"):(data.benefits??''),price:data.price,promo_price:data.promo_price ?? '',stock:data.stock,category_id:data.category_id ?? '',images:Array.isArray(data.images)?data.images.join("\\n"):(data.images??''),detail_images:Array.isArray(data.detail_images)?data.detail_images.join("\\n"):(data.detail_images??''),is_active:data.is_active,is_popular:data.is_popular}).replace(/"/g,'&quot;') + ')" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Modifier"><svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg></button>';
    html += '</div>';

    html += '<div class="grid gap-4 md:grid-cols-2">';
    html += '<div class="space-y-2 text-sm">';
    html += '<div class="flex gap-2"><span class="text-muted-foreground w-24">Slug :</span><span class="font-mono text-muted-foreground">/' + data.slug + '</span></div>';
    html += '<div class="flex gap-2"><span class="text-muted-foreground w-24">Catégorie :</span><span>' + data.category_name + '</span></div>';
    html += '<div class="flex gap-2"><span class="text-muted-foreground w-24">Prix :</span><span class="font-semibold">' + Number(data.price).toLocaleString('fr-FR') + ' FCFA</span></div>';
    html += '<div class="flex gap-2"><span class="text-muted-foreground w-24">Stock :</span><span>' + data.stock + '</span></div>';
    html += '<div class="flex gap-2"><span class="text-muted-foreground w-24">Statut :</span><span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ' + (data.is_active ? 'bg-success/15 text-success' : 'bg-muted text-muted-foreground') + '">' + (data.is_active ? 'Actif' : 'Masqué') + '</span>' + (data.is_popular ? ' <span class="ml-1 inline-flex rounded-full bg-accent/15 px-2 py-0.5 text-xs font-semibold text-accent">★ Populaire</span>' : '') + '</span></div>';
    if (data.promo_price && data.promo_price < data.price) {
        html += '<div class="flex gap-2"><span class="text-muted-foreground w-24">Promo :</span><span class="text-accent font-semibold">' + Number(data.promo_price).toLocaleString('fr-FR') + ' FCFA <span class="text-xs text-muted-foreground font-normal">(jusqu\'au ' + (data.promo_ends_at || '—') + ')</span></span></div>';
    }
    html += '</div>';
    html += '<div>';
    if (data.description || data.short_description) {
        html += '<h4 class="text-xs font-semibold uppercase text-muted-foreground mb-1">Description</h4>';
        html += '<p class="text-sm text-muted-foreground">' + (data.description || data.short_description) + '</p>';
    }
    if (data.benefits && data.benefits.length) {
        html += '<h4 class="text-xs font-semibold uppercase text-muted-foreground mt-2 mb-1">Avantages</h4>';
        html += '<ul class="space-y-1">';
        data.benefits.forEach(b => {
            html += '<li class="flex items-center gap-1 text-sm text-muted-foreground"><svg class="h-3.5 w-3.5 text-accent shrink-0" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' + b + '</li>';
        });
        html += '</ul>';
    }
    html += '</div></div>';

    if (data.images && data.images.length) {
        html += '<div class="mt-3"><h4 class="text-xs font-semibold uppercase text-muted-foreground mb-1">Images produit</h4><div class="flex flex-wrap gap-2">';
        data.images.forEach(url => { html += '<img src="' + url + '" class="h-16 w-16 rounded-lg object-cover border border-border">'; });
        html += '</div></div>';
    }
    if (data.detail_images && data.detail_images.length) {
        html += '<div class="mt-3"><h4 class="text-xs font-semibold uppercase text-muted-foreground mb-1">Images détails</h4><div class="flex flex-wrap gap-2">';
        data.detail_images.forEach(url => { html += '<img src="' + url + '" class="h-16 w-16 rounded-lg object-cover border border-border">'; });
        html += '</div></div>';
    }

    wrapper.innerHTML = html;
    li.after(wrapper);
}

function openPromoInline(btn, data) {
    const li = btn.closest('li');
    let panel = li.nextElementSibling;
    if (panel && panel.classList.contains('promo-panel')) {
        panel.remove();
        return;
    }
    document.querySelectorAll('.detail-panel, .edit-panel, .promo-panel').forEach(el => el.remove());

    const wrapper = document.createElement('div');
    wrapper.className = 'promo-panel col-span-full px-4 py-4 border-t border-border bg-muted/20';
    wrapper.innerHTML = '<form action="/admin/products/' + data.id + '/promo" method="POST" class="flex flex-wrap items-end gap-3">'
        + '<input type="hidden" name="_token" value="{{ csrf_token() }}">'
        + '<label class="block"><span class="text-xs font-semibold">Prix promo</span><input type="number" name="promo_price" value="' + (data.promo_price ?? '') + '" min="0" class="mt-1 w-40 rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></label>'
        + '<label class="block"><span class="text-xs font-semibold">Fin promo</span><input type="datetime-local" name="promo_ends_at" value="' + (data.promo_ends_at ?? '') + '" class="mt-1 w-48 rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></label>'
        + '<button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:opacity-90">Appliquer</button>'
        + '<button type="button" onclick="this.closest(\'.promo-panel\').remove()" class="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted">Annuler</button>'
        + '</form>';
    li.after(wrapper);
}

function fillForm(btn, data) {
    document.querySelectorAll('.detail-panel, .edit-panel, .promo-panel').forEach(el => el.remove());
    const li = btn.closest('li');

    // build inline edit panel
    const wrapper = document.createElement('div');
    wrapper.className = 'edit-panel col-span-full px-4 py-4 border-t border-border bg-muted/20';
    wrapper.innerHTML = '<h3 class="font-display text-sm font-bold mb-3">Modifier « ' + data.name + ' »</h3>'
        + '<form action="/admin/products/' + data.id + '" method="POST" enctype="multipart/form-data" class="space-y-3">'
        + '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="PATCH">'
        + '<div class="grid gap-3 sm:grid-cols-2">'
        + '<label class="block"><span class="mb-1 block text-xs font-semibold">Nom *</span><input type="text" name="name" required value="' + (data.name ?? '') + '" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></label>'
        + '<label class="block"><span class="mb-1 block text-xs font-semibold">Slug</span><input type="text" name="slug" value="' + (data.slug ?? '') + '" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></label>'
        + '</div>'
        + '<label class="block"><span class="mb-1 block text-xs font-semibold">Description courte</span><input type="text" name="short_description" maxlength="300" value="' + (data.short_description ?? '') + '" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></label>'
        + '<label class="block"><span class="mb-1 block text-xs font-semibold">Description complète</span><textarea name="description" rows="3" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">' + (data.description ?? '') + '</textarea></label>'
        + '<label class="block"><span class="mb-1 block text-xs font-semibold">Avantages (un par ligne)</span><textarea name="benefits" rows="3" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">' + (data.benefits ?? '') + '</textarea></label>'
        + '<div class="grid gap-3 sm:grid-cols-4">'
        + '<label class="block"><span class="mb-1 block text-xs font-semibold">Prix *</span><input type="number" name="price" required min="0" value="' + (data.price ?? '') + '" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></label>'
        + '<label class="block"><span class="mb-1 block text-xs font-semibold">Prix promo</span><input type="number" name="promo_price" min="0" value="' + (data.promo_price ?? '') + '" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></label>'
        + '<label class="block"><span class="mb-1 block text-xs font-semibold">Stock *</span><input type="number" name="stock" required min="0" value="' + (data.stock ?? '') + '" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></label>'
        + '<label class="block"><span class="mb-1 block text-xs font-semibold">Catégorie</span><select name="category_id" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">'
        + '<option value="">— Aucune —</option>'
        + '@foreach($categories as $c)'
        + '<option value="{{ $c->id }}" ' + (data.category_id == '{{ $c->id }}' ? 'selected' : '') + '>{{ $c->name }}</option>'
        + '@endforeach'
        + '</select></label>'
        + '</div>'
        + '<div class="flex flex-wrap gap-4 pt-1">'
        + '<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" ' + (data.is_active ? 'checked' : '') + ' class="rounded border-border"> Actif</label>'
        + '<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_popular" value="1" ' + (data.is_popular ? 'checked' : '') + ' class="rounded border-border"> Mis en avant</label>'
        + '</div>'
        + '<div class="flex justify-end gap-2 border-t border-border pt-3 mt-2">'
        + '<button type="button" onclick="this.closest(\'.edit-panel\').remove()" class="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted">Annuler</button>'
        + '<button type="submit" class="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground hover:opacity-90">Enregistrer</button>'
        + '</div></form>';
    li.after(wrapper);
}
</script>

{{-- Hidden template for new product form (kept at bottom) --}}
<div id="product-form" class="hidden mt-6 rounded-2xl border border-border bg-card shadow-card p-5">
    <h2 id="form-title" class="font-display text-lg font-bold mb-4">Nouveau produit</h2>
    <form id="product-form-tag" action="/admin/products" method="POST" enctype="multipart/form-data" class="space-y-3">
        @csrf
        <div class="grid gap-3 sm:grid-cols-2">
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Nom *</span>
                <input type="text" name="name" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Slug</span>
                <input type="text" name="slug" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
        </div>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Description courte</span>
            <input type="text" name="short_description" maxlength="300" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Description complète</span>
            <textarea name="description" rows="4" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></textarea>
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Avantages (un par ligne)</span>
            <textarea name="benefits" rows="4" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></textarea>
        </label>
        <div class="grid gap-3 sm:grid-cols-3">
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Prix (FCFA) *</span>
                <input type="number" name="price" required min="0" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Prix promo</span>
                <input type="number" name="promo_price" min="0" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Stock *</span>
                <input type="number" name="stock" required min="0" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
        </div>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Catégorie</span>
            <select name="category_id" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
                <option value="">— Aucune —</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </label>
        <div class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Images du produit (JPEG/PNG)</span>
            <input type="file" name="product_images[]" accept="image/jpeg,image/png" multiple class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
        </div>
        <div class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Images détails (JPEG/PNG)</span>
            <input type="file" name="detail_product_images[]" accept="image/jpeg,image/png" multiple class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
        </div>
        <div class="flex flex-wrap gap-4 pt-1">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-border"> Actif (visible)
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_popular" value="1" class="rounded border-border"> Mis en avant
            </label>
        </div>
        <div class="flex justify-end gap-2 border-t border-border pt-4 mt-4">
            <button type="button" onclick="document.getElementById('product-form').classList.add('hidden');" class="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted transition">Annuler</button>
            <button type="submit" class="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
