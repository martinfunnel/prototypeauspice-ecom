# Page d'accueil — Documentation

## Vue d'ensemble

La page d'accueil est le point d'entrée du site. Elle présente la marque, le produit phare, les catégories, les produits populaires, les avis clients, et un appel à l'action final.

**Route :** `/`  
**Controller :** `HomeController@index`  
**Vue :** `resources/views/home.blade.php`

---

## Logique métier (Backend)

### Requêtes Eloquent

```php
$categories = Category::orderBy('sort_order')->get();
$products = Product::active()->orderBy('is_popular', 'desc')->limit(8)->get();
$featured = Product::active()->where('slug', 'cacaocelyan')->first();
$testimonials = Testimonial::active()->limit(6)->get();
```

| Variable | Source | Description |
|----------|--------|-------------|
| `$categories` | `categories` table | 5 rayons triés par ordre |
| `$products` | `products` table | 8 produits actifs, populaires d'abord |
| `$featured` | `products` table | Le produit avec slug = "cacaocelyan" |
| `$testimonials` | `testimonials` table | 6 témoignages actifs |

---

## Sections de la page

### 1. Hero (bannière principale)

**Position :** Haut de page, pleine largeur  
**Fond :** Dégradé bleu (`gradient-hero` : `linear-gradient(135deg, #3b6ea5, #2c5a8a)`)  
**Effets visuels :** 2 blobs floutés en arrière-plan (accent/teal)

**Contenu exact :**
- Badge : "Auspice SARL · Bio & livraison Côte d'Ivoire" avec icône Sparkles
- Titre : "Le bien-être bio, enraciné dans la nature." ("enraciné dans la nature" en vert accent)
- Paragraphe : "Auspice Market vous propose des compléments alimentaires et produits de santé 100% issus de l'agriculture biologique. Découvrez notre best-seller : le **cacao à la cannelle de Ceylan**."
- Bouton primaire : "Découvrir le cacao Ceylan" + flèche (vert, shadow-accent)
- Bouton secondaire : "Voir la boutique" (bordure blanche transparente)
- 3 icônes avec texte : Livraison 24-48h, Paiement à la livraison, Certifié bio
- **Image droite (desktop uniquement)** : Image du produit phare dans un cadre arrondi avec badge "Best-seller bio"

**Comportement responsive :**
- Mobile (`< md`) : 1 colonne, texte centré, image cachée
- Desktop (`≥ md`) : 2 colonnes (texte | image), texte aligné à gauche

### 2. Produit phare (Featured Spotlight)

**Position :** Sous le hero  
**Condition :** Visible uniquement si `$featured` existe

**Layout :** 2 colonnes (image | texte) dans une carte arrondie avec bordure et shadow-elevated

**Contenu exact :**
- Badge "Produit phare" avec étoile (vert accent)
- Image du cacao (max-h-[420px])
- Label : "Auspice Market · Best-seller bio"
- Titre : "Cacao brut à la cannelle de Ceylan" ("cannelle de Ceylan" en vert)
- Description : `short_description` du produit, ou fallback
- Liste bénéfices : 4 items max avec icônes Check vertes
- Prix : `displayPrice()` en gras + prix barré si promo
- Bouton : "Commander maintenant" (vert, shadow-accent)
- Texte : "💵 Paiement à la livraison disponible"

### 3. Catégories

**Position :** Sous le produit phare  
**Layout :** Titre + lien "Tout voir →" à droite, grille 2/3/5 colonnes

**Contenu exact :**
- Titre : "Catégories"
- Lien : "Tout voir →" (vert accent, souligné au hover)
- Cartes : Emoji 🌿 + nom + description (2 lignes max)
- Style : border, shadow-card, hover translateY + border accent + shadow-elevated

### 4. Produits populaires

**Position :** Sous les catégories  
**Layout :** Titre + sous-titre + lien, grille 2/3/4 colonnes

**Contenu exact :**
- Titre : "Nos produits bio populaires"
- Sous-titre : "Sélectionnés par Auspice SARL pour leur pureté et leur efficacité."
- Lien : "Tout voir →"
- Si aucun produit : message d'état vide avec bordure pointillée
- Sinon : `ProductCard` pour chaque produit

### 5. Témoignages

**Position :** Sous les produits  
**Condition :** Visible si `$testimonials->count() > 0`

**Layout :** Grille 3 colonnes

**Contenu exact :**
- Titre : "Ce que disent nos clients"
- Carte : 5 étoiles + citation + auteur + rôle
- Style : border, shadow-card, rounded-2xl

### 6. CTA (Call to Action)

**Position :** Bas de page, avant le footer  
**Fond :** Dégradé vert (`gradient-accent`)

**Contenu exact :**
- Titre : "Commandez en 2 minutes"
- Texte : "Pas de carte bancaire. Vous payez le livreur en espèces à la réception, partout en Côte d'Ivoire."
- Bouton : "Commander le cacao Ceylan" + flèche (fond bleu primary)

---

## Composants réutilisables utilisés

| Composant | Fichier | Usage |
|-----------|---------|-------|
| Layout | `components/layout.blade.php` | Structure HTML de base |
| SiteHeader | `components/site-header.blade.php` | Navigation sticky |
| SiteFooter | `components/site-footer.blade.php` | Pied de page |
| Logo | `components/logo.blade.php` | Logo Auspice (header + footer) |
| ProductCard | `components/product-card.blade.php` | Carte produit dans la grille |

---

## Polices et styles

| Élément | Police | Taille | Poids |
|---------|--------|--------|-------|
| Titres (h1-h5) | Space Grotesk | variable | 700 |
| Body | DM Sans | 16px | 400 |
| Badge hero | DM Sans | 10px | 600 |
| Boutons | DM Sans | 14px | 600-700 |

---

## Couleurs du thème

| Token | Hex | Usage |
|-------|-----|-------|
| primary | #3b6ea5 | Bleu (boutons, liens, header) |
| accent | #4caf7a | Vert (badges, boutons CTA, hover) |
| background | #f9fbfa | Fond page |
| card | #ffffff | Fond cartes |
| border | #e2eae7 | Bordures |
| muted-foreground | #6b8a82 | Texte secondaire |

---

## Media queries clés

```css
/* Mobile par défaut */
/* md (768px+) : 2 colonnes hero, navigation desktop visible, image produit */
/* lg (1024px+) : 4 colonnes produits, 5 colonnes catégories */
```

---

## Prochaine page

→ [Page Catalogue](PAGE_CATALOG.md)
