# Pages Admin — Documentation

## Vue d'ensemble

Dashboard et CRUD admin protégés par `AdminMiddleware`.

**Middleware :** `App\Http\Middleware\AdminMiddleware`  
**Vérifie :** `auth()->check() && user->hasAnyRole(['admin', 'super_admin'])`

---

## Routes (préfixe `/admin`)

| Page | Route | Controller |
|------|-------|------------|
| Dashboard | `GET /admin` | AdminController@dashboard |
| Produits | `GET /admin/products` | AdminController@products |
| Catégories | `GET /admin/categories` | AdminController@categories |
| Communes | `GET /admin/communes` | AdminController@communes |
| Commandes | `GET /admin/orders` | AdminController@orders |
| Témoignages | `GET /admin/testimonials` | AdminController@testimonials |
| Bannières | `GET /admin/banners` | AdminController@banners |

---

## Dashboard

**Stats affichées :**
- Commandes totales
- En attente
- Livrées
- Chiffre d'affaires
- Nombre de produits
- Nombre de clients uniques

**Graphique :** Ventes des 7 derniers jours (table)

**Tableau :** 10 commandes récentes

---

## Design commun

- Fond : bg-background
- Cartes : bg-card, border-border, shadow-card
- Tableaux : thead bg-muted, hover:bg-muted
- Titres : font-display text-3xl bold
- Alertes : bg-success/10 border-success/20 text-success
- Boutons danger : text-destructive hover:opacity-70

---

## Prochaines étapes

- Formulaires de création/édition complets
- Upload d'images
- Export CSV
- Graphiques Chart.js
