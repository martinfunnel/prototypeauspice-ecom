import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useState } from "react";
import { toast } from "sonner";
import { supabase } from "@/integrations/supabase/client";

export const Route = createFileRoute("/admin/login")({
  head: () => ({ meta: [{ title: "Connexion admin — Auspice Market" }] }),
  component: AdminLogin,
});

const AUSPICE_DOMAIN = "auspice.local";

function AdminLogin() {
  const navigate = useNavigate();
  const [identifier, setIdentifier] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      const id = identifier.trim();
      // If user enters an internal identifier (no @), append the auspice domain
      const email = id.includes("@") ? id : `${id.toLowerCase()}@${AUSPICE_DOMAIN}`;
      const { error } = await supabase.auth.signInWithPassword({ email, password });
      if (error) throw error;
      toast.success("Connecté !");
      navigate({ to: "/admin" });
    } catch (err) {
      toast.error(err instanceof Error ? err.message : "Identifiant ou mot de passe invalide");
    } finally {
      setLoading(false);
    }
  };

  return (
    <section className="container mx-auto max-w-md px-4 py-16">
      <div className="rounded-2xl border border-border bg-card p-8 shadow-card">
        <h1 className="font-display text-2xl font-bold">Espace administrateur</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Connectez-vous avec l'identifiant fourni par votre super administrateur.
        </p>

        <form onSubmit={submit} className="mt-6 space-y-4">
          <div>
            <label className="mb-1 block text-xs font-medium text-muted-foreground">Identifiant</label>
            <input
              required
              autoComplete="username"
              placeholder="AUS-XXXXXX"
              value={identifier}
              onChange={(e) => setIdentifier(e.target.value)}
              className="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent"
            />
          </div>
          <div>
            <label className="mb-1 block text-xs font-medium text-muted-foreground">Mot de passe</label>
            <input
              required
              type="password"
              autoComplete="current-password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-sm outline-none focus:border-accent"
            />
          </div>
          <button
            disabled={loading}
            className="w-full rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground disabled:opacity-60"
          >
            {loading ? "..." : "Se connecter"}
          </button>
        </form>

        <p className="mt-5 text-center text-xs text-muted-foreground">
          Pas d'identifiant&nbsp;? Contactez le super administrateur de Auspice Market.
        </p>
      </div>
    </section>
  );
}
