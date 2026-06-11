# Guide des Migrations — Auspice Market (Laravel)

## Qu'est-ce qu'une migration ?

C'est un fichier PHP qui dit à Laravel **comment créer les tables dans la base de données**. Tu écris la structure une fois, et Laravel peut la recréer n'importe où (ton PC, le serveur de prod, celui d'un collègue). C'est comme un plan d'architecte pour la BDD.

Avant (Supabase) c'était du SQL brut. Maintenant c'est du PHP orienté objet, plus lisible.

---

## Tables à créer

### 1. `categories` — Les rayons de la boutique

| Champ | Type | Description |
|-------|------|-------------|
| `id` | UUID | Identifiant unique |
| `name` | string | Nom du rayon (ex: "Compléments alimentaires") |
| `slug` | string | URL-friendly (ex: "complements") |
| `description` | text | Description courte |
| `image_url` | string | Image du rayon (optionnel) |
| `sort_order` | integer | Ordre d'affichage |
| `created_at` | timestamp | Date de création |

**Exemple :** "Soins du corps", "Énergie & Vitalité", "Beauté & Peau", "Minceur"

---

### 2. `products` — Les articles en vente

| Champ | Type | Description |
|-------|------|-------------|
| `id` | UUID | Identifiant unique |
| `name` | string | Nom du produit |
| `slug` | string | URL du produit (ex: "cacaocelyan") |
| `description` | text | Description longue |
| `short_description` | string | Description courte (max 300 caractères) |
| `price` | decimal | Prix normal (ex: 5000 FCFA) |
| `promo_price` | decimal | Prix promo (nullable) |
| `images` | json | Tableau d'URLs d'images |
| `detail_images` | json | Images pour la section détails |
| `benefits` | json | Liste des bénéfices (ex: ["100% bio", "Riche en antioxydants"]) |
| `stock` | integer | Quantité en stock |
| `category_id` | UUID (nullable) | Rayon associé |
| `is_active` | boolean | Visible ou pas ? |
| `is_popular` | boolean | Mis en avant sur l'accueil ? |
| `created_at` | timestamp | Date de création |
| `updated_at` | timestamp | Date de dernière modification |

**Exemple phare :** Cacao brut à la cannelle de Ceylan — 5000 FCFA

---

### 3. `communes` — Zones de livraison (Côte d'Ivoire)

| Champ | Type | Description |
|-------|------|-------------|
| `id` | UUID | Identifiant unique |
| `name` | string | Nom de la commune (ex: "Cocody") |
| `zone` | string | Zone regroupée (ex: "Abidjan", "Intérieur") |
| `delivery_fee` | decimal | Frais de livraison (ex: 1500 FCFA) |
| `delivery_days` | integer | Délai estimé en jours |
| `is_active` | boolean | Livrable ou pas ? |
| `created_at` | timestamp | Date de création |

**Exemples :** Cocody (1500 FCFA, 1 jour), Bouaké (3500 FCFA, 3 jours)

---

### 4. `orders` — Les commandes clients

| Champ | Type | Description |
|-------|------|-------------|
| `id` | UUID | Identifiant unique |
| `order_number` | string | Numéro public (ex: "CMD-260610-01000") |
| `customer_name` | string | Nom du client |
| `customer_phone` | string | Téléphone (pour le suivi) |
| `commune_id` | UUID | Zone de livraison |
| `commune_name` | string | Nom de la commune (stocké pour l'historique) |
| `address` | text | Adresse précise |
| `notes` | text | Notes du client |
| `subtotal` | decimal | Total des produits |
| `delivery_fee` | decimal | Frais de livraison |
| `total` | decimal | Total à payer |
| `status` | enum | `pending`, `confirmed`, `processing`, `shipped`, `delivered`, `cancelled` |
| `created_at` | timestamp | Date de commande |
| `updated_at` | timestamp | Dernière mise à jour |

---

### 5. `order_items` — Les articles dans une commande

| Champ | Type | Description |
|-------|------|-------------|
| `id` | UUID | Identifiant unique |
| `order_id` | UUID | Commande parente |
| `product_id` | UUID (nullable) | Produit référencé |
| `product_name` | string | Nom du produit (stocké pour l'historique) |
| `unit_price` | decimal | Prix unitaire au moment de la commande |
| `quantity` | integer | Quantité commandée |
| `subtotal` | decimal | Total ligne (prix × quantité) |

**Pourquoi stocker `product_name` et `unit_price` ?** Parce que si le produit change de nom ou de prix plus tard, la commande garde ses données d'origine. C'est la loi comptable.

---

### 6. `testimonials` — Avis clients

| Champ | Type | Description |
|-------|------|-------------|
| `id` | UUID | Identifiant unique |
| `author_name` | string | Nom de la personne |
| `role` | string | Petit titre (ex: "Cliente fidèle") |
| `content` | text | Texte de l'avis |
| `rating` | integer | Note 1-5 étoiles |
| `media_url` | string | Photo ou vidéo (optionnel) |
| `media_type` | enum | `image` ou `video` |
| `is_active` | boolean | Affiché ou pas ? |
| `sort_order` | integer | Ordre d'affichage |
| `created_at` | timestamp | Date de création |

---

### 7. `promo_banners` — Bannières promotionnelles

| Champ | Type | Description |
|-------|------|-------------|
| `id` | UUID | Identifiant unique |
| `key` | string | Clé unique (ex: "catalogue") |
| `title` | string | Titre de la bannière |
| `subtitle` | string | Sous-titre |
| `cta_label` | string | Texte du bouton (ex: "Découvrir") |
| `cta_url` | string | Lien du bouton |
| `image_url` | string | Image de fond |
| `is_active` | boolean | Visible ou pas ? |
| `created_at` | timestamp | Date de création |

**Jargon vulgaire :** C'est la pancarte "PROMO" que tu mets dans la vitrine. Tu peux changer le message sans toucher au code.

---

## Relations entre les tables

```
categories ||--o{ products : "a beaucoup de"
products ||--o{ order_items : "apparaît dans"
orders ||--|{ order_items : "contient"
communes ||--o{ orders : "livraison vers"
```

**Explication simple :**
- Une **catégorie** peut avoir plusieurs **produits**
- Un **produit** peut apparaître dans plusieurs **lignes de commande**
- Une **commande** contient plusieurs **lignes de commande**
- Une **commune** peut recevoir plusieurs **commandes**

---

## Séquence de création

1. `categories` (pas de dépendance)
2. `products` (dépend de `categories`)
3. `communes` (pas de dépendance)
4. `orders` (dépend de `communes`)
5. `order_items` (dépend de `orders` et `products`)
6. `testimonials` (pas de dépendance)
7. `promo_banners` (pas de dépendance)

---

## Index recommandés

| Table | Colonne | Pourquoi |
|-------|---------|----------|
| `products` | `slug` | Recherche rapide par URL |
| `products` | `category_id` | Filtrer par rayon |
| `products` | `is_active` | N'afficher que les produits visibles |
| `orders` | `customer_phone` | Rechercher les commandes d'un client |
| `orders` | `status` | Filtrer par statut (admin) |
| `order_items` | `order_id` | Charger les articles d'une commande |

**Jargon vulgaire :** Les index c'est comme l'index d'un livre. Sans index, Laravel doit feuilleter toute la BDD pour trouver une commande. Avec un index, il va directement à la bonne page.
