<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin — Auspice')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background">

<div class="container mx-auto grid gap-6 px-4 py-6 lg:grid-cols-[220px_1fr]">
    {{-- Sidebar — identique au React AdminShell --}}
    <aside class="lg:sticky lg:top-6 lg:self-start">
        <div class="rounded-2xl border border-border bg-card p-3 shadow-card">
            <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                Administration
            </div>
            <nav class="flex flex-col gap-1">
                @php $path = request()->path(); @endphp

                @canDo('view_dashboard')
                <a href="/admin" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ $path === 'admin' ? 'bg-primary text-primary-foreground' : 'text-foreground/80 hover:bg-muted' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    Dashboard
                </a>
                @endcanDo

                @canDo('view_orders')
                <a href="/admin/orders" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ str_starts_with($path, 'admin/orders') ? 'bg-primary text-primary-foreground' : 'text-foreground/80 hover:bg-muted' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    Commandes
                </a>
                @endcanDo

                @canDo('view_products')
                <a href="/admin/products" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ str_starts_with($path, 'admin/products') ? 'bg-primary text-primary-foreground' : 'text-foreground/80 hover:bg-muted' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    Produits
                </a>
                @endcanDo

                @canDo('view_categories')
                <a href="/admin/categories" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ str_starts_with($path, 'admin/categories') ? 'bg-primary text-primary-foreground' : 'text-foreground/80 hover:bg-muted' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V4h3"/><path d="M4 17v3h3"/><path d="M20 7V4h-3"/><path d="M20 17v3h-3"/><rect width="8" height="6" x="8" y="9" rx="1"/></svg>
                    Catégories
                </a>
                @endcanDo

                @canDo('view_banners')
                <a href="/admin/banners" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ str_starts_with($path, 'admin/banners') ? 'bg-primary text-primary-foreground' : 'text-foreground/80 hover:bg-muted' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11 6v5"/><path d="M11 14v4"/><circle cx="11" cy="19" r="1"/></svg>
                    Bannières
                </a>
                @endcanDo

                @canDo('view_testimonials')
                <a href="/admin/testimonials" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ str_starts_with($path, 'admin/testimonials') ? 'bg-primary text-primary-foreground' : 'text-foreground/80 hover:bg-muted' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="m8 9 2 2 4-4"/></svg>
                    Témoignages
                </a>
                @endcanDo

                @canDo('view_communes')
                <a href="/admin/communes" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ str_starts_with($path, 'admin/communes') ? 'bg-primary text-primary-foreground' : 'text-foreground/80 hover:bg-muted' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    Communes
                </a>
                @endcanDo

                @isSuperAdmin
                <a href="/admin/users" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ str_starts_with($path, 'admin/users') ? 'bg-primary text-primary-foreground' : 'text-foreground/80 hover:bg-muted' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Utilisateurs
                </a>
                <a href="/admin/roles" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ str_starts_with($path, 'admin/roles') ? 'bg-primary text-primary-foreground' : 'text-foreground/80 hover:bg-muted' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Rôles
                </a>
                @endisSuperAdmin
            </nav>

            <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="mt-3 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-foreground/70 hover:bg-muted transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    {{-- Main content --}}
    <section>
        <h1 class="mb-4 font-display text-2xl font-bold">@yield('title')</h1>
        @yield('content')
    </section>
</div>

</body>
</html>
