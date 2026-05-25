import { createFileRoute } from "@tanstack/react-router";
import { useServerFn } from "@tanstack/react-start";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { getOrdersByPhone } from "@/lib/orders.functions";
import { formatCFA, formatDateTime } from "@/lib/format";
import { StatusBadge } from "@/components/StatusBadge";

export const Route = createFileRoute("/suivi")({
  head: () => ({ meta: [{ title: "Suivre ma commande — Santé Ivoire" }] }),
  component: TrackPage,
});

function TrackPage() {
  const fn = useServerFn(getOrdersByPhone);
  const [phone, setPhone] = useState("");
  const [orders, setOrders] = useState<Awaited<ReturnType<typeof fn>> | null>(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const last = typeof window !== "undefined" ? sessionStorage.getItem("last_phone") : null;
    if (last) {
      setPhone(last);
      lookup(last);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const lookup = async (p: string) => {
    if (!p) return;
    setLoading(true);
    try {
      const res = await fn({ data: { phone: p } });
      setOrders(res);
      if (res.length === 0) toast.info("Aucune commande trouvée pour ce numéro.");
    } catch {
      toast.error("Erreur lors de la recherche");
    } finally {
      setLoading(false);
    }
  };

  return (
    <section className="container mx-auto max-w-3xl px-4 py-10">
      <h1 className="font-display text-3xl font-bold">Suivre mes commandes</h1>
      <p className="mt-1 text-sm text-muted-foreground">Entrez le numéro de téléphone utilisé lors de la commande.</p>

      <form onSubmit={(e) => { e.preventDefault(); lookup(phone); }} className="mt-6 flex gap-2">
        <input value={phone} onChange={(e) => setPhone(e.target.value)} placeholder="+225 07 00 00 00 00" className="flex-1 rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent" />
        <button disabled={loading} className="rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground disabled:opacity-60">{loading ? "..." : "Rechercher"}</button>
      </form>

      {orders && orders.length > 0 ? (
        <ul className="mt-8 space-y-4">
          {orders.map((o) => (
            <li key={o.id} className="rounded-2xl border border-border bg-card p-5 shadow-card">
              <div className="flex flex-wrap items-center justify-between gap-2">
                <div>
                  <p className="font-display text-lg font-bold text-primary">{o.order_number}</p>
                  <p className="text-xs text-muted-foreground">{formatDateTime(o.created_at)}</p>
                </div>
                <StatusBadge status={o.status} />
              </div>
              <ul className="mt-3 space-y-1 text-sm text-foreground/80">
                {o.order_items.map((i, idx) => (
                  <li key={idx}>• {i.quantity}× {i.product_name}</li>
                ))}
              </ul>
              <div className="mt-3 flex items-center justify-between border-t border-border pt-3 text-sm">
                <span className="text-muted-foreground">{o.commune_name} — Livraison {formatCFA(o.delivery_fee)}</span>
                <span className="font-bold text-primary">{formatCFA(o.total)}</span>
              </div>
            </li>
          ))}
        </ul>
      ) : null}
    </section>
  );
}
