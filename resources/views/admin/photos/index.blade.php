@extends('layouts.app')
@section('title', 'Moderación de Fotos — Admin LOBBY69')
@section('content')
<div style="max-width:1200px;margin:2rem auto;padding:0 1rem;">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
    <div>
      <h1 style="font-size:1.6rem;font-weight:800;color:var(--color-text);margin:0;">🖼️ Moderación de Fotos</h1>
      <p style="color:#64748b;margin:.25rem 0 0;">Aprueba o rechaza las fotos subidas por los miembros</p>
    </div>
    <a href="{{ route('admin.invitations.index') }}" style="padding:.6rem 1rem;border:1px solid #e5e7eb;border-radius:8px;font-size:.9rem;color:#6b7280;text-decoration:none;">← Panel Admin</a>
  </div>

  @if(session('success'))
  <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;">✅ {{ session('success') }}</div>
  @endif

  {{-- Contadores --}}
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">
    @foreach(['pending'=>['⏳ Pendientes','#f59e0b'],'approved'=>['✅ Aprobadas','#10b981'],'rejected'=>['❌ Rechazadas','#ef4444']] as $s=>[$label,$color])
    <a href="{{ route('admin.photos.index', ['status'=>$s]) }}"
       style="background:white;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);padding:1.25rem;text-decoration:none;text-align:center;border-top:3px solid {{ $status===$s?$color:'#e5e7eb' }};">
      <div style="font-size:2rem;font-weight:800;color:{{ $color }};">{{ $counts[$s] }}</div>
      <div style="font-size:.85rem;color:#6b7280;">{{ $label }}</div>
    </a>
    @endforeach
  </div>

  {{-- Grid de fotos --}}
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;">
    @forelse($photos as $photo)
    <div style="background:white;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.08);overflow:hidden;">

      {{-- Imagen --}}
      <div style="aspect-ratio:1;background:#f8fafc;position:relative;">
        <img src="{{ route('admin.photos.serve', $photo->id) }}"
             alt="Foto"
             style="width:100%;height:100%;object-fit:cover;"
             onerror="this.style.display='none'">
        <div style="position:absolute;top:.4rem;left:.4rem;background:rgba(0,0,0,.6);color:white;padding:.2rem .5rem;border-radius:20px;font-size:.72rem;">
          {{ strtoupper($photo->album_type) }}
        </div>
      </div>

      {{-- Info usuario --}}
      <div style="padding:.85rem;">
        <div style="font-weight:600;font-size:.9rem;color:#374151;">{{ $photo->nickname ?? $photo->display_name }}</div>
        <div style="font-size:.78rem;color:#9ca3af;margin-bottom:.75rem;">
          {{ $photo->email }} · {{ ucfirst($photo->profile_type ?? '') }}
        </div>
        @if($photo->caption)
        <div style="font-size:.82rem;color:#6b7280;margin-bottom:.75rem;font-style:italic;">"{{ $photo->caption }}"</div>
        @endif

        @if($status === 'pending')
        {{-- Aprobar --}}
        <form method="POST" action="{{ route('admin.photos.approve', $photo->id) }}" style="margin-bottom:.5rem;">
          @csrf
          <button type="submit" style="width:100%;padding:.6rem;background:#10b981;color:white;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:.85rem;">
            ✅ Aprobar
          </button>
        </form>

        {{-- Rechazar --}}
        <div x-data="{ open: false }">
          <button @click="open=!open"
                  style="width:100%;padding:.6rem;background:#fee2e2;color:#991b1b;border:2px solid #fca5a5;border-radius:8px;font-weight:600;cursor:pointer;font-size:.85rem;">
            ❌ Rechazar
          </button>
          <div x-show="open" x-cloak style="margin-top:.5rem;">
            <form method="POST" action="{{ route('admin.photos.reject', $photo->id) }}">
              @csrf
              <textarea name="note" rows="2" required placeholder="Motivo del rechazo..."
                        style="width:100%;padding:.5rem;border:1px solid #e5e7eb;border-radius:6px;font-size:.82rem;box-sizing:border-box;resize:none;margin-bottom:.4rem;"></textarea>
              <button type="submit" style="width:100%;padding:.5rem;background:#ef4444;color:white;border:none;border-radius:6px;font-weight:700;cursor:pointer;font-size:.82rem;">
                Confirmar rechazo
              </button>
            </form>
          </div>
        </div>
        @else
        <div style="background:{{ $status==='approved'?'#d1fae5':'#fee2e2' }};padding:.5rem .75rem;border-radius:8px;font-size:.82rem;color:{{ $status==='approved'?'#065f46':'#991b1b' }};">
          {{ $status==='approved'?'✅ Aprobada':'❌ ' . ($photo->admin_note ?? 'Rechazada') }}
        </div>
        @endif
      </div>
    </div>
    @empty
    <div style="grid-column:1/-1;text-align:center;padding:3rem;color:#9ca3af;">
      No hay fotos {{ $status==='pending'?'pendientes':($status==='approved'?'aprobadas':'rechazadas') }}.
    </div>
    @endforelse
  </div>

  @if($photos->hasPages())
  <div style="margin-top:1.5rem;">{{ $photos->links() }}</div>
  @endif
</div>
@endsection