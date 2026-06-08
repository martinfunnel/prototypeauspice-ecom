import { createFileRoute } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { useServerFn } from "@tanstack/react-start";
import { useState } from "react";
import { toast } from "sonner";
import { Plus, Pencil, Trash2, Save, Upload, X, Star } from "lucide-react";
import { AdminShell } from "@/components/AdminShell";
import {
  listTestimonialsAdmin,
  upsertTestimonial,
  deleteTestimonial,
  uploadTestimonialMedia,
} from "@/lib/admin.functions";

export const Route = createFileRoute("/admin/testimonials")({
  head: () => ({ meta: [{ title: "Témoignages — Admin" }] }),
  component: AdminTestimonials,
});

type Row = {
  id: string;
  author_name: string;
  role: string | null;
  content: string;
  rating: number;
  media_url: string | null;
  media_type: "image" | "video";
  is_active: boolean;
  sort_order: number;
};

const EMPTY: Omit<Row, "id"> & { id: string | null } = {
  id: null,
  author_name: "",
  role: "",
  content: "",
  rating: 5,
  media_url: "",
  media_type: "image",
  is_active: true,
  sort_order: 0,
};

function AdminTestimonials() {
  const listFn = useServerFn(listTestimonialsAdmin);
  const saveFn = useServerFn(upsertTestimonial);
  const delFn = useServerFn(deleteTestimonial);
  const uploadFn = useServerFn(uploadTestimonialMedia);

  const { data, refetch, isLoading } = useQuery({
    queryKey: ["admin-testimonials"],
    queryFn: () => listFn(),
  });

  const [editing, setEditing] = useState<typeof EMPTY | null>(null);
  const [uploading, setUploading] = useState(false);
  const [saving, setSaving] = useState(false);

  const openNew = () => setEditing({ ...EMPTY });
  const openEdit = (r: Row) =>
    setEditing({
      id: r.id,
      author_name: r.author_name,
      role: r.role ?? "",
      content: r.content,
      rating: r.rating,
      media_url: r.media_url ?? "",
      media_type: r.media_type,
      is_active: r.is_active,
      sort_order: r.sort_order,
    });

  const onUpload = async (file: File) => {
    const allowed: Record<string, "jpg" | "png" | "webp" | "mp4" | "webm" | "mov"> = {
      "image/jpeg": "jpg",
      "image/png": "png",
      "image/webp": "webp",
      "video/mp4": "mp4",
      "video/webm": "webm",
      "video/quicktime": "mov",
    };
    const ext = allowed[file.type];
    if (!ext) {
      toast.error("Format non supporté (JPEG/PNG/WebP, MP4/WebM/MOV)");
      return;
    }
    if (file.size > 25 * 1024 * 1024) {
      toast.error("Fichier trop lourd (max 25 Mo)");
      return;
    }
    setUploading(true);
    try {
      const base64: string = await new Promise((res, rej) => {
        const reader = new FileReader();
        reader.onload = () => res(reader.result as string);
        reader.onerror = () => rej(new Error("Lecture échouée"));
        reader.readAsDataURL(file);
      });
      const result = await uploadFn({ data: { base64, ext } });
      const isVideo = file.type.startsWith("video/");
      setEditing((e) => (e ? { ...e, media_url: result.url, media_type: isVideo ? "video" : "image" } : e));
      toast.success("Fichier téléchargé");
    } catch (e) {
      toast.error(e instanceof Error ? e.message : "Erreur d'upload");
    } finally {
      setUploading(false);
    }
  };

  const save = async () => {
    if (!editing) return;
    setSaving(true);
    try {
      await saveFn({
        data: {
          id: editing.id ?? undefined,
          author_name: editing.author_name.trim(),
          role: editing.role?.trim() || null,
          content: editing.content.trim(),
          rating: editing.rating,
          media_url: editing.media_url?.trim() || null,
          media_type: editing.media_type,
          is_active: editing.is_active,
          sort_order: editing.sort_order,
        },
      });
      toast.success("Témoignage enregistré");
      setEditing(null);
      refetch();
    } catch (e) {
      toast.error(e instanceof Error ? e.message : "Erreur");
    } finally {
      setSaving(false);
    }
  };

  const remove = async (id: string) => {
    if (!confirm("Supprimer ce témoignage ?")) return;
    try {
      await delFn({ data: { id } });
      toast.success("Supprimé");
      refetch();
    } catch (e) {
      toast.error(e instanceof Error ? e.message : "Erreur");
    }
  };

  return (
    <AdminShell title="Témoignages clients">
      <div className="mb-4 flex justify-end">
        <button
          onClick={openNew}
          className="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground"
        >
          <Plus className="h-4 w-4" /> Nouveau témoignage
        </button>
      </div>

      {isLoading ? (
        <p className="text-sm text-muted-foreground">Chargement…</p>
      ) : (data ?? []).length === 0 ? (
        <div className="rounded-2xl border border-dashed border-border p-12 text-center text-sm text-muted-foreground">
          Aucun témoignage. Ajoutez le premier pour qu'il apparaisse sur le site.
        </div>
      ) : (
        <div className="grid gap-3">
          {(data as Row[]).map((r) => (
            <div key={r.id} className="flex items-start gap-4 rounded-xl border border-border bg-card p-4 shadow-card">
              {r.media_url ? (
                r.media_type === "video" ? (
                  <video src={r.media_url} className="h-20 w-20 shrink-0 rounded-lg object-cover" muted />
                ) : (
                  <img src={r.media_url} alt="" className="h-20 w-20 shrink-0 rounded-lg object-cover" />
                )
              ) : (
                <div className="grid h-20 w-20 shrink-0 place-items-center rounded-lg bg-muted text-xs text-muted-foreground">
                  Aucun média
                </div>
              )}
              <div className="min-w-0 flex-1">
                <div className="flex flex-wrap items-center gap-2">
                  <p className="font-semibold">{r.author_name}</p>
                  {r.role ? <span className="text-xs text-muted-foreground">· {r.role}</span> : null}
                  <span
                    className={`rounded-full px-2 py-0.5 text-[10px] font-bold uppercase ${
                      r.is_active ? "bg-success/10 text-success" : "bg-muted text-muted-foreground"
                    }`}
                  >
                    {r.is_active ? "Visible" : "Masqué"}
                  </span>
                  <span className="flex">
                    {Array.from({ length: 5 }).map((_, i) => (
                      <Star key={i} className={`h-3 w-3 ${i < r.rating ? "fill-accent text-accent" : "text-muted-foreground/30"}`} />
                    ))}
                  </span>
                </div>
                <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">{r.content}</p>
              </div>
              <div className="flex gap-2">
                <button onClick={() => openEdit(r)} className="rounded-md border border-border p-2 hover:bg-muted">
                  <Pencil className="h-4 w-4" />
                </button>
                <button onClick={() => remove(r.id)} className="rounded-md border border-border p-2 text-destructive hover:bg-destructive/10">
                  <Trash2 className="h-4 w-4" />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {editing ? (
        <div className="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4">
          <div className="w-full max-w-2xl rounded-2xl border border-border bg-background p-6 shadow-elevated">
            <div className="mb-4 flex items-center justify-between">
              <h2 className="font-display text-xl font-bold">{editing.id ? "Modifier le témoignage" : "Nouveau témoignage"}</h2>
              <button onClick={() => setEditing(null)} className="rounded-md p-1 hover:bg-muted">
                <X className="h-5 w-5" />
              </button>
            </div>

            <div className="grid gap-4">
              <div className="grid gap-4 sm:grid-cols-3">
                <Field label="Note (1-5)">
                  <input type="number" min={1} max={5} className={inputCls} value={editing.rating} onChange={(e) => setEditing({ ...editing, rating: Math.max(1, Math.min(5, Number(e.target.value) || 5)) })} />
                </Field>
                <Field label="Ordre d'affichage">
                  <input type="number" min={0} className={inputCls} value={editing.sort_order} onChange={(e) => setEditing({ ...editing, sort_order: Number(e.target.value) || 0 })} />
                </Field>
                <Field label="Type de média">
                  <select className={inputCls} value={editing.media_type} onChange={(e) => setEditing({ ...editing, media_type: e.target.value as "image" | "video" })}>
                    <option value="image">Image</option>
                    <option value="video">Vidéo</option>
                  </select>
                </Field>
              </div>

              <Field label="Image ou vidéo (JPEG/PNG/WebP, MP4/WebM/MOV — 25 Mo max)">
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime"
                  onChange={(e) => {
                    const f = e.target.files?.[0];
                    if (f) onUpload(f);
                    e.target.value = "";
                  }}
                  disabled={uploading}
                  className={inputCls}
                />
                {editing.media_url ? (
                  <div className="relative mt-3">
                    {editing.media_type === "video" ? (
                      <video src={editing.media_url} controls className="max-h-56 w-full rounded-lg border border-border" />
                    ) : (
                      <img src={editing.media_url} alt="" className="max-h-56 w-full rounded-lg border border-border object-cover" />
                    )}
                    <button
                      type="button"
                      onClick={() => setEditing({ ...editing, media_url: "" })}
                      className="absolute right-2 top-2 rounded-full bg-background/90 p-1 shadow"
                    >
                      <X className="h-4 w-4" />
                    </button>
                  </div>
                ) : (
                  <p className="mt-2 inline-flex items-center gap-1 text-xs text-muted-foreground">
                    <Upload className="h-3 w-3" /> {uploading ? "Téléchargement…" : "Aucun fichier"}
                  </p>
                )}
              </Field>

              <label className="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" checked={editing.is_active} onChange={(e) => setEditing({ ...editing, is_active: e.target.checked })} />
                Visible sur le site
              </label>

              <div className="flex justify-end gap-2 pt-2">
                <button type="button" onClick={() => setEditing(null)} className="rounded-md border border-border px-4 py-2 text-sm">
                  Annuler
                </button>
                <button
                  type="button"
                  onClick={save}
                  disabled={saving || !editing.media_url}
                  className="inline-flex items-center gap-2 rounded-md bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground disabled:opacity-60"
                >
                  <Save className="h-4 w-4" /> {saving ? "Enregistrement…" : "Enregistrer"}
                </button>
              </div>
            </div>
          </div>
        </div>
      ) : null}
    </AdminShell>
  );
}

const inputCls = "w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent";
function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <label className="block">
      <span className="mb-1.5 block text-sm font-semibold">{label}</span>
      {children}
    </label>
  );
}
