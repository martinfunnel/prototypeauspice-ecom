# Progression — Migration Auspice Market (React → Laravel)

## Contexte

Migration complète de l'e-commerce **Auspice Market** depuis une stack **React 19 + TypeScript + TanStack Router + Supabase** vers **Laravel 12 + Blade + Livewire + TailwindCSS v4**.

La branche de travail est **`laravel-migration`**. Le code React original reste intact sur `main`.

---

## Légende

| Statut | Signification |
|--------|---------------|
| ✅ Terminé | Code écrit, testé, fonctionnel |
| 🔄 En cours | En train d'être implémenté |
| ⏳ En attente | Pas encore commencé, dépend d'une étape précédente |
| ⚠️ Partiel | Fonctionne mais incomplet (manque un sous-feature) |

---

## Phase 1 — Fondations (Squelette de l'app)

| # | Tâche | Statut | Notes |
|---|-------|--------|-------|
| 1.1 | Création du projet Laravel | 🔄 En cours | `composer create-project` en cours d'exécution |
| 1.2 | Configuration BDD (SQLite par défaut) | ⏳ En attente | Simple, modifiable plus tard pour PostgreSQL/MySQL |
| 1.3 | Migrations (toutes les tables) | ⏳ En attente | `categories`, `products`, `communes`, `orders`, `order_items`, `testimonials`, `promo_banners` |
| 1.4 | Modèles Eloquent + Relations | ⏳ En attente | `Product belongsTo Category`, `Order hasMany OrderItem`, etc. |
| 1.5 | Seeders (données de base) | ⏳ En attente | 5 catégories, 18 communes (Côte d'Ivoire) |
| 1.6 | Installation Livewire + Tailwind | ⏳ En attente | `composer require livewire/livewire` + `npm install tailwindcss` |
| 1.7 | Layout principal (header/footer) | ⏳ En attente | Adapter le design Tailwind du React |

**Jargon vulgaire :** C'est le ciment. Sans ça, rien ne tient debout. On construit d'abord la ossature avant de mettre les murs.

---

## Phase 2 — Frontend Public (Ce que le client voit)

| # | Tâche | Statut | Notes |
|---|-------|--------|-------|
| 2.1 | Page d'accueil (`/`) | ⏳ En attente | Hero + produits populaires + catégories + témoignages + CTA |
| 2.2 | Catalogue (`/catalogue`) | ⏳ En attente | Grille produits + filtres catégories + recherche texte (Livewire) |
| 2.3 | Page produit (`/produit/{slug}`) | ⏳ En attente | Galerie images + formulaire commande directe + produits similaires |
| 2.4 | Panier (`/panier`) | ⏳ En attente | Liste articles + quantité +/- + suppression + sous-total |
| 2.5 | Commande (`/commande`) | ⏳ En attente | Formulaire (nom, téléphone, commune, adresse) + récapitulatif |
| 2.6 | Suivi commandes (`/suivi`) | ⏳ En attente | Recherche par téléphone + historique + statuts |

**Jargon vulgaire :** C'est la vitrine. C'est ce que ton client voit quand il arrive sur le site. Faut que ça soit beau et que ça marche sans bug.

---

## Phase 3 — Administration (Back-office)

| # | Tâche | Statut | Notes |
|---|-------|--------|-------|
| 3.1 | Auth + rôles (Spatie Permission) | ⏳ En attente | `admin`, `super_admin`, `vendeur`, `comptable` |
| 3.2 | Login admin | ⏳ En attente | Page de connexion sécurisée |
| 3.3 | Dashboard (`/admin`) | ⏳ En attente | Stats (CA, commandes, en attente, livrées) + graphique 7 jours |
| 3.4 | CRUD Produits | ⏳ En attente | Liste, création, édition, suppression + upload images |
| 3.5 | CRUD Catégories | ⏳ En attente | Simple, peu de champs |
| 3.6 | CRUD Communes | ⏳ En attente | Nom, zone, frais livraison, délai |
| 3.7 | Gestion Commandes | ⏳ En attente | Liste complète + changement de statut |
| 3.8 | CRUD Témoignages | ⏳ En attente | Avec upload média (image/vidéo) |
| 3.9 | Gestion Utilisateurs | ⏳ En attente | Création comptes staff + attribution rôles |
| 3.10 | Promo Banners | ⏳ En attente | Bannière personnalisable sur le catalogue |

**Jargon vulgaire :** C'est le bureau de ton équipe. C'est là qu'ils ajoutent les produits, gèrent les commandes, voient combien d'argent ils ont fait.

---

## Phase 4 — Finitions & Déploiement

| # | Tâche | Statut | Notes |
|---|-------|--------|-------|
| 4.1 | Upload d'images local (remplace Supabase Storage) | ⏳ En attente | Stockage dans `storage/app/public/products` |
| 4.2 | Notifications WhatsApp (deep link) | ⏳ En attente | Reproduire la logique actuelle |
| 4.3 | Génération PDF (étiquettes commandes) | ⏳ En attente | Reproduire `pdf-label.ts` |
| 4.4 | SEO (meta titles, descriptions) | ⏳ En attente | Facile avec Blade (`@section('title')`) |
| 4.5 | Responsive mobile | ⏳ En attente | Tailwind gère ça naturellement |
| 4.6 | Tests | ⏳ En attente | Feature tests sur les commandes, auth, etc. |
| 4.7 | Déploiement | ⏳ En attente | Shared hosting ou VPS (Laravel Forge, etc.) |

**Jargon vulgaire :** Les petits détails qui font la différence. C'est le polish avant de montrer au patron.

---

## Stack Technique Finale

| Couche | Technologie |
|--------|-------------|
| Framework | Laravel 12 (PHP 8.4) |
| Frontend | Blade + Livewire 3 |
| Styling | TailwindCSS v4 |
| Icons | Lucide (même icônes qu'avant) |
| Auth | Laravel Breeze (Blade) + Spatie Permission |
| Charts | Chart.js (remplace Recharts) |
| BDD | SQLite (dev) → PostgreSQL/MySQL (prod) |
| Upload | Storage local → S3 (prod) |

---

## Dernière mise à jour

**Date :** 10 juin 2026, 18h53 UTC
**Branche :** `laravel-migration`
**Statut global :** Phase 1 en cours — création du projet Laravel
