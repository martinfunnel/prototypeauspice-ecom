import { createFileRoute, Link, notFound, useNavigate } from "@tanstack/react-router";
import { queryOptions, useSuspenseQuery } from "@tanstack/react-query";
import { useState } from "react";
import { toast } from "sonner";
import { Minus, Plus, ShoppingCart } from "lucide-react";
import { supabase } from "@/integrations/supabase/client";
import { formatCFA } from "@/lib/format";
import { useCart } from "@/lib/cart";
import { ProductCard } from "@/components/ProductCard";

const productQuery = (slug: string) =>
  queryOptions({
    queryKey: ["product", slug],
    queryFn: async () => {
      const { data: product } = await supabase.from("products").select("*").eq("slug", slug).eq("is_active", true).maybeSingle();
      if (!product) throw notFound();
      const similarQuery = supabase.from("products").select("id,name,slug,price,promo_price,images,short_description").eq("is_active", true).neq("id", product.id).limit(4);
      const { data: similar } = product.category_id ? await similarQuery.eq("category_id", product.category_id) : await similarQuery;
      return { product, similar: similar ?? [] };
    },
  });

export const Route = createFileRoute("/produit/$slug")({
  loader: ({ context, params }) => context.queryClient.ensureQueryData(productQuery(params.slug)),
  head: ({ loaderData }) => ({
    meta: [
      { title: loaderData?.product?.name ? `${loaderData.product.name} — Santé Ivoire` : "Produit" },
      { name: "description", content: loaderData?.product?.short_description ?? "" },
    ],
  }),
  component: ProductPage,
});

function ProductPage() {
  const { slug } = Route.useParams();
  const { data } = useSuspenseQuery(productQuery(slug));
  const p = data.product;
  const [qty, setQty] = useState(1);
  const [img, setImg] = useState(0);
  const { addItem } = useCart();
  const navigate = useNavigate();
  const price = Number(p.promo_price ?? p.price);

  const addToCart = () => {
    addItem({ productId: p.id, name: p.name, price, image: p.images?.[0] ?? null }, qty);
    toast.success(`${p.name} ajouté au panier`);
    navigate({ to: "/panier" });
  };

  return (
    <section className="container mx-auto px-4 py-10">
      <div className="grid gap-8 md:grid-cols-2">
        <div>
          <div className="aspect-square overflow-hidden rounded-2xl bg-muted">
            {p.images?.[img] ? <img src={p.images[img]} alt={p.name} className="h-full w-full object-cover" /> : <div className="grid h-full place-items-center text-7xl">📦</div>}
          </div>
          {p.images && p.images.length > 1 ? (
            <div className="mt-3 flex gap-2 overflow-x-auto">
              {p.images.map((src, i) => (
                <button key={i} onClick={() => setImg(i)} className={`h-16 w-16 shrink-0 overflow-hidden rounded-md border-2 ${i === img ? "border-accent" : "border-transparent"}`}>
                  <img src={src} alt="" className="h-full w-full object-cover" />
                </button>
              ))}
            </div>
          ) : null}
        </div>
        <div>
          <h1 className="font-display text-3xl font-bold md:text-4xl">{p.name}</h1>
          <div className="mt-4 flex items-baseline gap-3">
            <span className="font-display text-3xl font-bold text-primary">{formatCFA(price)}</span>
            {p.promo_price ? <span className="text-base text-muted-foreground line-through">{formatCFA(p.price)}</span> : null}
          </div>
          <p className="mt-2 inline-block rounded-full bg-success/15 px-3 py-1 text-xs font-semibold text-success">💵 Paiement à la livraison disponible</p>
          {p.short_description ? <p className="mt-5 text-base text-foreground/80">{p.short_description}</p> : null}
          {p.description ? <p className="mt-3 whitespace-pre-line text-sm text-muted-foreground">{p.description}</p> : null}

          <div className="mt-8 flex items-center gap-3">
            <div className="flex items-center rounded-lg border border-border">
              <button onClick={() => setQty(Math.max(1, qty - 1))} className="grid h-11 w-11 place-items-center hover:bg-muted"><Minus className="h-4 w-4" /></button>
              <span className="w-10 text-center font-semibold">{qty}</span>
              <button onClick={() => setQty(qty + 1)} className="grid h-11 w-11 place-items-center hover:bg-muted"><Plus className="h-4 w-4" /></button>
            </div>
            <button onClick={addToCart} className="flex flex-1 items-center justify-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-bold text-accent-foreground shadow-accent hover:opacity-95">
              <ShoppingCart className="h-4 w-4" /> Ajouter au panier
            </button>
          </div>
          <Link to="/panier" className="mt-3 block w-full rounded-xl border border-primary px-6 py-3 text-center text-sm font-semibold text-primary hover:bg-primary hover:text-primary-foreground">Voir mon panier</Link>
        </div>
      </div>

      {data.similar.length > 0 ? (
        <div className="mt-16">
          <h2 className="font-display text-2xl font-bold">Produits similaires</h2>
          <div className="mt-5 grid grid-cols-2 gap-4 md:grid-cols-4">
            {data.similar.map((s) => <ProductCard key={s.id} product={s} />)}
          </div>
        </div>
      ) : null}
    </section>
  );
}
