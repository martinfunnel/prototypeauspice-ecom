@extends('components.layout')

@section('title', 'Finaliser ma commande — Santé Ivoire')

@section('content')
<section class="mx-auto max-w-5xl px-4 py-10">
    <h1 class="font-display text-3xl font-bold">Finaliser ma commande</h1>
    <p class="mt-1 text-sm text-success">💵 Paiement à la livraison disponible</p>

    @if(empty($cart['items']))
        <div class="mt-16 text-center">
            <p class="text-muted-foreground">Votre panier est vide.</p>
            <a href="/catalogue" class="mt-4 inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 transition">Voir le catalogue</a>
        </div>
    @else
        <div class="mt-8 grid gap-8 md:grid-cols-[1.5fr_1fr]">
            <form action="/commande" method="POST" class="space-y-4">
                @csrf
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Nom complet *</span>
                    <input type="text" name="customer_name" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                </label>
                <div class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Numéro de téléphone *</span>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <select id="country-code-select" name="country_code_id" required class="w-full shrink-0 rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none focus:border-accent sm:w-auto" onchange="updatePhoneFormat()">
                            <option value="">Pays</option>
                            @foreach(\App\Models\CountryCode::orderBy('sort_order')->get() as $cc)
                                <option value="{{ $cc->id }}" data-code="{{ $cc->code }}" data-digits="{{ $cc->digits }}" data-format="{{ $cc->format }}" data-pattern="{{ $cc->pattern }}" {{ $cc->iso === 'CIV' ? 'selected' : '' }}>@if($cc->flag_url)<img src="{{ $cc->flag_url }}" alt="" class="inline h-4 w-5 align-middle mr-1">@endif{{ $cc->name }} ({{ $cc->code }})</option>
                            @endforeach
                        </select>
                        <input type="tel" id="customer-phone" name="customer_phone" required placeholder="Choisir un pays d'abord" class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent" disabled>
                    </div>
                </div>
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Commune / lieu de livraison *</span>
                    <select name="commune_id" required class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                        <option value="">— Sélectionner —</option>
                        @foreach($communes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->zone }}) — {{ number_format($c->delivery_fee, 0, ',', ' ') }} FCFA</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Adresse précise *</span>
                    <textarea name="address" required rows="2" placeholder="Quartier, rue, point de repère..." class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent"></textarea>
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Notes (optionnel)</span>
                    <textarea name="notes" rows="2" placeholder="Instructions particulières..." class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent"></textarea>
                </label>
                <button type="submit" class="w-full rounded-xl bg-accent px-6 py-4 text-base font-bold text-accent-foreground shadow-accent transition hover:scale-[1.02]">
                    Confirmer la commande ({{ number_format($cart['total'], 0, ',', ' ') }} FCFA)
                </button>
            </form>

            <aside class="h-fit rounded-2xl border border-border bg-card p-5 shadow-card">
                <h3 class="font-display text-lg font-bold">Récapitulatif</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach($cart['items'] as $item)
                        <li class="flex justify-between">
                            <span class="text-foreground/80">{{ $item['quantity'] }}× {{ $item['product']->name }}</span>
                            <span class="font-semibold">{{ number_format($item['subtotal'], 0, ',', ' ') }} FCFA</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 space-y-1 border-t border-border pt-3 text-sm">
                    <div class="flex justify-between text-foreground/80"><span>Sous-total</span><span>{{ number_format($cart['total'], 0, ',', ' ') }} FCFA</span></div>
                    <div class="flex justify-between text-foreground/80"><span>Livraison</span><span>—</span></div>
                    <div class="flex justify-between text-base font-bold text-primary"><span>Total</span><span>{{ number_format($cart['total'], 0, ',', ' ') }} FCFA</span></div>
                </div>
            </aside>
        </div>
    @endif
</section>

<script>
const phoneInput = document.getElementById('customer-phone');
const countrySelect = document.getElementById('country-code-select');
let currentDigits = 0;
let currentFormat = '';
let currentPattern = null;

function updatePhoneFormat() {
    const opt = countrySelect.selectedOptions[0];
    if (!opt || !opt.value) {
        phoneInput.disabled = true;
        phoneInput.placeholder = 'Choisir un pays d\'abord';
        return;
    }
    phoneInput.disabled = false;
    phoneInput.value = '';
    currentDigits = parseInt(opt.dataset.digits, 10);
    currentFormat = opt.dataset.format;
    currentPattern = new RegExp(opt.dataset.pattern.replace(/^\//, '').replace(/\/$/, ''));
    phoneInput.placeholder = currentFormat;
    phoneInput.focus();
}

phoneInput.addEventListener('input', function(e) {
    if (!currentDigits) return;
    // Garde seulement les chiffres
    let raw = this.value.replace(/\D/g, '');
    // Limite au nombre de chiffres attendus
    if (raw.length > currentDigits) raw = raw.slice(0, currentDigits);

    // Formate selon le format du pays
    let formatted = '';
    let digitIdx = 0;
    for (let i = 0; i < currentFormat.length && digitIdx < raw.length; i++) {
        if (currentFormat[i] === 'X') {
            formatted += raw[digitIdx];
            digitIdx++;
        } else {
            // Si on est au début ou juste après un chiffre, ajoute le séparateur
            if (digitIdx > 0 || i === 0) {
                formatted += currentFormat[i];
            }
        }
    }
    this.value = formatted;
});

// Déclenche le format au chargement si un pays est pré-sélectionné
if (countrySelect.value) {
    updatePhoneFormat();
}

// Validation native avant soumission
document.querySelector('form[action="/commande"]').addEventListener('submit', function(e) {
    const opt = countrySelect.selectedOptions[0];
    if (!opt || !opt.value) {
        e.preventDefault();
        countrySelect.focus();
        return false;
    }
    const raw = phoneInput.value.replace(/\D/g, '');
    const pattern = new RegExp(opt.dataset.pattern.replace(/^\//, '').replace(/\/$/, ''));
    if (!pattern.test(raw)) {
        e.preventDefault();
        phoneInput.focus();
        return false;
    }
    // Stocke le code pays complet dans un champ hidden
    let hidden = document.querySelector('input[name="country_code"]');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'country_code';
        this.appendChild(hidden);
    }
    hidden.value = opt.dataset.code;
});
</script>
@endsection
