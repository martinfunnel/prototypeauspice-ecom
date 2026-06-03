import { createFileRoute, Link } from "@tanstack/react-router";
import { queryOptions, useSuspenseQuery } from "@tanstack/react-query";
import { ArrowRight, Truck, ShieldCheck, Phone, Sparkles, Check, Star } from "lucide-react";
import { supabase } from "@/integrations/supabase/client";
import { ProductCard } from "@/components/ProductCard";
import { formatCFA } from "@/lib/format";
import cacaoAsset from "@/assets/cacao-cannelle-ceylan.jpeg.asset.json";

const FEATURED_SLUG = "cacaocelyan";

const homeQuery = queryOptions({
  queryKey: ["home"],
  queryFn: async () => {
    const [{ data: products }, { data: categories }] = await Promise.all([
      supabase.from("products").select("id,name,slug,price,promo_price,images,short_description,is_popular").eq("is_active", true).order("is_popular", { ascending: false }).limit(8),
      supabase.from("categories").select("id,name,slug,description").order("sort_order").limit(6),
    ]);
    return { products: products ?? [], categories: categories ?? [] };
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
              <Sparkles className="h-3.5 w-3.5 text-accent" /> Livraison Abidjan & toute la Côte d'Ivoire
            </span>
            <h1 className="mt-5 font-display text-4xl font-bold leading-tight md:text-6xl">
              Votre santé,<br /><span className="text-accent">livrée à domicile.</span>
            </h1>
            <p className="mt-4 max-w-lg text-base text-primary-foreground/80 md:text-lg">
              Compléments alimentaires, vitamines et soins authentiques. Payez à la livraison, partout en Côte d'Ivoire.
            </p>
            <div className="mt-7 flex flex-wrap gap-3">
              <Link to="/catalogue" className="inline-flex items-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-semibold text-accent-foreground shadow-accent transition hover:scale-105">
                Découvrir les produits <ArrowRight className="h-4 w-4" />
              </Link>
              <Link to="/suivi" className="inline-flex items-center gap-2 rounded-xl border border-primary-foreground/30 px-6 py-3 text-sm font-semibold text-primary-foreground hover:bg-primary-foreground/10">
                Suivre ma commande
              </Link>
            </div>
            <div className="mt-8 grid grid-cols-3 gap-3 text-xs">
              <div className="flex items-center gap-2 text-primary-foreground/80"><Truck className="h-4 w-4 text-accent" />Livraison 24-48h</div>
              <div className="flex items-center gap-2 text-primary-foreground/80"><Phone className="h-4 w-4 text-accent" />Paiement livraison</div>
              <div className="flex items-center gap-2 text-primary-foreground/80"><ShieldCheck className="h-4 w-4 text-accent" />100% authentique</div>
            </div>
          </div>
          <div className="relative hidden md:block">
            <div className="relative mx-auto aspect-square w-full max-w-md rounded-3xl bg-gradient-to-br from-accent/40 to-teal/30 p-8 shadow-elevated">
              <div className="grid h-full place-items-center text-9xl">💊</div>
            </div>
          </div>
        </div>
      </section>

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
            <h2 className="font-display text-2xl font-bold md:text-3xl">Produits populaires</h2>
            <p className="mt-1 text-sm text-muted-foreground">Sélectionnés pour leur qualité.</p>
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
              <p className="mt-2 max-w-xl text-accent-foreground/90">Pas de carte bancaire requise. Vous payez le livreur en espèces à la réception.</p>
            </div>
            <Link to="/catalogue" className="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-primary-foreground hover:opacity-90">
              Commencer <ArrowRight className="h-4 w-4" />
            </Link>
          </div>
        </div>
      </section>
    </>
  );
}
