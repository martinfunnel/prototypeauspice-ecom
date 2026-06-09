import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { useServerFn } from "@tanstack/react-start";
import { useMemo, useState } from "react";
import { toast } from "sonner";
import { ArrowLeft, Plus, Trash2 } from "lucide-react";
import { AdminShell } from "@/components/AdminShell";
import {
  adminCreateOrder,
  listProductsLite,
  listCommunesAdmin,
} from "@/lib/admin.functions";
import { formatCFA, ORDER_STATUS_LABELS } from "@/lib/format";

export const Route = createFileRoute("/admin/orders/new")({
  head: () => ({ meta: [{ title: "Nouvelle commande — Admin" }] }),
  component: AdminNewOrder,
});

type Line = { product_id: string; quantity: number };

function AdminNewOrder() {
  const navigate = useNavigate();
  const productsFn = useServerFn(listProductsLite);
  const communesFn = useServerFn(listCommunesAdmin);
  const createFn = useServerFn(adminCreateOrder);

  const { data: products } = useQuery({ queryKey: ["admin-products-lite"], queryFn: () => productsFn() });
  const { data: communes } = useQuery({ queryKey: ["admin-communes"], queryFn: () => communesFn() });

  const [customer_name, setName] = useState("");
  const [customer_phone, setPhone] = useState("");
  const [commune_id, setCommune] = useState("");
  const [address, setAddress] = useState("");
  const [notes, setNotes] = useState("");
  const [status, setStatus] = useState<"pending" | "confirmed" | "processing" | "shipped" | "delivered" | "cancelled">("confirmed");
  const [lines, setLines] = useState<Line[]>([{ product_id: "", quantity: 1 }]);
  const [saving, setSaving] = useState(false);

  const productMap = useMemo(
    () => new Map((products ?? []).map((p) => [p.id, p])),
    [products],
  );
  const commune = (communes ?? []).find((c) => c.id === commune_id);

  const subtotal = lines.reduce((s, l) => {
    const p = productMap.get(l.product_id);
    if (!p) return s;
    const unit = Number(p.promo_price ?? p.price);
    return s + unit * l.quantity;
  }, 0);
  const delivery_fee = Number(commune?.delivery_fee ?? 0);
  const total = subtotal + delivery_fee;

  const updateLine = (i: number, patch: Partial<Line>) => {
    setLines((arr) => arr.map((l, idx) => (idx === i ? { ...l, ...patch } : l)));
  };
  const addLine = () => setLines((arr) => [...arr, { product_id: "", quantity: 1 }]);
  const removeLine = (i: number) => setLines((arr) => arr.filter((_, idx) => idx !== i));

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    const valid = lines.filter((l) => l.product_id && l.quantity > 0);
    if (valid.length === 0) {
      toast.error("Ajoutez au moins un produit");
      return;
    }
    setSaving(true);
    try {
      const res = await createFn({
        data: {
          customer_name,
          customer_phone,
          commune_id,
          address,
          notes: notes || undefined,
          status,
          items: valid.map((l) => ({ product_id: l.product_id, quantity: l.quantity })),
        },
      });
      toast.success(`Commande ${res.order_number} créée`);
      navigate({ to: "/admin/orders" });
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Erreur");
    } finally {
      setSaving(false);
    }
  };

  return (
    <AdminShell title="Nouvelle commande">
      <Link
        to="/admin/orders"
        className="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-accent"
      >
        <ArrowLeft className="h-4 w-4" /> Retour aux commandes
      </Link>

      <form onSubmit={submit} className="grid gap-6 lg:grid-cols-[1fr_320px]">
        <div className="space-y-6">
          <section className="rounded-2xl border border-border bg-card p-5 shadow-card">
            <h2 className="mb-4 font-display text-base font-bold">Informations client</h2>
            <div className="grid gap-3 sm:grid-cols-2">
              <div className="sm:col-span-2">
                <label className="mb-1 block text-xs font-medium">Nom du client *</label>
                <input
                  required minLength={2} maxLength={120}
                  value={customer_name} onChange={(e) => setName(e.target.value)}
                  className="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"
                />
              </div>
              <div>
                <label className="mb-1 block text-xs font-medium">Téléphone *</label>
                <input
                  required minLength={8} maxLength={20}
                  value={customer_phone} onChange={(e) => setPhone(e.target.value)}
                  placeholder="+225 0X XX XX XX XX"
                  className="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"
                />
              </div>
              <div>
                <label className="mb-1 block text-xs font-medium">Commune *</label>
                <select
                  required value={commune_id} onChange={(e) => setCommune(e.target.value)}
                  className="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm"
                >
                  <option value="">Sélectionner…</option>
                  {(communes ?? []).filter((c) => c.is_active).map((c) => (
                    <option key={c.id} value={c.id}>{c.name} — {formatCFA(c.delivery_fee)}</option>
                  ))}
                </select>
              </div>
              <div className="sm:col-span-2">
                <label className="mb-1 block text-xs font-medium">Adresse de livraison *</label>
                <textarea
                  required minLength={3} maxLength={500} rows={2}
                  value={address} onChange={(e) => setAddress(e.target.value)}
                  className="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"
                />
              </div>
              <div className="sm:col-span-2">
                <label className="mb-1 block text-xs font-medium">Note (optionnelle)</label>
                <textarea
                  maxLength={500} rows={2}
                  value={notes} onChange={(e) => setNotes(e.target.value)}
                  className="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"
                />
              </div>
            </div>
          </section>

          <section className="rounded-2xl border border-border bg-card p-5 shadow-card">
            <div className="mb-4 flex items-center justify-between">
              <h2 className="font-display text-base font-bold">Produits commandés</h2>
              <button
                type="button" onClick={addLine}
                className="inline-flex items-center gap-1 rounded-lg border border-border px-3 py-1.5 text-xs font-semibold hover:bg-muted"
              >
                <Plus className="h-3.5 w-3.5" /> Ajouter
              </button>
            </div>
            <div className="space-y-2">
              {lines.map((l, i) => {
                const p = productMap.get(l.product_id);
                const unit = p ? Number(p.promo_price ?? p.price) : 0;
                return (
                  <div key={i} className="grid grid-cols-[1fr_90px_110px_auto] items-center gap-2">
                    <select
                      value={l.product_id}
                      onChange={(e) => updateLine(i, { product_id: e.target.value })}
                      className="rounded-lg border border-border bg-background px-3 py-2 text-sm"
                    >
                      <option value="">— Produit —</option>
                      {(products ?? []).map((pp) => (
                        <option key={pp.id} value={pp.id}>
                          {pp.name} ({formatCFA(pp.promo_price ?? pp.price)})
                        </option>
                      ))}
                    </select>
                    <input
                      type="number" min={1} max={999} value={l.quantity}
                      onChange={(e) => updateLine(i, { quantity: Math.max(1, Number(e.target.value) || 1) })}
                      className="rounded-lg border border-border bg-background px-3 py-2 text-center text-sm"
                    />
                    <div className="text-right text-sm font-mono">{formatCFA(unit * l.quantity)}</div>
                    <button
                      type="button" onClick={() => removeLine(i)}
                      disabled={lines.length === 1}
                      className="rounded-lg p-2 text-destructive hover:bg-destructive/10 disabled:opacity-30"
                    >
                      <Trash2 className="h-4 w-4" />
                    </button>
                  </div>
                );
              })}
            </div>
          </section>
        </div>

        <aside className="space-y-4">
          <div className="rounded-2xl border border-border bg-card p-5 shadow-card lg:sticky lg:top-24">
            <h3 className="mb-3 font-display text-sm font-bold uppercase text-muted-foreground">Récapitulatif</h3>
            <div className="space-y-2 text-sm">
              <div className="flex justify-between"><span>Sous-total</span><span className="font-mono">{formatCFA(subtotal)}</span></div>
              <div className="flex justify-between text-muted-foreground"><span>Livraison</span><span className="font-mono">{formatCFA(delivery_fee)}</span></div>
              <div className="flex justify-between border-t border-border pt-2 font-bold">
                <span>Total</span><span className="font-mono">{formatCFA(total)}</span>
              </div>
            </div>
            <div className="mt-4">
              <label className="mb-1 block text-xs font-medium">Statut initial</label>
              <select
                value={status}
                onChange={(e) => setStatus(e.target.value as typeof status)}
                className="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm"
              >
                {Object.keys(ORDER_STATUS_LABELS).map((s) => (
                  <option key={s} value={s}>{ORDER_STATUS_LABELS[s].label}</option>
                ))}
              </select>
            </div>
            <button
              disabled={saving}
              className="mt-4 w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground disabled:opacity-60"
            >
              {saving ? "Création…" : "Créer la commande"}
            </button>
          </div>
        </aside>
      </form>
    </AdminShell>
  );
}
