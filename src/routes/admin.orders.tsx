import { createFileRoute, Link } from "@tanstack/react-router";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useServerFn } from "@tanstack/react-start";
import { useMemo, useState } from "react";
import { toast } from "sonner";
import { FileDown, Printer, MessageCircle, Search, Plus } from "lucide-react";
import { AdminShell } from "@/components/AdminShell";
import { listOrders, updateOrderStatus } from "@/lib/admin.functions";
import { formatCFA, formatDateTime, ORDER_STATUS_LABELS } from "@/lib/format";
import { StatusBadge } from "@/components/StatusBadge";
import { downloadLabel, printLabel } from "@/lib/pdf-label";
import { whatsappAdminLink } from "@/lib/whatsapp";

export const Route = createFileRoute("/admin/orders")({
  head: () => ({ meta: [{ title: "Commandes — Admin" }] }),
  component: AdminOrders,
});

const STATUSES = Object.keys(ORDER_STATUS_LABELS);

function AdminOrders() {
  const qc = useQueryClient();
  const list = useServerFn(listOrders);
  const update = useServerFn(updateOrderStatus);

  const { data: orders, isLoading } = useQuery({
    queryKey: ["admin-orders"],
    queryFn: () => list(),
  });

  const m = useMutation({
    mutationFn: (v: { id: string; status: string }) =>
      update({ data: { id: v.id, status: v.status as "pending" } }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["admin-orders"] });
      qc.invalidateQueries({ queryKey: ["admin-stats"] });
      toast.success("Statut mis à jour");
    },
    onError: (e) => toast.error(e instanceof Error ? e.message : "Erreur"),
  });

  const [q, setQ] = useState("");
  const [filter, setFilter] = useState<string>("all");

  const filtered = useMemo(() => {
    let arr = orders ?? [];
    if (filter !== "all") arr = arr.filter((o) => o.status === filter);
    if (q.trim()) {
      const s = q.trim().toLowerCase();
      arr = arr.filter(
        (o) =>
          o.order_number.toLowerCase().includes(s) ||
          o.customer_name.toLowerCase().includes(s) ||
          o.customer_phone.toLowerCase().includes(s),
      );
    }
    return arr;
  }, [orders, q, filter]);

  return (
    <AdminShell title="Commandes">
      <div className="mb-4 flex flex-wrap items-center gap-2">
        <div className="relative flex-1 min-w-[200px]">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <input
            value={q}
            onChange={(e) => setQ(e.target.value)}
            placeholder="N° commande, client, téléphone…"
            className="w-full rounded-lg border border-border bg-background pl-9 pr-3 py-2 text-sm outline-none focus:border-accent"
          />
        </div>
        <select
          value={filter}
          onChange={(e) => setFilter(e.target.value)}
          className="rounded-lg border border-border bg-background px-3 py-2 text-sm"
        >
          <option value="all">Tous les statuts</option>
          {STATUSES.map((s) => (
            <option key={s} value={s}>
              {ORDER_STATUS_LABELS[s].label}
            </option>
          ))}
        </select>
      </div>

      {isLoading ? (
        <div className="text-sm text-muted-foreground">Chargement…</div>
      ) : filtered.length === 0 ? (
        <div className="rounded-xl border border-dashed border-border bg-card p-10 text-center text-sm text-muted-foreground">
          Aucune commande.
        </div>
      ) : (
        <div className="space-y-3">
          {filtered.map((o) => (
            <details
              key={o.id}
              className="group rounded-2xl border border-border bg-card shadow-card"
            >
              <summary className="flex cursor-pointer items-center justify-between gap-3 p-4 [&::-webkit-details-marker]:hidden">
                <div className="min-w-0 flex-1">
                  <div className="flex flex-wrap items-center gap-2">
                    <span className="font-mono text-sm font-bold text-primary">{o.order_number}</span>
                    <StatusBadge status={o.status} />
                    <span className="text-xs text-muted-foreground">
                      {formatDateTime(o.created_at)}
                    </span>
                  </div>
                  <div className="mt-1 truncate text-sm">
                    <span className="font-semibold">{o.customer_name}</span>{" "}
                    <span className="text-muted-foreground">· {o.customer_phone} · {o.commune_name}</span>
                  </div>
                </div>
                <div className="text-right">
                  <div className="font-display text-base font-bold">{formatCFA(o.total)}</div>
                  <div className="text-[10px] uppercase text-muted-foreground">
                    {o.order_items?.length ?? 0} article(s)
                  </div>
                </div>
              </summary>

              <div className="grid gap-4 border-t border-border p-4 md:grid-cols-2">
                <div>
                  <div className="text-xs font-semibold uppercase text-muted-foreground">Adresse</div>
                  <p className="mt-1 text-sm">{o.address}</p>
                  {o.notes ? (
                    <p className="mt-2 rounded bg-muted/60 p-2 text-xs italic">{o.notes}</p>
                  ) : null}

                  <div className="mt-4 text-xs font-semibold uppercase text-muted-foreground">
                    Articles
                  </div>
                  <ul className="mt-1 space-y-1 text-sm">
                    {o.order_items?.map((i, idx) => (
                      <li key={idx} className="flex justify-between gap-2">
                        <span>
                          {i.quantity}× {i.product_name}
                        </span>
                        <span className="font-mono text-muted-foreground">
                          {formatCFA(i.subtotal)}
                        </span>
                      </li>
                    ))}
                  </ul>
                  <div className="mt-3 border-t border-border pt-2 text-sm">
                    <div className="flex justify-between text-muted-foreground">
                      <span>Sous-total</span>
                      <span>{formatCFA(o.subtotal)}</span>
                    </div>
                    <div className="flex justify-between text-muted-foreground">
                      <span>Livraison</span>
                      <span>{formatCFA(o.delivery_fee)}</span>
                    </div>
                    <div className="mt-1 flex justify-between font-bold">
                      <span>Total</span>
                      <span>{formatCFA(o.total)}</span>
                    </div>
                  </div>
                </div>

                <div className="space-y-3">
                  <div>
                    <div className="text-xs font-semibold uppercase text-muted-foreground">
                      Statut
                    </div>
                    <select
                      value={o.status}
                      onChange={(e) => m.mutate({ id: o.id, status: e.target.value })}
                      className="mt-1 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm"
                    >
                      {STATUSES.map((s) => (
                        <option key={s} value={s}>
                          {ORDER_STATUS_LABELS[s].label}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="grid grid-cols-2 gap-2">
                    <button
                      onClick={() => downloadLabel(o)}
                      className="flex items-center justify-center gap-2 rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-muted"
                    >
                      <FileDown className="h-4 w-4" /> PDF
                    </button>
                    <button
                      onClick={() => printLabel(o)}
                      className="flex items-center justify-center gap-2 rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-muted"
                    >
                      <Printer className="h-4 w-4" /> Imprimer
                    </button>
                    <a
                      href={`https://wa.me/${o.customer_phone.replace(/[^0-9]/g, "")}`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="flex items-center justify-center gap-2 rounded-lg bg-success/15 px-3 py-2 text-sm font-medium text-success hover:bg-success/25"
                    >
                      <MessageCircle className="h-4 w-4" /> Client
                    </a>
                    <a
                      href={whatsappAdminLink(o)}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="flex items-center justify-center gap-2 rounded-lg bg-accent/15 px-3 py-2 text-sm font-medium text-accent hover:bg-accent/25"
                    >
                      <MessageCircle className="h-4 w-4" /> Récap
                    </a>
                  </div>
                </div>
              </div>
            </details>
          ))}
        </div>
      )}
    </AdminShell>
  );
}
