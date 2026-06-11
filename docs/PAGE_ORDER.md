# Page Commande — Documentation

## Vue d'ensemble

Page de finalisation de commande depuis le panier.

**Route :** `/commande`  
**Controller :** `OrderController@create` / `@store`  
**Vue :** `resources/views/order.blade.php`

---

## Logique métier (Backend)

```php
$cart = CartService::items();
$communes = Commune::active()->orderBy('zone')->orderBy('name')->get();
```

---

## Sections

### 1. État vide
- Si panier vide : message + lien catalogue

### 2. Formulaire
- Nom complet *
- Numéro de téléphone *
- Commune / lieu de livraison * (select avec frais)
- Adresse précise *
- Notes (optionnel)
- Bouton : "Confirmer la commande (TOTAL FCFA)"

### 3. Récapitulatif
- Liste des articles (quantité × nom = sous-total)
- Sous-total, Livraison (—), Total (bold)

---

## Validation

| Champ | Règle |
|-------|-------|
| customer_name | required, string, max:255 |
| customer_phone | required, string, max:20 |
| commune_id | required, exists:communes |
| address | required, string, max:500 |
| notes | nullable, string, max:1000 |

---

## Prochaine page

→ [Page Suivi](PAGE_TRACK.md)
