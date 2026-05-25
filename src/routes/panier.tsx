import { createFileRoute, Link } from "@tanstack/react-router";
import { Minus, Plus, Trash2 } from "lucide-react";
import { useCart } from "@/lib/cart";
import { formatCFA } from "@/lib/format";

export const Route = createFileRoute("/panier")({
  head: () => ({ meta: [{ title: "Mon panier — Santé Ivoire" }] }),
  component: CartPage,
});

function CartPage() {
  const { items, updateQty, removeItem, subtotal } = useCart();
  return (
    <section className="container mx-auto max-w-3xl px-4 py-10">
      <h1 className="font-display text-3xl font-bold">Mon panier</h1>
      {items.length === 0 ? (
        <div className="mt-8 rounded-2xl border border-dashed border-border p-12 text-center">
          <p className="text-muted-foreground">Votre panier est vide.</p>
          <Link to="/catalogue" className="mt-4 inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground">Voir le catalogue</Link>
        </div>
      ) : (
        <>
          <ul className="mt-6 divide-y divide-border rounded-2xl border border-border bg-card">
            {items.map((it) => (
              <li key={it.productId} className="flex items-center gap-4 p-4">
                <div className="h-16 w-16 shrink-0 overflow-hidden rounded-md bg-muted">
                  {it.image ? <img src={it.image} alt="" className="h-full w-full object-cover" /> : <div className="grid h-full place-items-center text-2xl">📦</div>}
                </div>
                <div className="flex-1">
                  <p className="font-semibold">{it.name}</p>
                  <p className="text-sm text-muted-foreground">{formatCFA(it.price)}</p>
                </div>
                <div className="flex items-center rounded-lg border border-border">
                  <button onClick={() => updateQty(it.productId, it.quantity - 1)} className="grid h-9 w-9 place-items-center"><Minus className="h-3 w-3" /></button>
                  <span className="w-8 text-center text-sm font-semibold">{it.quantity}</span>
                  <button onClick={() => updateQty(it.productId, it.quantity + 1)} className="grid h-9 w-9 place-items-center"><Plus className="h-3 w-3" /></button>
                </div>
                <button onClick={() => removeItem(it.productId)} className="text-destructive hover:opacity-70"><Trash2 className="h-4 w-4" /></button>
              </li>
            ))}
          </ul>
          <div className="mt-6 flex items-center justify-between rounded-2xl border border-border bg-card p-5">
            <span className="text-sm text-muted-foreground">Sous-total (livraison calculée à la commande)</span>
            <span className="font-display text-xl font-bold text-primary">{formatCFA(subtotal)}</span>
          </div>
          <Link to="/commande" className="mt-4 block w-full rounded-xl bg-accent px-6 py-4 text-center text-base font-bold text-accent-foreground shadow-accent">Passer la commande →</Link>
        </>
      )}
    </section>
  );
}
