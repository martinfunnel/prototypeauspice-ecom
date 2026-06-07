import { Link, useNavigate, useRouterState } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { useServerFn } from "@tanstack/react-start";
import { useEffect, useState, type ReactNode } from "react";
import { LayoutDashboard, Package, ShoppingBag, MapPin, LogOut, ShieldCheck, Megaphone, Users, MessageSquareQuote } from "lucide-react";
import { toast } from "sonner";
import { supabase } from "@/integrations/supabase/client";
import { checkIsAdmin, claimFirstAdmin } from "@/lib/admin.functions";

const NAV: Array<{ to: string; label: string; icon: typeof LayoutDashboard; exact?: boolean }> = [
  { to: "/admin", label: "Dashboard", icon: LayoutDashboard, exact: true },
  { to: "/admin/orders", label: "Commandes", icon: ShoppingBag },
  { to: "/admin/products", label: "Produits", icon: Package },
  { to: "/admin/banner", label: "Bannière", icon: Megaphone },
  { to: "/admin/testimonials", label: "Témoignages", icon: MessageSquareQuote },
  { to: "/admin/communes", label: "Communes", icon: MapPin },
  { to: "/admin/users", label: "Utilisateurs", icon: Users },
];

export function AdminShell({ children, title }: { children: ReactNode; title: string }) {
  const navigate = useNavigate();
  const [sessionReady, setSessionReady] = useState(false);
  const [hasSession, setHasSession] = useState(false);

  useEffect(() => {
    let mounted = true;
    supabase.auth.getSession().then(({ data }) => {
      if (!mounted) return;
      setHasSession(!!data.session);
      setSessionReady(true);
    });
    const { data: sub } = supabase.auth.onAuthStateChange((_e, session) => {
      setHasSession(!!session);
    });
    return () => {
      mounted = false;
      sub.subscription.unsubscribe();
    };
  }, []);

  useEffect(() => {
    if (sessionReady && !hasSession) navigate({ to: "/admin/login" });
  }, [sessionReady, hasSession, navigate]);

  const check = useServerFn(checkIsAdmin);
  const claim = useServerFn(claimFirstAdmin);
  const { data: roleInfo, isLoading, refetch } = useQuery({
    queryKey: ["admin-role"],
    queryFn: () => check(),
    enabled: hasSession,
  });

  const pathname = useRouterState({ select: (s) => s.location.pathname });

  if (!sessionReady || (hasSession && isLoading)) {
    return (
      <div className="container mx-auto px-4 py-20 text-center text-sm text-muted-foreground">
        Chargement…
      </div>
    );
  }

  if (!hasSession) return null;

  if (!roleInfo?.isAdmin) {
    return (
      <section className="container mx-auto max-w-md px-4 py-16">
        <div className="rounded-2xl border border-border bg-card p-8 text-center shadow-card">
          <ShieldCheck className="mx-auto h-10 w-10 text-accent" />
          <h1 className="mt-3 font-display text-xl font-bold">Accès administrateur requis</h1>
          {!roleInfo?.anyAdmin ? (
            <>
              <p className="mt-2 text-sm text-muted-foreground">
                Aucun admin n'existe encore. Réclamez l'accès pour ce compte.
              </p>
              <button
                onClick={async () => {
                  try {
                    await claim();
                    toast.success("Vous êtes maintenant administrateur.");
                    refetch();
                  } catch (e) {
                    toast.error(e instanceof Error ? e.message : "Erreur");
                  }
                }}
                className="mt-5 inline-flex rounded-md bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground"
              >
                Devenir le premier administrateur
              </button>
            </>
          ) : (
            <p className="mt-2 text-sm text-muted-foreground">
              Votre compte n'a pas le rôle admin. Contactez un administrateur existant.
            </p>
          )}
          <button
            onClick={async () => {
              await supabase.auth.signOut();
              navigate({ to: "/admin/login" });
            }}
            className="mt-4 text-xs text-muted-foreground hover:text-accent"
          >
            Se déconnecter
          </button>
        </div>
      </section>
    );
  }

  return (
    <div className="container mx-auto grid gap-6 px-4 py-6 lg:grid-cols-[220px_1fr]">
      <aside className="lg:sticky lg:top-20 lg:self-start">
        <div className="rounded-2xl border border-border bg-card p-3 shadow-card">
          <div className="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
            Administration
          </div>
          <nav className="flex flex-col gap-1">
            {NAV.map((n) => {
              const active = n.exact ? pathname === n.to : pathname.startsWith(n.to);
              return (
                <Link
                  key={n.to}
                  to={n.to as "/admin"}
                  className={`flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition ${
                    active
                      ? "bg-primary text-primary-foreground"
                      : "text-foreground/80 hover:bg-muted"
                  }`}
                >
                  <n.icon className="h-4 w-4" />
                  {n.label}
                </Link>
              );
            })}
          </nav>
          <button
            onClick={async () => {
              await supabase.auth.signOut();
              navigate({ to: "/admin/login" });
            }}
            className="mt-3 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-foreground/70 hover:bg-muted"
          >
            <LogOut className="h-4 w-4" /> Déconnexion
          </button>
        </div>
      </aside>

      <section>
        <h1 className="mb-4 font-display text-2xl font-bold">{title}</h1>
        {children}
      </section>
    </div>
  );
}
