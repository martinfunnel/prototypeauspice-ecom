export function formatCFA(amount: number | string | null | undefined): string {
  const n = Number(amount ?? 0);
  return new Intl.NumberFormat("fr-FR").format(n) + " FCFA";
}

export function formatDate(d: string | Date): string {
  return new Date(d).toLocaleDateString("fr-FR", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
}

export function formatDateTime(d: string | Date): string {
  return new Date(d).toLocaleString("fr-FR", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

export const ORDER_STATUS_LABELS: Record<string, { label: string; color: string }> = {
  pending: { label: "En attente", color: "bg-warning/15 text-warning-foreground border-warning/30" },
  confirmed: { label: "Confirmée", color: "bg-teal/15 text-teal border-teal/30" },
  processing: { label: "En cours", color: "bg-teal/15 text-teal border-teal/30" },
  shipped: { label: "Expédiée", color: "bg-primary/10 text-primary border-primary/30" },
  delivered: { label: "Livrée", color: "bg-success/15 text-success border-success/30" },
  cancelled: { label: "Annulée", color: "bg-destructive/15 text-destructive border-destructive/30" },
};

export const WHATSAPP_ADMIN = "2250711751325"; // +225 07 11 75 13 25
