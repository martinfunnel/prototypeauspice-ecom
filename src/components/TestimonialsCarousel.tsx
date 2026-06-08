import { useQuery } from "@tanstack/react-query";
import { useEffect, useRef, useState } from "react";
import { Star, Quote, Play, Pause, Volume2, VolumeX } from "lucide-react";
import { supabase } from "@/integrations/supabase/client";

type Testimonial = {
  id: string;
  author_name: string;
  role: string | null;
  content: string;
  rating: number;
  media_url: string | null;
  media_type: "image" | "video";
};

function VideoPlayer({ src }: { src: string }) {
  const ref = useRef<HTMLVideoElement>(null);
  const [playing, setPlaying] = useState(false);
  const [muted, setMuted] = useState(true);

  const togglePlay = (e: React.MouseEvent) => {
    e.stopPropagation();
    const v = ref.current;
    if (!v) return;
    if (v.paused) {
      v.play();
      setPlaying(true);
    } else {
      v.pause();
      setPlaying(false);
    }
  };

  const toggleMute = (e: React.MouseEvent) => {
    e.stopPropagation();
    const v = ref.current;
    if (!v) return;
    v.muted = !v.muted;
    setMuted(v.muted);
  };

  return (
    <div className="relative h-full w-full">
      <video
        ref={ref}
        src={src}
        className="h-full w-full object-contain"
        muted={muted}
        loop
        playsInline
        preload="metadata"
        onPlay={() => setPlaying(true)}
        onPause={() => setPlaying(false)}
      />
      <div className="absolute inset-x-0 bottom-0 flex items-center justify-between gap-2 bg-gradient-to-t from-black/70 to-transparent p-3">
        <button
          type="button"
          onClick={togglePlay}
          aria-label={playing ? "Pause" : "Lecture"}
          className="grid h-9 w-9 place-items-center rounded-full bg-white/90 text-foreground shadow hover:bg-white"
        >
          {playing ? <Pause className="h-4 w-4" /> : <Play className="h-4 w-4" />}
        </button>
        <button
          type="button"
          onClick={toggleMute}
          aria-label={muted ? "Activer le son" : "Couper le son"}
          className="grid h-9 w-9 place-items-center rounded-full bg-white/90 text-foreground shadow hover:bg-white"
        >
          {muted ? <VolumeX className="h-4 w-4" /> : <Volume2 className="h-4 w-4" />}
        </button>
      </div>
    </div>
  );
}

export function TestimonialsCarousel({ heading = "Ils ont adopté Auspice Market", subheading = "Les clients du cacao à la cannelle de Ceylan partagent leur expérience." }: { heading?: string; subheading?: string }) {
  const { data } = useQuery({
    queryKey: ["testimonials-active"],
    queryFn: async () => {
      const { data } = await supabase
        .from("testimonials")
        .select("id,author_name,role,content,rating,media_url,media_type")
        .eq("is_active", true)
        .order("sort_order", { ascending: true })
        .order("created_at", { ascending: false });
      return (data ?? []) as Testimonial[];
    },
    staleTime: 60_000,
  });

  const trackRef = useRef<HTMLDivElement>(null);
  const pausedRef = useRef(false);

  useEffect(() => {
    const el = trackRef.current;
    if (!el || !data || data.length === 0) return;
    let raf = 0;
    let last = performance.now();
    const speed = 40;
    const tick = (now: number) => {
      const dt = (now - last) / 1000;
      last = now;
      if (!pausedRef.current && el.scrollWidth > el.clientWidth) {
        el.scrollLeft += speed * dt;
        if (el.scrollLeft >= el.scrollWidth / 2) {
          el.scrollLeft -= el.scrollWidth / 2;
        }
      }
      raf = requestAnimationFrame(tick);
    };
    raf = requestAnimationFrame(tick);
    return () => cancelAnimationFrame(raf);
  }, [data]);

  if (!data || data.length === 0) return null;

  const items = [...data, ...data];

  const hasMeta = (t: Testimonial) =>
    (t.author_name && t.author_name !== "—") || (t.content && t.content.trim().length > 0) || t.role;

  return (
    <section className="container mx-auto px-4 py-12 md:py-16">
      <div className="mb-6 flex flex-col items-start gap-1 md:mb-8 md:flex-row md:items-end md:justify-between">
        <div>
          <span className="text-xs font-semibold uppercase tracking-wider text-accent">Témoignages</span>
          <h2 className="mt-1 font-display text-2xl font-bold md:text-3xl">{heading}</h2>
          <p className="mt-1 max-w-xl text-sm text-muted-foreground">{subheading}</p>
        </div>
      </div>

      <div
        ref={trackRef}
        onMouseEnter={() => (pausedRef.current = true)}
        onMouseLeave={() => (pausedRef.current = false)}
        onTouchStart={() => (pausedRef.current = true)}
        onTouchEnd={() => (pausedRef.current = false)}
        className="flex gap-4 overflow-x-auto scroll-smooth pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
      >
        {items.map((t, idx) => (
          <article
            key={`${t.id}-${idx}`}
            className="flex w-[300px] shrink-0 flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-card md:w-[360px]"
          >
            {t.media_url ? (
              <div className="flex aspect-[4/5] w-full items-center justify-center overflow-hidden bg-black">
                {t.media_type === "video" ? (
                  <VideoPlayer src={t.media_url} />
                ) : (
                  <img src={t.media_url} alt={t.author_name} className="h-full w-full object-contain" loading="lazy" />
                )}
              </div>
            ) : null}
            {hasMeta(t) ? (
              <div className="flex flex-1 flex-col gap-3 p-5">
                <Quote className="h-5 w-5 text-accent" />
                {t.content ? (
                  <p className="line-clamp-5 text-sm leading-relaxed text-foreground/90">"{t.content}"</p>
                ) : null}
                <div className="mt-auto flex items-center justify-between gap-2 pt-2">
                  <div>
                    {t.author_name && t.author_name !== "—" ? (
                      <p className="text-sm font-semibold">{t.author_name}</p>
                    ) : null}
                    {t.role ? <p className="text-xs text-muted-foreground">{t.role}</p> : null}
                  </div>
                  <div className="flex">
                    {Array.from({ length: 5 }).map((_, i) => (
                      <Star
                        key={i}
                        className={`h-3.5 w-3.5 ${i < t.rating ? "fill-accent text-accent" : "text-muted-foreground/30"}`}
                      />
                    ))}
                  </div>
                </div>
              </div>
            ) : (
              <div className="flex items-center justify-end p-3">
                <div className="flex">
                  {Array.from({ length: 5 }).map((_, i) => (
                    <Star
                      key={i}
                      className={`h-3.5 w-3.5 ${i < t.rating ? "fill-accent text-accent" : "text-muted-foreground/30"}`}
                    />
                  ))}
                </div>
              </div>
            )}
          </article>
        ))}
      </div>
    </section>
  );
}
