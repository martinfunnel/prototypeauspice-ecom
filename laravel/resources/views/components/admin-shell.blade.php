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
                @php $pendingCount = \App\Models\Order::pending()->count(); @endphp
                <a href="/admin/orders" class="relative flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ str_starts_with($path, 'admin/orders') ? 'bg-primary text-primary-foreground' : 'text-foreground/80 hover:bg-muted' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    Commandes
                    @if($pendingCount > 0)
                    <span id="pending-count-badge" class="ml-auto inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-destructive px-1.5 text-[10px] font-bold text-white">{{ $pendingCount }}</span>
                    @else
                    <span id="pending-count-badge" class="ml-auto hidden inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-destructive px-1.5 text-[10px] font-bold text-white">0</span>
                    @endif
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
                <a href="/admin/logs" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ str_starts_with($path, 'admin/logs') ? 'bg-primary text-primary-foreground' : 'text-foreground/80 hover:bg-muted' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Logs
                </a>
                @endisSuperAdmin
            </nav>

            <div class="mt-2 border-t border-border pt-2">
                <a href="/admin/profil" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ str_starts_with($path, 'admin/profil') ? 'bg-primary text-primary-foreground' : 'text-foreground/80 hover:bg-muted' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Profil
                </a>
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="mt-1 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-foreground/70 hover:bg-muted transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <section class="relative">
        <h1 class="mb-4 font-display text-2xl font-bold">@yield('title')</h1>

        {{-- Page loader — uniquement sur le contenu --}}
        <div id="page-loader" class="page-loader hidden">
            <div style="position:relative">
                <div class="loader-pulse"></div>
                <div class="loader-ring"></div>
            </div>
            <div class="loader-text">Chargement</div>
            <div class="loader-bar"></div>
        </div>

        @yield('content')
    </section>
</div>

<script>
/* ---- Global C+D+E animations ---- */
window.accordionOpen = function(el, callback) {
    if (!el) return;
    el.style.display = '';
    el.style.visibility = 'visible';
    const targetH = el.scrollHeight;
    el.style.maxHeight = '0';
    el.style.opacity = '0';
    el.style.overflow = 'hidden';
    el.style.filter = 'blur(6px)';
    el.style.transition = 'max-height 0.8s cubic-bezier(0.34,1.56,0.64,1), opacity 0.6s ease 0.1s, filter 0.7s ease';
    requestAnimationFrame(() => {
        el.style.maxHeight = targetH + 'px';
        el.style.opacity = '1';
        el.style.filter = 'blur(0px)';
    });
    setTimeout(() => {
        el.style.maxHeight = '';
        el.style.overflow = '';
        if (callback) callback();
    }, 820);
};
window.accordionClose = function(el, callback) {
    if (!el) return;
    const h = el.scrollHeight;
    el.style.maxHeight = h + 'px';
    el.style.overflow = 'hidden';
    el.style.transition = 'max-height 0.6s ease-in, opacity 0.5s ease, filter 0.5s ease';
    requestAnimationFrame(() => {
        el.style.maxHeight = '0';
        el.style.opacity = '0';
        el.style.filter = 'blur(4px)';
    });
    setTimeout(() => {
        el.style.visibility = 'hidden';
        el.style.display = 'none';
        el.style.filter = '';
        if (callback) callback();
    }, 620);
};
window.staggerChildren = function(el) {
    if (!el) return;
    const kids = el.querySelectorAll('div, h3, h4, p, pre, code, label, button, li, ul, img, span, table, tr, td, th, input, textarea, select');
    kids.forEach((c, i) => {
        c.style.opacity = '0';
        c.style.transform = 'translateY(12px)';
        c.style.transition = 'opacity 0.5s ease ' + (i * 0.06) + 's, transform 0.5s ease ' + (i * 0.06) + 's';
    });
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            kids.forEach(c => {
                c.style.opacity = '1';
                c.style.transform = 'translateY(0)';
            });
        });
    });
};
window.closePanelAnim = function(el) {
    if (!el) return;
    window.accordionClose(el, () => el.remove());
};
</script>

{{-- Toast container --}}
<div id="toast-container" class="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2"></div>

<script>
// Show loader on admin navigation — loader couvre SEULEMENT la zone de contenu
(function() {
    const loader = document.getElementById('page-loader');
    function showLoader() { loader.classList.remove('hidden'); }
    function hideLoader() { loader.classList.add('hidden'); }

    // Intercept liens de navigation (sidebar + contenu) — loader s'affiche DANS le contenu
    document.querySelectorAll('aside a, section a, .admin-nav a').forEach(a => {
        a.addEventListener('click', () => showLoader());
    });

    // Intercept form submits (except AJAX/inline)
    document.querySelectorAll('form').forEach(f => {
        if (!f.closest('.detail-panel') && !f.closest('.edit-panel') && !f.closest('aside')) {
            f.addEventListener('submit', () => showLoader());
        }
    });

    // Hide when page fully loaded
    window.addEventListener('pageshow', hideLoader);
    if (document.readyState === 'complete') hideLoader();
    else window.addEventListener('load', hideLoader);
})();

/* ---- Toast notifications ---- */
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    let colorClass = 'bg-success text-success-foreground';
    let iconSvg = '<svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
    if (type === 'error') {
        colorClass = 'bg-destructive text-destructive-foreground';
        iconSvg = '<svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>';
    } else if (type === 'warning') {
        colorClass = 'bg-yellow-500 text-white';
        iconSvg = '<svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>';
    }
    toast.className = 'toast-enter flex items-center gap-2 rounded-lg px-4 py-3 text-sm font-medium shadow-lg ' + colorClass;
    toast.innerHTML = iconSvg + '<span>' + message + '</span>';
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.remove('toast-enter');
        toast.classList.add('toast-exit');
        setTimeout(() => toast.remove(), 250);
    }, 4000);
}

// Auto-show flash messages as toasts
(function() {
    @if(session('success')) showToast({!! json_encode(session('success')) !!}, 'success'); @endif
    @if(session('error'))   showToast({!! json_encode(session('error')) !!}, 'error');   @endif
    @if(session('warning')) showToast({!! json_encode(session('warning')) !!}, 'warning'); @endif
})();

/* ---- Temps réel : écoute nouvelles commandes ---- */
(function() {
    let lastCount = {{ $pendingCount ?? 0 }};
    const badge = document.getElementById('pending-count-badge');

    async function checkPendingOrders() {
        try {
            const res = await fetch('/admin/orders/pending-count', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            const newCount = data.count || 0;

            // Met à jour le badge dans la sidebar
            if (badge) {
                badge.textContent = newCount;
                if (newCount > 0) {
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }

            // Notification si nouvelle(s) commande(s)
            if (newCount > lastCount) {
                const diff = newCount - lastCount;
                const msg = diff === 1 ? 'Nouvelle commande en attente !' : diff + ' nouvelles commandes en attente !';
                showToast(msg, 'warning');
            }
            lastCount = newCount;
        } catch (e) {
            // Silencieux en cas d'erreur réseau
        }
    }

    // Vérifie toutes les 15 secondes
    setInterval(checkPendingOrders, 15000);
    // Vérifie aussi immédiatement au chargement (après 2s)
    setTimeout(checkPendingOrders, 2000);
})();
</script>

</body>
</html>
