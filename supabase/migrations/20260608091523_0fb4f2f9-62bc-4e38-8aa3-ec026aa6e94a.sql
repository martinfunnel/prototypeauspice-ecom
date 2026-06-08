
-- 1. Storage: remove broad SELECT (listing) policy on product-images and add admin-only write policies
DROP POLICY IF EXISTS "Product images public read" ON storage.objects;

CREATE POLICY "Admins insert product images"
ON storage.objects FOR INSERT TO authenticated
WITH CHECK (bucket_id = 'product-images' AND public.has_role(auth.uid(), 'admin'));

CREATE POLICY "Admins update product images"
ON storage.objects FOR UPDATE TO authenticated
USING (bucket_id = 'product-images' AND public.has_role(auth.uid(), 'admin'))
WITH CHECK (bucket_id = 'product-images' AND public.has_role(auth.uid(), 'admin'));

CREATE POLICY "Admins delete product images"
ON storage.objects FOR DELETE TO authenticated
USING (bucket_id = 'product-images' AND public.has_role(auth.uid(), 'admin'));

-- 2. Orders: remove permissive public INSERT (creation goes through the server function with service role)
DROP POLICY IF EXISTS "public insert orders" ON public.orders;
DROP POLICY IF EXISTS "public insert order items" ON public.order_items;

-- 3. has_role: switch to SECURITY INVOKER (user_roles RLS lets users read their own roles, which is sufficient)
CREATE OR REPLACE FUNCTION public.has_role(_user_id uuid, _role app_role)
RETURNS boolean
LANGUAGE sql
STABLE
SECURITY INVOKER
SET search_path = public
AS $$
  SELECT EXISTS (SELECT 1 FROM public.user_roles WHERE user_id = _user_id AND role = _role)
$$;
