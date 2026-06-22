@extends('layouts.app')
@section('title', 'Admin — Invitaciones')
@section('content')
<div class="container" style="padding-top:2rem;padding-bottom:4rem;">

    <div class="section-header">
        <div>
            <h1 class="h2">Panel Admin</h1>
            <p class="text-muted">Gestión de solicitudes de invitación</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn--ghost btn--sm">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="toast toast--success" style="position:relative;top:0;right:0;margin-bottom:1rem;animation:none;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="toast toast--error" style="position:relative;top:0;right:0;margin-bottom:1rem;animation:none;">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
        </div>
    @endif

    {{-- Contadores --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
        <div class="card" style="padding:1.5rem;text-align:center;border-top:3px solid #F39C12;">
            <div style="font-size:2rem;font-weight:700;color:#F39C12;">{{ $contadores['pending'] }}</div>
            <div class="text-sm text-muted">Pendientes</div>
        </div>
        <div class="card" style="padding:1.5rem;text-align:center;border-top:3px solid #27AE60;">
            <div style="font-size:2rem;font-weight:700;color:#27AE60;">{{ $contadores['approved'] }}</div>
            <div class="text-sm text-muted">Aprobadas</div>
        </div>
        <div class="card" style="padding:1.5rem;text-align:center;border-top:3px solid #E74C3C;">
            <div style="font-size:2rem;font-weight:700;color:#E74C3C;">{{ $contadores['rejected'] }}</div>
            <div class="text-sm text-muted">Rechazadas</div>
        </div>
        <div class="card" style="padding:1.5rem;text-align:center;border-top:3px solid #3498DB;">
            <div style="font-size:2rem;font-weight:700;color:#3498DB;">{{ $contadores['total'] }}</div>
            <div class="text-sm text-muted">Total</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card" style="padding:1.5rem;margin-bottom:1.5rem;">
        <form method="GET" action="{{ route('admin.invitations.index') }}" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
            <div class="form-group" style="margin:0;flex:1;min-width:180px;">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" placeholder="Nombre o email..." value="{{ $search }}">
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Estado</label>
                <select name="status" class="form-control">
                    <option value="all"      {{ $status==='all'      ?'selected':'' }}>Todos</option>
                    <option value="pending"  {{ $status==='pending'  ?'selected':'' }}>Pendientes</option>
                    <option value="approved" {{ $status==='approved' ?'selected':'' }}>Aprobados</option>
                    <option value="rejected" {{ $status==='rejected' ?'selected':'' }}>Rechazados</option>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Tipo</label>
                <select name="tipo_perfil" class="form-control">
                    <option value="">Todos</option>
                    <option value="single"    {{ $tipo==='single'    ?'selected':'' }}>Single</option>
                    <option value="unicornio" {{ $tipo==='unicornio' ?'selected':'' }}>Unicornio</option>
                    <option value="pareja"    {{ $tipo==='pareja'    ?'selected':'' }}>Pareja</option>
                </select>
            </div>
            <button type="submit" class="btn btn--primary btn--sm"><i class="fas fa-search"></i> Filtrar</button>
            <a href="{{ route('admin.invitations.index') }}" class="btn btn--ghost btn--sm">Limpiar</a>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:2px solid rgba(44,62,80,.08);">
                        <th style="padding:1rem;text-align:left;font-size:.8rem;color:#7F8C8D;text-transform:uppercase;">Nick</th>
                        <th style="padding:1rem;text-align:left;font-size:.8rem;color:#7F8C8D;text-transform:uppercase;">Email</th>
                        <th style="padding:1rem;text-align:left;font-size:.8rem;color:#7F8C8D;text-transform:uppercase;">Tipo</th>
                        <th style="padding:1rem;text-align:left;font-size:.8rem;color:#7F8C8D;text-transform:uppercase;">Estado</th>
                        <th style="padding:1rem;text-align:left;font-size:.8rem;color:#7F8C8D;text-transform:uppercase;">Fecha</th>
                        <th style="padding:1rem;text-align:left;font-size:.8rem;color:#7F8C8D;text-transform:uppercase;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($solicitudes as $s)
                    <tr style="border-bottom:1px solid rgba(44,62,80,.05);"
                        onmouseover="this.style.background='#FAF9F6'"
                        onmouseout="this.style.background=''">
                        <td style="padding:1rem;font-weight:600;">{{ $s->nombre }}</td>
                        <td style="padding:1rem;font-size:.9rem;color:#7F8C8D;">{{ $s->email }}</td>
                        <td style="padding:1rem;font-size:.85rem;">
                            @php $tipos=['single'=>'👤 Single','unicornio'=>'🦄 Unicornio','pareja'=>'👫 Pareja']; @endphp
                            {{ $tipos[$s->tipo_perfil] ?? $s->tipo_perfil }}
                        </td>
                        <td style="padding:1rem;">
                            @if($s->status==='pending')
                                <span class="badge" style="background:rgba(243,156,18,.12);color:#F39C12;">⏳ Pendiente</span>
                            @elseif($s->status==='approved')
                                <span class="badge" style="background:rgba(39,174,96,.12);color:#27AE60;">✅ Aprobado</span>
                            @else
                                <span class="badge" style="background:rgba(231,76,60,.12);color:#E74C3C;">❌ Rechazado</span>
                            @endif
                        </td>
                        <td style="padding:1rem;font-size:.85rem;color:#7F8C8D;">
                            {{ \Carbon\Carbon::parse($s->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td style="padding:1rem;">
                            <div style="display:flex;gap:.5rem;align-items:center;">
                                <a href="{{ route('admin.invitations.show', $s->id) }}" class="btn btn--ghost btn--sm" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($s->status==='pending')
                                <form method="POST" action="{{ route('admin.invitations.approve', $s->id) }}"
                                      onsubmit="return confirm('Aprobar a {{ $s->nombre }} y crear su cuenta?')">
                                    @csrf
                                    <button type="submit" class="btn btn--sm"
                                            style="background:rgba(39,174,96,.15);color:#27AE60;border:1px solid #27AE60;">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <button class="btn btn--sm"
                                        style="background:rgba(231,76,60,.15);color:#E74C3C;border:1px solid #E74C3C;"
                                        onclick="document.getElementById('modal-{{ $s->id }}').style.display='flex';document.body.style.overflow='hidden';">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:3rem;text-align:center;color:#7F8C8D;">
                            <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:1rem;"></i>
                            No hay solicitudes{{ $status!=='all' ? " con estado '$status'" : '' }}.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($solicitudes->hasPages())
        <div style="padding:1rem 1.5rem;border-top:1px solid rgba(44,62,80,.08);">
            {{ $solicitudes->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modales de rechazo (fuera de la tabla) --}}
@foreach($solicitudes as $s)
@if($s->status==='pending')
<div class="modal-overlay" id="modal-{{ $s->id }}" role="dialog">
    <div class="modal">
        <div class="modal__header">
            <h3 class="h4">Rechazar — {{ $s->nombre }}</h3>
            <button type="button" class="btn btn--ghost btn--sm"
                    onclick="document.getElementById('modal-{{ $s->id }}').style.display='none';document.body.style.overflow='';">
                &#x2715;
            </button>
        </div>
        <form method="POST" action="{{ route('admin.invitations.reject', $s->id) }}">
            @csrf
            <div class="modal__body">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Motivo del rechazo <span style="color:#E74C3C;">*</span></label>
                    <textarea name="admin_notes" class="form-control" rows="3"
                              placeholder="Indica el motivo..." required minlength="10"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--ghost"
                        onclick="document.getElementById('modal-{{ $s->id }}').style.display='none';document.body.style.overflow='';">
                    Cancelar
                </button>
                <button type="submit" class="btn" style="background:#E74C3C;color:#fff;">
                    <i class="fas fa-times"></i> Rechazar
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endforeach
@endsection