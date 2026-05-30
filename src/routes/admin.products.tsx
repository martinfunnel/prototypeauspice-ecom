import { createFileRoute } from "@tanstack/react-router";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useServerFn } from "@tanstack/react-start";
import { useState } from "react";
import { toast } from "sonner";
import { Plus, Pencil, Trash2, X, Image as ImageIcon, XCircle } from "lucide-react";
import { AdminShell } from "@/components/AdminShell";
import {
  listProductsAdmin,
  listCategoriesAdmin,
  upsertProduct,
  deleteProduct,
  uploadProductImage,
} from "@/lib/admin.functions";
import { formatCFA } from "@/lib/format";

type ImageItem =
  | { kind: "url"; value: string }
  | { kind: "file"; file: File; preview: string };

export const Route = createFileRoute("/admin/products")({
  head: () => ({ meta: [{ title: "Produits — Admin" }] }),
  component: AdminProducts,
});

type Editing = {
  id?: string | null;
  name: string;
  slug: string;
  short_description: string;
  description: string;
  benefits: string;
  price: string;
  promo_price: string;
  stock: string;
  category_id: string;
  is_active: boolean;
  is_popular: boolean;
};

const empty: Editing = {
  id: null,
  name: "",
  slug: "",
  short_description: "",
  description: "",
  benefits: "",
  price: "",
  promo_price: "",
  stock: "0",
  category_id: "",
  is_active: true,
  is_popular: false,
};

function slugify(s: string) {
  return s
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/(^-|-$)+/g, "");
}

function AdminProducts() {
  const qc = useQueryClient();
  const listP = useServerFn(listProductsAdmin);
  const listC = useServerFn(listCategoriesAdmin);
  const upsert = useServerFn(upsertProduct);
  const del = useServerFn(deleteProduct);

  const { data: products, isLoading } = useQuery({
    queryKey: ["admin-products"],
    queryFn: () => listP(),
  });
  const { data: categories } = useQuery({
    queryKey: ["admin-categories"],
    queryFn: () => listC(),
  });

  const [open, setOpen] = useState(false);
  const [form, setForm] = useState<Editing>(empty);
  const [images, setImages] = useState<ImageItem[]>([]);
  const uploadImg = useServerFn(uploadProductImage);

  const save = useMutation({
    mutationFn: async () => {
      const imageUrls: string[] = [];
      for (const img of images) {
        if (img.kind === "url") {
          imageUrls.push(img.value);
        } else {
          const ext = img.file.type === "image/png" ? "png" : "jpg";
          const res = await uploadImg({ data: { base64: img.preview, ext } });
          imageUrls.push(res.url);
        }
      }
      return upsert({
        data: {
          id: form.id ?? undefined,
          name: form.name,
          slug: form.slug || slugify(form.name),
          short_description: form.short_description || null,
          description: form.description || null,
          price: Number(form.price),
          promo_price: form.promo_price ? Number(form.promo_price) : null,
          stock: Number(form.stock),
          category_id: form.category_id || null,
          images: imageUrls,
          is_active: form.is_active,
          is_popular: form.is_popular,
        },
      });
    },
    onSuccess: () => {
      toast.success("Produit enregistré");
      qc.invalidateQueries({ queryKey: ["admin-products"] });
      qc.invalidateQueries({ queryKey: ["admin-stats"] });
      setImages([]);
      setOpen(false);
    },
    onError: (e) => toast.error(e instanceof Error ? e.message : "Erreur"),
  });

  const remove = useMutation({
    mutationFn: (id: string) => del({ data: { id } }),
    onSuccess: () => {
      toast.success("Produit supprimé");
      qc.invalidateQueries({ queryKey: ["admin-products"] });
    },
    onError: (e) => toast.error(e instanceof Error ? e.message : "Erreur"),
  });

  function startNew() {
    setForm(empty);
    setImages([]);
    setOpen(true);
  }
  function startEdit(p: NonNullable<typeof products>[number]) {
    setForm({
      id: p.id,
      name: p.name,
      slug: p.slug,
      short_description: p.short_description ?? "",
      description: p.description ?? "",
      price: String(p.price),
      promo_price: p.promo_price ? String(p.promo_price) : "",
      stock: String(p.stock),
      category_id: p.category_id ?? "",
      is_active: p.is_active,
      is_popular: p.is_popular,
    });
    setImages((p.images ?? []).map((url) => ({ kind: "url" as const, value: url })));
    setOpen(true);
  }

  return (
    <AdminShell title="Produits">
      <div className="mb-4 flex justify-end">
        <button
          onClick={startNew}
          className="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground"
        >
          <Plus className="h-4 w-4" /> Nouveau produit
        </button>
      </div>

      {isLoading ? (
        <div className="text-sm text-muted-foreground">Chargement…</div>
      ) : (products?.length ?? 0) === 0 ? (
        <div className="rounded-xl border border-dashed border-border bg-card p-10 text-center text-sm text-muted-foreground">
          Aucun produit. Créez votre premier produit.
        </div>
      ) : (
        <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-card">
          <div className="hidden grid-cols-[1fr_120px_100px_120px_100px] gap-3 border-b border-border bg-muted/40 px-4 py-2 text-xs font-semibold uppercase text-muted-foreground md:grid">
            <div>Produit</div>
            <div>Prix</div>
            <div>Stock</div>
            <div>Statut</div>
            <div className="text-right">Actions</div>
          </div>
          <ul className="divide-y divide-border">
            {products?.map((p) => (
              <li
                key={p.id}
                className="grid grid-cols-1 gap-3 px-4 py-3 md:grid-cols-[1fr_120px_100px_120px_100px] md:items-center"
              >
                <div className="flex items-center gap-3 min-w-0">
                  {p.images?.[0] ? (
                    <img
                      src={p.images[0]}
                      alt=""
                      className="h-12 w-12 flex-shrink-0 rounded-lg object-cover"
                    />
                  ) : (
                    <div className="h-12 w-12 flex-shrink-0 rounded-lg bg-muted" />
                  )}
                  <div className="min-w-0">
                    <div className="truncate font-semibold">{p.name}</div>
                    <div className="truncate text-xs text-muted-foreground">/{p.slug}</div>
                  </div>
                </div>
                <div className="font-mono text-sm">
                  {p.promo_price ? (
                    <>
                      <span className="font-bold text-accent">{formatCFA(p.promo_price)}</span>
                      <span className="ml-1 text-xs text-muted-foreground line-through">
                        {formatCFA(p.price)}
                      </span>
                    </>
                  ) : (
                    formatCFA(p.price)
                  )}
                </div>
                <div className={`text-sm ${p.stock <= 3 ? "text-warning-foreground font-semibold" : ""}`}>
                  {p.stock}
                </div>
                <div>
                  <span
                    className={`inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${
                      p.is_active
                        ? "bg-success/15 text-success"
                        : "bg-muted text-muted-foreground"
                    }`}
                  >
                    {p.is_active ? "Actif" : "Masqué"}
                  </span>
                  {p.is_popular ? (
                    <span className="ml-1 inline-flex rounded-full bg-accent/15 px-2 py-0.5 text-xs font-semibold text-accent">
                      ★
                    </span>
                  ) : null}
                </div>
                <div className="flex items-center justify-end gap-1">
                  <button
                    onClick={() => startEdit(p)}
                    className="grid h-9 w-9 place-items-center rounded-lg text-foreground/70 hover:bg-muted"
                  >
                    <Pencil className="h-4 w-4" />
                  </button>
                  <button
                    onClick={() => {
                      if (confirm(`Supprimer "${p.name}" ?`)) remove.mutate(p.id);
                    }}
                    className="grid h-9 w-9 place-items-center rounded-lg text-destructive hover:bg-destructive/10"
                  >
                    <Trash2 className="h-4 w-4" />
                  </button>
                </div>
              </li>
            ))}
          </ul>
        </div>
      )}

      {open ? (
        <div className="fixed inset-0 z-50 flex items-end justify-center bg-black/60 p-0 sm:items-center sm:p-4">
          <div className="relative w-full max-w-2xl rounded-t-2xl bg-background p-5 shadow-xl sm:rounded-2xl">
            <div className="mb-4 flex items-center justify-between">
              <h2 className="font-display text-lg font-bold">
                {form.id ? "Modifier le produit" : "Nouveau produit"}
              </h2>
              <button
                onClick={() => setOpen(false)}
                className="grid h-9 w-9 place-items-center rounded-lg hover:bg-muted"
              >
                <X className="h-4 w-4" />
              </button>
            </div>

            <form
              onSubmit={(e) => {
                e.preventDefault();
                save.mutate();
              }}
              className="max-h-[70vh] space-y-3 overflow-y-auto pr-1"
            >
              <div className="grid gap-3 sm:grid-cols-2">
                <Field label="Nom *">
                  <input
                    required
                    value={form.name}
                    onChange={(e) => {
                      const name = e.target.value;
                      setForm((f) => ({
                        ...f,
                        name,
                        slug: f.slug || slugify(name),
                      }));
                    }}
                    className={inputCls}
                  />
                </Field>
                <Field label="Slug *">
                  <input
                    required
                    value={form.slug}
                    onChange={(e) => setForm((f) => ({ ...f, slug: slugify(e.target.value) }))}
                    className={inputCls}
                  />
                </Field>
              </div>

              <Field label="Description courte">
                <input
                  value={form.short_description}
                  onChange={(e) => setForm((f) => ({ ...f, short_description: e.target.value }))}
                  className={inputCls}
                  maxLength={300}
                />
              </Field>

              <Field label="Description complète">
                <textarea
                  value={form.description}
                  onChange={(e) => setForm((f) => ({ ...f, description: e.target.value }))}
                  rows={4}
                  className={inputCls}
                />
              </Field>

              <div className="grid gap-3 sm:grid-cols-3">
                <Field label="Prix (FCFA) *">
                  <input
                    required
                    type="number"
                    min={0}
                    value={form.price}
                    onChange={(e) => setForm((f) => ({ ...f, price: e.target.value }))}
                    className={inputCls}
                  />
                </Field>
                <Field label="Prix promo">
                  <input
                    type="number"
                    min={0}
                    value={form.promo_price}
                    onChange={(e) => setForm((f) => ({ ...f, promo_price: e.target.value }))}
                    className={inputCls}
                  />
                </Field>
                <Field label="Stock *">
                  <input
                    required
                    type="number"
                    min={0}
                    value={form.stock}
                    onChange={(e) => setForm((f) => ({ ...f, stock: e.target.value }))}
                    className={inputCls}
                  />
                </Field>
              </div>

              <Field label="Catégorie">
                <select
                  value={form.category_id}
                  onChange={(e) => setForm((f) => ({ ...f, category_id: e.target.value }))}
                  className={inputCls}
                >
                  <option value="">— Aucune —</option>
                  {categories?.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.name}
                    </option>
                  ))}
                </select>
              </Field>

              <Field label="Images du produit (JPEG/PNG)">
                <input
                  type="file"
                  accept="image/jpeg,image/png"
                  multiple
                  onChange={(e) => {
                    const files = Array.from(e.target.files || []).filter((f) =>
                      ["image/jpeg", "image/png"].includes(f.type),
                    );
                    files.forEach((file) => {
                      const reader = new FileReader();
                      reader.onload = () => {
                        setImages((prev) => [
                          ...prev,
                          { kind: "file" as const, file, preview: reader.result as string },
                        ]);
                      };
                      reader.readAsDataURL(file);
                    });
                    e.target.value = "";
                  }}
                  className={inputCls}
                />
                {images.length > 0 && (
                  <div className="mt-3 flex flex-wrap gap-2">
                    {images.map((img, idx) => (
                      <div key={idx} className="relative">
                        <img
                          src={img.kind === "url" ? img.value : img.preview}
                          alt=""
                          className="h-20 w-20 rounded-lg object-cover border border-border"
                        />
                        <button
                          type="button"
                          onClick={() => setImages((prev) => prev.filter((_, i) => i !== idx))}
                          className="absolute -right-1.5 -top-1.5 grid h-5 w-5 place-items-center rounded-full bg-destructive text-white"
                        >
                          <XCircle className="h-3.5 w-3.5" />
                        </button>
                      </div>
                    ))}
                  </div>
                )}
              </Field>

              <div className="flex flex-wrap gap-4 pt-1">
                <label className="flex items-center gap-2 text-sm">
                  <input
                    type="checkbox"
                    checked={form.is_active}
                    onChange={(e) => setForm((f) => ({ ...f, is_active: e.target.checked }))}
                  />
                  Actif (visible)
                </label>
                <label className="flex items-center gap-2 text-sm">
                  <input
                    type="checkbox"
                    checked={form.is_popular}
                    onChange={(e) => setForm((f) => ({ ...f, is_popular: e.target.checked }))}
                  />
                  Mis en avant
                </label>
              </div>

              <div className="sticky bottom-0 -mx-5 mt-4 flex justify-end gap-2 border-t border-border bg-background px-5 py-3">
                <button
                  type="button"
                  onClick={() => setOpen(false)}
                  className="rounded-lg border border-border px-4 py-2 text-sm"
                >
                  Annuler
                </button>
                <button
                  disabled={save.isPending}
                  className="rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground disabled:opacity-60"
                >
                  {save.isPending ? "..." : "Enregistrer"}
                </button>
              </div>
            </form>
          </div>
        </div>
      ) : null}
    </AdminShell>
  );
}

const inputCls =
  "w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent";

function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <label className="block">
      <span className="mb-1 block text-xs font-semibold uppercase tracking-wider text-muted-foreground">
        {label}
      </span>
      {children}
    </label>
  );
}
