import { createServerFn } from "@tanstack/react-start";
import { z } from "zod";
import { requireSupabaseAuth } from "@/integrations/supabase/auth-middleware";
import { supabaseAdmin } from "@/integrations/supabase/client.server";

async function assertAdmin(userId: string) {
  const { data, error } = await supabaseAdmin
    .from("user_roles")
    .select("role")
    .eq("user_id", userId)
    .eq("role", "admin")
    .maybeSingle();
  if (error) throw new Error("Erreur de vérification du rôle");
  if (!data) throw new Error("Accès refusé : rôle admin requis");
}

// ---------- Stats ----------
export const getAdminStats = createServerFn({ method: "GET" })
  .middleware([requireSupabaseAuth])
  .handler(async ({ context }) => {
    await assertAdmin(context.userId);
    const [orders, products, communes] = await Promise.all([
      supabaseAdmin.from("orders").select("id, status, total, created_at"),
      supabaseAdmin.from("products").select("id, is_active, stock"),
      supabaseAdmin.from("communes").select("id, is_active"),
    ]);
    const allOrders = orders.data ?? [];
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const last7 = new Date(today.getTime() - 6 * 86400000);

    const revenue = allOrders
      .filter((o) => o.status !== "cancelled")
      .reduce((s, o) => s + Number(o.total ?? 0), 0);
    const pending = allOrders.filter((o) => o.status === "pending").length;
    const delivered = allOrders.filter((o) => o.status === "delivered").length;

    // 7-day chart
    const days: { date: string; count: number; revenue: number }[] = [];
    for (let i = 6; i >= 0; i--) {
      const d = new Date(today.getTime() - i * 86400000);
      days.push({
        date: d.toISOString().slice(0, 10),
        count: 0,
        revenue: 0,
      });
    }
    allOrders.forEach((o) => {
      const d = new Date(o.created_at);
      if (d >= last7) {
        const key = d.toISOString().slice(0, 10);
        const slot = days.find((x) => x.date === key);
        if (slot) {
          slot.count += 1;
          if (o.status !== "cancelled") slot.revenue += Number(o.total ?? 0);
        }
      }
    });

    return {
      ordersTotal: allOrders.length,
      revenue,
      pending,
      delivered,
      productsActive: (products.data ?? []).filter((p) => p.is_active).length,
      productsTotal: (products.data ?? []).length,
      lowStock: (products.data ?? []).filter((p) => (p.stock ?? 0) <= 3 && p.is_active).length,
      communesActive: (communes.data ?? []).filter((c) => c.is_active).length,
      chart: days,
    };
  });

// ---------- Orders ----------
export const listOrders = createServerFn({ method: "GET" })
  .middleware([requireSupabaseAuth])
  .handler(async ({ context }) => {
    await assertAdmin(context.userId);
    const { data, error } = await supabaseAdmin
      .from("orders")
      .select(
        "id, order_number, customer_name, customer_phone, commune_name, address, notes, subtotal, delivery_fee, total, status, created_at, order_items(product_name, quantity, unit_price, subtotal)",
      )
      .order("created_at", { ascending: false })
      .limit(500);
    if (error) throw new Error(error.message);
    return data ?? [];
  });

const OrderStatus = z.enum([
  "pending",
  "confirmed",
  "processing",
  "shipped",
  "delivered",
  "cancelled",
]);

export const updateOrderStatus = createServerFn({ method: "POST" })
  .middleware([requireSupabaseAuth])
  .inputValidator((i: unknown) =>
    z.object({ id: z.string().uuid(), status: OrderStatus }).parse(i),
  )
  .handler(async ({ context, data }) => {
    await assertAdmin(context.userId);
    const { error } = await supabaseAdmin
      .from("orders")
      .update({ status: data.status })
      .eq("id", data.id);
    if (error) throw new Error(error.message);
    return { ok: true };
  });

// ---------- Products ----------
export const listProductsAdmin = createServerFn({ method: "GET" })
  .middleware([requireSupabaseAuth])
  .handler(async ({ context }) => {
    await assertAdmin(context.userId);
    const { data, error } = await supabaseAdmin
      .from("products")
      .select("*, categories(id, name)")
      .order("created_at", { ascending: false });
    if (error) throw new Error(error.message);
    return data ?? [];
  });

export const listCategoriesAdmin = createServerFn({ method: "GET" })
  .middleware([requireSupabaseAuth])
  .handler(async ({ context }) => {
    await assertAdmin(context.userId);
    const { data, error } = await supabaseAdmin
      .from("categories")
      .select("*")
      .order("sort_order");
    if (error) throw new Error(error.message);
    return data ?? [];
  });

const ProductSchema = z.object({
  id: z.string().uuid().optional().nullable(),
  name: z.string().trim().min(2).max(200),
  slug: z
    .string()
    .trim()
    .min(2)
    .max(200)
    .regex(/^[a-z0-9-]+$/, "Slug: lettres minuscules, chiffres et tirets uniquement"),
  short_description: z.string().trim().max(300).optional().nullable(),
  description: z.string().trim().max(5000).optional().nullable(),
  price: z.number().min(0).max(10_000_000),
  promo_price: z.number().min(0).max(10_000_000).nullable().optional(),
  stock: z.number().int().min(0).max(100000),
  category_id: z.string().uuid().nullable().optional(),
  images: z.array(z.string().url().max(1000)).max(10).default([]),
  detail_images: z.array(z.string().url().max(1000)).max(20).default([]),
  benefits: z.array(z.string().trim().min(1).max(300)).max(20).default([]),
  is_active: z.boolean().default(true),
  is_popular: z.boolean().default(false),
});

export const upsertProduct = createServerFn({ method: "POST" })
  .middleware([requireSupabaseAuth])
  .inputValidator((i: unknown) => ProductSchema.parse(i))
  .handler(async ({ context, data }) => {
    await assertAdmin(context.userId);
    const payload = {
      name: data.name,
      slug: data.slug,
      short_description: data.short_description ?? null,
      description: data.description ?? null,
      price: data.price,
      promo_price: data.promo_price ?? null,
      stock: data.stock,
      category_id: data.category_id ?? null,
      images: data.images,
      detail_images: data.detail_images,
      benefits: data.benefits,
      is_active: data.is_active,
      is_popular: data.is_popular,
    };
    if (data.id) {
      const { error } = await supabaseAdmin.from("products").update(payload).eq("id", data.id);
      if (error) throw new Error(error.message);
      return { id: data.id };
    }
    const { data: row, error } = await supabaseAdmin
      .from("products")
      .insert(payload)
      .select("id")
      .single();
    if (error) throw new Error(error.message);
    return { id: row.id };
  });

export const deleteProduct = createServerFn({ method: "POST" })
  .middleware([requireSupabaseAuth])
  .inputValidator((i: unknown) => z.object({ id: z.string().uuid() }).parse(i))
  .handler(async ({ context, data }) => {
    await assertAdmin(context.userId);
    const { error } = await supabaseAdmin.from("products").delete().eq("id", data.id);
    if (error) throw new Error(error.message);
    return { ok: true };
  });

// ---------- Communes ----------
export const listCommunesAdmin = createServerFn({ method: "GET" })
  .middleware([requireSupabaseAuth])
  .handler(async ({ context }) => {
    await assertAdmin(context.userId);
    const { data, error } = await supabaseAdmin
      .from("communes")
      .select("*")
      .order("zone")
      .order("name");
    if (error) throw new Error(error.message);
    return data ?? [];
  });

const CommuneSchema = z.object({
  id: z.string().uuid().optional().nullable(),
  name: z.string().trim().min(2).max(100),
  zone: z.string().trim().min(2).max(100),
  delivery_fee: z.number().min(0).max(1_000_000),
  delivery_days: z.number().int().min(0).max(30),
  is_active: z.boolean(),
});

export const upsertCommune = createServerFn({ method: "POST" })
  .middleware([requireSupabaseAuth])
  .inputValidator((i: unknown) => CommuneSchema.parse(i))
  .handler(async ({ context, data }) => {
    await assertAdmin(context.userId);
    const payload = {
      name: data.name,
      zone: data.zone,
      delivery_fee: data.delivery_fee,
      delivery_days: data.delivery_days,
      is_active: data.is_active,
    };
    if (data.id) {
      const { error } = await supabaseAdmin.from("communes").update(payload).eq("id", data.id);
      if (error) throw new Error(error.message);
      return { id: data.id };
    }
    const { data: row, error } = await supabaseAdmin
      .from("communes")
      .insert(payload)
      .select("id")
      .single();
    if (error) throw new Error(error.message);
    return { id: row.id };
  });

export const deleteCommune = createServerFn({ method: "POST" })
  .middleware([requireSupabaseAuth])
  .inputValidator((i: unknown) => z.object({ id: z.string().uuid() }).parse(i))
  .handler(async ({ context, data }) => {
    await assertAdmin(context.userId);
    const { error } = await supabaseAdmin.from("communes").delete().eq("id", data.id);
    if (error) throw new Error(error.message);
    return { ok: true };
  });

// ---------- Image Upload ----------
export const uploadProductImage = createServerFn({ method: "POST" })
  .middleware([requireSupabaseAuth])
  .inputValidator((i: unknown) =>
    z.object({
      base64: z.string(),
      ext: z.enum(["jpg", "png"]),
    }).parse(i),
  )
  .handler(async ({ context, data }) => {
    await assertAdmin(context.userId);
    const base64Data = data.base64.replace(/^data:image\/\w+;base64,/, "");
    const buffer = Buffer.from(base64Data, "base64");
    const filename = `${crypto.randomUUID()}.${data.ext}`;
    const { data: uploadData, error } = await supabaseAdmin.storage
      .from("product-images")
      .upload(filename, buffer, {
        contentType: data.ext === "png" ? "image/png" : "image/jpeg",
      });
    if (error) throw new Error(error.message);
    const { data: urlData } = supabaseAdmin.storage
      .from("product-images")
      .getPublicUrl(uploadData.path);
    return { url: urlData.publicUrl };
  });

// ---------- Promo Banners ----------
export const getPromoBanner = createServerFn({ method: "GET" })
  .inputValidator((i: unknown) => z.object({ key: z.string().min(1).max(50) }).parse(i))
  .handler(async ({ data }) => {
    const { data: row } = await supabaseAdmin
      .from("promo_banners")
      .select("*")
      .eq("key", data.key)
      .maybeSingle();
    return row;
  });

const BannerSchema = z.object({
  key: z.string().trim().min(1).max(50),
  title: z.string().trim().max(200).nullable().optional(),
  subtitle: z.string().trim().max(500).nullable().optional(),
  cta_label: z.string().trim().max(50).nullable().optional(),
  cta_url: z.string().trim().max(500).nullable().optional(),
  image_url: z.string().trim().max(1000).nullable().optional(),
  is_active: z.boolean(),
});

export const upsertPromoBanner = createServerFn({ method: "POST" })
  .middleware([requireSupabaseAuth])
  .inputValidator((i: unknown) => BannerSchema.parse(i))
  .handler(async ({ context, data }) => {
    await assertAdmin(context.userId);
    const { error } = await supabaseAdmin
      .from("promo_banners")
      .upsert({ ...data }, { onConflict: "key" });
    if (error) throw new Error(error.message);
    return { ok: true };
  });

// ---------- User Roles Management ----------
const ManagedRole = z.enum(["admin", "vendeur", "comptable"]);

export const listUsersWithRoles = createServerFn({ method: "GET" })
  .middleware([requireSupabaseAuth])
  .handler(async ({ context }) => {
    await assertAdmin(context.userId);
    const { data: users, error } = await supabaseAdmin.auth.admin.listUsers({ perPage: 200 });
    if (error) throw new Error(error.message);
    const { data: roles } = await supabaseAdmin.from("user_roles").select("user_id, role");
    const { data: profiles } = await supabaseAdmin.from("profiles").select("id, full_name");
    const profileMap = new Map((profiles ?? []).map((p) => [p.id, p.full_name]));
    return users.users.map((u) => ({
      id: u.id,
      email: u.email ?? "",
      full_name: profileMap.get(u.id) ?? "",
      created_at: u.created_at,
      roles: (roles ?? []).filter((r) => r.user_id === u.id).map((r) => r.role as string),
    }));
  });

export const setUserRole = createServerFn({ method: "POST" })
  .middleware([requireSupabaseAuth])
  .inputValidator((i: unknown) =>
    z.object({
      user_id: z.string().uuid(),
      role: ManagedRole,
      grant: z.boolean(),
    }).parse(i),
  )
  .handler(async ({ context, data }) => {
    await assertAdmin(context.userId);
    if (data.grant) {
      const { error } = await supabaseAdmin
        .from("user_roles")
        .upsert({ user_id: data.user_id, role: data.role }, { onConflict: "user_id,role" });
      if (error) throw new Error(error.message);
    } else {
      // prevent removing last admin
      if (data.role === "admin") {
        const { count } = await supabaseAdmin
          .from("user_roles")
          .select("id", { count: "exact", head: true })
          .eq("role", "admin");
        if ((count ?? 0) <= 1) throw new Error("Impossible de retirer le dernier administrateur");
      }
      const { error } = await supabaseAdmin
        .from("user_roles")
        .delete()
        .eq("user_id", data.user_id)
        .eq("role", data.role);
      if (error) throw new Error(error.message);
    }
    return { ok: true };
  });

// ---------- Self-promote (bootstrap first admin) ----------
export const claimFirstAdmin = createServerFn({ method: "POST" })
  .middleware([requireSupabaseAuth])
  .handler(async ({ context }) => {
    const { count, error: cErr } = await supabaseAdmin
      .from("user_roles")
      .select("id", { count: "exact", head: true })
      .eq("role", "admin");
    if (cErr) throw new Error(cErr.message);
    if ((count ?? 0) > 0) throw new Error("Un administrateur existe déjà");
    const { error } = await supabaseAdmin
      .from("user_roles")
      .insert({ user_id: context.userId, role: "admin" });
    if (error) throw new Error(error.message);
    return { ok: true };
  });

export const checkIsAdmin = createServerFn({ method: "GET" })
  .middleware([requireSupabaseAuth])
  .handler(async ({ context }) => {
    const { data } = await supabaseAdmin
      .from("user_roles")
      .select("role")
      .eq("user_id", context.userId)
      .eq("role", "admin")
      .maybeSingle();
    const { count } = await supabaseAdmin
      .from("user_roles")
      .select("id", { count: "exact", head: true })
      .eq("role", "admin");
    return { isAdmin: !!data, anyAdmin: (count ?? 0) > 0 };
  });

// ---------- Testimonials ----------
const TestimonialSchema = z.object({
  id: z.string().uuid().optional().nullable(),
  author_name: z.string().trim().min(1).max(120),
  role: z.string().trim().max(120).nullable().optional(),
  content: z.string().trim().min(1).max(2000),
  rating: z.number().int().min(1).max(5),
  media_url: z.string().trim().max(1000).nullable().optional(),
  media_type: z.enum(["image", "video"]),
  is_active: z.boolean(),
  sort_order: z.number().int().min(0).max(10000),
});

export const listTestimonialsAdmin = createServerFn({ method: "GET" })
  .middleware([requireSupabaseAuth])
  .handler(async ({ context }) => {
    await assertAdmin(context.userId);
    const { data, error } = await supabaseAdmin
      .from("testimonials")
      .select("*")
      .order("sort_order", { ascending: true })
      .order("created_at", { ascending: false });
    if (error) throw new Error(error.message);
    return data ?? [];
  });

export const upsertTestimonial = createServerFn({ method: "POST" })
  .middleware([requireSupabaseAuth])
  .inputValidator((i: unknown) => TestimonialSchema.parse(i))
  .handler(async ({ context, data }) => {
    await assertAdmin(context.userId);
    const payload = {
      author_name: data.author_name,
      role: data.role ?? null,
      content: data.content,
      rating: data.rating,
      media_url: data.media_url ?? null,
      media_type: data.media_type,
      is_active: data.is_active,
      sort_order: data.sort_order,
    };
    if (data.id) {
      const { error } = await supabaseAdmin.from("testimonials").update(payload).eq("id", data.id);
      if (error) throw new Error(error.message);
      return { id: data.id };
    }
    const { data: row, error } = await supabaseAdmin
      .from("testimonials")
      .insert(payload)
      .select("id")
      .single();
    if (error) throw new Error(error.message);
    return { id: row.id };
  });

export const deleteTestimonial = createServerFn({ method: "POST" })
  .middleware([requireSupabaseAuth])
  .inputValidator((i: unknown) => z.object({ id: z.string().uuid() }).parse(i))
  .handler(async ({ context, data }) => {
    await assertAdmin(context.userId);
    const { error } = await supabaseAdmin.from("testimonials").delete().eq("id", data.id);
    if (error) throw new Error(error.message);
    return { ok: true };
  });

const MEDIA_MIME: Record<string, string> = {
  jpg: "image/jpeg",
  png: "image/png",
  webp: "image/webp",
  mp4: "video/mp4",
  webm: "video/webm",
  mov: "video/quicktime",
};

export const uploadTestimonialMedia = createServerFn({ method: "POST" })
  .middleware([requireSupabaseAuth])
  .inputValidator((i: unknown) =>
    z.object({
      base64: z.string().max(40_000_000),
      ext: z.enum(["jpg", "png", "webp", "mp4", "webm", "mov"]),
    }).parse(i),
  )
  .handler(async ({ context, data }) => {
    await assertAdmin(context.userId);
    const base64Data = data.base64.replace(/^data:[^;]+;base64,/, "");
    const buffer = Buffer.from(base64Data, "base64");
    const filename = `testimonials/${crypto.randomUUID()}.${data.ext}`;
    const contentType = MEDIA_MIME[data.ext];
    const { data: uploadData, error } = await supabaseAdmin.storage
      .from("product-images")
      .upload(filename, buffer, { contentType });
    if (error) throw new Error(error.message);
    const { data: urlData } = supabaseAdmin.storage
      .from("product-images")
      .getPublicUrl(uploadData.path);
    return { url: urlData.publicUrl };
  });
