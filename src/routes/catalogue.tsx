import { createFileRoute, Link } from "@tanstack/react-router";
import { queryOptions, useSuspenseQuery } from "@tanstack/react-query";
import { useState } from "react";
import { supabase } from "@/integrations/supabase/client";
import { ProductCard } from "@/components/ProductCard";

const catalogQuery = queryOptions({
  queryKey: ["catalog"],
  queryFn: async () => {
    const [{ data: products }, { data: categories }, { data: banner }] = await Promise.all([
      supabase.from("products").select("id,name,slug,price,promo_price,images,short_description,is_popular,category_id").eq("is_active", true).order("created_at", { ascending: false }),
      supabase.from("categories").select("id,name,slug").order("sort_order"),
      supabase.from("promo_banners").select("title,subtitle,cta_label,cta_url,image_url,is_active").eq("key", "catalogue").eq("is_active", true).maybeSingle(),
    ]);
    return { products: products ?? [], categories: categories ?? [], banner: banner ?? null };
  },
});

export const Route = createFileRoute("/catalogue")({
  head: () => ({ meta: [{ title: "Catalogue — Santé Ivoire" }, { name: "description", content: "Tous nos produits de santé et compléments alimentaires." }] }),
  loader: ({ context }) => context.queryClient.ensureQueryData(catalogQuery),
  component: Catalog,
});

function Catalog() {
  const { data } = useSuspenseQuery(catalogQuery);
  const [cat, setCat] = useState<string | null>(null);
  const [q, setQ] = useState("");
  const filtered = data.products.filter((p) => {
    if (cat && p.category_id !== cat) return false;
    if (q && !p.name.toLowerCase().includes(q.toLowerCase())) return false;
    return true;
  });
  return (
    <section className="container mx-auto px-4 py-10">
      <h1 className="font-display text-3xl font-bold md:text-4xl">Catalogue</h1>
      <p className="mt-2 text-muted-foreground">{filtered.length} produit(s)</p>

      <div className="mt-6 flex flex-col gap-4 md:flex-row md:items-center">
        <input
          value={q}
          onChange={(e) => setQ(e.target.value)}
          placeholder="Rechercher un produit..."
          className="w-full rounded-lg border border-border bg-card px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent md:max-w-sm"
        />
        <div className="flex flex-wrap gap-2">
          <button onClick={() => setCat(null)} className={`rounded-full border px-3 py-1.5 text-xs font-semibold ${!cat ? "border-accent bg-accent text-accent-foreground" : "border-border bg-card hover:border-accent"}`}>Tous</button>
          {data.categories.map((c) => (
            <button key={c.id} onClick={() => setCat(c.id)} className={`rounded-full border px-3 py-1.5 text-xs font-semibold ${cat === c.id ? "border-accent bg-accent text-accent-foreground" : "border-border bg-card hover:border-accent"}`}>{c.name}</button>
          ))}
        </div>
      </div>

      {filtered.length === 0 ? (
        <div className="mt-12 rounded-2xl border border-dashed border-border p-12 text-center text-sm text-muted-foreground">Aucun produit trouvé.</div>
      ) : (
        <div className="mt-8 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
          {filtered.map((p) => <ProductCard key={p.id} product={p} />)}
        </div>
      )}
    </section>
  );
}
