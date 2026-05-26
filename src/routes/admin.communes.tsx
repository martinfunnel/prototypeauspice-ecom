import { createFileRoute } from "@tanstack/react-router";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useServerFn } from "@tanstack/react-start";
import { useState } from "react";
import { toast } from "sonner";
import { Plus, Pencil, Trash2, X } from "lucide-react";
import { AdminShell } from "@/components/AdminShell";
import { listCommunesAdmin, upsertCommune, deleteCommune } from "@/lib/admin.functions";
import { formatCFA } from "@/lib/format";

export const Route = createFileRoute("/admin/communes")({
  head: () => ({ meta: [{ title: "Communes — Admin" }] }),
  component: AdminCommunes,
});

type Form = {
  id?: string | null;
  name: string;
  zone: string;
  delivery_fee: string;
  delivery_days: string;
  is_active: boolean;
};
const empty: Form = { id: null, name: "", zone: "Abidjan", delivery_fee: "1000", delivery_days: "1", is_active: true };

function AdminCommunes() {
  const qc = useQueryClient();
  const list = useServerFn(listCommunesAdmin);
  const upsert = useServerFn(upsertCommune);
  const del = useServerFn(deleteCommune);

  const { data: communes, isLoading } = useQuery({
    queryKey: ["admin-communes"],
    queryFn: () => list(),
  });

  const [open, setOpen] = useState(false);
  const [form, setForm] = useState<Form>(empty);

  const save = useMutation({
    mutationFn: () =>
      upsert({
        data: {
          id: form.id ?? undefined,
          name: form.name,
          zone: form.zone,
          delivery_fee: Number(form.delivery_fee),
          delivery_days: Number(form.delivery_days),
          is_active: form.is_active,
        },
      }),
    onSuccess: () => {
      toast.success("Commune enregistrée");
      qc.invalidateQueries({ queryKey: ["admin-communes"] });
      setOpen(false);
    },
    onError: (e) => toast.error(e instanceof Error ? e.message : "Erreur"),
  });

  const remove = useMutation({
    mutationFn: (id: string) => del({ data: { id } }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["admin-communes"] });
      toast.success("Commune supprimée");
    },
    onError: (e) => toast.error(e instanceof Error ? e.message : "Erreur"),
  });

  return (
    <AdminShell title="Communes & livraison">
      <div className="mb-4 flex justify-end">
        <button
          onClick={() => {
            setForm(empty);
            setOpen(true);
          }}
          className="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground"
        >
          <Plus className="h-4 w-4" /> Nouvelle commune
        </button>
      </div>

      {isLoading ? (
        <div className="text-sm text-muted-foreground">Chargement…</div>
      ) : (
        <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-card">
          <ul className="divide-y divide-border">
            {communes?.map((c) => (
              <li key={c.id} className="flex flex-wrap items-center gap-3 px-4 py-3">
                <div className="min-w-0 flex-1">
                  <div className="flex items-center gap-2">
                    <span className="font-semibold">{c.name}</span>
                    <span className="rounded-full bg-muted px-2 py-0.5 text-[10px] uppercase text-muted-foreground">
                      {c.zone}
                    </span>
                    {!c.is_active ? (
                      <span className="rounded-full bg-destructive/15 px-2 py-0.5 text-[10px] uppercase text-destructive">
                        Désactivée
                      </span>
                    ) : null}
                  </div>
                  <div className="text-xs text-muted-foreground">
                    {formatCFA(c.delivery_fee)} · Livraison {c.delivery_days}j
                  </div>
                </div>
                <button
                  onClick={() => {
                    setForm({
                      id: c.id,
                      name: c.name,
                      zone: c.zone,
                      delivery_fee: String(c.delivery_fee),
                      delivery_days: String(c.delivery_days),
                      is_active: c.is_active,
                    });
                    setOpen(true);
                  }}
                  className="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted"
                >
                  <Pencil className="h-4 w-4" />
                </button>
                <button
                  onClick={() => {
                    if (confirm(`Supprimer "${c.name}" ?`)) remove.mutate(c.id);
                  }}
                  className="grid h-9 w-9 place-items-center rounded-lg text-destructive hover:bg-destructive/10"
                >
                  <Trash2 className="h-4 w-4" />
                </button>
              </li>
            ))}
          </ul>
        </div>
      )}

      {open ? (
        <div className="fixed inset-0 z-50 flex items-end justify-center bg-black/60 p-0 sm:items-center sm:p-4">
          <div className="w-full max-w-md rounded-t-2xl bg-background p-5 shadow-xl sm:rounded-2xl">
            <div className="mb-4 flex items-center justify-between">
              <h2 className="font-display text-lg font-bold">
                {form.id ? "Modifier la commune" : "Nouvelle commune"}
              </h2>
              <button
                onClick={() => setOpen(false)}
                className="grid h-9 w-9 place-items-center rounded-lg hover:bg-muted"
              >
                <X className="h-4 w-4" />
              </button>
            </div>
            <form
              onSubmit={(e) => {
                e.preventDefault();
                save.mutate();
              }}
              className="space-y-3"
            >
              <L label="Nom *">
                <input
                  required
                  value={form.name}
                  onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
                  className={ip}
                />
              </L>
              <L label="Zone">
                <input
                  value={form.zone}
                  onChange={(e) => setForm((f) => ({ ...f, zone: e.target.value }))}
                  className={ip}
                />
              </L>
              <div className="grid grid-cols-2 gap-3">
                <L label="Frais livraison (FCFA)">
                  <input
                    type="number"
                    min={0}
                    value={form.delivery_fee}
                    onChange={(e) => setForm((f) => ({ ...f, delivery_fee: e.target.value }))}
                    className={ip}
                  />
                </L>
                <L label="Délai (jours)">
                  <input
                    type="number"
                    min={0}
                    value={form.delivery_days}
                    onChange={(e) => setForm((f) => ({ ...f, delivery_days: e.target.value }))}
                    className={ip}
                  />
                </L>
              </div>
              <label className="flex items-center gap-2 text-sm">
                <input
                  type="checkbox"
                  checked={form.is_active}
                  onChange={(e) => setForm((f) => ({ ...f, is_active: e.target.checked }))}
                />
                Active (proposée au checkout)
              </label>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setOpen(false)}
                  className="rounded-lg border border-border px-4 py-2 text-sm"
                >
                  Annuler
                </button>
                <button
                  disabled={save.isPending}
                  className="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground disabled:opacity-60"
                >
                  {save.isPending ? "..." : "Enregistrer"}
                </button>
              </div>
            </form>
          </div>
        </div>
      ) : null}
    </AdminShell>
  );
}

const ip = "w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent";
function L({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <label className="block">
      <span className="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">
        {label}
      </span>
      {children}
    </label>
  );
}
