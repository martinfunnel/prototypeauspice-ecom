
-- Add new roles to enum
ALTER TYPE public.app_role ADD VALUE IF NOT EXISTS 'vendeur';
ALTER TYPE public.app_role ADD VALUE IF NOT EXISTS 'comptable';

-- Catalogue promotional banner (single-row settings via fixed key)
CREATE TABLE IF NOT EXISTS public.promo_banners (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  key text NOT NULL UNIQUE,
  title text,
  subtitle text,
  cta_label text,
  cta_url text,
  image_url text,
  is_active boolean NOT NULL DEFAULT true,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

GRANT SELECT ON public.promo_banners TO anon, authenticated;
GRANT ALL ON public.promo_banners TO service_role;

ALTER TABLE public.promo_banners ENABLE ROW LEVEL SECURITY;

CREATE POLICY "public read active banners" ON public.promo_banners
  FOR SELECT TO public USING (is_active = true);

CREATE POLICY "admins manage banners" ON public.promo_banners
  FOR ALL TO authenticated
  USING (public.has_role(auth.uid(), 'admin'))
  WITH CHECK (public.has_role(auth.uid(), 'admin'));

CREATE TRIGGER promo_banners_updated_at BEFORE UPDATE ON public.promo_banners
  FOR EACH ROW EXECUTE FUNCTION public.touch_updated_at();

-- Seed default catalogue banner row
INSERT INTO public.promo_banners (key, title, subtitle, cta_label, cta_url, is_active)
VALUES ('catalogue', 'Cacao à la cannelle de Ceylan', 'Notre produit phare bio — profitez de l''offre du moment', 'Découvrir l''offre', '/produit/cacaocelyan', true)
ON CONFLICT (key) DO NOTHING;
