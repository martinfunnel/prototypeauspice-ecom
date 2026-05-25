import { Link } from "@tanstack/react-router";
import { formatCFA } from "@/lib/format";

type Product = {
  id: string;
  name: string;
  slug: string;
  price: number | string;
  promo_price: number | string | null;
  images: string[];
  short_description: string | null;
  is_popular?: boolean;
};

export function ProductCard({ product }: { product: Product }) {
  const img = product.images?.[0] ?? null;
  const hasPromo = product.promo_price != null;
  const displayPrice = hasPromo ? product.promo_price! : product.price;
  return (
    <Link
      to="/produit/$slug"
      params={{ slug: product.slug }}
      className="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-card transition hover:-translate-y-0.5 hover:shadow-elevated"
    >
      <div className="relative aspect-square overflow-hidden bg-muted">
        {img ? (
          <img
            src={img}
            alt={product.name}
            loading="lazy"
            className="h-full w-full object-cover transition group-hover:scale-105"
          />
        ) : (
          <div className="grid h-full w-full place-items-center text-muted-foreground">📦</div>
        )}
        {hasPromo ? (
          <span className="absolute left-3 top-3 rounded-full bg-accent px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-accent-foreground shadow-accent">
            Promo
          </span>
        ) : null}
        {product.is_popular ? (
          <span className="absolute right-3 top-3 rounded-full bg-primary px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-primary-foreground">
            ★ Populaire
          </span>
        ) : null}
      </div>
      <div className="flex flex-1 flex-col gap-2 p-4">
        <h3 className="line-clamp-2 font-display text-base font-semibold leading-tight text-foreground">
          {product.name}
        </h3>
        {product.short_description ? (
          <p className="line-clamp-2 text-xs text-muted-foreground">{product.short_description}</p>
        ) : null}
        <div className="mt-auto flex items-baseline gap-2 pt-2">
          <span className="font-display text-lg font-bold text-primary">
            {formatCFA(displayPrice)}
          </span>
          {hasPromo ? (
            <span className="text-xs text-muted-foreground line-through">
              {formatCFA(product.price)}
            </span>
          ) : null}
        </div>
      </div>
    </Link>
  );
}
