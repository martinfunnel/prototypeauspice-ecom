# Page Catalogue — Documentation

## Vue d'ensemble

Page de liste de tous les produits avec recherche texte, filtres par catégorie, et bannière promotionnelle.

**Route :** `/catalogue`  
**Controller :** `CatalogController@index`  
**Vue :** `resources/views/catalog.blade.php`

---

## Logique métier (Backend)

### Requêtes Eloquent

```php
$query = Product::active()->with('category');

// Filtre par catégorie (via slug dans l'URL)
if ($request->filled('category')) {
    $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
}

// Recherche texte
if ($request->filled('search')) {
    $query->where(function ($sub) use ($q) {
        $sub->where('name', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%");
    });
}

$products = $query->orderBy('name')->paginate(12);
$categories = Category::orderBy('sort_order')->get();
$banner = PromoBanner::active()->where('key', 'catalogue')->first();
```

---

## Sections de la page

### 1. Bannière promotionnelle

**Condition :** Visible si `$banner` existe et est active  
**Clé recherchée :** `catalogue`

**Layout :** Carte pleine largeur avec fond dégradé bleu→vert ou image de fond

**Contenu exact :**
- Badge : "Offre spéciale" (vert semi-transparent)
- Titre : `$banner->title` (font-display, 2xl→4xl)
- Sous-titre : `$banner->subtitle` (opacity-95)
- CTA : `$banner->cta_label` + flèche (fond blanc, texte bleu)
- **Image de fond** : Si `$banner->image_url` existe, overlay sombre par-dessus

**Style sans image :**
```
bg-gradient-to-r from-primary via-primary to-accent/80
```

**Style avec image :**
```
background-image: linear-gradient(90deg, rgba(0,0,0,0.6), rgba(0,0,0,0.25), rgba(0,0,0,0.1)), url(image)
```

### 2. Titre et compteur

**Contenu exact :**
- Titre : "Catalogue" (font-display, 3xl→4xl, bold)
- Compteur : "{total} produit(s)" (muted-foreground)

### 3. Barre de filtres

**Layout :** Input recherche à gauche + boutons catégories à droite (flex-col mobile, flex-row desktop)

**Input recherche :**
- Placeholder : "Rechercher un produit..."
- Style : border-border, bg-card, shadow-sm, focus:border-accent
- Largeur max : `md:max-w-sm`

**Boutons catégories :**
- "Tous" : toujours présent, actif si aucune catégorie sélectionnée
- Un bouton par catégorie existante
- **État actif** : `border-accent bg-accent text-accent-foreground`
- **État inactif** : `border-border bg-card hover:border-accent`
- Forme : `rounded-full` (pill)
- Taille texte : `text-xs font-semibold`

**Comportement :** Les boutons sont des liens `<a>` qui rechargent la page avec le paramètre `?category={slug}`. La recherche texte est conservée si présente.

### 4. Grille de produits

**Layout :** Grille responsive
- Mobile : 2 colonnes (`grid-cols-2`)
- MD : 3 colonnes (`md:grid-cols-3`)
- LG : 4 colonnes (`lg:grid-cols-4`)
- Gap : `gap-4`

**Produit :** `ProductCard` (voir `components/product-card.blade.php`)

### 5. État vide

**Condition :** `$products->count() === 0`

**Style :**
- Bordure pointillée : `border-dashed border-border`
- Fond : `bg-muted/30`
- Texte : "Aucun produit trouvé." (muted-foreground)
- Padding : `p-12`

### 6. Pagination

**Condition :** `$products->hasPages()`

**Composant :** `{{ $products->links() }}` (pagination Laravel par défaut)

---

## Paramètres URL

| Paramètre | Type | Description |
|-----------|------|-------------|
| `category` | string (slug) | Filtre par catégorie |
| `search` | string | Recherche texte dans nom/description |
| `page` | integer | Numéro de page (géré par pagination Laravel) |

---

## Composants réutilisables

| Composant | Usage |
|-----------|-------|
| Layout | Structure de page |
| SiteHeader | Navigation |
| SiteFooter | Pied de page |
| Logo | Logo dans header/footer |
| ProductCard | Carte produit dans la grille |

---

## Prochaine page

→ [Page Produit détail](PAGE_PRODUCT.md)
