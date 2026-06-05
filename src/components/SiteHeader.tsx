import { Link } from "@tanstack/react-router";
import { ShoppingCart, Search, Package, Menu, X, LogIn } from "lucide-react";
import { useState } from "react";
import { useCart } from "@/lib/cart";
import logoAsset from "@/assets/auspice-logo.png.asset.json";

export function SiteHeader() {
  const { itemCount } = useCart();
  const [open, setOpen] = useState(false);

  const nav = [
    { to: "/", label: "Accueil" },
    { to: "/catalogue", label: "Boutique" },
    { to: "/suivi", label: "Mes commandes" },
  ];

  return (
    <header className="sticky top-0 z-40 w-full border-b border-border/60 bg-background/80 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="container mx-auto flex h-16 items-center justify-between gap-4 px-4">
        <Link to="/" className="flex items-center gap-2">
          <img src={logoAsset.url} alt="Auspice Market — Bien-être bio" className="h-10 w-10 object-contain" />
          <div className="flex flex-col leading-tight">
            <span className="font-display text-base font-bold">Auspice Market</span>
            <span className="text-[10px] uppercase tracking-wider text-muted-foreground">
              Santé & bien-être bio
            </span>
          </div>
        </Link>

        <nav className="hidden items-center gap-1 md:flex">
          {nav.map((n) => (
            <Link
              key={n.to}
              to={n.to}
              className="rounded-md px-4 py-2 text-sm font-medium text-foreground/80 transition hover:bg-muted hover:text-foreground"
              activeProps={{ className: "text-foreground bg-muted" }}
            >
              {n.label}
            </Link>
          ))}
        </nav>

        <div className="flex items-center gap-2">
          <Link
            to="/catalogue"
            className="hidden h-10 w-10 place-items-center rounded-md text-foreground/70 transition hover:bg-muted hover:text-foreground sm:grid"
            aria-label="Rechercher"
          >
            <Search className="h-5 w-5" />
          </Link>
          <Link
            to="/panier"
            className="relative grid h-10 w-10 place-items-center rounded-md text-foreground/80 transition hover:bg-muted"
            aria-label="Panier"
          >
            <ShoppingCart className="h-5 w-5" />
            {itemCount > 0 ? (
              <span className="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-accent px-1 text-[11px] font-bold text-accent-foreground">
                {itemCount}
              </span>
            ) : null}
          </Link>
          <Link
            to="/admin/login"
            className="hidden h-10 items-center gap-1.5 rounded-md border border-border bg-card px-3 text-xs font-semibold text-foreground/80 transition hover:border-accent hover:text-accent sm:inline-flex"
            aria-label="Espace administrateur"
            title="Espace administrateur"
          >
            <LogIn className="h-4 w-4" /> Admin
          </Link>
          <Link
            to="/admin/login"
            className="grid h-10 w-10 place-items-center rounded-md text-foreground/80 transition hover:bg-muted sm:hidden"
            aria-label="Espace administrateur"
          >
            <LogIn className="h-5 w-5" />
          </Link>
          <button
            className="grid h-10 w-10 place-items-center rounded-md text-foreground/80 hover:bg-muted md:hidden"
            onClick={() => setOpen((s) => !s)}
            aria-label="Menu"
          >
            {open ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
          </button>
        </div>
      </div>

      {open ? (
        <div className="border-t border-border/60 bg-background md:hidden">
          <nav className="container mx-auto flex flex-col gap-1 px-4 py-3">
            {nav.map((n) => (
              <Link
                key={n.to}
                to={n.to}
                onClick={() => setOpen(false)}
                className="rounded-md px-3 py-2 text-sm font-medium text-foreground/80 hover:bg-muted"
                activeProps={{ className: "text-foreground bg-muted" }}
              >
                {n.label}
              </Link>
            ))}
            <Link
              to="/suivi"
              onClick={() => setOpen(false)}
              className="mt-1 flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-accent"
            >
              <Package className="h-4 w-4" /> Suivre une commande
            </Link>
          </nav>
        </div>
      ) : null}
    </header>
  );
}
