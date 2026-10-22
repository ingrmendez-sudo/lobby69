-- ============================================================================
-- Club Lobby69 · Cobro SPEI — versión definitiva
--
-- Ajustada a lo que existe de verdad en Supabase (verificado, oct 2026):
--   · 43 tablas, todas con 0 filas → se valida de inmediato, sin NOT VALID
--   · membership_payments ya existe con los campos de revisión manual
--   · memberships es la fuente de verdad del tier (6 niveles)
--   · NO existe tabla de auditoría → payment_events sí es necesaria
--   · trigger_set_updated_at() ya existe en public
--
-- Versión 1.2 · octubre 2026
-- ============================================================================

begin;

-- ----------------------------------------------------------------------------
-- 1. Columnas nuevas en membership_payments
--    Se reutilizan: amount, payment_method, payment_reference, receipt_path,
--    reviewed_at, reviewed_by, admin_note, currency.
-- ----------------------------------------------------------------------------
alter table membership_payments
    add column if not exists public_code       varchar(24),
    add column if not exists email             varchar(191),
    add column if not exists expires_at        timestamptz,
    add column if not exists bank_tracking_key varchar(64),
    add column if not exists payer_name        varchar(120),
    add column if not exists payer_bank        varchar(80),
    add column if not exists plan_snapshot     jsonb,
    add column if not exists referral_code     varchar(32),
    add column if not exists metadata          jsonb not null default '{}'::jsonb;

-- ----------------------------------------------------------------------------
-- 2. Índices
--    Hoy la tabla solo tiene el índice de la llave primaria.
-- ----------------------------------------------------------------------------
create unique index if not exists membership_payments_public_code_key
    on membership_payments (public_code) where public_code is not null;

create unique index if not exists membership_payments_tracking_key
    on membership_payments (bank_tracking_key) where bank_tracking_key is not null;

create index if not exists membership_payments_user_idx
    on membership_payments (user_id, created_at desc);

create index if not exists membership_payments_status_idx
    on membership_payments (status, created_at desc);

create index if not exists membership_payments_expiry_queue_idx
    on membership_payments (status, expires_at) where expires_at is not null;

create index if not exists membership_payments_email_idx
    on membership_payments (lower(email)) where email is not null;

create index if not exists membership_payments_referral_idx
    on membership_payments (referral_code) where referral_code is not null;

-- ----------------------------------------------------------------------------
-- 3. Restricciones — validación inmediata (no hay filas históricas)
-- ----------------------------------------------------------------------------

-- Estados alineados a los que ya usa la app: pending → under_review →
-- approved / rejected, más expired para la ventana de pago vencida.
alter table membership_payments
    drop constraint if exists membership_payments_status_check;
alter table membership_payments
    add constraint membership_payments_status_check
    check (status in ('pending', 'under_review', 'approved', 'rejected', 'expired'));

-- Aprobar exige revisión registrada y referencia bancaria verificable.
-- Defensa estructural contra aprobar con una captura editada.
alter table membership_payments
    drop constraint if exists membership_payments_approved_check;
alter table membership_payments
    add constraint membership_payments_approved_check
    check (
        status <> 'approved'
        or (
            reviewed_at is not null
            and (bank_tracking_key is not null or payment_reference is not null)
        )
    );

alter table membership_payments
    drop constraint if exists membership_payments_rejected_check;
alter table membership_payments
    add constraint membership_payments_rejected_check
    check (status <> 'rejected' or admin_note is not null);

alter table membership_payments
    drop constraint if exists membership_payments_amount_check;
alter table membership_payments
    add constraint membership_payments_amount_check
    check (amount is null or amount > 0);

alter table membership_payments
    drop constraint if exists membership_payments_expiry_check;
alter table membership_payments
    add constraint membership_payments_expiry_check
    check (expires_at is null or created_at is null or expires_at > created_at);

-- ----------------------------------------------------------------------------
-- 4. updated_at automático
--    Reutiliza trigger_set_updated_at() si existe; si no, crea una propia.
--    Nunca duplica: el bloque comprueba antes de crear.
-- ----------------------------------------------------------------------------
do $$
begin
    if exists (
        select 1 from pg_proc p
        join pg_namespace n on n.oid = p.pronamespace
        where n.nspname = 'public' and p.proname = 'trigger_set_updated_at'
    ) then
        drop trigger if exists membership_payments_touch on membership_payments;
        create trigger membership_payments_touch
            before update on membership_payments
            for each row execute function trigger_set_updated_at();
        raise notice 'Reutilizando trigger_set_updated_at() existente';
    else
        create or replace function membership_payments_touch_updated_at()
        returns trigger as $f$
        begin
            new.updated_at = now();
            return new;
        end;
        $f$ language plpgsql;

        drop trigger if exists membership_payments_touch on membership_payments;
        create trigger membership_payments_touch
            before update on membership_payments
            for each row execute function membership_payments_touch_updated_at();
        raise notice 'Creada función propia: membership_payments_touch_updated_at()';
    end if;
end $$;

-- ----------------------------------------------------------------------------
-- 5. Bitácora de eventos — tabla nueva (no hay auditoría en la base)
--    order_id es bigint porque membership_payments.id es bigserial.
-- ----------------------------------------------------------------------------
create table if not exists payment_events (
    id          bigserial   primary key,
    order_id    bigint      not null
                references membership_payments (id) on delete restrict,
    type        varchar(32) not null,
    actor_type  varchar(16) not null default 'system',
    actor_id    varchar(100),
    payload     jsonb       not null default '{}'::jsonb,
    ip          inet,
    user_agent  text,
    created_at  timestamptz not null default now(),

    constraint payment_events_type_check check (
        type in ('created','proof_uploaded','review_started','approved',
                 'rejected','expired','email_sent','reminder_sent','note_added')
    ),
    constraint payment_events_actor_type_check check (
        actor_type in ('system','customer','admin')
    ),
    constraint payment_events_admin_actor_check check (
        actor_type <> 'admin' or actor_id is not null
    )
);

-- Un pedido se aprueba una sola vez: hace imposible la doble aprobación.
create unique index if not exists payment_events_single_approval_idx
    on payment_events (order_id) where type = 'approved';

create index if not exists payment_events_order_timeline_idx
    on payment_events (order_id, created_at desc);

create index if not exists payment_events_type_created_idx
    on payment_events (type, created_at desc);

-- ----------------------------------------------------------------------------
-- 6. Append-only a nivel de base
-- ----------------------------------------------------------------------------
create or replace function payment_events_block_mutation()
returns trigger as $$
begin
    raise exception 'payment_events es append-only: % no está permitido', tg_op
        using hint = 'Registra un evento nuevo en lugar de modificar el anterior.';
end;
$$ language plpgsql;

drop trigger if exists payment_events_no_update on payment_events;
create trigger payment_events_no_update
    before update on payment_events
    for each row execute function payment_events_block_mutation();

drop trigger if exists payment_events_no_delete on payment_events;
create trigger payment_events_no_delete
    before delete on payment_events
    for each row execute function payment_events_block_mutation();

-- ----------------------------------------------------------------------------
-- 7. RLS (Supabase): cerrado por defecto, sin políticas.
--    Laravel entra con el rol dueño o service_role, que no pasan por RLS.
-- ----------------------------------------------------------------------------
alter table payment_events enable row level security;

commit;

-- ============================================================================
-- ROLLBACK
-- ============================================================================
-- drop table if exists payment_events;
-- drop function if exists payment_events_block_mutation();
-- drop trigger if exists membership_payments_touch on membership_payments;
-- alter table membership_payments
--   drop constraint if exists membership_payments_status_check,
--   drop constraint if exists membership_payments_approved_check,
--   drop constraint if exists membership_payments_rejected_check,
--   drop constraint if exists membership_payments_amount_check,
--   drop constraint if exists membership_payments_expiry_check;
-- drop index if exists membership_payments_public_code_key;
-- drop index if exists membership_payments_tracking_key;
-- drop index if exists membership_payments_user_idx;
-- drop index if exists membership_payments_status_idx;
-- drop index if exists membership_payments_expiry_queue_idx;
-- drop index if exists membership_payments_email_idx;
-- drop index if exists membership_payments_referral_idx;
-- alter table membership_payments
--   drop column if exists public_code, drop column if exists email,
--   drop column if exists expires_at, drop column if exists bank_tracking_key,
--   drop column if exists payer_name, drop column if exists payer_bank,
--   drop column if exists plan_snapshot, drop column if exists referral_code,
--   drop column if exists metadata;
