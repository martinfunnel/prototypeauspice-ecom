@auth
    @if(request()->is('admin*'))
        <div class="admin-nav border-b border-border bg-card">
            <div class="max-w-7xl mx-auto px-4 py-2 flex flex-wrap gap-1">
                @canDo('view_dashboard')
                    <a href="/admin" class="rounded-md px-3 py-1.5 text-xs font-medium {{ request()->is('admin') ? 'bg-muted text-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }} transition">Tableau de bord</a>
                @endcanDo
                @canDo('view_products')
                    <a href="/admin/products" class="rounded-md px-3 py-1.5 text-xs font-medium {{ request()->is('admin/products*') ? 'bg-muted text-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }} transition">Produits</a>
                @endcanDo
                @canDo('view_categories')
                    <a href="/admin/categories" class="rounded-md px-3 py-1.5 text-xs font-medium {{ request()->is('admin/categories*') ? 'bg-muted text-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }} transition">Catégories</a>
                @endcanDo
                @canDo('view_communes')
                    <a href="/admin/communes" class="rounded-md px-3 py-1.5 text-xs font-medium {{ request()->is('admin/communes*') ? 'bg-muted text-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }} transition">Communes</a>
                @endcanDo
                @canDo('view_orders')
                    <a href="/admin/orders" class="rounded-md px-3 py-1.5 text-xs font-medium {{ request()->is('admin/orders*') ? 'bg-muted text-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }} transition">Commandes</a>
                @endcanDo
                @canDo('view_testimonials')
                    <a href="/admin/testimonials" class="rounded-md px-3 py-1.5 text-xs font-medium {{ request()->is('admin/testimonials*') ? 'bg-muted text-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }} transition">Témoignages</a>
                @endcanDo
                @canDo('view_banners')
                    <a href="/admin/banners" class="rounded-md px-3 py-1.5 text-xs font-medium {{ request()->is('admin/banners*') ? 'bg-muted text-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }} transition">Bannières</a>
                @endcanDo
                @isSuperAdmin
                    <a href="/admin/users" class="rounded-md px-3 py-1.5 text-xs font-medium {{ request()->is('admin/users*') ? 'bg-muted text-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }} transition">Utilisateurs</a>
                    <a href="/admin/roles" class="rounded-md px-3 py-1.5 text-xs font-medium {{ request()->is('admin/roles*') ? 'bg-muted text-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }} transition">Rôles</a>
                    <a href="/admin/logs" class="rounded-md px-3 py-1.5 text-xs font-medium {{ request()->is('admin/logs*') ? 'bg-muted text-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }} transition">Logs</a>
                @endisSuperAdmin
            </div>
        </div>
    @endif
@endauth
