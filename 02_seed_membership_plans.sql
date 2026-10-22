-- ============================================================================
-- Seed de membership_plans — alineado a los precios vigentes y al CHECK real
--
-- ADVERTENCIA: los valores de `features` son PROPUESTA. Ajuste cada uno a lo
-- que su producto realmente permite. MembershipService lee exactamente estas
-- claves: max_photos, max_videos, max_messages_day, max_direct_messages_day,
-- can_view_private_photos, can_video_call, can_see_visitors,
-- can_send_friend_request, grace_period_hours.
--
-- Idempotente: se puede correr varias veces. No toca filas que no sean de
-- estos slugs (respeta planes que agregue el módulo de administrador).
-- ============================================================================

begin;

insert into membership_plans
    (slug, name, description, price_promo, price_normal, duration_days,
     is_lifetime, is_active, promo_active, sort_order, features)
values
-- 1. Invitado — gratis, sin cobro. Existe porque el CHECK lo permite y
--    MembershipService lo usa como fallback.
('invitado', 'Invitado', 'Acceso limitado para conocer la plataforma',
 0, 0, null, false, true, false, 0,
 '{"max_photos":3,"max_videos":0,"max_messages_day":5,"max_direct_messages_day":2,
   "can_view_private_photos":false,"can_video_call":false,"can_see_visitors":false,
   "can_send_friend_request":true,"grace_period_hours":0}'::jsonb),

-- 2. Explorer — 1 mes. Es el escalón de entrada y el que ancla el precio.
('explorer', 'Explorer', 'Estoy explorando la plataforma',
 180.00, 297.00, 30, false, true, true, 1,
 '{"max_photos":10,"max_videos":1,"max_messages_day":30,"max_direct_messages_day":10,
   "can_view_private_photos":false,"can_video_call":false,"can_see_visitors":true,
   "can_send_friend_request":true,"grace_period_hours":72}'::jsonb),

-- 3. Connectors — 3 meses
('connectors', 'Connectors', 'Quiero conectar con más gente',
 299.00, 493.00, 90, false, true, true, 2,
 '{"max_photos":25,"max_videos":5,"max_messages_day":100,"max_direct_messages_day":40,
   "can_view_private_photos":true,"can_video_call":true,"can_see_visitors":true,
   "can_send_friend_request":true,"grace_period_hours":72}'::jsonb),

-- 4. Influencer — 6 meses
('influencer', 'Influencer', 'Quiero destacar en la comunidad',
 599.00, 988.00, 180, false, true, true, 3,
 '{"max_photos":60,"max_videos":20,"max_messages_day":999,"max_direct_messages_day":999,
   "can_view_private_photos":true,"can_video_call":true,"can_see_visitors":true,
   "can_send_friend_request":true,"grace_period_hours":72}'::jsonb),

-- 5. VIP Elite — 1 año. El slug es vip_elite, no "vip".
('vip_elite', 'VIP Elite', 'Acceso completo todo el año',
 1199.00, 1978.00, 365, false, true, true, 4,
 '{"max_photos":999,"max_videos":999,"max_messages_day":999,"max_direct_messages_day":999,
   "can_view_private_photos":true,"can_video_call":true,"can_see_visitors":true,
   "can_send_friend_request":true,"grace_period_hours":168}'::jsonb),

-- 6. Fundador — PERMANENTE. El nombre comercial es "Fundador" pero el slug
--    DEBE ser 'vitalicio': es el valor que acepta memberships_tier_check.
--    Duración null + is_lifetime true → expires_at queda en NULL.
('vitalicio', 'Fundador', 'Solo 20 membresías disponibles',
 3500.00, 5775.00, null, true, true, true, 5,
 '{"max_photos":999,"max_videos":999,"max_messages_day":999,"max_direct_messages_day":999,
   "can_view_private_photos":true,"can_video_call":true,"can_see_visitors":true,
   "can_send_friend_request":true,"grace_period_hours":168}'::jsonb)

on conflict (slug) do update set
    name          = excluded.name,
    description   = excluded.description,
    price_promo   = excluded.price_promo,
    price_normal  = excluded.price_normal,
    duration_days = excluded.duration_days,
    is_lifetime   = excluded.is_lifetime,
    is_active     = excluded.is_active,
    promo_active  = excluded.promo_active,
    sort_order    = excluded.sort_order,
    features      = excluded.features,
    updated_at    = now();

commit;

-- ----------------------------------------------------------------------------
-- Verificación
-- ----------------------------------------------------------------------------
select slug, name, price_promo, price_normal, duration_days, is_lifetime,
       round(price_promo / nullif(duration_days/30.0, 0), 2) as promo_por_mes,
       round(100 - (price_promo / nullif(price_normal,0) * 100), 1) as desc_pct
from membership_plans
order by sort_order;

-- Cuota de Fundador: el tope de 20 no está en la base. Si es un límite duro,
-- hay que validarlo en la aplicación al crear el pedido (ver nota en el informe).
