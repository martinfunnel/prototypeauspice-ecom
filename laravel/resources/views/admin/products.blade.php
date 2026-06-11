@extends('components.admin-shell')

@section('title', 'Produits')

@section('content')
@if(session('success'))
    <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
@endif

{{-- Bouton Nouveau produit --}}
<div class="mb-4 flex justify-end">
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
        <div class="hidden md:grid grid-cols-[1fr_120px_100px_120px_100px] gap-3 border-b border-border bg-muted/40 px-4 py-2 text-xs font-semibold uppercase text-muted-foreground">
            <div>Produit</div>
            <div>Prix</div>
            <div>Stock</div>
            <div>Statut</div>
            <div class="text-right">Actions</div>
        </div>
        <ul class="divide-y divide-border">
            @foreach($products as $p)
            <li class="grid grid-cols-1 gap-3 px-4 py-3 md:grid-cols-[1fr_120px_100px_120px_100px] md:items-center">
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
                        <span class="font-bold text-accent">{{ number_format($p->promo_price, 0, ',', ' ') }} FCFA</span>
                        <span class="ml-1 text-xs text-muted-foreground line-through">{{ number_format($p->price, 0, ',', ' ') }} FCFA</span>
                    @else
                        {{ number_format($p->price, 0, ',', ' ') }} FCFA
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
                    @canDo('edit_products')
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

        <label class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Images du produit (URL, une par ligne)</span>
            <textarea name="images" id="f-images" rows="3" placeholder="https://…&#10;https://…" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></textarea>
        </label>

        <label class="block">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">Images de la section « Détails » (URL, une par ligne)</span>
            <textarea name="detail_images" id="f-detail_images" rows="3" placeholder="https://…&#10;https://…" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"></textarea>
        </label>

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
    document.getElementById('f-images').value = data.images;
    document.getElementById('f-detail_images').value = data.detail_images;
    document.getElementById('f-is_active').checked = data.is_active;
    document.getElementById('f-is_popular').checked = data.is_popular;

    form.scrollIntoView({ behavior: 'smooth' });
}
</script>
@endsection
