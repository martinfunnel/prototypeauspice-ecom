@extends('components.admin-shell')

@section('title', 'Bannière promotionnelle (catalogue)')

@section('content')
@if(session('success'))
    <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
@endif

<div class="grid gap-6 lg:grid-cols-[1fr_360px]">
    {{-- Formulaire --}}
    <div class="space-y-4 rounded-2xl border border-border bg-card p-6 shadow-card">
        <form action="/admin/banners" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold">Titre</span>
                <input type="text" name="title" value="{{ old('title', $banner->title ?? '') }}" class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
            </label>

            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold">Sous-titre / offre</span>
                <textarea name="subtitle" rows="2" class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">{{ old('subtitle', $banner->subtitle ?? '') }}</textarea>
            </label>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Texte du bouton</span>
                    <input type="text" name="cta_label" value="{{ old('cta_label', $banner->cta_label ?? '') }}" placeholder="Découvrir l'offre" class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                </label>
                <label class="block">
                    <span class="mb-1.5 block text-sm font-semibold">Lien du bouton</span>
                    <input type="text" name="cta_url" value="{{ old('cta_url', $banner->cta_url ?? '') }}" placeholder="/produit/cacaocelyan" class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">
                </label>
            </div>

            <div class="block">
                <span class="mb-1.5 block text-sm font-semibold">Image (JPEG/PNG, format horizontal recommandé)</span>
                <input type="file" name="image" accept="image/jpeg,image/png" onchange="previewImage(this)" class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent">

                <div id="image-preview-container" class="mt-3 relative {{ ($banner->image_url ?? '') ? '' : 'hidden' }}">
                    <img id="image-preview" src="{{ $banner->image_url ?? '' }}" alt="Bannière" class="max-h-48 w-full rounded-lg border border-border object-cover">
                    <button type="button" onclick="removeImage()" class="absolute right-2 top-2 rounded-full bg-background/90 p-1.5 shadow hover:bg-background transition">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <input type="hidden" name="remove_image" id="remove-image-flag" value="">
            </div>

            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $banner->is_active ?? true) ? 'checked' : '' }} class="rounded border-border">
                Bannière active (visible sur le catalogue)
            </label>

            @canDo('edit_banners')
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:opacity-90">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Enregistrer
            </button>
            @endcanDo
        </form>
    </div>

    {{-- Aperçu --}}
    <div>
        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Aperçu</p>
        <div id="banner-preview" class="overflow-hidden rounded-2xl border border-border shadow-card">
            <div id="preview-bg" class="relative grid min-h-[180px] grid-cols-[1fr_auto] items-center gap-4 bg-gradient-to-r from-primary to-primary/80 p-5 text-primary-foreground"
                @if($banner->image_url ?? false)
                    style="background-image: linear-gradient(90deg, rgba(0,0,0,0.55), rgba(0,0,0,0.15)), url('{{ $banner->image_url }}'); background-size: cover; background-position: center;"
                @endif
            >
                <div>
                    <h3 id="preview-title" class="font-display text-xl font-bold">{{ $banner->title ?? 'Titre' }}</h3>
                    <p id="preview-subtitle" class="mt-1 text-sm opacity-90">{{ $banner->subtitle ?? 'Sous-titre' }}</p>
                    <span id="preview-cta" class="mt-3 inline-block rounded-full bg-accent px-4 py-1.5 text-xs font-bold text-accent-foreground {{ ($banner->cta_label ?? '') ? '' : 'hidden' }}">
                        {{ $banner->cta_label ?? '' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const url = e.target.result;
        const container = document.getElementById('image-preview-container');
        const img = document.getElementById('image-preview');
        img.src = url;
        container.classList.remove('hidden');
        updatePreviewBg(url);
    };
    reader.readAsDataURL(file);
    document.getElementById('remove-image-flag').value = '';
}

function removeImage() {
    document.getElementById('image-preview-container').classList.add('hidden');
    document.getElementById('image-preview').src = '';
    document.querySelector('input[name="image"]').value = '';
    document.getElementById('remove-image-flag').value = '1';
    updatePreviewBg(null);
}

function updatePreviewBg(url) {
    const bg = document.getElementById('preview-bg');
    if (url) {
        bg.style.backgroundImage = 'linear-gradient(90deg, rgba(0,0,0,0.55), rgba(0,0,0,0.15)), url(' + url + ')';
        bg.style.backgroundSize = 'cover';
        bg.style.backgroundPosition = 'center';
    } else {
        bg.style.backgroundImage = '';
    }
}

// Live update preview text
const titleInput = document.querySelector('input[name="title"]');
const subtitleInput = document.querySelector('textarea[name="subtitle"]');
const ctaInput = document.querySelector('input[name="cta_label"]');

function updatePreviewText() {
    document.getElementById('preview-title').textContent = titleInput.value || 'Titre';
    document.getElementById('preview-subtitle').textContent = subtitleInput.value || 'Sous-titre';
    const cta = document.getElementById('preview-cta');
    if (ctaInput.value) { cta.textContent = ctaInput.value; cta.classList.remove('hidden'); }
    else { cta.classList.add('hidden'); }
}

titleInput.addEventListener('input', updatePreviewText);
subtitleInput.addEventListener('input', updatePreviewText);
ctaInput.addEventListener('input', updatePreviewText);
</script>
@endsection
