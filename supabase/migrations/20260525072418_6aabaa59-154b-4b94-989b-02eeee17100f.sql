
REVOKE EXECUTE ON FUNCTION public.has_role(UUID, app_role) FROM PUBLIC, anon, authenticated;
GRANT EXECUTE ON FUNCTION public.has_role(UUID, app_role) TO authenticated;
REVOKE EXECUTE ON FUNCTION public.handle_new_user() FROM PUBLIC, anon, authenticated;
REVOKE EXECUTE ON FUNCTION public.generate_order_number() FROM PUBLIC;
GRANT EXECUTE ON FUNCTION public.generate_order_number() TO anon, authenticated;
REVOKE EXECUTE ON FUNCTION public.touch_updated_at() FROM PUBLIC;

INSERT INTO public.categories (name, slug, description, sort_order) VALUES
  ('Compléments alimentaires','complements','Vitamines, minéraux et compléments pour votre santé',1),
  ('Soins du corps','soins-corps','Produits naturels pour le bien-être quotidien',2),
  ('Énergie & Vitalité','energie','Boosters d''énergie et tonus',3),
  ('Beauté & Peau','beaute','Soins naturels pour une peau éclatante',4),
  ('Minceur','minceur','Solutions naturelles pour la silhouette',5);

INSERT INTO public.communes (name, zone, delivery_fee, delivery_days) VALUES
  ('Cocody','Abidjan',1500,1),
  ('Plateau','Abidjan',1500,1),
  ('Marcory','Abidjan',1500,1),
  ('Treichville','Abidjan',1500,1),
  ('Yopougon','Abidjan',2000,1),
  ('Abobo','Abidjan',2000,1),
  ('Adjamé','Abidjan',1500,1),
  ('Koumassi','Abidjan',1500,1),
  ('Port-Bouët','Abidjan',2000,2),
  ('Attécoubé','Abidjan',2000,2),
  ('Bingerville','Abidjan périphérie',2500,2),
  ('Songon','Abidjan périphérie',2500,2),
  ('Anyama','Abidjan périphérie',2500,2),
  ('Bouaké','Intérieur',3500,3),
  ('Yamoussoukro','Intérieur',3500,3),
  ('San-Pédro','Intérieur',4000,4),
  ('Daloa','Intérieur',4000,4),
  ('Korhogo','Intérieur',4500,5);
