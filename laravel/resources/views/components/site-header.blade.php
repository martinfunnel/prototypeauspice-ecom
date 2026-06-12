<header class="sticky top-0 z-40 w-full border-b border-border/60 bg-background/80 backdrop-blur supports-[backdrop-filter]:bg-background/60">
    <div class="max-w-7xl mx-auto flex h-16 items-center justify-between gap-4 px-4">
        {{-- Logo --}}
        <a href="/" class="flex items-center gap-2">
            @include('components.logo', ['size' => 'md'])
            <div class="flex flex-col leading-tight">
                <span class="font-display text-base font-bold text-foreground">Auspice Market</span>
                <span class="text-[10px] uppercase tracking-wider text-muted-foreground">Santé & bien-être bio</span>
            </div>
        </a>

        {{-- Navigation --}}
        <nav class="hidden items-center gap-1 md:flex">
            <a href="/" class="rounded-md px-4 py-2 text-sm font-medium text-foreground/80 transition hover:bg-muted hover:text-foreground">Accueil</a>
            <a href="/catalogue" class="rounded-md px-4 py-2 text-sm font-medium text-foreground/80 transition hover:bg-muted hover:text-foreground">Boutique</a>
            <a href="/suivi" class="rounded-md px-4 py-2 text-sm font-medium text-foreground/80 transition hover:bg-muted hover:text-foreground">Mes commandes</a>
        </nav>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            <a href="/catalogue" class="hidden h-10 w-10 place-items-center rounded-md text-foreground/70 transition hover:bg-muted hover:text-foreground sm:grid" aria-label="Rechercher">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </a>
            <a href="/panier" class="relative grid h-10 w-10 place-items-center rounded-md text-foreground/80 transition hover:bg-muted" aria-label="Panier">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span id="cart-badge" class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-accent px-1 text-[11px] font-bold text-accent-foreground transition {{ session('cart_count', 0) > 0 ? '' : 'hidden' }}">
                    {{ session('cart_count') }}
                </span>
            </a>
            @auth
                <a href="/admin" class="hidden h-10 items-center gap-1.5 rounded-md border border-border bg-card px-3 text-xs font-semibold text-foreground/80 transition hover:border-accent hover:text-accent sm:inline-flex" aria-label="Dashboard admin">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>
                <form action="/logout" method="POST" class="hidden sm:inline">
                    @csrf
                    <button type="submit" class="h-10 rounded-md px-3 text-xs font-semibold text-muted-foreground transition hover:bg-muted hover:text-foreground">Déconnexion</button>
                </form>
            @else
                <a href="/login" class="hidden h-10 items-center gap-1.5 rounded-md border border-border bg-card px-3 text-xs font-semibold text-foreground/80 transition hover:border-accent hover:text-accent sm:inline-flex" aria-label="Espace administrateur" title="Espace administrateur">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Admin
                </a>
            @endauth
        </div>
    </div>
</header>
