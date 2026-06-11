# Page Suivi — Documentation

## Vue d'ensemble

Page de recherche et affichage des commandes par numéro de téléphone.

**Route :** `/suivi` (GET/POST)  
**Controller :** `TrackController@index` / `@search`  
**Vue :** `resources/views/track.blade.php`

---

## Logique métier (Backend)

```php
$orders = Order::where('customer_phone', $request->phone)
    ->with('items')
    ->orderByDesc('created_at')
    ->get();

session(['last_phone' => $request->phone]);
```

---

## Sections

### 1. Recherche
- Titre : "Suivre mes commandes"
- Input téléphone avec placeholder "+225 07 00 00 00 00"
- Valeur pré-remplie avec `session('last_phone')`
- Bouton "Rechercher" (bg-accent)

### 2. Résultats
- Cartes rounded-2xl, border, bg-card, shadow-card
- Par commande : N° (font-display, bold, primary), date, status badge, items, commune, total

### 3. Statuts visuels
| Statut | Couleur |
|--------|---------|
| pending | bg-yellow-100 text-yellow-800 |
| confirmed | bg-blue-100 text-blue-800 |
| processing | bg-purple-100 text-purple-800 |
| shipped | bg-indigo-100 text-indigo-800 |
| delivered | bg-green-100 text-green-800 |
| cancelled | bg-red-100 text-red-800 |

---

## Prochaine page

→ [Page Auth](PAGE_AUTH.md)
