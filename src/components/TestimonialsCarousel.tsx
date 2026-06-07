import { useQuery } from "@tanstack/react-query";
import { useEffect, useRef } from "react";
import { Star, Quote } from "lucide-react";
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
    const speed = 40; // px/sec
    const tick = (now: number) => {
      const dt = (now - last) / 1000;
      last = now;
      if (!pausedRef.current && el.scrollWidth > el.clientWidth) {
        el.scrollLeft += speed * dt;
        // Loop: when we've passed half (because we duplicate items), reset
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

  // Duplicate list for seamless loop
  const items = [...data, ...data];

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
              <div className="aspect-[4/3] w-full overflow-hidden bg-muted">
                {t.media_type === "video" ? (
                  <video
                    src={t.media_url}
                    className="h-full w-full object-cover"
                    muted
                    loop
                    playsInline
                    autoPlay
                    preload="metadata"
                  />
                ) : (
                  <img src={t.media_url} alt={t.author_name} className="h-full w-full object-cover" loading="lazy" />
                )}
              </div>
            ) : null}
            <div className="flex flex-1 flex-col gap-3 p-5">
              <Quote className="h-5 w-5 text-accent" />
              <p className="line-clamp-5 text-sm leading-relaxed text-foreground/90">"{t.content}"</p>
              <div className="mt-auto flex items-center justify-between gap-2 pt-2">
                <div>
                  <p className="text-sm font-semibold">{t.author_name}</p>
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
          </article>
        ))}
      </div>
    </section>
  );
}
