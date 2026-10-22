# Bugs encontrados en `MembershipService` / `Membership`

Cuatro defectos, verificados leyendo el código y ejecutando el flujo contra un
PostgreSQL con tu esquema real. Tres rompen el cobro SPEI directamente.

---

## BUG 1 · Crítico — La renovación falla y deja al usuario SIN membresía

### Qué pasa

`Membership::activateForUser()` hace esto, en este orden:

```php
// 1. Marca la anterior como superseded
UPDATE memberships SET status = 'superseded' WHERE user_id = ? AND status = 'active'

// 2. Inserta una nueva
INSERT INTO memberships (...) VALUES (...)
```

Pero `memberships.user_id` tiene **`UNIQUE`**:

```
memberships_user_id_unique  user_id  btree, unique
```

El `INSERT` de una segunda fila para el mismo usuario **siempre** viola la
restricción. Y como el `UPDATE` del paso 1 ya se ejecutó y no hay transacción
que lo revierta, el resultado es el peor posible:

> El usuario pagó, su membresía anterior quedó `superseded`, y la nueva nunca
> se creó. **No tiene nada.**

### Evidencia

Ejecuté el flujo completo contra una réplica con tu esquema:

```
PRIMERA activación (compra)      → OK
SEGUNDA activación (renovación)  → rechazada por memberships_user_id_unique

Estado final: 1 fila · explorer:superseded
```

Una sola fila, marcada como reemplazada, sin reemplazo.

### Por qué importa para SPEI

Un pago por transferencia es, por definición, una renovación la mayoría de las
veces. `activateForUser()` se rompe exactamente en el caso de uso principal.

### Arreglo

Reemplazar `INSERT` por `UPDATE ... ON CONFLICT`. Ver el archivo
`Membership.php` corregido:

```php
DB::statement("
    INSERT INTO memberships
        (id, user_id, tier, price, currency, payment_method, transaction_id,
         started_at, expires_at, auto_renew, status, created_at, updated_at)
    VALUES
        (?::uuid, ?::uuid, ?, ?, 'MXN', ?, ?, NOW(), ?, false, 'active', NOW(), NOW())
    ON CONFLICT (user_id) DO UPDATE SET
        tier           = EXCLUDED.tier,
        price          = EXCLUDED.price,
        payment_method = EXCLUDED.payment_method,
        transaction_id = EXCLUDED.transaction_id,
        started_at     = EXCLUDED.started_at,
        expires_at     = EXCLUDED.expires_at,
        auto_renew     = EXCLUDED.auto_renew,
        status         = 'active',
        updated_at     = NOW()
", [...]);
```

Y todo dentro de `DB::transaction()`, para que un fallo no deje al usuario a medias.

### Nota de diseño

Con `user_id` único, el histórico de membresías **no puede vivir en
`memberships`**: solo guarda el derecho vigente. El histórico de compras ya lo
tienen `membership_payments` (cada pedido) y ahora `payment_events` (cada
evento). Eso es correcto — no hay que quitar el `UNIQUE`.

---

## BUG 2 · Alto — El usuario paga y sigue viendo el plan viejo

### Qué pasa

Dos claves de caché distintas para el mismo dato:

```php
// MembershipService::getPlan()
Cache::remember("membership_plan_{$userId}", 300, ...)

// Membership::activateForUser()
cache()->forget("user.membership.{$userId}")
```

`activateForUser()` borra una clave que **nadie lee**. La clave real sigue viva
hasta 300 segundos.

### Consecuencia

Tras aprobar un pago, el usuario entra y sigue en `invitado` hasta 5 minutos.
En el flujo SPEI es peor que en tarjeta: el cliente acaba de esperar horas por
la confirmación manual, y encima el acceso no aparece. Es el reclamo garantizado.

### Arreglo

Una sola fuente para la clave:

```php
// en MembershipService
public static function cacheKey($userId): string
{
    return "membership_plan_{$userId}";
}
```

Y en `activateForUser()`, al final:

```php
\App\Services\MembershipService::clearCache($userId);
```

`clearCache()` ya existe y usa la clave correcta. Solo hay que llamarlo.

---

## BUG 3 · Alto — `getSlug()` siempre devuelve `invitado`

### Qué pasa

`getPlan()` construye y devuelve un objeto con **tres** propiedades:

```php
return (object) [
    'slug'     => $slug,
    'features' => $features,
    'plan'     => $plan,
];
```

Pero `getSlug()` lee otra:

```php
return self::getPlan($userId)->active_slug ?? 'invitado';
```

`active_slug` no existe → `null` → `'invitado'`, **siempre**.

### Consecuencias

- `hasMinLevel($userId, 'vip_elite')` devuelve `false` para todos, incluidos los VIP.
- Cualquier gate de acceso basado en `getSlug()` niega el contenido a quien pagó.
- `summary()` lee `$plan->name` y `$plan->active_slug`, que tampoco existen:
  devuelve `null` en ambos campos.

### Arreglo

```php
public static function getSlug($userId): string
{
    return self::getPlan($userId)->slug ?? 'invitado';
}
```

Y en `summary()`, usar el objeto anidado:

```php
'plan'      => $plan->slug,
'plan_name' => $plan->plan->name ?? null,
```

---

## BUG 4 · Medio — Los niveles no coinciden con la restricción de la base

### Qué pasa

Tres listas de tiers que no concuerdan:

| Fuente | Valores |
|---|---|
| `CHECK memberships_tier_check` | `invitado`, `explorer`, `connectors`, `influencer`, `vip_elite`, `vitalicio` |
| `MembershipService::$levels` | `invitado`, **`trial`**, `explorer`, `connectors`, `influencer`, `vip_elite`, **`fundador`** |
| `hasMinLevel()::$levels` | `invitado`, `explorer`, `connectors`, `influencer`, `vip_elite`, **`fundador`** |
| Default de la columna | **`free`** |

- `trial` y `fundador` **no existen** en el `CHECK`: un `INSERT` con esos valores falla.
- `vitalicio` **sí existe** en el `CHECK` pero **no** está en los mapas de niveles:
  un vitalicio se evalúa como nivel `0`, igual que `invitado`.
- `free` es el default de la columna y tampoco está permitido. Solo no explota
  porque siempre se pasa el tier explícito.

### Arreglo

Una sola constante, alineada al `CHECK`:

```php
public const LEVELS = [
    'invitado'   => 0,
    'explorer'   => 1,
    'connectors' => 2,
    'influencer' => 3,
    'vip_elite'  => 4,
    'vitalicio'  => 5,   // ← faltaba: es el tope real
];
```

Y corregir el default de la columna:

```sql
alter table memberships alter column tier set default 'invitado';
```

---

## Resumen

| # | Bug | Severidad | Efecto |
|---|---|---|---|
| 1 | `activateForUser` viola `UNIQUE`, sin transacción | **Crítico** | Renovar deja al usuario sin membresía |
| 2 | Claves de caché distintas | **Alto** | Paga y sigue viendo el plan viejo 5 min |
| 3 | `getSlug()` lee propiedad inexistente | **Alto** | Todos son `invitado` para los gates |
| 4 | Tiers desalineados del `CHECK` | Medio | `vitalicio` vale 0; `trial`/`fundador` fallan |

Los cuatro afectan el cobro SPEI de forma directa: el 1 y el 2 se disparan
justo al aprobar un pago.

**Ninguno se detecta con `php artisan test`** porque las 19 pruebas no cubren
membresías. Conviene añadir pruebas de activación y renovación antes de tocar
el flujo de cobro.
