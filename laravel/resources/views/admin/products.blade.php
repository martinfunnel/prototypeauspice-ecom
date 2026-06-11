@extends('components.admin-shell')

@section('title', 'Produits')

@section('content')
@if(session('success'))
    <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
@endif

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
                    <button type="button" onclick="showProductDetail({{ json_encode([
                        'id' => $p->id,
                        'name' => $p->name,
                        'slug' => $p->slug,
                        'short_description' => $p->short_description ?? '',
                        'description' => $p->description ?? '',
                        'benefits' => $p->benefits ?? [],
                        'price' => $p->price,
                        'promo_price' => $p->promo_price,
                        'promo_ends_at' => $p->promo_ends_at?->format('d/m/Y H:i'),
                        'stock' => $p->stock,
                        'category_name' => $p->category?->name ?? '—',
                        'images' => $p->images ?? [],
                        'detail_images' => $p->detail_images ?? [],
                        'is_active' => $p->is_active,
                        'is_popular' => $p->is_popular,
                    ]) }})" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Voir">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    @canDo('edit_products')
                    {{-- Toggle Active --}}
                    <form action="/admin/products/{{ $p->id }}/toggle" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg {{ $p->is_active ? 'text-success hover:bg-success/10' : 'text-muted-foreground hover:bg-muted' }} transition" title="{{ $p->is_active ? 'Désactiver' : 'Activer' }}">
                            @if($p->is_active)
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.36 6.64A9 9 0 0 1 20.77 15"/><path d="M5.64 5.64A9 9 0 1 0 20.36 18.36"/><path d="m22 2-2 2"/></svg>
                            @else
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" x2="12" y1="2" y2="12"/></svg>
                            @endif
                        </button>
                    </form>
                    {{-- Stock +/- --}}
                    <form action="/admin/products/{{ $p->id }}/stock" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="amount" value="-1">
                        <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Sortie -1">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                        </button>
                    </form>
                    <form action="/admin/products/{{ $p->id }}/stock" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="amount" value="1">
                        <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted transition" title="Entrée +1">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        </button>
                    </form>
                    {{-- Promo --}}
                    <button type="button" onclick="openPromoModal({{ json_encode([
                        'id' => $p->id,
                        'name' => $p->name,
                        'promo_price' => $p->promo_price ?? '',
                        'promo_ends_at' => $p->promo_ends_at?->format('Y-m-d\TH:i') ?? '',
                    ]) }})" class="grid h-9 w-9 place-items-center rounded-lg {{ $p->promo_price ? 'text-accent hover:bg-accent/10' : 'text-foreground/70 hover:bg-muted' }} transition" title="Promotion">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                    </button>
                    {{-- Edit --}}
                    <button type="button" onclick="fillForm({{ json_encode([
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

    // Images existantes
    const existingImages = data.images ? data.images.split('\n').filter(u => u.trim()) : [];
    document.getElementById('f-existing_images').value = existingImages.join('\n');
    renderPreview('preview-product-images', existingImages, 'f-existing_images');

    const existingDetailImages = data.detail_images ? data.detail_images.split('\n').filter(u => u.trim()) : [];
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

function showProductDetail(data) {
    document.getElementById('detail-modal').classList.remove('hidden');
    document.getElementById('detail-title').textContent = data.name;
    document.getElementById('detail-slug').textContent = '/' + data.slug;
    document.getElementById('detail-desc').textContent = data.description || data.short_description || 'Aucune description';
    document.getElementById('detail-category').textContent = data.category_name;
    document.getElementById('detail-price').textContent = Number(data.price).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('detail-stock').textContent = data.stock;
    document.getElementById('detail-status').textContent = data.is_active ? 'Actif' : 'Masqué';
    document.getElementById('detail-status-badge').className = 'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ' + (data.is_active ? 'bg-success/15 text-success' : 'bg-muted text-muted-foreground');
    document.getElementById('detail-popular').style.display = data.is_popular ? '' : 'none';

    const promoEl = document.getElementById('detail-promo');
    if (data.promo_price && data.promo_price < data.price) {
        promoEl.classList.remove('hidden');
        document.getElementById('detail-promo-price').textContent = Number(data.promo_price).toLocaleString('fr-FR') + ' FCFA';
        document.getElementById('detail-promo-ends').textContent = data.promo_ends_at || '—';
    } else {
        promoEl.classList.add('hidden');
    }

    const benefitsEl = document.getElementById('detail-benefits');
    benefitsEl.innerHTML = '';
    if (data.benefits && data.benefits.length) {
        data.benefits.forEach(b => {
            const li = document.createElement('li');
            li.className = 'flex items-center gap-1 text-sm text-muted-foreground';
            li.innerHTML = '<svg class="h-3.5 w-3.5 text-accent" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' + b;
            benefitsEl.appendChild(li);
        });
        document.getElementById('detail-benefits-section').classList.remove('hidden');
    } else {
        document.getElementById('detail-benefits-section').classList.add('hidden');
    }

    // Images
    const gallery = document.getElementById('detail-images');
    gallery.innerHTML = '';
    if (data.images && data.images.length) {
        data.images.forEach(url => {
            const img = document.createElement('img');
            img.src = url;
            img.className = 'h-20 w-20 rounded-lg object-cover border border-border';
            gallery.appendChild(img);
        });
    }
    document.getElementById('detail-images-section').style.display = (data.images && data.images.length) ? '' : 'none';

    const detailGallery = document.getElementById('detail-detail-images');
    detailGallery.innerHTML = '';
    if (data.detail_images && data.detail_images.length) {
        data.detail_images.forEach(url => {
            const img = document.createElement('img');
            img.src = url;
            img.className = 'h-20 w-20 rounded-lg object-cover border border-border';
            detailGallery.appendChild(img);
        });
    }
    document.getElementById('detail-detail-images-section').style.display = (data.detail_images && data.detail_images.length) ? '' : 'none';
}

function closeDetailModal() {
    document.getElementById('detail-modal').classList.add('hidden');
}

function openPromoModal(data) {
    document.getElementById('promo-modal').classList.remove('hidden');
    document.getElementById('promo-form').action = '/admin/products/' + data.id + '/promo';
    document.getElementById('promo-product-name').textContent = data.name;
    document.getElementById('promo-price').value = data.promo_price ?? '';
    document.getElementById('promo-ends').value = data.promo_ends_at ?? '';
}

function closePromoModal() {
    document.getElementById('promo-modal').classList.add('hidden');
}
</script>

{{-- Modal Détail Produit --}}
<div id="detail-modal" class="hidden fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4 pt-10" onclick="if(event.target===this) closeDetailModal()">
    <div class="w-full max-w-3xl rounded-2xl border border-border bg-card p-6 shadow-elevated">
        <div class="mb-4 flex items-center justify-between">
            <h2 id="detail-title" class="font-display text-xl font-bold"></h2>
            <button type="button" onclick="closeDetailModal()" class="rounded-md p-1 hover:bg-muted transition">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="space-y-5">
            <div class="flex items-start gap-4 rounded-xl border border-border bg-muted/30 p-4">
                <div class="flex-1 grid gap-2 text-sm">
                    <div class="flex gap-2"><span class="text-muted-foreground w-28">Slug :</span><span id="detail-slug" class="font-mono text-muted-foreground"></span></div>
                    <div class="flex gap-2"><span class="text-muted-foreground w-28">Catégorie :</span><span id="detail-category"></span></div>
                    <div class="flex gap-2"><span class="text-muted-foreground w-28">Prix :</span><span id="detail-price" class="font-semibold"></span></div>
                    <div class="flex gap-2"><span class="text-muted-foreground w-28">Stock :</span><span id="detail-stock"></span></div>
                    <div class="flex gap-2"><span class="text-muted-foreground w-28">Statut :</span><span id="detail-status-badge"><span id="detail-status"></span></span> <span id="detail-popular" class="ml-1 inline-flex rounded-full bg-accent/15 px-2 py-0.5 text-xs font-semibold text-accent">★ Populaire</span></div>
                    <div id="detail-promo" class="hidden flex gap-2"><span class="text-muted-foreground w-28">Promo :</span><span class="text-accent font-semibold"><span id="detail-promo-price"></span> <span class="text-xs text-muted-foreground font-normal">(jusqu'au <span id="detail-promo-ends"></span>)</span></span></div>
                </div>
            </div>
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-2">Description</h3>
                <p id="detail-desc" class="text-sm text-muted-foreground leading-relaxed"></p>
            </div>
            <div id="detail-benefits-section" class="hidden">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-2">Avantages</h3>
                <ul id="detail-benefits" class="space-y-1"></ul>
            </div>
            <div id="detail-images-section">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-2">Images produit</h3>
                <div id="detail-images" class="flex flex-wrap gap-2"></div>
            </div>
            <div id="detail-detail-images-section">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-2">Images détails</h3>
                <div id="detail-detail-images" class="flex flex-wrap gap-2"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Promotion --}}
<div id="promo-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this) closePromoModal()">
    <form id="promo-form" action="" method="POST" onclick="event.stopPropagation()" class="w-full max-w-md rounded-2xl border border-border bg-card p-6 shadow-card">
        @csrf
        <div class="mb-4 flex items-center justify-between">
            <h2 class="font-display text-lg font-bold">Promotion</h2>
            <button type="button" onclick="closePromoModal()" class="rounded-md p-1 hover:bg-muted transition">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <p class="mb-4 text-xs text-muted-foreground">Produit : <strong id="promo-product-name"></strong></p>
        <div class="space-y-3">
            <label class="block">
                <span class="mb-1 block text-xs font-semibold">Prix promotionnel (FCFA)</span>
                <input type="number" name="promo_price" id="promo-price" min="0" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold">Fin de la promotion</span>
                <input type="datetime-local" name="promo_ends_at" id="promo-ends" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
        </div>
        <button type="submit" class="mt-5 w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground transition hover:opacity-90">
            Appliquer la promotion
        </button>
        <button type="button" onclick="document.getElementById('promo-price').value='';document.getElementById('promo-ends').value='';" class="mt-2 w-full rounded-lg border border-border bg-background px-4 py-2 text-sm transition hover:bg-muted">
            Retirer la promotion
        </button>
    </form>
</div>
@endsection
