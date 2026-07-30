@extends('layouts.admin')

@section('title', 'Gestión de Usuarios')

@section('content')

{{-- Header --}}
<div class="adm-page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <h1 class="adm-page-title">
        <i class="fas fa-users"></i> Gestión de Usuarios
    </h1>
    <a href="{{ route('admin.users.export') }}"
       style="padding:.45rem 1rem;background:var(--theme-accent);color:#fff;border-radius:6px;font-size:.85rem;text-decoration:none;">
        <i class="fas fa-file-csv"></i> Exportar CSV
    </a>
</div>

{{-- Stats --}}
@if($stats)
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.75rem;margin-bottom:1.5rem;">
    <div class="adm-card" style="text-align:center;padding:.9rem;">
        <div style="font-size:1.5rem;font-weight:700;color:var(--theme-accent);">{{ $stats->total }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Total</div>
    </div>
    <div class="adm-card" style="text-align:center;padding:.9rem;">
        <div style="font-size:1.5rem;font-weight:700;color:#22c55e;">{{ $stats->activos }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Activos</div>
    </div>
    <div class="adm-card" style="text-align:center;padding:.9rem;">
        <div style="font-size:1.5rem;font-weight:700;color:#ef4444;">{{ $stats->suspendidos }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Suspendidos</div>
    </div>
    <div class="adm-card" style="text-align:center;padding:.9rem;">
        <div style="font-size:1.5rem;font-weight:700;color:#f59e0b;">{{ $stats->trial }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Invitado</div>
    </div>
    <div class="adm-card" style="text-align:center;padding:.9rem;">
        <div style="font-size:1.5rem;font-weight:700;color:#a855f7;">{{ $stats->premium }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">VIP Elite</div>
    </div>
    <div class="adm-card" style="text-align:center;padding:.9rem;">
        <div style="font-size:1.5rem;font-weight:700;color:#ec4899;">{{ $stats->vip }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Fundador</div>
    </div>
    <div class="adm-card" style="text-align:center;padding:.9rem;">
        <div style="font-size:1.5rem;font-weight:700;color:#06b6d4;">{{ $stats->nuevos_semana }}</div>
        <div style="font-size:.72rem;color:var(--theme-muted);text-transform:uppercase;">Nuevos (7d)</div>
    </div>
</div>
@endif

{{-- Notificación --}}
@if(session('success'))
<div style="background:#22c55e22;border:1px solid #22c55e;color:#22c55e;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- Filtros --}}
<div class="adm-card" style="margin-bottom:1.5rem;padding:1rem;">
    <form method="GET" action="{{ route('admin.users.index') }}"
          style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;">
        <div style="flex:1;min-width:180px;">
            <label style="font-size:.72rem;color:var(--theme-muted);display:block;margin-bottom:.2rem;">Buscar</label>
            <input type="text" name="q" value="{{ $search }}" placeholder="usuario, email, nickname, ciudad…"
                   style="width:100%;padding:.4rem .7rem;border-radius:6px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.83rem;">
        </div>
        <div style="min-width:130px;">
            <label style="font-size:.72rem;color:var(--theme-muted);display:block;margin-bottom:.2rem;">Membresía</label>
            <select name="membresia" style="width:100%;padding:.4rem .7rem;border-radius:6px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.83rem;">
                <option value="invitado"   {{ ($membership??'')==='invitado'   ? 'selected' : '' }}>Invitado</option>
                <option value="explorer"   {{ ($membership??'')==='explorer'   ? 'selected' : '' }}>Explorer</option>
                <option value="connectors" {{ ($membership??'')==='connectors' ? 'selected' : '' }}>Connectors</option>
                <option value="influencer" {{ ($membership??'')==='influencer' ? 'selected' : '' }}>Influencer</option>
                <option value="vip_elite"  {{ ($membership??'')==='vip_elite'  ? 'selected' : '' }}>VIP Elite</option>
                <option value="fundador"   {{ ($membership??'')==='fundador'   ? 'selected' : '' }}>Fundador</option>
            </select>
        </div>
        <div style="min-width:130px;">
            <label style="font-size:.72rem;color:var(--theme-muted);display:block;margin-bottom:.2rem;">Estado</label>
            <select name="estado" style="width:100%;padding:.4rem .7rem;border-radius:6px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.83rem;">
                <option value="">Todos</option>
                <option value="activo"     {{ $status==='activo'     ?'selected':'' }}>Activo</option>
                <option value="suspendido" {{ $status==='suspendido' ?'selected':'' }}>Suspendido</option>
            </select>
        </div>
        <div style="min-width:130px;">
            <label style="font-size:.72rem;color:var(--theme-muted);display:block;margin-bottom:.2rem;">Verificado</label>
            <select name="verificado" style="width:100%;padding:.4rem .7rem;border-radius:6px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.83rem;">
                <option value="">Todos</option>
                <option value="si" {{ $verified==='si' ?'selected':'' }}>Verificados</option>
                <option value="no" {{ $verified==='no' ?'selected':'' }}>No verificados</option>
            </select>
        </div>
        <button type="submit" style="padding:.4rem 1rem;background:var(--theme-accent);color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:.83rem;">
            <i class="fas fa-search"></i> Filtrar
        </button>
        @if($search || $membership || $status || $verified)
        <a href="{{ route('admin.users.index') }}" style="padding:.4rem .9rem;background:var(--theme-muted);color:#fff;border-radius:6px;font-size:.83rem;text-decoration:none;">
            <i class="fas fa-times"></i> Limpiar
        </a>
        @endif
    </form>
</div>

{{-- Tabla --}}
<div class="adm-card" style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
        <thead>
            <tr style="border-bottom:2px solid var(--theme-border);">
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Usuario</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Ubicación</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Membresía</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:center;">Fotos</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:center;">Estado</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Registro</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:left;">Último acceso</th>
                <th style="padding:.6rem .8rem;color:var(--theme-muted);font-weight:600;text-align:center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr style="border-bottom:1px solid var(--theme-border);" id="row-{{ $user->id }}">

                {{-- Usuario --}}
                <td style="padding:.6rem .8rem;">
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <div>
                            <div style="font-weight:600;color:var(--theme-text);">
                                {{ $user->nickname ?? $user->display_name ?? $user->username }}
                                @if($user->verified_profile)
                                    <i class="fas fa-check-circle" style="color:#22c55e;font-size:.7rem;" title="Verificado"></i>
                                @endif
                            </div>
                            <div style="font-size:.72rem;color:var(--theme-muted);">
                                @{{ $user->username }} · {{ $user->email }}
                            </div>
                            <div style="font-size:.7rem;color:var(--theme-muted);">
                                {{ $user->gender ?? '—' }} · {{ $user->age ? $user->age.' años' : '' }}
                                {{ $user->orientation ? '· '.$user->orientation : '' }}
                            </div>
                        </div>
                    </div>
                </td>

                {{-- Ubicación --}}
                <td style="padding:.6rem .8rem;color:var(--theme-muted);">
                    {{ $user->city ?? '—' }}{{ $user->state ? ', '.$user->state : '' }}
                </td>

                {{-- Membresía con cambio inline --}}
                <td style="padding:.6rem .8rem;">
                    <form method="POST" action="{{ route('admin.users.membership', $user->id) }}">
                        @csrf
                        <select name="tier" onchange="this.form.submit()"
                                style="padding:.22rem .5rem;border-radius:5px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.76rem;cursor:pointer;">
                            <option value="invitado"   {{ $user->membership_type==='invitado'   ?'selected':' }}>Invitado</option>
                            <option value="explorer"   {{ $user->membership_type==='explorer'   ?'selected':' }}>Explorer</option>
                            <option value="connectors" {{ $user->membership_type==='connectors' ?'selected':' }}>Connectors</option>
                            <option value="influencer" {{ $user->membership_type==='influencer' ?'selected':' }}>Influencer</option>
                            <option value="vip_elite"  {{ $user->membership_type==='vip_elite'  ?'selected':' }}>VIP Elite</option>
                            <option value="Fundador"  {{ $user->membership_type==='Fundador'  ?'selected':' }}>Fundador</option>
                        </select>
                    </form>
                    @if($user->membership_expires_at)
                    <div style="font-size:.68rem;color:var(--theme-muted);margin-top:.2rem;">
                        Vence: {{ \Carbon\Carbon::parse($user->membership_expires_at)->format('d/m/Y') }}
                    </div>
                    @endif
                </td>

                {{-- Fotos --}}
                <td style="padding:.6rem .8rem;text-align:center;">
                    <a href="{{ route('admin.photos.index') }}?user={{ $user->id }}"
                       style="color:var(--theme-accent);font-weight:600;">
                        {{ $user->photo_count }}
                    </a>
                </td>

                {{-- Estado --}}
                <td style="padding:.6rem .8rem;text-align:center;">
                    @if($user->active)
                        <span style="background:#22c55e22;color:#22c55e;padding:.2rem .55rem;border-radius:20px;font-size:.72rem;font-weight:600;">Activo</span>
                    @else
                        <span style="background:#ef444422;color:#ef4444;padding:.2rem .55rem;border-radius:20px;font-size:.72rem;font-weight:600;">Suspendido</span>
                    @endif
                </td>

                {{-- Registro --}}
                <td style="padding:.6rem .8rem;color:var(--theme-muted);font-size:.75rem;">
                    {{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}
                </td>

                {{-- Último acceso --}}
                <td style="padding:.6rem .8rem;color:var(--theme-muted);font-size:.75rem;">
                    {{ $user->last_seen_at ? \Carbon\Carbon::parse($user->last_seen_at)->diffForHumans() : '—' }}
                </td>

                {{-- Acciones --}}
                <td style="padding:.6rem .8rem;text-align:center;">
                    <div style="display:flex;gap:.3rem;justify-content:center;flex-wrap:wrap;">

                        {{-- Ver detalle --}}
                        <button onclick="verDetalle('{{ $user->id }}')"
                                style="padding:.22rem .5rem;background:var(--theme-accent);color:#fff;border:none;border-radius:5px;font-size:.72rem;cursor:pointer;"
                                title="Ver perfil completo">
                            <i class="fas fa-eye"></i>
                        </button>

                        {{-- Suspender / Activar --}}
                        @if($user->active)
                        <form method="POST" action="{{ route('admin.users.suspend', $user->id) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('¿Suspender a {{ addslashes($user->username) }}?')"
                                    style="padding:.22rem .5rem;background:#ef444422;color:#ef4444;border:1px solid #ef4444;border-radius:5px;font-size:.72rem;cursor:pointer;"
                                    title="Suspender">
                                <i class="fas fa-ban"></i>
                            </button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('admin.users.activate', $user->id) }}">
                            @csrf
                            <button type="submit"
                                    style="padding:.22rem .5rem;background:#22c55e22;color:#22c55e;border:1px solid #22c55e;border-radius:5px;font-size:.72rem;cursor:pointer;"
                                    title="Activar">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        @endif

                        {{-- Reset contraseña --}}
                        <form method="POST" action="{{ route('admin.users.reset-password', $user->id) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('¿Resetear contraseña de {{ addslashes($user->username) }}?')"
                                    style="padding:.22rem .5rem;background:#f59e0b22;color:#f59e0b;border:1px solid #f59e0b;border-radius:5px;font-size:.72rem;cursor:pointer;"
                                    title="Resetear contraseña">
                                <i class="fas fa-key"></i>
                            </button>
                        </form>

                        {{-- Eliminar --}}
                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('⚠️ ELIMINAR cuenta de {{ addslashes($user->username) }}?\nEsta acción es irreversible y borrará todos sus datos.')"
                                    style="padding:.22rem .5rem;background:#dc262622;color:#dc2626;border:1px solid #dc2626;border-radius:5px;font-size:.72rem;cursor:pointer;"
                                    title="Eliminar cuenta">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>

                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="padding:2.5rem;text-align:center;color:var(--theme-muted);">
                    <i class="fas fa-users" style="font-size:2rem;opacity:.2;display:block;margin-bottom:.5rem;"></i>
                    No se encontraron usuarios con los filtros aplicados.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación --}}
@if($users->hasPages())
<div style="margin-top:1rem;display:flex;justify-content:center;">
    {{ $users->appends(request()->query())->links() }}
</div>
@endif

{{-- Modal detalle usuario --}}
<div id="modalDetalle" style="display:none;position:fixed;inset:0;background:#000a;z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--theme-card);border-radius:12px;padding:1.5rem;max-width:560px;width:90%;max-height:85vh;overflow-y:auto;position:relative;">
        <button onclick="cerrarModal()"
                style="position:absolute;top:.75rem;right:.75rem;background:none;border:none;color:var(--theme-muted);font-size:1.2rem;cursor:pointer;">
            <i class="fas fa-times"></i>
        </button>
        <div id="modalDetalleContent" style="color:var(--theme-text);">
            <div style="text-align:center;padding:2rem;color:var(--theme-muted);">
                <i class="fas fa-spinner fa-spin"></i> Cargando…
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function verDetalle(id) {
    document.getElementById('modalDetalle').style.display = 'flex';
    document.getElementById('modalDetalleContent').innerHTML =
        '<div style="text-align:center;padding:2rem;color:var(--theme-muted);"><i class="fas fa-spinner fa-spin"></i> Cargando…</div>';

    fetch('/admin/usuarios/' + id + '/detalle')
        .then(r => r.json())
        .then(d => {
            const u = d.user;
            const nombre = u.nickname || u.display_name || u.username;
            document.getElementById('modalDetalleContent').innerHTML = `
                <h2 style="margin:0 0 .25rem;font-size:1.1rem;">${nombre}</h2>
                <div style="color:var(--theme-muted);font-size:.8rem;margin-bottom:1rem;">@${u.username} · ${u.email}</div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem .75rem;font-size:.82rem;margin-bottom:1rem;">
                    <div><span style="color:var(--theme-muted);">Membresía:</span> <strong>${u.membership_type ?? '—'}</strong></div>
                    <div><span style="color:var(--theme-muted);">Estado:</span>
                        <strong style="color:${u.active ? '#22c55e' : '#ef4444'}">${u.active ? 'Activo' : 'Suspendido'}</strong>
                    </div>
                    <div><span style="color:var(--theme-muted);">Género:</span> ${u.gender ?? '—'}</div>
                    <div><span style="color:var(--theme-muted);">Edad:</span> ${u.age ?? '—'}</div>
                    <div><span style="color:var(--theme-muted);">Ciudad:</span> ${u.city ?? '—'}, ${u.state ?? ''}</div>
                    <div><span style="color:var(--theme-muted);">Orientación:</span> ${u.orientation ?? '—'}</div>
                    <div><span style="color:var(--theme-muted);">Verificado:</span> ${u.verified_profile ? '✅ Sí' : '❌ No'}</div>
                    <div><span style="color:var(--theme-muted);">Perfil completo:</span> ${u.profile_completed ? '✅ Sí' : '❌ No'}</div>
                    <div><span style="color:var(--theme-muted);">Verificación ID:</span> ${u.verification_status ?? '—'}</div>
                    <div><span style="color:var(--theme-muted);">Referidos:</span> ${u.referral_count ?? 0}</div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:.5rem;margin-bottom:1rem;text-align:center;">
                    <div class="adm-card" style="padding:.6rem;border-radius:8px;">
                        <div style="font-size:1.2rem;font-weight:700;color:var(--theme-accent);">${d.photo_count}</div>
                        <div style="font-size:.68rem;color:var(--theme-muted);">Fotos</div>
                    </div>
                    <div class="adm-card" style="padding:.6rem;border-radius:8px;">
                        <div style="font-size:1.2rem;font-weight:700;color:#ec4899;">${d.like_count}</div>
                        <div style="font-size:.68rem;color:var(--theme-muted);">Likes dados</div>
                    </div>
                    <div class="adm-card" style="padding:.6rem;border-radius:8px;">
                        <div style="font-size:1.2rem;font-weight:700;color:#06b6d4;">${d.comment_count}</div>
                        <div style="font-size:.68rem;color:var(--theme-muted);">Comentarios</div>
                    </div>
                    <div class="adm-card" style="padding:.6rem;border-radius:8px;">
                        <div style="font-size:1.2rem;font-weight:700;color:#a855f7;">${d.follow_count}</div>
                        <div style="font-size:.68rem;color:var(--theme-muted);">Siguiendo</div>
                    </div>
                    <div class="adm-card" style="padding:.6rem;border-radius:8px;">
                        <div style="font-size:1.2rem;font-weight:700;color:#f59e0b;">${d.follower_count}</div>
                        <div style="font-size:.68rem;color:var(--theme-muted);">Seguidores</div>
                    </div>
                </div>

                ${u.bio ? `<div style="font-size:.8rem;color:var(--theme-muted);padding:.75rem;background:var(--theme-bg);border-radius:8px;line-height:1.5;">${u.bio}</div>` : ''}
            `;
        })
        .catch(() => {
            document.getElementById('modalDetalleContent').innerHTML =
                '<div style="color:#ef4444;text-align:center;padding:1rem;"><i class="fas fa-exclamation-triangle"></i> Error al cargar datos.</div>';
        });
}

function cerrarModal() {
    document.getElementById('modalDetalle').style.display = 'none';
}

document.getElementById('modalDetalle').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
</script>
@endpush




