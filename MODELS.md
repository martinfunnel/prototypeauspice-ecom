# Guide des Modèles Eloquent — Auspice Market (Laravel)

## Qu'est-ce qu'un modèle Eloquent ?

C'est une **classe PHP** qui représente une table de la base de données. Au lieu d'écrire du SQL à la main, tu écris du PHP orienté objet. C'est plus propre, plus sûr, et ça marche avec n'importe quelle BDD (SQLite, MySQL, PostgreSQL).

**Avant (Supabase) :**
```typescript
const { data } = await supabase.from("products").select("*").eq("slug", slug);
```

**Maintenant (Laravel) :**
```php
$product = Product::where('slug', $slug)->first();
```

C'est la même idée mais en PHP. Et avec des relations magiques.

---

## 1. Category (Rayon)

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'image_url', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    // Une catégorie a plusieurs produits
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
```

**Usage :**
```php
$category = Category::where('slug', 'complements')->first();
$products = $category->products; // Tous les produits du rayon
```

---

## 2. Product (Produit)

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'short_description',
        'price', 'promo_price', 'images', 'detail_images', 'benefits',
        'stock', 'category_id', 'is_active', 'is_popular'
    ];

    protected $casts = [
        'price' => 'decimal:0',
        'promo_price' => 'decimal:0',
        'images' => 'array',
        'detail_images' => 'array',
        'benefits' => 'array',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
    ];

    // Scopes réutilisables (filtres pré-fabriqués)
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    // Relations
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Helper : prix affiché (promo ou normal)
    public function displayPrice(): float
    {
        return $this->promo_price ?? $this->price;
    }

    // Helper : y a-t-il une promo ?
    public function hasPromo(): bool
    {
        return $this->promo_price !== null && $this->promo_price < $this->price;
    }
}
```

**Usage :**
```php
// Produits populaires actifs
$featured = Product::active()->popular()->limit(8)->get();

// Prix affiché
$price = $product->displayPrice();

// Catégorie d'un produit
$catName = $product->category->name;
```

**Jargon vulgaire :** Le modèle Product c'est la fiche produit. Il sait tout : son prix, ses images, son stock, à quel rayon il appartient. Les `scope` c'est comme des filtres pré-réglés sur ton appareil photo.

---

## 3. Commune (Zone de livraison)

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commune extends Model
{
    protected $fillable = ['name', 'zone', 'delivery_fee', 'delivery_days', 'is_active'];

    protected $casts = [
        'delivery_fee' => 'decimal:0',
        'delivery_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

**Usage :**
```php
$communes = Commune::active()->orderBy('zone')->orderBy('name')->get();
```

---

## 4. Order (Commande)

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_name', 'customer_phone',
        'commune_id', 'commune_name', 'address', 'notes',
        'subtotal', 'delivery_fee', 'total', 'status'
    ];

    protected $casts = [
        'subtotal' => 'decimal:0',
        'delivery_fee' => 'decimal:0',
        'total' => 'decimal:0',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    // Scopes
    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeConfirmed($query) { return $query->where('status', 'confirmed'); }
    public function scopeShipped($query) { return $query->where('status', 'shipped'); }
    public function scopeDelivered($query) { return $query->where('status', 'delivered'); }
    public function scopeCancelled($query) { return $query->where('status', 'cancelled'); }

    // Helper : statut en français
    public function statusLabel(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'processing' => 'En préparation',
            'shipped' => 'Expédiée',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
            default => 'Inconnu',
        };
    }
}
```

**Usage :**
```php
// Commandes d'un client
$orders = Order::where('customer_phone', '+2250700000000')
    ->with('items') // Eager loading
    ->orderByDesc('created_at')
    ->get();

// Statistiques
$revenue = Order::where('status', '!=', 'cancelled')->sum('total');
$pendingCount = Order::pending()->count();
```

**Jargon vulgaire :** Le modèle Order c'est le dossier client. Il contient : qui a commandé, où ça va, combien ça coûte, et la liste des articles. `with('items')` c'est dire à Laravel "prends aussi les articles, j'en aurai besoin", ça évite de faire 2 voyages à la BDD.

---

## 5. OrderItem (Ligne de commande)

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name',
        'unit_price', 'quantity', 'subtotal'
    ];

    protected $casts = [
        'unit_price' => 'decimal:0',
        'quantity' => 'integer',
        'subtotal' => 'decimal:0',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
```

---

## 6. Testimonial (Avis client)

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'author_name', 'role', 'content', 'rating',
        'media_url', 'media_type', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'rating' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
```

---

## 7. PromoBanner (Bannière promo)

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoBanner extends Model
{
    protected $fillable = [
        'key', 'title', 'subtitle', 'cta_label', 'cta_url', 'image_url', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

---

## Conventions Eloquent (les règles du jeu)

| Règle | Explication |
|-------|-------------|
| `id` | Clé primaire auto-générée (UUID ou auto-increment) |
| `created_at` / `updated_at` | Gérés automatiquement par Laravel |
| `fillable` | Liste des champs qu'on peut remplir en masse (sécurité) |
| `casts` | Conversion automatique des types (ex: JSON → Array PHP) |
| `scopeXxx` | Filtres réutilisables (appelés avec `Model::xxx()`) |
| `xxx(): Relation` | Définit le lien entre 2 tables |

**Jargon vulgaire :** `fillable` c'est la liste des champs que ton modèle accepte de recevoir. Si un hacker essaie d'injecter un champ "is_admin=true", Laravel le refuse parce qu'il n'est pas dans `fillable`. C'est la porte de sécurité.

---

## Relations visuelles

```
┌─────────────┐         ┌─────────────┐
│  Category   │◄───────│   Product   │
│  (rayon)    │ 1    n  │  (article)  │
└─────────────┘         └─────────────┘
                               │
                               │ n
                               ▼
                        ┌─────────────┐
                        │  OrderItem  │
                        │   (ligne)   │
                        └─────────────┘
                               │ n
                               ▼
                        ┌─────────────┐
                        │    Order    │
                        │  (commande) │
                        └─────────────┘
                               │
                               ▼
                        ┌─────────────┐
                        │   Commune   │
                        │  (livraison)│
                        └─────────────┘
```
