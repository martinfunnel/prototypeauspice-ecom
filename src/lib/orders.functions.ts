import { createServerFn } from "@tanstack/react-start";
import { z } from "zod";
import { supabaseAdmin } from "@/integrations/supabase/client.server";

const OrderItemSchema = z.object({
  productId: z.string().uuid(),
  quantity: z.number().int().min(1).max(99),
});

const CreateOrderSchema = z.object({
  customerName: z.string().trim().min(2).max(120),
  customerPhone: z.string().trim().min(8).max(20).regex(/^[0-9+\s()-]+$/),
  communeId: z.string().uuid(),
  address: z.string().trim().min(3).max(500),
  notes: z.string().trim().max(500).optional().nullable(),
  items: z.array(OrderItemSchema).min(1).max(50),
});

export const createOrder = createServerFn({ method: "POST" })
  .inputValidator((input: unknown) => CreateOrderSchema.parse(input))
  .handler(async ({ data }) => {
    // Fetch commune
    const { data: commune, error: commErr } = await supabaseAdmin
      .from("communes")
      .select("id, name, delivery_fee, is_active")
      .eq("id", data.communeId)
      .single();
    if (commErr || !commune || !commune.is_active) {
      throw new Error("Commune de livraison invalide");
    }

    // Fetch products
    const productIds = data.items.map((i) => i.productId);
    const { data: products, error: prodErr } = await supabaseAdmin
      .from("products")
      .select("id, name, price, promo_price, is_active, stock")
      .in("id", productIds);
    if (prodErr || !products || products.length !== productIds.length) {
      throw new Error("Un ou plusieurs produits sont introuvables");
    }

    let subtotal = 0;
    const itemsToInsert = data.items.map((i) => {
      const p = products.find((x) => x.id === i.productId)!;
      if (!p.is_active) throw new Error(`Produit indisponible: ${p.name}`);
      const unit = Number(p.promo_price ?? p.price);
      const line = unit * i.quantity;
      subtotal += line;
      return {
        product_id: p.id,
        product_name: p.name,
        unit_price: unit,
        quantity: i.quantity,
        subtotal: line,
      };
    });

    const deliveryFee = Number(commune.delivery_fee);
    const total = subtotal + deliveryFee;

    // Order number
    const { data: numRow, error: numErr } = await supabaseAdmin.rpc("generate_order_number");
    if (numErr) throw new Error("Impossible de générer le numéro de commande");
    const orderNumber = numRow as unknown as string;

    const { data: order, error: orderErr } = await supabaseAdmin
      .from("orders")
      .insert({
        order_number: orderNumber,
        customer_name: data.customerName,
        customer_phone: data.customerPhone,
        commune_id: commune.id,
        commune_name: commune.name,
        address: data.address,
        notes: data.notes ?? null,
        subtotal,
        delivery_fee: deliveryFee,
        total,
      })
      .select("id, order_number")
      .single();
    if (orderErr || !order) throw new Error("Impossible de créer la commande");

    const { error: itemsErr } = await supabaseAdmin
      .from("order_items")
      .insert(itemsToInsert.map((i) => ({ ...i, order_id: order.id })));
    if (itemsErr) throw new Error("Impossible d'enregistrer les articles");

    return { id: order.id, orderNumber: order.order_number, total };
  });

export const getOrdersByPhone = createServerFn({ method: "POST" })
  .inputValidator((input: unknown) =>
    z.object({ phone: z.string().trim().min(8).max(20) }).parse(input),
  )
  .handler(async ({ data }) => {
    const { data: orders, error } = await supabaseAdmin
      .from("orders")
      .select(
        "id, order_number, customer_name, customer_phone, commune_name, address, subtotal, delivery_fee, total, status, created_at, order_items(product_name, quantity, unit_price, subtotal)",
      )
      .eq("customer_phone", data.phone)
      .order("created_at", { ascending: false });
    if (error) throw new Error("Erreur lors de la recherche");
    return orders ?? [];
  });
