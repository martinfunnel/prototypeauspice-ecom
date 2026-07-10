<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Auspice Market')</title>
    <link rel="icon" type="image/png" href="/images/logo.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2e7d4a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Auspice">
    <meta name="description" content="Votre boutique en ligne qui vous rapproche du 100% naturel. Découvrez nos compléments alimentaires bio, produits de santé naturels et bien-être. Livraison partout en Côte d'Ivoire — Paiement à la livraison.">
    <meta property="og:title" content="@yield('title', 'Auspice Market')">
    <meta property="og:description" content="Votre boutique en ligne qui vous rapproche du 100% naturel. Compléments alimentaires bio, produits de santé et bien-être. Livraison partout en Côte d'Ivoire — Paiement à la livraison.">
    <meta property="og:image" content="{{ asset('/images/logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Auspice Market')">
    <meta name="twitter:description" content="Votre boutique en ligne qui vous rapproche du 100% naturel. Compléments alimentaires bio, produits de santé et bien-être. Livraison partout en Côte d'Ivoire — Paiement à la livraison.">
    <meta name="twitter:image" content="{{ asset('/images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @include('components.vite-fallback')
</head>
<body class="min-h-screen flex flex-col">

    {{-- Header --}}
    @if(! Illuminate\Support\Facades\View::hasSection('hide-nav'))
        @include('components.site-header')
    @endif

    {{-- Contenu principal --}}
    <main class="flex-grow">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('components.site-footer')

    {{-- Toast container --}}
    <div id="toast-container" class="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2"></div>

    {{-- PWA Install / Open Banner --}}
    <div id="pwa-install-banner" class="fixed bottom-0 left-0 right-0 z-[9998] translate-y-full transition-transform duration-300 ease-out">
        <div class="mx-auto mb-4 max-w-md px-4">
            <div class="flex items-center gap-3 rounded-2xl border border-accent/30 bg-accent/10 p-4 shadow-lg backdrop-blur-sm">
                <div class="shrink-0">
                    <img src="/images/logo.png" alt="Auspice" class="h-10 w-10 rounded-xl object-cover">
                </div>
                <div class="flex-1 min-w-0">
                    <p id="pwa-banner-title" class="text-sm font-bold text-foreground">Installer Auspice Market</p>
                    <p id="pwa-banner-desc" class="text-xs text-foreground/70">Accédez rapidement depuis votre écran d'accueil</p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <button id="pwa-install-btn" type="button" class="rounded-lg bg-accent px-3 py-1.5 text-xs font-bold text-accent-foreground transition hover:brightness-110">
                        Installer
                    </button>
                    <button id="pwa-open-btn" type="button" style="display:none" class="rounded-lg bg-accent px-3 py-1.5 text-xs font-bold text-accent-foreground transition hover:brightness-110">
                        Ouvrir
                    </button>
                    <button id="pwa-dismiss-btn" type="button" class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-foreground/50 hover:bg-muted transition">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        const isSuccess = type === 'success';
        toast.className = 'toast-enter flex items-center gap-2 rounded-lg px-4 py-3 text-sm font-medium shadow-lg ' +
            (isSuccess ? 'bg-success text-success-foreground' : 'bg-destructive text-destructive-foreground');
        toast.innerHTML =
            (isSuccess
                ? '<svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>'
                : '<svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>'
            )
            + '<span>' + message + '</span>';
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('toast-enter');
            toast.classList.add('toast-exit');
            setTimeout(() => toast.remove(), 250);
        }, 4000);
    }
    @if(session('success')) showToast({!! json_encode(session('success')) !!}, 'success'); @endif
    @if(session('error'))   showToast({!! json_encode(session('error')) !!}, 'error');   @endif
    </script>

    {{-- PWA Install / Open Prompt --}}
    <script>
    (function() {
        let deferredPrompt = null;
        let isInstalled = false;
        const banner = document.getElementById('pwa-install-banner');
        const installBtn = document.getElementById('pwa-install-btn');
        const openBtn = document.getElementById('pwa-open-btn');
        const dismissBtn = document.getElementById('pwa-dismiss-btn');
        const title = document.getElementById('pwa-banner-title');
        const desc = document.getElementById('pwa-banner-desc');
        const dismissedKey = 'pwa-install-dismissed';

        // Déjà dans l'app installée → rien
        if (window.matchMedia('(display-mode: standalone)').matches) return;
        if (localStorage.getItem(dismissedKey)) return;

        function showBanner(mode) {
            if (mode === 'open') {
                title.textContent = 'Auspice Market installée';
                desc.textContent = 'Ouvrez l\'application pour accéder rapidement';
                installBtn.style.display = 'none';
                openBtn.style.display = 'inline-block';
            } else {
                title.textContent = 'Installer Auspice Market';
                desc.textContent = 'Accédez rapidement depuis votre écran d\'accueil';
                installBtn.style.display = 'inline-block';
                openBtn.style.display = 'none';
            }
            banner.classList.remove('translate-y-full');
        }

        // Vérifier si l'app est déjà installée via getInstalledRelatedApps
        if ('getInstalledRelatedApps' in navigator) {
            navigator.getInstalledRelatedApps().then(function(apps) {
                if (apps.length > 0) {
                    isInstalled = true;
                    showBanner('open');
                }
            }).catch(function() {});
        }

        // Chrome déclenche beforeinstallprompt quand l'app est installable
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            if (isInstalled) return;
            deferredPrompt = e;
            showBanner('install');
        });

        window.addEventListener('appinstalled', function() {
            banner.classList.add('translate-y-full');
            deferredPrompt = null;
            isInstalled = true;
        });

        if (installBtn) {
            installBtn.addEventListener('click', function() {
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(choice) {
                    if (choice.outcome === 'accepted') {
                        banner.classList.add('translate-y-full');
                    }
                    deferredPrompt = null;
                });
            });
        }

        if (openBtn) {
            openBtn.addEventListener('click', function() {
                // Tente d'ouvrir la PWA via un nouvel onglet / protocole
                window.open('/', '_blank');
                banner.classList.add('translate-y-full');
            });
        }

        if (dismissBtn) {
            dismissBtn.addEventListener('click', function() {
                banner.classList.add('translate-y-full');
                localStorage.setItem(dismissedKey, Date.now());
            });
        }
    })();
    </script>

    {{-- Service Worker PWA --}}
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('[PWA] Service Worker enregistré', registration.scope);
                })
                .catch(function(err) {
                    console.log('[PWA] Erreur enregistrement SW', err);
                });
        });
    }
    </script>

</body>
</html>
