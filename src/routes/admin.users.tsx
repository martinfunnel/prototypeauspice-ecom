import { createFileRoute } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { useServerFn } from "@tanstack/react-start";
import { useState } from "react";
import { toast } from "sonner";
import { ShieldCheck, UserCog, UserPlus, Trash2, Copy, X } from "lucide-react";
import { AdminShell } from "@/components/AdminShell";
import {
  listUsersWithRoles,
  setUserRole,
  createStaffUser,
  deleteStaffUser,
  checkIsAdmin,
} from "@/lib/admin.functions";

export const Route = createFileRoute("/admin/users")({
  head: () => ({ meta: [{ title: "Utilisateurs — Admin" }] }),
  component: AdminUsers,
});

const ROLES = [
  { key: "super_admin", label: "Super Admin", desc: "Crée les comptes & gère les rôles" },
  { key: "admin", label: "Admin", desc: "Accès complet à la gestion" },
  { key: "vendeur", label: "Vendeur", desc: "Gestion commandes & produits" },
  { key: "comptable", label: "Comptable", desc: "Suivi financier" },
] as const;

type Role = (typeof ROLES)[number]["key"];

function AdminUsers() {
  const listFn = useServerFn(listUsersWithRoles);
  const setFn = useServerFn(setUserRole);
  const createFn = useServerFn(createStaffUser);
  const deleteFn = useServerFn(deleteStaffUser);
  const checkFn = useServerFn(checkIsAdmin);

  const { data: me } = useQuery({ queryKey: ["admin-role"], queryFn: () => checkFn() });
  const isSuper = !!me?.isSuperAdmin;

  const { data: users, isLoading, refetch } = useQuery({
    queryKey: ["admin-users"],
    queryFn: () => listFn(),
  });

  const [q, setQ] = useState("");
  const [busy, setBusy] = useState<string | null>(null);
  const [showCreate, setShowCreate] = useState(false);
  const [newName, setNewName] = useState("");
  const [newRole, setNewRole] = useState<Role>("vendeur");
  const [creating, setCreating] = useState(false);
  const [credentials, setCredentials] = useState<{ identifier: string; password: string } | null>(null);

  const toggle = async (userId: string, role: Role, grant: boolean) => {
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

  const submitCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    setCreating(true);
    try {
      const res = await createFn({ data: { full_name: newName.trim() || undefined, role: newRole } });
      setCredentials({ identifier: res.identifier, password: res.password });
      setShowCreate(false);
      setNewName("");
      setNewRole("vendeur");
      refetch();
    } catch (e) {
      toast.error(e instanceof Error ? e.message : "Erreur");
    } finally {
      setCreating(false);
    }
  };

  const removeUser = async (userId: string) => {
    if (!confirm("Supprimer définitivement ce compte ?")) return;
    setBusy(`del-${userId}`);
    try {
      await deleteFn({ data: { user_id: userId } });
      toast.success("Compte supprimé");
      refetch();
    } catch (e) {
      toast.error(e instanceof Error ? e.message : "Erreur");
    } finally {
      setBusy(null);
    }
  };

  const filtered = (users ?? []).filter(
    (u) =>
      !q ||
      u.identifier.toLowerCase().includes(q.toLowerCase()) ||
      (u.full_name ?? "").toLowerCase().includes(q.toLowerCase()),
  );

  return (
    <AdminShell title="Gestion des utilisateurs">
      <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <input
          value={q}
          onChange={(e) => setQ(e.target.value)}
          placeholder="Rechercher par identifiant ou nom…"
          className="w-full rounded-lg border border-border bg-card px-4 py-2 text-sm shadow-sm outline-none focus:border-accent sm:max-w-sm"
        />
        <div className="flex items-center gap-3">
          <span className="text-xs text-muted-foreground">{filtered.length} utilisateur(s)</span>
          {isSuper && (
            <button
              onClick={() => setShowCreate(true)}
              className="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground"
            >
              <UserPlus className="h-4 w-4" /> Nouveau compte
            </button>
          )}
        </div>
      </div>

      {!isSuper && (
        <p className="mb-3 rounded-lg border border-border bg-muted/40 p-3 text-xs text-muted-foreground">
          Seul un <strong>Super Administrateur</strong> peut créer des comptes et attribuer des rôles.
        </p>
      )}

      {isLoading ? (
        <p className="text-sm text-muted-foreground">Chargement…</p>
      ) : filtered.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-border p-10 text-center text-sm text-muted-foreground">
          Aucun utilisateur trouvé.
        </div>
      ) : (
        <div className="overflow-x-auto rounded-2xl border border-border bg-card shadow-card">
          <table className="w-full text-sm">
            <thead className="bg-muted/50 text-xs uppercase text-muted-foreground">
              <tr>
                <th className="px-4 py-3 text-left">Utilisateur</th>
                {ROLES.map((r) => (
                  <th key={r.key} className="px-4 py-3 text-center">{r.label}</th>
                ))}
                {isSuper && <th className="px-4 py-3"></th>}
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
                        <div className="font-mono text-xs text-muted-foreground">{u.identifier}</div>
                      </div>
                    </div>
                  </td>
                  {ROLES.map((r) => {
                    const has = u.roles.includes(r.key);
                    const key = `${u.id}-${r.key}`;
                    return (
                      <td key={r.key} className="px-4 py-3 text-center">
                        <button
                          onClick={() => isSuper && toggle(u.id, r.key, !has)}
                          disabled={!isSuper || busy === key}
                          className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition disabled:opacity-50 ${
                            has
                              ? "bg-accent text-accent-foreground"
                              : "border border-border bg-background text-foreground/70 hover:border-accent"
                          }`}
                          title={r.desc}
                        >
                          {has ? <ShieldCheck className="h-3 w-3" /> : null}
                          {has ? "Attribué" : isSuper ? "Attribuer" : "—"}
                        </button>
                      </td>
                    );
                  })}
                  {isSuper && (
                    <td className="px-4 py-3 text-right">
                      <button
                        onClick={() => removeUser(u.id)}
                        disabled={busy === `del-${u.id}`}
                        className="rounded-lg p-2 text-destructive hover:bg-destructive/10 disabled:opacity-50"
                        title="Supprimer le compte"
                      >
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </td>
                  )}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {/* Create modal */}
      {showCreate && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onClick={() => setShowCreate(false)}>
          <form
            onClick={(e) => e.stopPropagation()}
            onSubmit={submitCreate}
            className="w-full max-w-md rounded-2xl border border-border bg-card p-6 shadow-card"
          >
            <div className="mb-4 flex items-center justify-between">
              <h2 className="font-display text-lg font-bold">Nouveau compte</h2>
              <button type="button" onClick={() => setShowCreate(false)}><X className="h-4 w-4" /></button>
            </div>
            <p className="mb-4 text-xs text-muted-foreground">
              Le système génère automatiquement un identifiant et un mot de passe à transmettre à l'utilisateur.
            </p>
            <div className="space-y-3">
              <div>
                <label className="mb-1 block text-xs font-medium">Nom complet (optionnel)</label>
                <input
                  value={newName}
                  onChange={(e) => setNewName(e.target.value)}
                  placeholder="Ex. Jean Kouassi"
                  className="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent"
                />
              </div>
              <div>
                <label className="mb-1 block text-xs font-medium">Rôle initial</label>
                <select
                  value={newRole}
                  onChange={(e) => setNewRole(e.target.value as Role)}
                  className="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm"
                >
                  {ROLES.map((r) => (
                    <option key={r.key} value={r.key}>{r.label} — {r.desc}</option>
                  ))}
                </select>
              </div>
            </div>
            <button
              type="submit"
              disabled={creating}
              className="mt-5 w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground disabled:opacity-60"
            >
              {creating ? "Création…" : "Créer le compte"}
            </button>
          </form>
        </div>
      )}

      {/* Credentials modal — shown once */}
      {credentials && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
          <div className="w-full max-w-md rounded-2xl border border-border bg-card p-6 shadow-card">
            <h2 className="font-display text-lg font-bold text-success">Compte créé ✓</h2>
            <p className="mt-1 text-xs text-muted-foreground">
              Notez ou copiez ces identifiants <strong>maintenant</strong>. Le mot de passe ne sera plus affiché.
            </p>
            <div className="mt-4 space-y-3">
              <div className="rounded-lg border border-border bg-muted/40 p-3">
                <div className="text-[10px] uppercase text-muted-foreground">Identifiant</div>
                <div className="flex items-center justify-between gap-2">
                  <code className="font-mono text-base font-bold">{credentials.identifier}</code>
                  <button
                    onClick={() => { navigator.clipboard.writeText(credentials.identifier); toast.success("Copié"); }}
                    className="rounded p-1.5 hover:bg-muted"
                  >
                    <Copy className="h-3.5 w-3.5" />
                  </button>
                </div>
              </div>
              <div className="rounded-lg border border-border bg-muted/40 p-3">
                <div className="text-[10px] uppercase text-muted-foreground">Mot de passe</div>
                <div className="flex items-center justify-between gap-2">
                  <code className="font-mono text-base font-bold">{credentials.password}</code>
                  <button
                    onClick={() => { navigator.clipboard.writeText(credentials.password); toast.success("Copié"); }}
                    className="rounded p-1.5 hover:bg-muted"
                  >
                    <Copy className="h-3.5 w-3.5" />
                  </button>
                </div>
              </div>
            </div>
            <button
              onClick={() => setCredentials(null)}
              className="mt-5 w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground"
            >
              J'ai noté, fermer
            </button>
          </div>
        </div>
      )}
    </AdminShell>
  );
}
