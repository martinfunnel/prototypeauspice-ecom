import { WHATSAPP_ADMIN } from "./format";
import { formatCFA } from "./format";

export type WhatsAppOrder = {
  order_number: string;
  customer_name: string;
  customer_phone: string;
  commune_name: string;
  address: string;
  subtotal: number | string;
  delivery_fee: number | string;
  total: number | string;
  notes?: string | null;
  order_items: Array<{ product_name: string; quantity: number }>;
};

export function buildAdminWhatsAppMessage(order: WhatsAppOrder): string {
  const items = order.order_items
    .map((i) => `• ${i.quantity}× ${i.product_name}`)
    .join("\n");
  return [
    `🛒 *NOUVELLE COMMANDE* ${order.order_number}`,
    ``,
    `👤 *Client:* ${order.customer_name}`,
    `📞 *Tél:* ${order.customer_phone}`,
    `📍 *Commune:* ${order.commune_name}`,
    `🏠 *Adresse:* ${order.address}`,
    order.notes ? `📝 *Notes:* ${order.notes}` : null,
    ``,
    `*Articles:*`,
    items,
    ``,
    `Sous-total: ${formatCFA(order.subtotal)}`,
    `Livraison: ${formatCFA(order.delivery_fee)}`,
    `*TOTAL: ${formatCFA(order.total)}*`,
    ``,
    `💵 Paiement à la livraison`,
  ]
    .filter(Boolean)
    .join("\n");
}

export function whatsappAdminLink(order: WhatsAppOrder): string {
  const msg = encodeURIComponent(buildAdminWhatsAppMessage(order));
  return `https://wa.me/${WHATSAPP_ADMIN}?text=${msg}`;
}

export function notifyAdminInNewTab(order: WhatsAppOrder) {
  if (typeof window === "undefined") return;
  window.open(whatsappAdminLink(order), "_blank", "noopener,noreferrer");
}
