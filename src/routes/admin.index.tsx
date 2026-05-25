import { createFileRoute, Link } from "@tanstack/react-router";

export const Route = createFileRoute("/admin/")({
  head: () => ({ meta: [{ title: "Dashboard admin — Santé Ivoire" }] }),
  component: AdminHome,
});

function AdminHome() {
  return (
    <section className="container mx-auto max-w-2xl px-4 py-16 text-center">
      <h1 className="font-display text-3xl font-bold">Dashboard administrateur</h1>
      <p className="mt-3 text-muted-foreground">
        Connectez-vous d'abord, puis l'interface complète (produits, commandes, communes, stats, PDF, WhatsApp) sera disponible dans la prochaine itération.
      </p>
      <Link to="/admin/login" className="mt-6 inline-flex rounded-md bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground">
        Aller à la connexion
      </Link>
    </section>
  );
}
