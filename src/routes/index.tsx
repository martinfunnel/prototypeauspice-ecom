import { createFileRoute, Link } from "@tanstack/react-router";
import { queryOptions, useSuspenseQuery } from "@tanstack/react-query";
import { ArrowRight, Truck, ShieldCheck, Phone, Sparkles, Check, Star } from "lucide-react";
import { supabase } from "@/integrations/supabase/client";
import { ProductCard } from "@/components/ProductCard";
import { formatCFA } from "@/lib/format";
import cacaoAsset from "@/assets/cacao-cannelle-ceylan.jpeg.asset.json";
import { TestimonialsCarousel } from "@/components/TestimonialsCarousel";

const FEATURED_SLUG = "cacaocelyan";

const homeQuery = queryOptions({
  queryKey: ["home"],
  queryFn: async () => {
    const [{ data: products }, { data: categories }, { data: featured }] = await Promise.all([
      supabase.from("products").select("id,name,slug,price,promo_price,images,short_description,is_popular").eq("is_active", true).order("is_popular", { ascending: false }).limit(8),
      supabase.from("categories").select("id,name,slug,description").order("sort_order").limit(6),
      supabase.from("products").select("id,name,slug,price,promo_price,short_description,benefits").eq("slug", FEATURED_SLUG).eq("is_active", true).maybeSingle(),
    ]);
    return { products: products ?? [], categories: categories ?? [], featured: featured ?? null };
  },
});

export const Route = createFileRoute("/")({
  loader: ({ context }) => context.queryClient.ensureQueryData(homeQuery),
  component: Home,
});

function Home() {
  const { data } = useSuspenseQuery(homeQuery);
  return (
    <>
      {/* HERO */}
      <section className="relative overflow-hidden gradient-hero text-primary-foreground">
        <div className="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-accent/30 blur-3xl" />
        <div className="absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-teal/20 blur-3xl" />
        <div className="container relative mx-auto grid gap-10 px-4 py-16 md:grid-cols-2 md:py-24">
          <div className="flex flex-col justify-center">
            <span className="inline-flex w-fit items-center gap-2 rounded-full border border-primary-foreground/20 bg-primary-foreground/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider">
              <Sparkles className="h-3.5 w-3.5 text-accent" /> Auspice SARL · Bio & livraison Côte d'Ivoire
            </span>
            <h1 className="mt-5 font-display text-4xl font-bold leading-tight md:text-6xl">
              Le bien-être bio,<br /><span className="text-accent">enraciné dans la nature.</span>
            </h1>
            <p className="mt-4 max-w-lg text-base text-primary-foreground/80 md:text-lg">
              Auspice Market vous propose des compléments alimentaires et produits de santé
              100% issus de l'agriculture biologique. Découvrez notre best-seller&nbsp;:
              le <strong className="text-accent">cacao à la cannelle de Ceylan</strong>.
            </p>
            <div className="mt-7 flex flex-wrap gap-3">
              <Link to="/produit/$slug" params={{ slug: FEATURED_SLUG }} className="inline-flex items-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-semibold text-accent-foreground shadow-accent transition hover:scale-105">
                Découvrir le cacao Ceylan <ArrowRight className="h-4 w-4" />
              </Link>
              <Link to="/catalogue" className="inline-flex items-center gap-2 rounded-xl border border-primary-foreground/30 px-6 py-3 text-sm font-semibold text-primary-foreground hover:bg-primary-foreground/10">
                Voir la boutique
              </Link>
            </div>
            <div className="mt-8 grid grid-cols-3 gap-3 text-xs">
              <div className="flex items-center gap-2 text-primary-foreground/80"><Truck className="h-4 w-4 text-accent" />Livraison 24-48h</div>
              <div className="flex items-center gap-2 text-primary-foreground/80"><Phone className="h-4 w-4 text-accent" />Paiement à la livraison</div>
              <div className="flex items-center gap-2 text-primary-foreground/80"><ShieldCheck className="h-4 w-4 text-accent" />Certifié bio</div>
            </div>
          </div>
          <div className="relative hidden md:block">
            <Link to="/produit/$slug" params={{ slug: FEATURED_SLUG }} className="group relative mx-auto block aspect-square w-full max-w-md rounded-3xl bg-gradient-to-br from-accent/40 to-teal/30 p-6 shadow-elevated transition hover:scale-[1.02]">
              <img src={cacaoAsset.url} alt="Cacao brut à la cannelle de Ceylan — Auspice Market" className="h-full w-full object-contain drop-shadow-2xl" />
              <span className="absolute left-4 top-4 inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1 text-xs font-bold text-accent-foreground shadow-accent">
                <Star className="h-3 w-3 fill-current" /> Best-seller bio
              </span>
            </Link>
          </div>
        </div>
      </section>

      {/* FEATURED PRODUCT SPOTLIGHT */}
      {data.featured ? (
        <section className="container mx-auto px-4 py-12 md:py-16">
          <div className="overflow-hidden rounded-3xl border border-border bg-card shadow-elevated">
            <div className="grid gap-0 md:grid-cols-2">
              <div className="relative flex items-center justify-center bg-gradient-to-br from-accent/20 via-background to-teal/10 p-8 md:p-12">
                <span className="absolute left-6 top-6 inline-flex items-center gap-1.5 rounded-full bg-accent px-3 py-1 text-xs font-bold text-accent-foreground shadow-accent">
                  <Star className="h-3 w-3 fill-current" /> Produit phare
                </span>
                <img src={cacaoAsset.url} alt={data.featured.name} className="max-h-[420px] w-auto object-contain drop-shadow-2xl" />
              </div>
              <div className="flex flex-col justify-center p-8 md:p-12">
                <span className="text-xs font-semibold uppercase tracking-wider text-accent">Auspice Market · Best-seller bio</span>
                <h2 className="mt-3 font-display text-3xl font-bold leading-tight md:text-4xl">
                  Cacao brut à la <span className="text-accent">cannelle de Ceylan</span>
                </h2>
                <p className="mt-3 text-base text-foreground/80">
                  {data.featured.short_description ?? "Une poudre 100% naturelle, riche en antioxydants, pour un cacao chaud onctueux et plein de bienfaits."}
                </p>
                <ul className="mt-5 space-y-2">
                  {(data.featured.benefits && data.featured.benefits.length > 0
                    ? data.featured.benefits.slice(0, 4)
                    : ["100% naturel et bio", "Riche en antioxydants", "Cannelle de Ceylan authentique", "Sans sucre ajouté"]
                  ).map((b: string, i: number) => (
                    <li key={i} className="flex items-start gap-2 text-sm">
                      <Check className="mt-0.5 h-4 w-4 shrink-0 text-success" />
                      <span>{b}</span>
                    </li>
                  ))}
                </ul>
                <div className="mt-6 flex items-baseline gap-3">
                  <span className="font-display text-3xl font-bold text-primary">{formatCFA(Number(data.featured.promo_price ?? data.featured.price))}</span>
                  {data.featured.promo_price ? (
                    <span className="text-base text-muted-foreground line-through">{formatCFA(Number(data.featured.price))}</span>
                  ) : null}
                </div>
                <Link to="/produit/$slug" params={{ slug: data.featured.slug }} className="mt-6 inline-flex w-fit items-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-bold text-accent-foreground shadow-accent transition hover:scale-105">
                  Commander maintenant <ArrowRight className="h-4 w-4" />
                </Link>
                <p className="mt-3 text-xs text-success">💵 Paiement à la livraison disponible</p>
              </div>
            </div>
          </div>
        </section>
      ) : null}

      {/* CATEGORIES */}
      <section className="container mx-auto px-4 py-12 md:py-16">
        <div className="mb-6 flex items-end justify-between">
          <h2 className="font-display text-2xl font-bold md:text-3xl">Catégories</h2>
          <Link to="/catalogue" className="text-sm font-semibold text-accent hover:underline">Tout voir →</Link>
        </div>
        <div className="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5">
          {data.categories.map((c) => (
            <Link key={c.id} to="/catalogue" search={{ cat: c.slug } as never} className="group rounded-xl border border-border bg-card p-5 shadow-card transition hover:-translate-y-0.5 hover:border-accent hover:shadow-elevated">
              <div className="text-3xl">🌿</div>
              <h3 className="mt-3 font-display text-sm font-bold">{c.name}</h3>
              <p className="mt-1 line-clamp-2 text-xs text-muted-foreground">{c.description}</p>
            </Link>
          ))}
        </div>
      </section>

      {/* FEATURED */}
      <section className="container mx-auto px-4 pb-16">
        <div className="mb-6 flex items-end justify-between">
          <div>
            <h2 className="font-display text-2xl font-bold md:text-3xl">Nos produits bio populaires</h2>
            <p className="mt-1 text-sm text-muted-foreground">Sélectionnés par Auspice SARL pour leur pureté et leur efficacité.</p>
          </div>
          <Link to="/catalogue" className="text-sm font-semibold text-accent hover:underline">Tout voir →</Link>
        </div>
        {data.products.length === 0 ? (
          <div className="rounded-2xl border border-dashed border-border bg-muted/30 p-12 text-center">
            <p className="text-sm text-muted-foreground">Aucun produit pour le moment. L'administrateur peut en ajouter depuis le dashboard.</p>
            <Link to="/admin/login" className="mt-4 inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground">Espace admin</Link>
          </div>
        ) : (
          <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
            {data.products.map((p) => <ProductCard key={p.id} product={p} />)}
          </div>
        )}
      </section>

      {/* CTA */}
      <section className="container mx-auto px-4 pb-16">
        <div className="overflow-hidden rounded-3xl gradient-accent p-10 text-accent-foreground shadow-accent md:p-14">
          <div className="grid items-center gap-6 md:grid-cols-[1fr_auto]">
            <div>
              <h3 className="font-display text-3xl font-bold">Commandez en 2 minutes</h3>
              <p className="mt-2 max-w-xl text-accent-foreground/90">Pas de carte bancaire. Vous payez le livreur en espèces à la réception, partout en Côte d'Ivoire.</p>
            </div>
            <Link to="/produit/$slug" params={{ slug: FEATURED_SLUG }} className="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-primary-foreground hover:opacity-90">
              Commander le cacao Ceylan <ArrowRight className="h-4 w-4" />
            </Link>
          </div>
        </div>
      </section>
    </>
  );
}
