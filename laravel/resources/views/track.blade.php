@extends('components.layout')

@section('title', 'Suivre ma commande — Santé Ivoire')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="font-display text-3xl font-bold">Suivre ma commande</h1>
    <p class="mt-1 text-sm text-muted-foreground">Entrez le numéro de commande reçu (ex : CMD-260611-01006).</p>

    <form action="/suivi" method="POST" class="mt-6 flex gap-2">
        @csrf
        <input type="text" name="order_number" required value="{{ old('order_number') }}" placeholder="CMD-260611-01006" class="flex-1 rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent">
        <button type="submit" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground transition hover:scale-[1.02]">Rechercher</button>
    </form>

    @if(session('success'))
        @php $orderNum = preg_match('/CMD-[A-Z0-9-]+/', session('success'), $m) ? $m[0] : ''; @endphp
        <div class="mt-6 rounded-2xl border border-accent/30 bg-accent/5 p-5">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent text-accent-foreground">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div class="flex-1">
                    <p class="font-display text-base font-bold text-accent">Votre commande a bien été envoyée !</p>
                    <p class="mt-1 text-sm text-foreground/80">Voici votre numéro de commande. Il vous servira à suivre votre commande et à la récupérer à la livraison.</p>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <div class="rounded-lg border border-accent/40 bg-background px-4 py-2.5 font-mono text-lg font-bold tracking-wide text-accent" id="order-number-display">
                            {{ $orderNum }}
                        </div>
                        <button type="button" onclick="copyOrderNumber()" class="inline-flex items-center gap-1.5 rounded-lg border border-accent/40 bg-background px-3 py-2.5 text-sm font-medium text-accent transition hover:bg-accent/10" id="copy-btn">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                            <span id="copy-label">Copier</span>
                        </button>
                    </div>

                    <div class="mt-3 flex items-center gap-2 rounded-lg border border-warning/30 bg-warning/5 px-3 py-2 text-xs font-medium text-warning-foreground">
                        <svg class="h-4 w-4 shrink-0 text-warning" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                        Conservez bien ce numéro. Sans lui, vous ne pourrez pas suivre votre commande.
                    </div>
                </div>
            </div>
        </div>

        <script>
        function copyOrderNumber() {
            const text = document.getElementById('order-number-display').textContent.trim();
            navigator.clipboard.writeText(text).then(function() {
                const label = document.getElementById('copy-label');
                const btn = document.getElementById('copy-btn');
                const original = label.textContent;
                label.textContent = 'Copié !';
                btn.classList.add('bg-accent/20');
                setTimeout(function() {
                    label.textContent = original;
                    btn.classList.remove('bg-accent/20');
                }, 2000);
            });
        }
        </script>
    @endif

    @if(isset($orders))
        @if($orders->count())
            <ul class="mt-8 space-y-4">
                @foreach($orders as $order)
                    <li class="rounded-2xl border border-border bg-card p-5 shadow-card">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="font-display text-lg font-bold text-primary">{{ $order->order_number }}</p>
                                <p class="text-xs text-muted-foreground">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                @if($order->status == 'delivered') bg-green-100 text-green-800
                                @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                                @elseif($order->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->status == 'confirmed') bg-blue-100 text-blue-800
                                @elseif($order->status == 'processing') bg-purple-100 text-purple-800
                                @elseif($order->status == 'shipped') bg-indigo-100 text-indigo-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $order->statusLabel() }}
                            </span>
                        </div>
                        <ul class="mt-3 space-y-1 text-sm text-foreground/80">
                            @foreach($order->items as $item)
                                <li>• {{ $item->quantity }}× {{ $item->product_name }}</li>
                            @endforeach
                        </ul>
                        <div class="mt-3 flex items-center justify-between border-t border-border pt-3 text-sm">
                            <span class="text-muted-foreground">{{ $order->commune_name }} — Livraison {{ number_format($order->delivery_fee, 0, ',', ' ') }} FCFA</span>
                            <span class="font-bold text-primary">{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="mt-3 flex justify-end">
                            <a href="/suivi/{{ $order->id }}" class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-accent-foreground transition hover:scale-[1.02]">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                Voir les détails
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="mt-8 rounded-2xl border border-dashed border-border p-12 text-center text-sm text-muted-foreground">
                Aucune commande trouvée pour ce numéro de commande.
            </div>
        @endif
    @endif
</section>
@endsection
