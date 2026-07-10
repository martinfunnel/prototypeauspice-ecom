@props(['testimonials', 'heading' => 'Ils ont adopté Auspice Market', 'subheading' => 'Les clients du cacao à la cannelle de Ceylan partagent leur expérience.', 'label' => 'Témoignages'])

@if($testimonials->count())
<section class="mx-auto px-4 py-12 md:py-16 max-w-7xl">
    <div class="mb-6 flex flex-col items-start gap-1 md:mb-8 md:flex-row md:items-end md:justify-between">
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-accent">{{ $label }}</span>
            <h2 class="mt-1 font-display text-2xl font-bold md:text-3xl">{{ $heading }}</h2>
            <p class="mt-1 max-w-xl text-sm text-muted-foreground">{{ $subheading }}</p>
        </div>
    </div>

    <div class="testimonials-track flex gap-4 overflow-x-auto scroll-smooth pb-2"
         style="scrollbar-width:none; -ms-overflow-style:none;">
        @php $items = $testimonials->concat($testimonials); @endphp
        @foreach($items as $idx => $t)
            <article class="flex w-[300px] shrink-0 flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-card md:w-[360px]">
                @if($t->media_url)
                    <div class="flex aspect-[4/5] w-full items-center justify-center overflow-hidden bg-black">
                        @if($t->media_type === 'video')
                            <div class="video-player relative h-full w-full" data-src="{{ $t->media_url }}">
                                <video class="h-full w-full object-contain" muted loop playsinline preload="metadata"></video>
                                <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-2 bg-gradient-to-t from-black/70 to-transparent p-3">
                                    <button type="button" class="play-btn grid h-9 w-9 place-items-center rounded-full bg-white/90 text-foreground shadow hover:bg-white transition">
                                        <svg class="play-icon h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </button>
                                    <button type="button" class="mute-btn grid h-9 w-9 place-items-center rounded-full bg-white/90 text-foreground shadow hover:bg-white transition">
                                        <svg class="mute-icon h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                                    </button>
                                </div>
                            </div>
                        @else
                            <img src="{{ $t->media_url }}" alt="{{ $t->author_name }}" class="h-full w-full object-contain" loading="lazy">
                        @endif
                    </div>
                @endif

                @php
                    $hasMeta = ($t->author_name && $t->author_name !== '—') || ($t->content && trim($t->content)) || $t->role;
                @endphp
                @if($hasMeta)
                    <div class="flex flex-1 flex-col gap-3 p-5">
                        <svg class="h-5 w-5 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V21M15 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3"/></svg>
                        @if($t->content)
                            <p class="line-clamp-5 text-sm leading-relaxed text-foreground/90">"{{ $t->content }}"</p>
                        @endif
                        <div class="mt-auto flex items-center justify-between gap-2 pt-2">
                            <div>
                                @if($t->author_name && $t->author_name !== '—')
                                    <p class="text-sm font-semibold">{{ $t->author_name }}</p>
                                @endif
                                @if($t->role)
                                    <p class="text-xs text-muted-foreground">{{ $t->role }}</p>
                                @endif
                            </div>
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="h-3.5 w-3.5 {{ $i <= $t->rating ? 'text-accent fill-accent' : 'text-muted-foreground/30' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @endfor
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-end p-3">
                        <div class="flex">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="h-3.5 w-3.5 {{ $i <= $t->rating ? 'text-accent fill-accent' : 'text-muted-foreground/30' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            @endfor
                        </div>
                    </div>
                @endif
            </article>
        @endforeach
    </div>
</section>

<script>
(function() {
    const track = document.querySelector('.testimonials-track');
    if (!track) return;

    // Hide webkit scrollbar
    const style = document.createElement('style');
    style.textContent = '.testimonials-track::-webkit-scrollbar { display: none !important; }';
    document.head.appendChild(style);

    let raf = 0;
    let last = performance.now();
    const speed = 40;

    function tick(now) {
        const dt = (now - last) / 1000;
        last = now;
        if (!track.classList.contains('paused') && track.scrollWidth > track.clientWidth) {
            track.scrollLeft += speed * dt;
            if (track.scrollLeft >= track.scrollWidth / 2) {
                track.scrollLeft -= track.scrollWidth / 2;
            }
        }
        raf = requestAnimationFrame(tick);
    }
    raf = requestAnimationFrame(tick);

    track.addEventListener('mouseenter', () => track.classList.add('paused'));
    track.addEventListener('mouseleave', () => track.classList.remove('paused'));
    track.addEventListener('touchstart', () => track.classList.add('paused'));
    track.addEventListener('touchend', () => track.classList.remove('paused'));

    // Video players
    const playPath = '<path d="M8 5v14l11-7z"/>';
    const pausePath = '<path d="M6 4h4v16H6zm8 0h4v16h-4z"/>';
    const volOnPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>';
    const volOffPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>';

    document.querySelectorAll('.video-player').forEach(function(wrapper) {
        const video = wrapper.querySelector('video');
        const playBtn = wrapper.querySelector('.play-btn');
        const muteBtn = wrapper.querySelector('.mute-btn');
        if (!video || !playBtn || !muteBtn) return;

        video.src = wrapper.dataset.src;

        function setPlayIcon() {
            playBtn.innerHTML = '<svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">' + (video.paused ? playPath : pausePath) + '</svg>';
        }
        function setMuteIcon() {
            muteBtn.innerHTML = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">' + (video.muted ? volOffPath : volOnPath) + '</svg>';
        }

        playBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (video.paused) video.play(); else video.pause();
            setPlayIcon();
        });
        muteBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            video.muted = !video.muted;
            setMuteIcon();
        });
        video.addEventListener('play', setPlayIcon);
        video.addEventListener('pause', setPlayIcon);

        setPlayIcon();
        setMuteIcon();
    });
})();
</script>
@endif
