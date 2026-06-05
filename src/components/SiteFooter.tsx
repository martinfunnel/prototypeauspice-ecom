import { Link } from "@tanstack/react-router";
import { ShieldCheck, Truck, Phone, Leaf } from "lucide-react";
import logoAsset from "@/assets/auspice-logo.png.asset.json";

export function SiteFooter() {
  return (
    <footer className="mt-20 border-t border-border/60 bg-primary text-primary-foreground">
      <div className="container mx-auto grid gap-10 px-4 py-12 md:grid-cols-4">
        <div>
          <div className="flex items-center gap-2">
            <div className="grid h-10 w-10 place-items-center rounded-lg bg-white p-1">
              <img src={logoAsset.url} alt="Auspice Market" className="h-full w-full object-contain" />
            </div>
            <span className="font-display text-lg font-bold">Auspice Market</span>
          </div>
          <p className="mt-3 text-sm text-primary-foreground/70">
            <strong>Auspice SARL</strong> — Spécialiste des compléments alimentaires et produits
            de santé issus de l'agriculture biologique. Notre best-seller : le cacao à la cannelle de Ceylan.
          </p>
          <p className="mt-3 inline-flex items-center gap-1.5 rounded-full bg-accent/20 px-3 py-1 text-xs font-semibold text-accent">
            <Leaf className="h-3.5 w-3.5" /> 100% bio & naturel
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
          <span>© {new Date().getFullYear()} Auspice SARL — Auspice Market. Tous droits réservés.</span>
        </div>
      </div>
    </footer>
  );
}
