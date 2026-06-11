<header class="bg-emerald-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            {{-- Logo --}}
            <a href="/" class="text-xl font-bold tracking-tight">
                Auspice Market
            </a>

            {{-- Navigation --}}
            <nav class="hidden md:flex space-x-8">
                <a href="/" class="hover:text-emerald-200 transition">Accueil</a>
                <a href="/catalogue" class="hover:text-emerald-200 transition">Catalogue</a>
                <a href="/suivi" class="hover:text-emerald-200 transition">Suivi</a>
            </nav>

            <div class="flex items-center gap-4">
                {{-- Admin link --}}
                @auth
                    @if(auth()->user()->hasAnyRole(['admin', 'super_admin']))
                        <a href="/admin" class="hidden md:block text-sm hover:text-emerald-200 transition">Admin</a>
                    @endif
                    <form action="/logout" method="POST" class="hidden md:block">
                        @csrf
                        <button type="submit" class="text-sm hover:text-emerald-200 transition">Déconnexion</button>
                    </form>
                @else
                    <a href="/login" class="hidden md:block text-sm hover:text-emerald-200 transition">Connexion</a>
                @endauth

                {{-- Panier --}}
                <a href="/panier" class="relative p-2 hover:text-emerald-200 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    @if(session('cart_count', 0) > 0)
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            {{ session('cart_count') }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</header>
