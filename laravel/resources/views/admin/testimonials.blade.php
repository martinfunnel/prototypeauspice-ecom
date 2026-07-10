@extends('components.admin-shell')

@section('title', 'Témoignages clients')

@section('content')
{{-- Stats cards --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Témoignages</p>
        <p class="mt-1 font-display text-2xl font-bold">{{ $testimonials->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Visibles</p>
        <p class="mt-1 font-display text-2xl font-bold text-success">{{ $testimonials->where('is_active', true)->count() }}</p>
    </div>
    <div class="rounded-2xl border border-border bg-card p-4 shadow-card">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Masqués</p>
        <p class="mt-1 font-display text-2xl font-bold text-muted-foreground">{{ $testimonials->where('is_active', false)->count() }}</p>
    </div>
</div>

{{-- Search + button --}}
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="relative w-full sm:max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" id="search-testimonials" oninput="filterTestimonials()" placeholder="Rechercher un témoignage…" class="w-full rounded-lg border border-border bg-card pl-9 pr-3 py-2 text-sm shadow-sm outline-none focus:border-accent">
    </div>
    @canDo('create_testimonials')
    <button type="button" onclick="openForm()" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Nouveau témoignage
    </button>
    @endcanDo
</div>

{{-- Inline form panel (moved by JS) --}}
<div id="form-panel-container" class="mt-4"></div>
<div id="testimonial-form-panel" class="rounded-2xl border border-border bg-card p-6 shadow-card" style="display:none;">
    <div class="mb-4 flex items-center justify-between">
        <h2 id="form-title" class="font-display text-lg font-bold">Nouveau témoignage</h2>
        <button type="button" onclick="closeForm()" class="rounded-md p-1 hover:bg-muted transition">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
    </div>
    <form id="testimonial-form" action="/admin/testimonials" method="POST" enctype="multipart/form-data" class="space-y-3">
        @csrf
        <input type="hidden" name="_method" id="method-override" value="">
        <input type="hidden" name="remove_media" id="remove-media-flag" value="">

        <div class="grid gap-3 sm:grid-cols-3">
            <label class="block">
                <span class="mb-1 block text-xs font-semibold">Note (1-5)</span>
                <input type="number" name="rating" id="f-rating" min="1" max="5" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold">Ordre</span>
                <input type="number" name="sort_order" id="f-sort_order" min="0" required class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-semibold">Type média</span>
                <select name="media_type" id="f-media_type" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
                    <option value="image">Image</option>
                    <option value="video">Vidéo</option>
                </select>
            </label>
        </div>

        <div class="block">
            <span class="mb-1 block text-xs font-semibold">Image ou vidéo</span>
            <input type="file" name="media" id="f-media" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime" onchange="previewMedia(this)" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent">
            <div id="media-preview-container" class="mt-3 relative hidden">
                <div id="media-preview"></div>
                <button type="button" onclick="removeMedia()" class="absolute right-2 top-2 rounded-full bg-background/90 p-1.5 shadow hover:bg-background transition">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
            <input type="hidden" name="existing_media_url" id="f-existing_media" value="">
        </div>

        <input type="hidden" name="author_name" id="f-author_name" value="—">
        <input type="hidden" name="role" id="f-role" value="">
        <input type="hidden" name="content" id="f-content" value="">

        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" id="f-is_active" value="1" checked class="rounded border-border">
            Visible sur le site
        </label>

        <div class="flex justify-end gap-2 border-t border-border pt-3">
            <button type="button" onclick="closeForm()" class="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted transition">Annuler</button>
            <button type="submit" id="save-btn" class="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition hover:opacity-90">Enregistrer</button>
        </div>
    </form>
</div>

@if($testimonials->isEmpty())
    <div class="rounded-2xl border border-dashed border-border p-12 text-center text-sm text-muted-foreground">
        Aucun témoignage. Ajoutez le premier pour qu'il apparaisse sur le site.
    </div>
@else
    <div class="grid gap-3">
        @foreach($testimonials as $t)
        <div class="testimonial-row flex items-start gap-4 rounded-xl border border-border bg-card p-4 shadow-card">
            @if($t->media_url)
                @if($t->media_type === 'video')
                    <video src="{{ $t->media_url }}" class="h-20 w-20 shrink-0 rounded-lg object-cover" muted></video>
                @else
                    <img src="{{ $t->media_url }}" alt="" class="h-20 w-20 shrink-0 rounded-lg object-cover">
                @endif
            @else
                <div class="grid h-20 w-20 shrink-0 place-items-center rounded-lg bg-muted text-xs text-muted-foreground">
                    Aucun média
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $t->is_active ? 'bg-success/10 text-success' : 'bg-muted text-muted-foreground' }}">
                        {{ $t->is_active ? 'Visible' : 'Masqué' }}
                    </span>
                    <span class="flex">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="h-3 w-3 {{ $i <= $t->rating ? 'fill-accent text-accent' : 'text-muted-foreground/30' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </span>
                </div>
            </div>

            <div class="flex gap-1">
                @canDo('edit_testimonials')
                <button type="button" onclick="fillForm(this, {{ json_encode([
                    'id' => $t->id,
                    'rating' => $t->rating,
                    'media_url' => $t->media_url ?? '',
                    'media_type' => $t->media_type,
                    'is_active' => $t->is_active,
                    'sort_order' => $t->sort_order,
                ]) }})" class="rounded-md border border-border p-2 hover:bg-muted transition">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                </button>
                @endcanDo
                @canDo('delete_testimonials')
                <form action="/admin/testimonials/{{ $t->id }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce témoignage ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="rounded-md border border-border p-2 text-destructive hover:bg-destructive/10 transition">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    </button>
                </form>
                @endcanDo
            </div>
        </div>
        @endforeach
    </div>
@endif

<script>
function getPanel() {
    return document.getElementById('testimonial-form-panel');
}
function getContainer() {
    return document.getElementById('form-panel-container');
}

function openForm() {
    closeForm();
    const panel = getPanel();
    getContainer().appendChild(panel);
    accordionOpen(panel, () => staggerChildren(panel));
    document.getElementById('form-title').textContent = 'Nouveau témoignage';
    document.getElementById('testimonial-form').action = '/admin/testimonials';
    document.getElementById('method-override').value = '';
    document.getElementById('f-rating').value = '5';
    document.getElementById('f-sort_order').value = '0';
    document.getElementById('f-media_type').value = 'image';
    document.getElementById('f-is_active').checked = true;
    document.getElementById('f-existing_media').value = '';
    document.getElementById('media-preview-container').classList.add('hidden');
    document.getElementById('media-preview').innerHTML = '';
    document.getElementById('remove-media-flag').value = '';
    panel.scrollIntoView({ behavior: 'smooth' });
}

function fillForm(btn, data) {
    const card = btn.closest('.testimonial-row');
    const panel = getPanel();
    // Si le panel est déjà juste après cette carte, on le ferme (toggle)
    if (panel.previousElementSibling === card) {
        closeForm();
        return;
    }
    closeForm();
    card.after(panel);
    accordionOpen(panel, () => staggerChildren(panel));

    document.getElementById('form-title').textContent = 'Modifier le témoignage';
    document.getElementById('testimonial-form').action = '/admin/testimonials/' + data.id;
    document.getElementById('method-override').value = 'PATCH';

    document.getElementById('f-rating').value = data.rating;
    document.getElementById('f-sort_order').value = data.sort_order;
    document.getElementById('f-media_type').value = data.media_type;
    document.getElementById('f-is_active').checked = data.is_active;
    document.getElementById('f-existing_media').value = data.media_url;

    const previewContainer = document.getElementById('media-preview-container');
    const preview = document.getElementById('media-preview');
    if (data.media_url) {
        previewContainer.classList.remove('hidden');
        if (data.media_type === 'video') {
            preview.innerHTML = '<video src="' + data.media_url + '" controls class="max-h-56 w-full rounded-lg border border-border"></video>';
        } else {
            preview.innerHTML = '<img src="' + data.media_url + '" alt="" class="max-h-56 w-full rounded-lg border border-border object-cover">';
        }
    } else {
        previewContainer.classList.add('hidden');
        preview.innerHTML = '';
    }
    document.getElementById('remove-media-flag').value = '';
}

function closeForm() {
    const panel = getPanel();
    // Si déjà caché, pas besoin d'animation — juste remettre dans le container
    if (panel.style.display === 'none') {
        getContainer().appendChild(panel);
        return;
    }
    accordionClose(panel, () => {
        getContainer().appendChild(panel);
    });
}

function previewMedia(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const url = e.target.result;
        const container = document.getElementById('media-preview-container');
        const preview = document.getElementById('media-preview');
        container.classList.remove('hidden');
        if (file.type.startsWith('video/')) {
            preview.innerHTML = '<video src="' + url + '" controls class="max-h-56 w-full rounded-lg border border-border"></video>';
        } else {
            preview.innerHTML = '<img src="' + url + '" alt="" class="max-h-56 w-full rounded-lg border border-border object-cover">';
        }
    };
    reader.readAsDataURL(file);
    document.getElementById('remove-media-flag').value = '';
}

function removeMedia() {
    document.getElementById('media-preview-container').classList.add('hidden');
    document.getElementById('media-preview').innerHTML = '';
    document.querySelector('input[name="media"]').value = '';
    document.getElementById('remove-media-flag').value = '1';
}

function filterTestimonials() {
    const q = document.getElementById('search-testimonials').value.toLowerCase();
    const items = document.querySelectorAll('.testimonial-row');
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(q) ? '' : 'none';
    });
}
</script>
@endsection
