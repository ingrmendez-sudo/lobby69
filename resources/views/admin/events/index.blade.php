@extends('layouts.admin')
@php use Illuminate\Support\Facades\Storage; @endphp

@section('title', 'Eventos')
@section('page-title', 'Gestión de Eventos')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <p style="color:var(--theme-muted);font-size:.85rem;margin-top:.25rem;">
            {{ $events->total() }} evento(s) registrado(s)
        </p>
    </div>
    <a href="{{ route('admin.events.create') }}"
       style="padding:.5rem 1.2rem;background:var(--theme-accent);color:#fff;border-radius:8px;text-decoration:none;font-size:.85rem;display:flex;align-items:center;gap:.5rem;">
        <i class="fas fa-plus"></i> Nuevo evento
    </a>
</div>

@if(session('success'))
<div style="background:#22c55e22;border:1px solid #22c55e;color:#22c55e;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@if($events->isEmpty())
<div class="adm-card" style="padding:3rem;text-align:center;color:var(--theme-muted);">
    <i class="fas fa-calendar-alt" style="font-size:3rem;opacity:.2;display:block;margin-bottom:1rem;"></i>
    <p style="font-size:.9rem;">No hay eventos registrados aún.</p>
    <a href="{{ route('admin.events.create') }}"
       style="display:inline-block;margin-top:1rem;padding:.5rem 1.2rem;background:var(--theme-accent);color:#fff;border-radius:8px;text-decoration:none;font-size:.85rem;">
        <i class="fas fa-plus"></i> Crear primer evento
    </a>
</div>
@else
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem;">
    @foreach($events as $event)
    <div class="adm-card" style="overflow:hidden;display:flex;flex-direction:column;">

        {{-- Imagen --}}
        @if($event->image_path)
        <div style="height:160px;overflow:hidden;background:var(--theme-border);">
            <img loading="lazy" src="{{ Storage::url($event->image_path) }}" alt="{{ $event->title }}"
                style="width:100%;height:100%;object-fit:cover;"
                onerror="this.parentElement.style.display='none'">
        </div>

        @else
        <div style="height:100px;background:linear-gradient(135deg,#6C3FC522,#ec489922);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-calendar-star" style="font-size:2rem;color:var(--theme-accent);opacity:.4;"></i>
        </div>
        @endif

        {{-- Contenido --}}
        <div style="padding:1rem;flex:1;display:flex;flex-direction:column;gap:.5rem;">

            {{-- Badges --}}
            <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                @if($event->is_published ?? false)
                    <span style="background:#22c55e22;color:#22c55e;padding:.15rem .5rem;border-radius:20px;font-size:.7rem;font-weight:600;">
                        <i class="fas fa-circle" style="font-size:.45rem;"></i> Publicado
                    </span>
                @else
                    <span style="background:#f59e0b22;color:#f59e0b;padding:.15rem .5rem;border-radius:20px;font-size:.7rem;font-weight:600;">
                        <i class="fas fa-circle" style="font-size:.45rem;"></i> Borrador
                    </span>
                @endif
                @if($event->is_online ?? false)
                    <span style="background:#06b6d422;color:#06b6d4;padding:.15rem .5rem;border-radius:20px;font-size:.7rem;font-weight:600;">
                        <i class="fas fa-wifi"></i> Online
                    </span>
                @endif
            </div>

            {{-- Título --}}
            <h3 style="font-size:.95rem;font-weight:700;color:var(--theme-text);margin:0;line-height:1.3;">
                {{ $event->title }}
            </h3>

            {{-- Fecha y lugar --}}
            <div style="font-size:.78rem;color:var(--theme-muted);display:flex;flex-direction:column;gap:.2rem;">
                @if($event->starts_at)
                <span>
                    <i class="fas fa-calendar" style="width:14px;"></i>
                    {{ \Carbon\Carbon::parse($event->starts_at)->format('d/m/Y H:i') }}
                </span>
                @endif
                @if($event->address)
                <span>
                    <i class="fas fa-map-marker-alt" style="width:14px;"></i>
                    {{ $event->address }}
                </span>
                @endif
            </div>

            {{-- Descripción --}}
            @if($event->description)
            <p style="font-size:.78rem;color:var(--theme-muted);line-height:1.5;margin:0;
                       overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                {{ $event->description }}
            </p>
            @endif

            {{-- Acciones --}}
            <div style="display:flex;gap:.5rem;margin-top:auto;padding-top:.75rem;border-top:1px solid var(--theme-border);">

                {{-- Publicar/Despublicar --}}
                <form method="POST" action="{{ route('admin.events.update', $event->id) }}" style="flex:1;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="title"       value="{{ $event->title }}">
                    <input type="hidden" name="description" value="{{ $event->description }}">
                    <input type="hidden" name="event_date"  value="{{ $event->starts_at }}">
                    <input type="hidden" name="location"    value="{{ $event->address }}">
                    <input type="hidden" name="image_path"   value="{{ $event->image_path }}">
                    <input type="hidden" name="is_online"   value="{{ $event->is_online ? '1' : '0' }}">
                    <input type="hidden" name="is_published" value="{{ ($event->is_published ?? false) ? '0' : '1' }}">
                    <button type="submit"
                            style="width:100%;padding:.3rem .6rem;border-radius:6px;font-size:.75rem;cursor:pointer;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-muted);">
                        @if($event->is_published ?? false)
                            <i class="fas fa-eye-slash"></i> Despublicar
                        @else
                            <i class="fas fa-eye"></i> Publicar
                        @endif
                    </button>
                </form>

                {{-- Editar --}}
                <a href="{{ route('admin.events.edit', $event->id) }}"
                   style="padding:.3rem .8rem;background:var(--theme-accent);color:#fff;border-radius:6px;font-size:.75rem;text-decoration:none;display:flex;align-items:center;gap:.3rem;">
                    <i class="fas fa-edit"></i> Editar
                </a>

                {{-- Eliminar --}}
                <form method="POST" action="{{ route('admin.events.destroy', $event->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('¿Eliminar el evento «{{ addslashes($event->title) }}»?')"
                            style="padding:.3rem .7rem;background:#ef444422;color:#ef4444;border:1px solid #ef4444;border-radius:6px;font-size:.75rem;cursor:pointer;">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>

            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Paginación --}}
@if($events->hasPages())
<div style="margin-top:1.5rem;display:flex;justify-content:center;">
    {{ $events->links() }}
</div>
@endif
@endif

@endsection


