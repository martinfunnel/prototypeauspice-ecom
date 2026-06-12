<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Auspice Market')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

</body>
</html>
