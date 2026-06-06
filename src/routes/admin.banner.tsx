import { createFileRoute } from "@tanstack/react-router";
import { useQuery } from "@tanstack/react-query";
import { useServerFn } from "@tanstack/react-start";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { Save, Upload, X } from "lucide-react";
import { AdminShell } from "@/components/AdminShell";
import { getPromoBanner, upsertPromoBanner, uploadProductImage } from "@/lib/admin.functions";

export const Route = createFileRoute("/admin/banner")({
  head: () => ({ meta: [{ title: "Bannière promo — Admin" }] }),
  component: AdminBanner,
});

const BANNER_KEY = "catalogue";

function AdminBanner() {
  const getFn = useServerFn(getPromoBanner);
  const saveFn = useServerFn(upsertPromoBanner);
  const uploadFn = useServerFn(uploadProductImage);
  const { data, refetch, isLoading } = useQuery({
    queryKey: ["promo-banner", BANNER_KEY],
    queryFn: () => getFn({ data: { key: BANNER_KEY } }),
  });

  const [form, setForm] = useState({
    title: "",
    subtitle: "",
    cta_label: "",
    cta_url: "",
    image_url: "",
    is_active: true,
  });
  const [saving, setSaving] = useState(false);
  const [uploading, setUploading] = useState(false);

  useEffect(() => {
    if (data) {
      setForm({
        title: data.title ?? "",
        subtitle: data.subtitle ?? "",
        cta_label: data.cta_label ?? "",
        cta_url: data.cta_url ?? "",
        image_url: data.image_url ?? "",
        is_active: data.is_active,
      });
    }
  }, [data]);

  const onUpload = async (file: File) => {
    if (!["image/jpeg", "image/png"].includes(file.type)) {
      toast.error("Format non supporté (JPEG/PNG uniquement)");
      return;
    }
    setUploading(true);
    try {
      const reader = new FileReader();
      const base64: string = await new Promise((res, rej) => {
        reader.onload = () => res(reader.result as string);
        reader.onerror = () => rej(new Error("Lecture échouée"));
        reader.readAsDataURL(file);
      });
      const ext = file.type === "image/png" ? "png" : "jpg";
      const result = await uploadFn({ data: { base64, ext } });
      setForm((f) => ({ ...f, image_url: result.url }));
      toast.success("Image téléchargée");
    } catch (e) {
      toast.error(e instanceof Error ? e.message : "Erreur d'upload");
    } finally {
      setUploading(false);
    }
  };

  const save = async () => {
    setSaving(true);
    try {
      await saveFn({
        data: {
          key: BANNER_KEY,
          title: form.title || null,
          subtitle: form.subtitle || null,
          cta_label: form.cta_label || null,
          cta_url: form.cta_url || null,
          image_url: form.image_url || null,
          is_active: form.is_active,
        },
      });
      toast.success("Bannière enregistrée");
      refetch();
    } catch (e) {
      toast.error(e instanceof Error ? e.message : "Erreur");
    } finally {
      setSaving(false);
    }
  };

  return (
    <AdminShell title="Bannière promotionnelle (catalogue)">
      {isLoading ? (
        <p className="text-sm text-muted-foreground">Chargement…</p>
      ) : (
        <div className="grid gap-6 lg:grid-cols-[1fr_360px]">
          <div className="space-y-4 rounded-2xl border border-border bg-card p-6 shadow-card">
            <Field label="Titre">
              <input className={inputCls} value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} />
            </Field>
            <Field label="Sous-titre / offre">
              <textarea rows={2} className={inputCls} value={form.subtitle} onChange={(e) => setForm({ ...form, subtitle: e.target.value })} />
            </Field>
            <div className="grid gap-4 sm:grid-cols-2">
              <Field label="Texte du bouton">
                <input className={inputCls} value={form.cta_label} onChange={(e) => setForm({ ...form, cta_label: e.target.value })} placeholder="Découvrir l'offre" />
              </Field>
              <Field label="Lien du bouton">
                <input className={inputCls} value={form.cta_url} onChange={(e) => setForm({ ...form, cta_url: e.target.value })} placeholder="/produit/cacaocelyan" />
              </Field>
            </div>

            <Field label="Image (JPEG/PNG, format horizontal recommandé)">
              <input
                type="file"
                accept="image/jpeg,image/png"
                onChange={(e) => {
                  const file = e.target.files?.[0];
                  if (file) onUpload(file);
                  e.target.value = "";
                }}
                disabled={uploading}
                className={inputCls}
              />
              {form.image_url ? (
                <div className="mt-3 relative">
                  <img src={form.image_url} alt="Bannière" className="max-h-48 w-full rounded-lg border border-border object-cover" />
                  <button
                    type="button"
                    onClick={() => setForm({ ...form, image_url: "" })}
                    className="absolute right-2 top-2 rounded-full bg-background/90 p-1 shadow"
                  >
                    <X className="h-4 w-4" />
                  </button>
                </div>
              ) : (
                <p className="mt-2 text-xs text-muted-foreground inline-flex items-center gap-1">
                  <Upload className="h-3 w-3" /> {uploading ? "Téléchargement…" : "Aucune image"}
                </p>
              )}
            </Field>

            <label className="inline-flex items-center gap-2 text-sm">
              <input type="checkbox" checked={form.is_active} onChange={(e) => setForm({ ...form, is_active: e.target.checked })} />
              Bannière active (visible sur le catalogue)
            </label>

            <button
              type="button"
              onClick={save}
              disabled={saving}
              className="inline-flex items-center gap-2 rounded-md bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground disabled:opacity-60"
            >
              <Save className="h-4 w-4" /> {saving ? "Enregistrement…" : "Enregistrer"}
            </button>
          </div>

          <div>
            <p className="mb-2 text-xs font-semibold uppercase text-muted-foreground">Aperçu</p>
            <BannerPreview {...form} />
          </div>
        </div>
      )}
    </AdminShell>
  );
}

function BannerPreview(b: { title: string; subtitle: string; cta_label: string; image_url: string }) {
  return (
    <div className="overflow-hidden rounded-2xl border border-border shadow-card">
      <div
        className="relative grid min-h-[180px] grid-cols-[1fr_auto] items-center gap-4 bg-gradient-to-r from-primary to-primary/80 p-5 text-primary-foreground"
        style={
          b.image_url
            ? { backgroundImage: `linear-gradient(90deg, rgba(0,0,0,0.55), rgba(0,0,0,0.15)), url(${b.image_url})`, backgroundSize: "cover", backgroundPosition: "center" }
            : undefined
        }
      >
        <div>
          <h3 className="font-display text-xl font-bold">{b.title || "Titre"}</h3>
          <p className="mt-1 text-sm opacity-90">{b.subtitle || "Sous-titre"}</p>
          {b.cta_label ? (
            <span className="mt-3 inline-block rounded-full bg-accent px-4 py-1.5 text-xs font-bold text-accent-foreground">
              {b.cta_label}
            </span>
          ) : null}
        </div>
      </div>
    </div>
  );
}

const inputCls = "w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-accent";
function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return <label className="block"><span className="mb-1.5 block text-sm font-semibold">{label}</span>{children}</label>;
}
