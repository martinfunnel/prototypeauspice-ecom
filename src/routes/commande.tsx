import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { queryOptions, useSuspenseQuery } from "@tanstack/react-query";
import { useServerFn } from "@tanstack/react-start";
import { useMemo, useState } from "react";
import { toast } from "sonner";
import { supabase } from "@/integrations/supabase/client";
import { useCart } from "@/lib/cart";
import { formatCFA } from "@/lib/format";
import { createOrder } from "@/lib/orders.functions";
import { notifyAdminInNewTab } from "@/lib/whatsapp";

const communesQuery = queryOptions({
  queryKey: ["communes"],
  queryFn: async () => {
    const { data } = await supabase.from("communes").select("id,name,zone,delivery_fee,delivery_days").eq("is_active", true).order("zone").order("name");
    return data ?? [];
  },
});

export const Route = createFileRoute("/commande")({
  head: () => ({ meta: [{ title: "Finaliser ma commande — Santé Ivoire" }] }),
  loader: ({ context }) => context.queryClient.ensureQueryData(communesQuery),
  component: OrderPage,
});

function OrderPage() {
  const { data: communes } = useSuspenseQuery(communesQuery);
  const { items, subtotal, clear } = useCart();
  const navigate = useNavigate();
  const createOrderFn = useServerFn(createOrder);

  const [form, setForm] = useState({ customerName: "", customerPhone: "", communeId: "", address: "" });
  const [submitting, setSubmitting] = useState(false);

  const commune = useMemo(() => communes.find((c) => c.id === form.communeId), [communes, form.communeId]);
  const deliveryFee = commune ? Number(commune.delivery_fee) : 0;
  const total = subtotal + deliveryFee;

  if (items.length === 0) {
    return (
      <section className="container mx-auto max-w-2xl px-4 py-16 text-center">
        <p className="text-muted-foreground">Votre panier est vide.</p>
        <Link to="/catalogue" className="mt-4 inline-flex rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground">Voir le catalogue</Link>
      </section>
    );
  }

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.customerName || !form.customerPhone || !form.communeId || !form.address) {
      toast.error("Veuillez remplir tous les champs obligatoires.");
      return;
    }
    setSubmitting(true);
    try {
      const result = await createOrderFn({
        data: {
          customerName: form.customerName,
          customerPhone: form.customerPhone,
          communeId: form.communeId,
          address: form.address,
          items: items.map((i) => ({ productId: i.productId, quantity: i.quantity })),
        },
      });
      // Notify admin via WhatsApp deep link
      notifyAdminInNewTab({
        order_number: result.orderNumber,
        customer_name: form.customerName,
        customer_phone: form.customerPhone,
        commune_name: commune?.name ?? "",
        address: form.address,
        subtotal,
        delivery_fee: deliveryFee,
        total: result.total,
        order_items: items.map((i) => ({ product_name: i.name, quantity: i.quantity })),
      });
      toast.success("Commande envoyée ! L'admin a été notifié.");
      clear();
      sessionStorage.setItem("last_phone", form.customerPhone);
      navigate({ to: "/suivi" });
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Erreur lors de la commande");
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <section className="container mx-auto max-w-5xl px-4 py-10">
      <h1 className="font-display text-3xl font-bold">Finaliser ma commande</h1>
      <p className="mt-1 text-sm text-success">💵 Paiement à la livraison disponible</p>

      <div className="mt-8 grid gap-8 md:grid-cols-[1.5fr_1fr]">
        <form onSubmit={submit} className="space-y-4">
          <Field label="Nom complet *">
            <input required value={form.customerName} onChange={(e) => setForm({ ...form, customerName: e.target.value })} className={inputCls} />
          </Field>
          <Field label="Numéro de téléphone *">
            <input required type="tel" value={form.customerPhone} onChange={(e) => setForm({ ...form, customerPhone: e.target.value })} placeholder="+225 07 00 00 00 00" className={inputCls} />
          </Field>
          <Field label="Commune / lieu de livraison *">
            <select required value={form.communeId} onChange={(e) => setForm({ ...form, communeId: e.target.value })} className={inputCls}>
              <option value="">— Sélectionner —</option>
              {communes.map((c) => (
                <option key={c.id} value={c.id}>{c.name} ({c.zone}) — {formatCFA(c.delivery_fee)}</option>
              ))}
            </select>
          </Field>
          <Field label="Adresse précise *">
            <textarea required rows={2} value={form.address} onChange={(e) => setForm({ ...form, address: e.target.value })} placeholder="Quartier, rue, point de repère..." className={inputCls} />
          </Field>
          <button disabled={submitting} className="w-full rounded-xl bg-accent px-6 py-4 text-base font-bold text-accent-foreground shadow-accent disabled:opacity-60">
            {submitting ? "Envoi..." : `Confirmer la commande (${formatCFA(total)})`}
          </button>
        </form>

        <aside className="h-fit rounded-2xl border border-border bg-card p-5 shadow-card">
          <h3 className="font-display text-lg font-bold">Récapitulatif</h3>
          <ul className="mt-4 space-y-2 text-sm">
            {items.map((i) => (
              <li key={i.productId} className="flex justify-between">
                <span className="text-foreground/80">{i.quantity}× {i.name}</span>
                <span className="font-semibold">{formatCFA(i.price * i.quantity)}</span>
              </li>
            ))}
          </ul>
          <div className="mt-4 space-y-1 border-t border-border pt-3 text-sm">
            <Row label="Sous-total" value={formatCFA(subtotal)} />
            <Row label="Livraison" value={commune ? formatCFA(deliveryFee) : "—"} />
            <Row label="Total" value={formatCFA(total)} bold />
          </div>
        </aside>
      </div>
    </section>
  );
}

const inputCls = "w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent";
function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return <label className="block"><span className="mb-1.5 block text-sm font-semibold">{label}</span>{children}</label>;
}
function Row({ label, value, bold }: { label: string; value: string; bold?: boolean }) {
  return <div className={`flex justify-between ${bold ? "text-base font-bold text-primary" : "text-foreground/80"}`}><span>{label}</span><span>{value}</span></div>;
}
