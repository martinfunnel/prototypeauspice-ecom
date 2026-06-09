import jsPDF from "jspdf";
import QRCode from "qrcode";
import { formatCFA } from "./format";

export type LabelOrder = {
  order_number: string;
  customer_name: string;
  customer_phone: string;
  commune_name: string;
  address: string;
  total: number | string;
  status: string;
  created_at: string;
  notes?: string | null;
  order_items: Array<{ product_name: string; quantity: number }>;
};

export async function generateLabelPDF(order: LabelOrder): Promise<jsPDF> {
  // 500x500 px label
  const pdf = new jsPDF({ unit: "px", format: [500, 500], orientation: "portrait" });

  // Header band
  pdf.setFillColor(12, 35, 64); // primary navy
  pdf.rect(0, 0, 500, 60, "F");
  pdf.setTextColor(255);
  pdf.setFont("helvetica", "bold");
  pdf.setFontSize(20);
  pdf.text("AUSPICE MARKET", 20, 28);
  pdf.setFontSize(9);
  pdf.setFont("helvetica", "normal");
  pdf.text("Auspice SARL · Compléments bio · Paiement à la livraison", 20, 46);

  pdf.setTextColor(255, 107, 107);
  pdf.setFont("helvetica", "bold");
  pdf.setFontSize(13);
  pdf.text(order.order_number, 490, 28, { align: "right" });
  pdf.setTextColor(255);
  pdf.setFontSize(9);
  pdf.text(new Date(order.created_at).toLocaleDateString("fr-FR"), 490, 46, { align: "right" });

  // QR
  const qrDataUrl = await QRCode.toDataURL(order.order_number, { margin: 0, width: 120 });
  pdf.addImage(qrDataUrl, "PNG", 370, 80, 110, 110);

  // Client info
  pdf.setTextColor(20);
  pdf.setFont("helvetica", "bold");
  pdf.setFontSize(11);
  pdf.text("DESTINATAIRE", 20, 90);

  pdf.setFont("helvetica", "bold");
  pdf.setFontSize(14);
  pdf.text(order.customer_name, 20, 110);

  pdf.setFont("helvetica", "normal");
  pdf.setFontSize(11);
  pdf.text(`Tél: ${order.customer_phone}`, 20, 128);
  pdf.setFont("helvetica", "bold");
  pdf.text(`${order.commune_name}`, 20, 148);
  pdf.setFont("helvetica", "normal");

  const addrLines = pdf.splitTextToSize(order.address, 330);
  pdf.text(addrLines, 20, 164);

  // Divider
  pdf.setDrawColor(220);
  pdf.line(20, 210, 480, 210);

  // Items
  pdf.setFont("helvetica", "bold");
  pdf.setFontSize(11);
  pdf.text("ARTICLES", 20, 228);
  pdf.setFont("helvetica", "normal");
  pdf.setFontSize(10);
  let y = 248;
  order.order_items.slice(0, 8).forEach((it) => {
    const line = `• ${it.quantity}× ${it.product_name}`;
    const wrapped = pdf.splitTextToSize(line, 460);
    pdf.text(wrapped, 20, y);
    y += wrapped.length * 13;
  });
  if (order.order_items.length > 8) {
    pdf.text(`+ ${order.order_items.length - 8} autre(s)…`, 20, y);
    y += 13;
  }

  if (order.notes) {
    pdf.setFont("helvetica", "italic");
    pdf.setFontSize(9);
    const notesLines = pdf.splitTextToSize(`Note: ${order.notes}`, 460);
    pdf.text(notesLines, 20, y + 4);
  }

  // Footer total band
  pdf.setFillColor(255, 107, 107);
  pdf.rect(0, 440, 500, 60, "F");
  pdf.setTextColor(255);
  pdf.setFont("helvetica", "bold");
  pdf.setFontSize(11);
  pdf.text("À ENCAISSER", 20, 462);
  pdf.setFontSize(22);
  pdf.text(formatCFA(order.total), 20, 488);
  pdf.setFont("helvetica", "normal");
  pdf.setFontSize(10);
  pdf.text(`Statut: ${order.status}`, 490, 488, { align: "right" });

  return pdf;
}

export async function downloadLabel(order: LabelOrder) {
  const pdf = await generateLabelPDF(order);
  pdf.save(`etiquette-${order.order_number}.pdf`);
}

export async function printLabel(order: LabelOrder) {
  const pdf = await generateLabelPDF(order);
  pdf.autoPrint();
  window.open(pdf.output("bloburl"), "_blank");
}
