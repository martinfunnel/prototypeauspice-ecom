import { createFileRoute, notFound, useNavigate } from "@tanstack/react-router";
import { queryOptions, useSuspenseQuery } from "@tanstack/react-query";
import { useMemo, useState } from "react";
import { toast } from "sonner";
import { Minus, Plus, Check, ShieldCheck, Truck, Wallet } from "lucide-react";
import { useServerFn } from "@tanstack/react-start";
import { supabase } from "@/integrations/supabase/client";
import { formatCFA } from "@/lib/format";
import { ProductCard } from "@/components/ProductCard";
import { createOrder } from "@/lib/orders.functions";
import { notifyAdminInNewTab } from "@/lib/whatsapp";
import { ShareButtons } from "@/components/ShareButtons";
import { TestimonialsCarousel } from "@/components/TestimonialsCarousel";

const productQuery = (slug: string) =>
  queryOptions({
    queryKey: ["product", slug],
    queryFn: async () => {
      const { data: product } = await supabase.from("products").select("*").eq("slug", slug).eq("is_active", true).maybeSingle();
      if (!product) throw notFound();
      const similarQuery = supabase.from("products").select("id,name,slug,price,promo_price,images,short_description").eq("is_active", true).neq("id", product.id).limit(4);
      const { data: similar } = product.category_id ? await similarQuery.eq("category_id", product.category_id) : await similarQuery;
      const { data: communes } = await supabase.from("communes").select("id,name,zone,delivery_fee,delivery_days").eq("is_active", true).order("zone").order("name");
      return { product, similar: similar ?? [], communes: communes ?? [] };
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
  const navigate = useNavigate();
  const createOrderFn = useServerFn(createOrder);
  const price = Number(p.promo_price ?? p.price);

  const [form, setForm] = useState({ customerName: "", customerPhone: "", communeId: "", address: "" });
  const [submitting, setSubmitting] = useState(false);

  const commune = useMemo(() => data.communes.find((c) => c.id === form.communeId), [data.communes, form.communeId]);
  const subtotal = price * qty;
  const deliveryFee = commune ? Number(commune.delivery_fee) : 0;
  const total = subtotal + deliveryFee;

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
          items: [{ productId: p.id, quantity: qty }],
        },
      });
      notifyAdminInNewTab({
        order_number: result.orderNumber,
        customer_name: form.customerName,
        customer_phone: form.customerPhone,
        commune_name: commune?.name ?? "",
        address: form.address,
        subtotal,
        delivery_fee: deliveryFee,
        total: result.total,
        order_items: [{ product_name: p.name, quantity: qty }],
      });
      toast.success("Commande envoyée ! L'admin a été notifié.");
      sessionStorage.setItem("last_phone", form.customerPhone);
      navigate({ to: "/suivi" });
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Erreur lors de la commande");
    } finally {
      setSubmitting(false);
    }
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

          {Array.isArray((p as { benefits?: string[] }).benefits) && (p as { benefits?: string[] }).benefits!.length > 0 ? (
            <ul className="mt-5 space-y-2">
              {(p as { benefits: string[] }).benefits.slice(0, 4).map((b, i) => (
                <li key={i} className="flex items-start gap-2 text-sm">
                  <Check className="mt-0.5 h-4 w-4 shrink-0 text-success" />
                  <span>{b}</span>
                </li>
              ))}
            </ul>
          ) : null}

          <div className="mt-6 flex items-center gap-3">
            <span className="text-sm font-semibold">Quantité :</span>
            <div className="flex items-center rounded-lg border border-border">
              <button type="button" onClick={() => setQty(Math.max(1, qty - 1))} className="grid h-11 w-11 place-items-center hover:bg-muted"><Minus className="h-4 w-4" /></button>
              <span className="w-10 text-center font-semibold">{qty}</span>
              <button type="button" onClick={() => setQty(qty + 1)} className="grid h-11 w-11 place-items-center hover:bg-muted"><Plus className="h-4 w-4" /></button>
            </div>
          </div>

          <div className="mt-6 border-t border-border pt-5">
            <ShareButtons
              url={typeof window !== "undefined" ? window.location.href : `https://prototypeauspice-ecom.lovable.app/produit/${p.slug}`}
              title={p.name}
            />
          </div>
        </div>
      </div>

      <div className="mt-12 rounded-2xl border border-border bg-card p-6 shadow-card md:p-8">
        <h2 className="font-display text-2xl font-bold">Commander ce produit</h2>
        <p className="mt-1 text-sm text-success">💵 Paiement à la livraison</p>

        <div className="mt-6 grid gap-8 md:grid-cols-[1.5fr_1fr]">
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
                {data.communes.map((c) => (
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

          <aside className="h-fit rounded-2xl border border-border bg-background p-5">
            <h3 className="font-display text-lg font-bold">Récapitulatif</h3>
            <div className="mt-4 flex justify-between text-sm">
              <span className="text-foreground/80">{qty}× {p.name}</span>
              <span className="font-semibold">{formatCFA(subtotal)}</span>
            </div>
            <div className="mt-4 space-y-1 border-t border-border pt-3 text-sm">
              <Row label="Sous-total" value={formatCFA(subtotal)} />
              <Row label="Livraison" value={commune ? formatCFA(deliveryFee) : "—"} />
              <Row label="Total" value={formatCFA(total)} bold />
            </div>
          </aside>
        </div>
      </div>

      {/* ----- Section vente : avantages + détails + galerie ----- */}
      {(() => {
        const benefits = (p as { benefits?: string[] }).benefits ?? [];
        const gallery = (p.images ?? []).slice(1);
        const detailImgs = ((p as { detail_images?: string[] | null }).detail_images) ?? [];
        const hasContent = benefits.length > 0 || p.description || gallery.length > 0 || detailImgs.length > 0;
        if (!hasContent) return null;
        return (
          <div className="mt-16 space-y-12">
            {benefits.length > 0 ? (
              <section>
                <h2 className="font-display text-2xl font-bold md:text-3xl">Pourquoi choisir ce produit ?</h2>
                <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                  {benefits.map((b, i) => (
                    <div key={i} className="flex items-start gap-3 rounded-2xl border border-border bg-card p-5 shadow-card">
                      <div className="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-success/15 text-success">
                        <Check className="h-5 w-5" />
                      </div>
                      <p className="text-sm font-medium text-foreground/90">{b}</p>
                    </div>
                  ))}
                </div>
              </section>
            ) : null}

            {(() => {
              const detailImgs = ((p as { detail_images?: string[] | null }).detail_images) ?? [];
              if (!p.description && detailImgs.length === 0) return null;
              return (
                <section>
                  <h2 className="font-display text-2xl font-bold md:text-3xl">Détails du produit</h2>
                  <div className="mt-5 rounded-2xl border border-border bg-card p-6 md:p-8">
                    {p.description ? (
                      <p className="whitespace-pre-line text-base leading-relaxed text-foreground/80">{p.description}</p>
                    ) : null}
                    {detailImgs.length > 0 ? (
                      <div className={`grid gap-4 ${p.description ? "mt-6" : ""} sm:grid-cols-2`}>
                        {detailImgs.map((src, i) => (
                          <div key={i} className="overflow-hidden rounded-xl bg-muted">
                            <img src={src} alt={`${p.name} détail ${i + 1}`} className="h-full w-full object-cover" loading="lazy" />
                          </div>
                        ))}
                      </div>
                    ) : null}
                  </div>
                </section>
              );
            })()}

            {gallery.length > 0 ? (
              <section>
                <h2 className="font-display text-2xl font-bold md:text-3xl">En images</h2>
                <div className="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
                  {gallery.map((src, i) => (
                    <div key={i} className="aspect-square overflow-hidden rounded-xl bg-muted">
                      <img src={src} alt={`${p.name} ${i + 2}`} className="h-full w-full object-cover" loading="lazy" />
                    </div>
                  ))}
                </div>
              </section>
            ) : null}

            <section className="grid gap-4 sm:grid-cols-3">
              <Reassurance icon={<Truck className="h-5 w-5" />} title="Livraison rapide" text="Partout à Abidjan et environs" />
              <Reassurance icon={<Wallet className="h-5 w-5" />} title="Paiement à la livraison" text="Payez seulement à la réception" />
              <Reassurance icon={<ShieldCheck className="h-5 w-5" />} title="Produits vérifiés" text="Qualité contrôlée à chaque commande" />
            </section>
          </div>
        );
      })()}

      <TestimonialsCarousel heading="Ce que disent nos clients" subheading="Avis vérifiés de la communauté Auspice Market." />

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

function Reassurance({ icon, title, text }: { icon: React.ReactNode; title: string; text: string }) {
  return (
    <div className="flex items-start gap-3 rounded-2xl border border-border bg-background p-5">
      <div className="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-primary/10 text-primary">{icon}</div>
      <div>
        <p className="font-semibold">{title}</p>
        <p className="text-sm text-muted-foreground">{text}</p>
      </div>
    </div>
  );
}

const inputCls = "w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm shadow-sm outline-none focus:border-accent";
function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return <label className="block"><span className="mb-1.5 block text-sm font-semibold">{label}</span>{children}</label>;
}
function Row({ label, value, bold }: { label: string; value: string; bold?: boolean }) {
  return <div className={`flex justify-between ${bold ? "text-base font-bold text-primary" : "text-foreground/80"}`}><span>{label}</span><span>{value}</span></div>;
}
