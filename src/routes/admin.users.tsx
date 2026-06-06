import { createFileRoute } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { useServerFn } from "@tanstack/react-start";
import { useState } from "react";
import { toast } from "sonner";
import { ShieldCheck, UserCog } from "lucide-react";
import { AdminShell } from "@/components/AdminShell";
import { listUsersWithRoles, setUserRole } from "@/lib/admin.functions";

export const Route = createFileRoute("/admin/users")({
  head: () => ({ meta: [{ title: "Utilisateurs — Admin" }] }),
  component: AdminUsers,
});

const ROLES = [
  { key: "admin", label: "Admin", desc: "Accès complet" },
  { key: "vendeur", label: "Vendeur", desc: "Gestion commandes & produits" },
  { key: "comptable", label: "Comptable", desc: "Suivi financier" },
] as const;

function AdminUsers() {
  const listFn = useServerFn(listUsersWithRoles);
  const setFn = useServerFn(setUserRole);
  const { data: users, isLoading, refetch } = useQuery({
    queryKey: ["admin-users"],
    queryFn: () => listFn(),
  });
  const [q, setQ] = useState("");
  const [busy, setBusy] = useState<string | null>(null);

  const toggle = async (userId: string, role: "admin" | "vendeur" | "comptable", grant: boolean) => {
    setBusy(`${userId}-${role}`);
    try {
      await setFn({ data: { user_id: userId, role, grant } });
      toast.success(grant ? "Rôle attribué" : "Rôle retiré");
      refetch();
    } catch (e) {
      toast.error(e instanceof Error ? e.message : "Erreur");
    } finally {
      setBusy(null);
    }
  };

  const filtered = (users ?? []).filter(
    (u) => !q || u.email.toLowerCase().includes(q.toLowerCase()) || (u.full_name ?? "").toLowerCase().includes(q.toLowerCase()),
  );

  return (
    <AdminShell title="Gestion des utilisateurs">
      <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <input
          value={q}
          onChange={(e) => setQ(e.target.value)}
          placeholder="Rechercher par email ou nom…"
          className="w-full rounded-lg border border-border bg-card px-4 py-2 text-sm shadow-sm outline-none focus:border-accent sm:max-w-sm"
        />
        <p className="text-xs text-muted-foreground">{filtered.length} utilisateur(s)</p>
      </div>

      {isLoading ? (
        <p className="text-sm text-muted-foreground">Chargement…</p>
      ) : filtered.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-border p-10 text-center text-sm text-muted-foreground">
          Aucun utilisateur trouvé.
        </div>
      ) : (
        <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-card">
          <table className="w-full text-sm">
            <thead className="bg-muted/50 text-xs uppercase text-muted-foreground">
              <tr>
                <th className="px-4 py-3 text-left">Utilisateur</th>
                {ROLES.map((r) => (
                  <th key={r.key} className="px-4 py-3 text-center">{r.label}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {filtered.map((u) => (
                <tr key={u.id} className="border-t border-border">
                  <td className="px-4 py-3">
                    <div className="flex items-start gap-2">
                      <UserCog className="mt-0.5 h-4 w-4 text-muted-foreground" />
                      <div>
                        <div className="font-medium">{u.full_name || "—"}</div>
                        <div className="text-xs text-muted-foreground">{u.email}</div>
                      </div>
                    </div>
                  </td>
                  {ROLES.map((r) => {
                    const has = u.roles.includes(r.key);
                    const key = `${u.id}-${r.key}`;
                    return (
                      <td key={r.key} className="px-4 py-3 text-center">
                        <button
                          onClick={() => toggle(u.id, r.key, !has)}
                          disabled={busy === key}
                          className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition disabled:opacity-50 ${
                            has
                              ? "bg-accent text-accent-foreground"
                              : "border border-border bg-background text-foreground/70 hover:border-accent"
                          }`}
                          title={r.desc}
                        >
                          {has ? <ShieldCheck className="h-3 w-3" /> : null}
                          {has ? "Attribué" : "Attribuer"}
                        </button>
                      </td>
                    );
                  })}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <p className="mt-4 text-xs text-muted-foreground">
        Les utilisateurs créent leur compte via la page de connexion admin, puis vous pouvez leur attribuer un rôle ici.
      </p>
    </AdminShell>
  );
}
