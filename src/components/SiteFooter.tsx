import { Link } from "@tanstack/react-router";
import { ShieldCheck, Truck, Phone } from "lucide-react";

export function SiteFooter() {
  return (
    <footer className="mt-20 border-t border-border/60 bg-primary text-primary-foreground">
      <div className="container mx-auto grid gap-10 px-4 py-12 md:grid-cols-4">
        <div>
          <div className="flex items-center gap-2">
            <div className="grid h-9 w-9 place-items-center rounded-lg gradient-accent font-display font-bold text-accent-foreground">
              S
            </div>
            <span className="font-display text-lg font-bold">Santé Ivoire</span>
          </div>
          <p className="mt-3 text-sm text-primary-foreground/70">
            Produits de santé et compléments alimentaires livrés partout en Côte d'Ivoire.
            Paiement à la livraison.
          </p>
        </div>
        <div>
          <h4 className="mb-3 text-sm font-semibold uppercase tracking-wider text-primary-foreground/60">
            Boutique
          </h4>
          <ul className="space-y-2 text-sm">
            <li><Link to="/catalogue" className="hover:text-accent">Tous les produits</Link></li>
            <li><Link to="/suivi" className="hover:text-accent">Suivre ma commande</Link></li>
            <li><Link to="/panier" className="hover:text-accent">Mon panier</Link></li>
          </ul>
        </div>
        <div>
          <h4 className="mb-3 text-sm font-semibold uppercase tracking-wider text-primary-foreground/60">
            Garanties
          </h4>
          <ul className="space-y-3 text-sm text-primary-foreground/80">
            <li className="flex items-start gap-2"><Truck className="mt-0.5 h-4 w-4 text-accent" /> Livraison rapide à Abidjan & intérieur</li>
            <li className="flex items-start gap-2"><ShieldCheck className="mt-0.5 h-4 w-4 text-accent" /> Produits authentiques</li>
            <li className="flex items-start gap-2"><Phone className="mt-0.5 h-4 w-4 text-accent" /> Paiement à la livraison</li>
          </ul>
        </div>
        <div>
          <h4 className="mb-3 text-sm font-semibold uppercase tracking-wider text-primary-foreground/60">
            Contact
          </h4>
          <ul className="space-y-2 text-sm text-primary-foreground/80">
            <li>📞 +225 07 11 75 13 25</li>
            <li>📍 Abidjan, Côte d'Ivoire</li>
            <li>
              <a
                href="https://wa.me/2250711751325"
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-1 rounded-md bg-accent px-3 py-1.5 text-xs font-semibold text-accent-foreground hover:opacity-90"
              >
                WhatsApp
              </a>
            </li>
          </ul>
        </div>
      </div>
      <div className="border-t border-primary-foreground/10">
        <div className="container mx-auto px-4 py-4 text-center text-xs text-primary-foreground/60">
          <span>© {new Date().getFullYear()} Santé Ivoire. Tous droits réservés.</span>
        </div>
      </div>
    </footer>
  );
}
