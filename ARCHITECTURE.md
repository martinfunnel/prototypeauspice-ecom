# Architecture Technique — Auspice Market (Laravel)

## Vue d'ensemble

C'est un **e-commerce mono-produit** (une seule app Laravel) qui gère :
- La **vitrine** (ce que le client voit)
- Le **panier** (stocké en session)
- Les **commandes** (paiement à la livraison)
- L'**administration** (gestion produits, commandes, utilisateurs)

Avant c'était séparé : React faisait le front, Supabase faisait le back. Maintenant **Laravel fait tout**. C'est plus simple, une seule techno, un seul déploiement.

---

## Schéma d'architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        NAVIGATEUR                            │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │  Accueil    │  │  Catalogue  │  │    Admin (auth)     │  │
│  │   Blade     │  │  Livewire   │  │     Livewire        │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                             │
                    ┌────────┴────────┐
                    │    LARAVEL      │
                    │   (PHP 8.4)     │
                    │                 │
                    │  ┌───────────┐  │
                    │  │  Routes   │  │  ← web.php / auth.php
                    │  └───────────┘  │
                    │  ┌───────────┐  │
                    │  │Controllers│  │  ← Logique métier
                    │  └───────────┘  │
                    │  ┌───────────┐  │
                    │  │ Livewire  │  │  ← Composants réactifs
                    │  │Components │  │
                    │  └───────────┘  │
                    │  ┌───────────┐  │
                    │  │  Eloquent │  │  ← Models + Relations
                    │  │  Models   │  │
                    │  └───────────┘  │
                    │  ┌───────────┐  │
                    │  │   BDD     │  │  ← SQLite / MySQL / PG
                    │  │ (SQLite)  │  │
                    │  └───────────┘  │
                    └─────────────────┘
```

---

## Structure des dossiers

```
prototypeauspice-ecom/
├── src/                          ← Code React ORIGINAL (inchangé)
├── supabase/                     ← Migrations Supabase ORIGINALES
├── laravel/                      ← NOUVEAU PROJET LARAVEL
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/      ← Contrôleurs classiques
│   │   ├── Livewire/
│   │   │   ├── Catalog.php       ← Filtres en temps réel
│   │   │   ├── Cart.php          ← Panier interactif
│   │   │   └── Admin/
│   │   │       ├── Dashboard.php ← Stats + graphiques
│   │   │       ├── Products.php  ← CRUD produits
│   │   │       └── Orders.php    ← Gestion commandes
│   │   ├── Models/
│   │   │   ├── Category.php
│   │   │   ├── Product.php
│   │   │   ├── Commune.php
│   │   │   ├── Order.php
│   │   │   ├── OrderItem.php
│   │   │   ├── Testimonial.php
│   │   │   └── PromoBanner.php
│   │   └── Services/
│   │       └── CartService.php   ← Logique panier réutilisable
│   ├── database/
│   │   ├── migrations/           ← Toutes les tables
│   │   └── seeders/              ← Données de base
│   ├── resources/
│   │   ├── views/
│   │   │   ├── components/       ← Composants Blade réutilisables
│   │   │   │   ├── layout.blade.php
│   │   │   │   ├── site-header.blade.php
│   │   │   │   ├── site-footer.blade.php
│   │   │   │   └── product-card.blade.php
│   │   │   ├── home.blade.php    ← Page d'accueil
│   │   │   ├── catalog.blade.php ← Page catalogue
│   │   │   ├── product.blade.php ← Page produit
│   │   │   ├── cart.blade.php    ← Page panier
│   │   │   ├── order.blade.php   ← Page commande
│   │   │   ├── track.blade.php   ← Page suivi
│   │   │   └── admin/
│   │   │       └── ...           ← Toutes les pages admin
│   │   └── css/
│   │       └── app.css           ← Tailwind + variables custom
│   └── routes/
│       └── web.php               ← Toutes les routes
├── PROGRESS.md
├── ARCHITECTURE.md
└── MIGRATIONS.md
```

**Jargon vulgaire :** C'est l'armoire à pharmacie. Chaque tiroir a son étiquette. Les modèles c'est les fiches patients, les contrôleurs c'est le médecin qui prend les décisions, les vues c'est ce que le patient voit à l'accueil.

---

## Différences clés avec l'ancien projet

| Avant (React) | Maintenant (Laravel) | Pourquoi c'est mieux |
|---------------|----------------------|----------------------|
| 2 projets (front + back) | 1 seul projet | Moins de complexité, déploiement plus simple |
| TypeScript + JSX | PHP + Blade | Un seul langage, moins à apprendre |
| Supabase Auth | Laravel Auth + Spatie | Plus flexible, pas de dépendance externe |
| Supabase Storage | `storage/app/public` | Gratuit, pas de quota, contrôle total |
| TanStack Query | Eloquent + Livewire | Pas de gestion d'état complexe, le serveur fait le travail |
| `useState` + `useEffect` | Propriétés Livewire | Moins de bugs, moins de code |
| Recharts | Chart.js | Moins lourd, intégration simple avec Alpine.js |

---

## Flux de données

### 1. Chargement d'une page (ex: Accueil)

```
Navigateur → Route Laravel → Controller → Eloquent → BDD
                                              ↓
Navigateur ← Vue Blade ← Controller ← Données
```

Le serveur génère **tout le HTML** et l'envoie. Google peut tout indexer. C'est bon pour le SEO.

### 2. Interaction en temps réel (ex: filtre catalogue)

```
User tape "cacao" → Livewire envoie requête AJAX
                           ↓
                    Méthode PHP s'exécute
                           ↓
                    Requête Eloquent filtrée
                           ↓
                    HTML partiel renvoyé
                           ↓
                    DOM mis à jour automatiquement
```

L'utilisateur ne voit pas de rechargement de page. C'est fluide comme React, mais côté serveur.

### 3. Commande (ex: passer une commande)

```
Formulaire POST → OrderController@store
                    ↓
              Validation (règles Laravel)
                    ↓
              Transaction BDD (orders + order_items)
                    ↓
              Redirection vers /suivi avec message succès
```

Pas d'API REST compliquée. Un simple formulaire HTML classique.

---

## Sécurité

| Aspect | Implémentation |
|--------|---------------|
| Auth | Laravel Breeze (sessions PHP) |
| Rôles | Spatie Laravel Permission (`admin`, `super_admin`, `vendeur`, `comptable`) |
| CSRF | Généré automatiquement par Laravel sur tous les formulaires |
| Validation | `FormRequest` + règles inline (equivalent Zod) |
| Upload | Validation type, taille, stockage sécurisé |
| SQL Injection | Impossible avec Eloquent (requêtes préparées) |
| XSS | Blade échappe automatiquement le HTML (`{{ $var }}`) |

---

## Performance

| Optimisation | Outil |
|-------------|-------|
| Cache requêtes | Laravel Query Cache / Redis |
| Cache vues | Blade compilation (fichiers PHP en cache) |
| Cache assets | Laravel Mix / Vite (hashing fichiers) |
| Images | `loading="lazy"` + formats optimisés |
| BDD | Index sur `slug`, `category_id`, `status`, `customer_phone` |

---

## Stack complète

```
PHP 8.4
Laravel 12.x
Livewire 3.x
TailwindCSS 4.x
Alpine.js (inclus avec Livewire)
Chart.js (graphiques admin)
Lucide (icônes SVG)
SQLite (développement)
Spatie Laravel Permission (rôles)
Laravel Breeze (auth blade)
```
