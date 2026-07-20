<footer class="mt-20 border-t border-border/60 bg-primary text-primary-foreground">
    <div class="max-w-7xl mx-auto grid gap-10 px-4 py-12 md:grid-cols-4">
        <div>
            <div class="flex items-center gap-2">
                @include('components.logo', ['size' => 'md', 'variant' => 'card'])
                <span class="font-display text-lg font-bold">Auspice Market</span>
            </div>
            <p class="mt-3 text-sm text-primary-foreground/70">
                <strong>Auspice SARL</strong> — Spécialiste des compléments alimentaires et produits de santé issus de l'agriculture biologique. Notre best-seller : le cacao à la cannelle de Ceylan.
            </p>
            <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-accent/20 px-3 py-1 text-xs font-semibold text-accent">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                100% bio & naturel
            </p>
        </div>
        <div>
            <h4 class="mb-3 text-sm font-semibold uppercase tracking-wider text-primary-foreground/60">Boutique</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="/catalogue" class="hover:text-accent">Tous les produits</a></li>
                <li><a href="/suivi" class="hover:text-accent">Suivre ma commande</a></li>
                <li><a href="/panier" class="hover:text-accent">Mon panier</a></li>
            </ul>
        </div>
        <div>
            <h4 class="mb-3 text-sm font-semibold uppercase tracking-wider text-primary-foreground/60">Garanties</h4>
            <ul class="space-y-3 text-sm text-primary-foreground/80">
                <li class="flex items-start gap-2">
                    <svg class="mt-0.5 h-4 w-4 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    Livraison rapide à Abidjan & intérieur
                </li>
                <li class="flex items-start gap-2">
                    <svg class="mt-0.5 h-4 w-4 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Produits authentiques
                </li>
                <li class="flex items-start gap-2">
                    <svg class="mt-0.5 h-4 w-4 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Paiement à la livraison
                </li>
            </ul>
        </div>
        <div>
            <h4 class="mb-3 text-sm font-semibold uppercase tracking-wider text-primary-foreground/60">Contact</h4>
            <ul class="space-y-2 text-sm text-primary-foreground/80">
                <li>📞 +225 07 77 03 69 77</li>
                <li>📍 Abidjan, Côte d'Ivoire</li>
                <li>
                    <a href="https://wa.me/2250777036977" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 rounded-md bg-accent px-3 py-1.5 text-xs font-semibold text-accent-foreground hover:opacity-90">
                        WhatsApp
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="border-t border-primary-foreground/10">
        <div class="max-w-7xl mx-auto px-4 py-4 text-center text-xs text-primary-foreground/60">
            <span>© {{ date('Y') }} Auspice SARL — Auspice Market. Tous droits réservés.</span>
        </div>
    </div>
</footer>
