# Page Panier — Documentation

## Vue d'ensemble

Page du panier avec gestion des quantités, suppression d'articles, et passage à la commande.

**Route :** `/panier`  
**Controller :** `CartController@index`  
**Vue :** `resources/views/cart.blade.php`

---

## Logique métier (Backend)

```php
$cart = CartService::items();
```

---

## Sections

### 1. État vide
- Message : "Votre panier est vide."
- Bouton : "Voir le catalogue" (bg-primary)

### 2. Liste d'articles
- Tableau avec divide-y, border, bg-card
- Chaque ligne : image 64×64, nom, prix unitaire, quantité (+/-), poubelle
- Bouton - désactivé si quantité = 1

### 3. Sous-total
- Carte avec texte "Sous-total (livraison calculée à la commande)"
- Prix en font-display text-xl bold text-primary

### 4. CTA
- Bouton "Passer la commande →" (bg-accent, shadow-accent)

---

## Routes liées

| Méthode | Route | Action |
|---------|-------|--------|
| POST | `/panier/ajouter` | CartController@add |
| POST | `/panier/maj` | CartController@update |
| POST | `/panier/supprimer` | CartController@remove |

---

## Prochaine page

→ [Page Commande](PAGE_ORDER.md)
