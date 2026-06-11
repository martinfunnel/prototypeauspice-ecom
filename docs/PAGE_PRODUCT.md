# Page Produit détail — Documentation

## Vue d'ensemble

Page de détail d'un produit avec galerie d'images, sélection de quantité, commande directe, et produits similaires.

**Route :** `/produit/{slug}`  
**Controller :** `ProductController@show`  
**Vue :** `resources/views/product.blade.php`

---

## Logique métier (Backend)

```php
$product = Product::active()->where('slug', $slug)->firstOrFail();
$related = Product::active()->where('category_id', $product->category_id)->where('id', '!=', $product->id)->limit(4)->get();
$communes = Commune::active()->orderBy('zone')->orderBy('name')->get();
```

---

## Sections

### 1. Images et détails
- Image principale avec thumbnails cliquables (JS vanilla)
- Nom, prix (avec promo barrée si applicable)
- Badge "Paiement à la livraison disponible"
- Description courte + 4 bénéfices max
- Sélecteur de quantité (+/-) + bouton "Ajouter au panier"

### 2. Commande directe
- Formulaire : nom, téléphone, commune, adresse
- Récapitulatif en direct (JS) : sous-total + frais de livraison + total
- Bouton "Confirmer la commande"
- Route : `POST /commande-directe` → `OrderController@storeDirect`

### 3. Pourquoi choisir ce produit
- Grille des bénéfices avec icônes Check

### 4. Détails du produit
- Description longue (whitespace-pre-line)
- Images de détail

### 5. En images
- Galerie des images restantes

### 6. Réassurance
- 3 cartes : Livraison rapide, Paiement à la livraison, Produits vérifiés

### 7. Produits similaires
- Grille de 4 ProductCard

---

## Paramètres URL

| Paramètre | Type | Description |
|-----------|------|-------------|
| `slug` | string | Slug unique du produit |

---

## Composants réutilisables

| Composant | Usage |
|-----------|-------|
| ProductCard | Produits similaires |
| Logo | Header/footer |

---

## Prochaine page

→ [Page Panier](PAGE_CART.md)
