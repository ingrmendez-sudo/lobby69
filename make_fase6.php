<?php
/**
 * make_fase6.php
 * Fase 6 — Sistema de Fotos + Perfil Completo
 * Crea: PhotoController, AdminPhotoController,
 *       vistas de perfil público, álbum, moderación admin
 *       y actualiza setup/edit con campos físicos
 */

$files = [];

// ══════════════════════════════════════════════════════
// 1. PHOTO CONTROLLER
// ══════════════════════════════════════════════════════
$files['app/Http/Controllers/Photo/PhotoController.php'] = <<<'PHP'
<?php
namespace App\Http\Controllers\Photo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PhotoController extends Controller
{
    // Límites por álbum (sin límite real, pero controlable)
    const ALBUM_TYPES = ['public', 'private', 'vip'];

    public function index()
    {
        $userId = auth()->id();
        $photos = DB::table('photos')
            ->whereRaw('user_id::text = ?', [$userId])
            ->orderBy('album_type')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = [
            'public'  => $photos->where('album_type', 'public'),
            'private' => $photos->where('album_type', 'private'),
            'vip'     => $photos->where('album_type', 'vip'),
        ];

        $user    = auth()->user();
        $profile = DB::table('profiles')->whereRaw('user_id::text = ?', [$userId])->first();

        return view('photos.index', compact('grouped', 'user', 'profile'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'photos.*'   => 'required|image|mimes:jpeg,jpg,png,webp|max:10240',
            'album_type' => 'required|in:public,private,vip',
            'caption'    => 'nullable|string|max:200',
        ], [
            'photos.*.image'  => 'Cada archivo debe ser una imagen.',
            'photos.*.mimes'  => 'Solo JPG, PNG o WEBP.',
            'photos.*.max'    => 'Cada imagen máximo 10MB.',
        ]);

        $userId    = auth()->id();
        $albumType = $request->input('album_type', 'public');
        $caption   = $request->input('caption', '');
        $uploaded  = 0;

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $filename = 'photo_' . $userId . '_' . time() . '_' . $uploaded
                          . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('photos/' . $userId, $filename, 'private');

                DB::table('photos')->insert([
                    'user_id'    => $userId,
                    'album_type' => $albumType,
                    'file_path'  => $path,
                    'status'     => 'pending',
                    'caption'    => $caption,
                    'sort_order' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $uploaded++;
            }
        }

        return redirect()->route('photos.index')
            ->with('success', "✅ {$uploaded} foto(s) subidas correctamente. El equipo las revisará pronto.");
    }

    public function setProfilePhoto(Request $request, $id)
    {
        $userId = auth()->id();
        $photo  = DB::table('photos')
            ->whereRaw('id = ?', [$id])
            ->whereRaw('user_id::text = ?', [$userId])
            ->where('status', 'approved')
            ->first();

        if (!$photo) {
            return back()->with('error', 'Foto no encontrada o no aprobada.');
        }

        // Quitar foto de perfil anterior
        DB::table('photos')
            ->whereRaw('user_id::text = ?', [$userId])
            ->update(['is_profile_photo' => false, 'updated_at' => Carbon::now()]);

        // Establecer nueva
        DB::table('photos')->where('id', $id)
            ->update(['is_profile_photo' => true, 'updated_at' => Carbon::now()]);

        // Actualizar avatar_url en profiles
        DB::table('profiles')
            ->whereRaw('user_id::text = ?', [$userId])
            ->update([
                'avatar_url' => route('photos.serve', $id),
                'updated_at' => Carbon::now(),
            ]);

        return back()->with('success', '✅ Foto de perfil actualizada.');
    }

    public function destroy($id)
    {
        $userId = auth()->id();
        $photo  = DB::table('photos')
            ->where('id', $id)
            ->whereRaw('user_id::text = ?', [$userId])
            ->first();

        if (!$photo) return back()->with('error', 'Foto no encontrada.');

        // Eliminar archivo
        $fullPath = storage_path('app/private/' . $photo->file_path);
        if (file_exists($fullPath)) unlink($fullPath);

        DB::table('photos')->where('id', $id)->delete();

        return back()->with('success', 'Foto eliminada.');
    }

    public function serve($id)
    {
        $userId = auth()->id();

        $photo = DB::table('photos')->where('id', $id)->first();
        if (!$photo) abort(404);

        // Verificar acceso según album_type y membresía
        $membershipType = DB::table('users')
            ->whereRaw('id::text = ?', [$userId])
            ->value('membership_type') ?? 'trial';

        $canView = false;
        switch ($photo->album_type) {
            case 'public':
                $canView = in_array($membershipType, ['trial_verified','explorer','connectors','influencer','vip_elite','vitalicio','admin']);
                break;
            case 'private':
                $canView = in_array($membershipType, ['connectors','influencer','vip_elite','vitalicio','admin']);
                // También si son amigos (fase 9)
                break;
            case 'vip':
                $canView = in_array($membershipType, ['vip_elite','vitalicio','admin']);
                break;
        }

        // El dueño siempre puede ver sus propias fotos
        if ($photo->user_id === $userId) $canView = true;

        if (!$canView) abort(403, 'No tienes acceso a esta foto.');

        $path = storage_path('app/private/' . $photo->file_path);
        if (!file_exists($path)) abort(404);

        return response()->file($path, [
            'Content-Type'  => mime_content_type($path),
            'Cache-Control' => 'private, max-age=3600',
            'X-Robots-Tag'  => 'noindex',
        ]);
    }
}
PHP;

// ══════════════════════════════════════════════════════
// 2. ADMIN PHOTO CONTROLLER
// ══════════════════════════════════════════════════════
$files['app/Http/Controllers/Admin/AdminPhotoController.php'] = <<<'PHP'
<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminPhotoController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $photos = DB::table('photos as ph')
            ->join('users as u', DB::raw('u.id::text'), '=', DB::raw('ph.user_id::text'))
            ->leftJoin('profiles as p', DB::raw('p.user_id::text'), '=', DB::raw('ph.user_id::text'))
            ->select('ph.*', 'u.email', 'p.nickname', 'p.display_name', 'p.profile_type')
            ->whereRaw("ph.status::text = ?", [$status])
            ->orderBy('ph.created_at', 'asc')
            ->paginate(24);

        $counts = [
            'pending'  => DB::table('photos')->whereRaw("status::text = 'pending'")->count(),
            'approved' => DB::table('photos')->whereRaw("status::text = 'approved'")->count(),
            'rejected' => DB::table('photos')->whereRaw("status::text = 'rejected'")->count(),
        ];

        return view('admin.photos.index', compact('photos', 'counts', 'status'));
    }

    public function approve($id)
    {
        DB::table('photos')->where('id', $id)->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);
        return back()->with('success', "✅ Foto #{$id} aprobada.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['note' => 'required|min:5']);

        DB::table('photos')->where('id', $id)->update([
            'status'      => 'rejected',
            'admin_note'  => $request->input('note'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);
        return back()->with('success', "Foto #{$id} rechazada.");
    }

    public function serve($id)
    {
        $photo = DB::table('photos')->where('id', $id)->first();
        if (!$photo) abort(404);

        $path = storage_path('app/private/' . $photo->file_path);
        if (!file_exists($path)) abort(404);

        return response()->file($path, [
            'Content-Type'  => mime_content_type($path),
            'Cache-Control' => 'no-store',
            'X-Robots-Tag'  => 'noindex',
        ]);
    }
}
PHP;

// ══════════════════════════════════════════════════════
// 3. VISTA — photos/index.blade.php (Mi Álbum)
// ══════════════════════════════════════════════════════
$files['resources/views/photos/index.blade.php'] = <<<'BLADE'
@extends('layouts.app')
@section('title', 'Mis Fotos — LOBBY69')
@section('content')
<div style="max-width:1000px;margin:2rem auto;padding:0 1rem;">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
    <div>
      <h1 style="font-size:1.6rem;font-weight:800;color:var(--color-text);margin:0;">📸 Mis Fotos</h1>
      <p style="color:#64748b;margin:.25rem 0 0;">Gestiona tus álbumes. Las fotos son revisadas antes de publicarse.</p>
    </div>
    <a href="{{ route('dashboard') }}" style="padding:.6rem 1rem;border:1px solid #e5e7eb;border-radius:8px;font-size:.9rem;color:#6b7280;text-decoration:none;">← Dashboard</a>
  </div>

  {{-- Mensajes --}}
  @if(session('success'))
  <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;">✅ {{ session('success') }}</div>
  @endif
  @if(session('error'))
  <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:1rem;border-radius:10px;margin-bottom:1.5rem;">⚠️ {{ session('error') }}</div>
  @endif

  {{-- Formulario de subida --}}
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:2rem;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.25rem;">➕ Subir nuevas fotos</h2>

    <form method="POST" action="{{ route('photos.store') }}" enctype="multipart/form-data" id="uploadForm">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1rem;">

        {{-- Álbum --}}
        <div>
          <label style="display:block;font-weight:600;font-size:.85rem;color:#374151;margin-bottom:.4rem;">Álbum</label>
          <select name="album_type" style="width:100%;padding:.65rem .9rem;border:2px solid #e5e7eb;border-radius:8px;font-size:.9rem;">
            <option value="public">🌐 Público (todos los verificados)</option>
            <option value="private">🔒 Privado (Connectors+)</option>
            <option value="vip">👑 VIP (VIP Elite+)</option>
          </select>
        </div>

        {{-- Caption --}}
        <div style="grid-column:span 2;">
          <label style="display:block;font-weight:600;font-size:.85rem;color:#374151;margin-bottom:.4rem;">Descripción (opcional)</label>
          <input type="text" name="caption" maxlength="200" placeholder="Agrega una descripción..."
                 style="width:100%;padding:.65rem .9rem;border:2px solid #e5e7eb;border-radius:8px;font-size:.9rem;box-sizing:border-box;">
        </div>
      </div>

      {{-- Dropzone --}}
      <div id="dropzone"
           style="border:2px dashed #e5e7eb;border-radius:12px;padding:2rem;text-align:center;cursor:pointer;transition:all .2s;"
           onclick="document.getElementById('photoInput').click()"
           ondragover="event.preventDefault();this.style.borderColor='#8b5cf6';this.style.background='#faf5ff'"
           ondragleave="this.style.borderColor='#e5e7eb';this.style.background='white'"
           ondrop="handlePhotoDrop(event)">
        <div id="dropContent">
          <div style="font-size:2.5rem;margin-bottom:.5rem;">🖼️</div>
          <p style="font-weight:600;color:#374151;margin:0;">Haz clic o arrastra tus fotos aquí</p>
          <p style="font-size:.82rem;color:#9ca3af;margin:.3rem 0 0;">JPG, PNG o WEBP · Máx 10MB por foto · Múltiples fotos permitidas</p>
        </div>
        <input type="file" id="photoInput" name="photos[]" accept="image/jpeg,image/png,image/webp"
               multiple style="display:none;" onchange="previewPhotos(this)">
      </div>

      {{-- Preview grid --}}
      <div id="previewGrid" style="display:none;margin-top:1rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.75rem;"></div>

      {{-- Barra de progreso --}}
      <div id="progressContainer" style="display:none;margin-top:1rem;">
        <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;">
          <span style="font-size:.85rem;color:#6b7280;font-weight:600;">Subiendo fotos...</span>
          <span id="progressPct" style="font-size:.85rem;color:#8b5cf6;font-weight:700;">0%</span>
        </div>
        <div style="background:#f1f5f9;border-radius:999px;height:8px;overflow:hidden;">
          <div id="progressBar" style="height:100%;width:0%;background:linear-gradient(90deg,#8b5cf6,#ec4899);border-radius:999px;transition:width .3s;"></div>
        </div>
      </div>

      <button type="submit" id="uploadBtn"
              style="margin-top:1.25rem;padding:.85rem 2rem;background:linear-gradient(135deg,#8b5cf6,#ec4899);color:white;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:.95rem;">
        📤 Subir fotos
      </button>
    </form>
  </div>

  {{-- Álbumes --}}
  @foreach(['public' => ['🌐 Álbum Público', '#8b5cf6'], 'private' => ['🔒 Álbum Privado', '#ec4899'], 'vip' => ['👑 Álbum VIP', '#f59e0b']] as $type => [$label, $color])
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;color:{{ $color }};">{{ $label }}
      <span style="font-size:.8rem;color:#9ca3af;font-weight:400;">({{ $grouped[$type]->count() }} fotos)</span>
    </h2>

    @if($grouped[$type]->isEmpty())
    <p style="color:#9ca3af;font-size:.9rem;text-align:center;padding:1rem 0;">No hay fotos en este álbum.</p>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.75rem;">
      @foreach($grouped[$type] as $photo)
      <div style="position:relative;border-radius:10px;overflow:hidden;aspect-ratio:1;background:#f8fafc;">

        {{-- Imagen --}}
        <img src="{{ route('photos.serve', $photo->id) }}"
             alt="Foto"
             style="width:100%;height:100%;object-fit:cover;"
             onerror="this.src='';this.parentElement.style.background='#f1f5f9'">

        {{-- Estado badge --}}
        <div style="position:absolute;top:.4rem;right:.4rem;
             background:{{ $photo->status==='approved'?'#10b981':($photo->status==='rejected'?'#ef4444':'#f59e0b') }};
             color:white;padding:.2rem .5rem;border-radius:20px;font-size:.7rem;font-weight:700;">
          {{ $photo->status==='approved'?'✅':($photo->status==='rejected'?'❌':'⏳') }}
          {{ ucfirst($photo->status) }}
        </div>

        {{-- Foto de perfil badge --}}
        @if($photo->is_profile_photo)
        <div style="position:absolute;top:.4rem;left:.4rem;background:#8b5cf6;color:white;padding:.2rem .5rem;border-radius:20px;font-size:.7rem;font-weight:700;">
          ⭐ Perfil
        </div>
        @endif

        {{-- Acciones --}}
        <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,.7));padding:.75rem .5rem .5rem;display:flex;gap:.4rem;justify-content:center;">
          @if($photo->status === 'approved' && !$photo->is_profile_photo)
          <form method="POST" action="{{ route('photos.profile', $photo->id) }}" style="margin:0;">
            @csrf
            <button type="submit" title="Usar como foto de perfil"
                    style="background:#8b5cf6;color:white;border:none;border-radius:6px;padding:.3rem .5rem;cursor:pointer;font-size:.75rem;">
              ⭐
            </button>
          </form>
          @endif
          <form method="POST" action="{{ route('photos.destroy', $photo->id) }}" style="margin:0;"
                onsubmit="return confirm('¿Eliminar esta foto?')">
            @csrf @method('DELETE')
            <button type="submit" title="Eliminar"
                    style="background:#ef4444;color:white;border:none;border-radius:6px;padding:.3rem .5rem;cursor:pointer;font-size:.75rem;">
              🗑️
            </button>
          </form>
        </div>

        {{-- Nota de rechazo --}}
        @if($photo->status === 'rejected' && $photo->admin_note)
        <div style="position:absolute;inset:0;background:rgba(239,68,68,.9);display:flex;align-items:center;justify-content:center;padding:.5rem;opacity:0;transition:opacity .2s;"
             onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
          <p style="color:white;font-size:.75rem;text-align:center;margin:0;">{{ $photo->admin_note }}</p>
        </div>
        @endif
      </div>
      @endforeach
    </div>
    @endif
  </div>
  @endforeach

</div>

<script>
function previewPhotos(input) {
    const grid = document.getElementById('previewGrid');
    grid.innerHTML = '';
    grid.style.display = 'grid';

    Array.from(input.files).forEach(function(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.style.cssText = 'border-radius:8px;overflow:hidden;aspect-ratio:1;background:#f8fafc;';
            div.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
            grid.appendChild(div);
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('dropContent').innerHTML =
        '<p style="color:#10b981;font-weight:600;margin:0;">✅ ' + input.files.length + ' foto(s) seleccionada(s)</p>' +
        '<p style="font-size:.8rem;color:#9ca3af;margin:.25rem 0 0;">Haz clic para cambiar</p>';
    document.getElementById('dropzone').style.borderColor = '#10b981';
}

function handlePhotoDrop(e) {
    e.preventDefault();
    const input = document.getElementById('photoInput');
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).forEach(f => { if(f.type.startsWith('image/')) dt.items.add(f); });
    input.files = dt.files;
    previewPhotos(input);
}

// Upload con progreso
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('photoInput');
    if (!input.files || input.files.length === 0) {
        alert('Selecciona al menos una foto.');
        return;
    }

    const btn = document.getElementById('uploadBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Subiendo...';
    document.getElementById('progressContainer').style.display = 'block';

    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const pct = Math.round((e.loaded / e.total) * 95);
            document.getElementById('progressBar').style.width = pct + '%';
            document.getElementById('progressPct').textContent = pct + '%';
        }
    });

    xhr.addEventListener('load', function() {
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressPct').textContent = '100%';
        setTimeout(() => { window.location.href = xhr.responseURL || '{{ route("photos.index") }}'; }, 400);
    });

    xhr.addEventListener('error', function() {
        btn.disabled = false;
        btn.innerHTML = '📤 Subir fotos';
        alert('Error de conexión. Intenta de nuevo.');
    });

    xhr.open('POST', this.action);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
});
</script>
@endsection
BLADE;

// ══════════════════════════════════════════════════════
// 4. VISTA ADMIN — admin/photos/index.blade.php
// ══════════════════════════════════════════════════════
$files['resources/views/admin/photos/index.blade.php'] = <<<'BLADE'
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
BLADE;

// ══════════════════════════════════════════════════════
// 5. VISTA PÚBLICA — profile/show.blade.php
// ══════════════════════════════════════════════════════
$files['resources/views/profile/show.blade.php'] = <<<'BLADE'
@extends('layouts.app')
@section('title', '{{ $profile->nickname ?? "Perfil" }} — LOBBY69')
@section('content')
<div style="max-width:900px;margin:2rem auto;padding:0 1rem;">

@php
    $isPairing  = $profile->profile_type === 'pareja';
    $isUnicorn  = $profile->profile_type === 'unicornio';
    $isSingle   = $profile->profile_type === 'single';
    $showName   = $profile->show_name ?? true;
    $showPName  = $profile->show_partner_name ?? true;
    $mainName   = $showName ? ($profile->display_name ?? 'Nombre oculto') : '-Nombre oculto-';
    $partName   = $showPName ? ($profile->partner_name ?? '') : '-Nombre oculto-';

    $lookingFor = json_decode($profile->looking_for ?? '[]', true) ?? [];
    $interests  = json_decode($profile->interests  ?? '[]', true) ?? [];
    $languages  = json_decode($profile->languages  ?? '[]', true) ?? [];
    $partLanguages = json_decode($profile->partner_languages ?? '[]', true) ?? [];

    $allLookingFor = ['Parejas heterosexuales','Parejas bisexuales','Parejas (ella bisexual)','Parejas (él bisexual)','Hombres heterosexuales','Hombres bisexuales','Mujeres heterosexuales','Mujeres bisexuales'];
    $allInterests  = ['Intercambio completo','Intercambio light','Sexo en grupo','Tríos','Sólo ellas','Mirar y ser vistos','Cuckold','Prácticas BDSM','Compartir fetiches','Cybersexo','Intercambio de fotos','Sexo por separado','Relaciones abiertas','Amistad','Otros'];

    // Foto de perfil
    $profilePhoto = DB::table('photos')
        ->whereRaw('user_id::text = ?', [$profile->user_id])
        ->where('is_profile_photo', true)
        ->where('status', 'approved')
        ->first();
    $profilePhotoUrl = $profilePhoto ? route('photos.serve', $profilePhoto->id) : asset('img/default-avatar.svg');

    // Fotos aprobadas del álbum público
    $photos = DB::table('photos')
        ->whereRaw('user_id::text = ?', [$profile->user_id])
        ->where('album_type', 'public')
        ->where('status', 'approved')
        ->orderBy('sort_order')
        ->get();

    $verificationStatus = DB::table('users')
        ->whereRaw('id::text = ?', [$profile->user_id])
        ->value('verification_status');
@endphp

  {{-- Header del perfil --}}
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
    <div style="display:flex;gap:1.5rem;align-items:flex-start;">

      {{-- Avatar --}}
      <div style="flex-shrink:0;position:relative;">
        <img src="{{ $profilePhotoUrl }}"
             alt="{{ $profile->nickname }}"
             style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid #8b5cf6;"
             onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
        @if($verificationStatus === 'approved')
        <div style="position:absolute;bottom:4px;right:4px;background:#3b82f6;color:white;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:.75rem;border:2px solid white;" title="Identidad verificada">✓</div>
        @endif
      </div>

      {{-- Info principal --}}
      <div style="flex:1;">
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
          <h1 style="font-size:1.6rem;font-weight:800;color:var(--color-text);margin:0;">{{ $profile->nickname }}</h1>
          @if($verificationStatus === 'approved')
          <span style="background:#dbeafe;color:#1d4ed8;padding:.2rem .7rem;border-radius:20px;font-size:.78rem;font-weight:600;">✓ Verificado</span>
          @endif
          <span style="background:#f3f4f6;color:#374151;padding:.2rem .7rem;border-radius:20px;font-size:.78rem;">
            {{ $isSingle?'Single':($isPairing?'Pareja':'Unicornio') }}
          </span>
        </div>
        <p style="color:#6b7280;font-size:.9rem;margin:.4rem 0;">
          📍 {{ implode(', ', array_filter([$profile->city, $profile->state, $profile->country])) }}
        </p>
        @if($profile->bio)
        <p style="color:#4b5563;font-size:.95rem;margin:.5rem 0 0;line-height:1.6;">{{ $profile->bio }}</p>
        @endif
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

    {{-- SOBRE ELLOS --}}
    <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:1.75rem;">
      <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">
        👤 Sobre {{ $isPairing ? 'ellos' : ($isUnicorn ? 'ella/él' : 'mí') }}
      </h2>

      @if($isPairing)
      {{-- DOS COLUMNAS para pareja --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

        {{-- Miembro 1 --}}
        <div>
          <h3 style="font-size:.95rem;font-weight:700;color:#8b5cf6;margin-bottom:.75rem;">
            {{ $profile->gender === 'masculino' ? '♂️' : '♀️' }} {{ $mainName }}
          </h3>
          @include('profile._physical_data', ['p' => $profile, 'isPartner' => false])
        </div>

        {{-- Miembro 2 (pareja) --}}
        @if($profile->partner_name || $profile->partner_age)
        <div>
          <h3 style="font-size:.95rem;font-weight:700;color:#ec4899;margin-bottom:.75rem;">
            {{ $profile->partner_gender === 'masculino' ? '♂️' : '♀️' }} {{ $partName }}
          </h3>
          @include('profile._physical_data', ['p' => $profile, 'isPartner' => true])
        </div>
        @endif
      </div>

      @else
      {{-- UNA COLUMNA para single/unicornio --}}
      @include('profile._physical_data', ['p' => $profile, 'isPartner' => false])
      @endif
    </div>

    {{-- BUSCAN / PARA --}}
    <div>
      {{-- Buscan --}}
      <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:1.75rem;margin-bottom:1.5rem;">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">🔍 Buscan</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
          @foreach($allLookingFor as $opt)
          @if(in_array($opt, $lookingFor))
          <span style="font-size:.85rem;color:#8b5cf6;font-weight:600;">{{ $opt }}</span>
          @else
          <span style="font-size:.85rem;color:#d1d5db;text-decoration:line-through;">{{ $opt }}</span>
          @endif
          @endforeach
        </div>
      </div>

      {{-- Para --}}
      <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:1.75rem;">
        <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:2px solid #f1f5f9;">💫 Para</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
          @foreach($allInterests as $opt)
          @if(in_array($opt, $interests))
          <span style="font-size:.85rem;color:#ec4899;font-weight:600;">{{ $opt }}</span>
          @else
          <span style="font-size:.85rem;color:#d1d5db;text-decoration:line-through;">{{ $opt }}</span>
          @endif
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Fotos --}}
  @if($photos->isNotEmpty())
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:1.75rem;margin-top:1.5rem;">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;">📸 Fotos públicas</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.75rem;">
      @foreach($photos as $photo)
      <div style="border-radius:10px;overflow:hidden;aspect-ratio:1;background:#f8fafc;">
        <img src="{{ route('photos.serve', $photo->id) }}"
             alt="{{ $photo->caption }}"
             style="width:100%;height:100%;object-fit:cover;">
      </div>
      @endforeach
    </div>
  </div>
  @endif

</div>
@endsection
BLADE;

// ══════════════════════════════════════════════════════
// 6. PARTIAL — profile/_physical_data.blade.php
// ══════════════════════════════════════════════════════
$files['resources/views/profile/_physical_data.blade.php'] = <<<'BLADE'
@php
    $prefix = $isPartner ? 'partner_' : '';
    $age         = $isPartner ? ($p->partner_age ?? null)         : ($p->age ?? null);
    $gender      = $isPartner ? ($p->partner_gender ?? null)      : ($p->gender ?? null);
    $orientation = $isPartner ? ($p->partner_orientation ?? null) : ($p->orientation ?? null);
    $height      = $isPartner ? ($p->partner_height ?? null)      : ($p->height ?? null);
    $weight      = $isPartner ? ($p->partner_weight ?? null)      : ($p->weight ?? null);
    $ethnicity   = $isPartner ? ($p->partner_ethnicity ?? null)   : ($p->ethnicity ?? null);
    $nationality = $isPartner ? ($p->partner_nationality ?? null) : ($p->nationality ?? 'México');
    $tattoos     = $isPartner ? ($p->partner_tattoos ?? null)     : ($p->tattoos ?? null);
    $piercings   = $isPartner ? ($p->partner_piercings ?? null)   : ($p->piercings ?? null);
    $smokes      = $isPartner ? ($p->partner_smokes ?? null)      : ($p->smokes ?? null);
    $drinks      = $isPartner ? ($p->partner_drinks ?? null)      : ($p->drinks ?? null);
    $langs       = json_decode($isPartner ? ($p->partner_languages ?? '[]') : ($p->languages ?? '[]'), true) ?? [];

    // Campos específicos por género
    $penisSize   = !$isPartner ? ($p->penis_size ?? null)          : ($p->partner_penis_size ?? null);
    $breastSize  = !$isPartner ? ($p->breast_size ?? null)         : ($p->partner_breast_size ?? null);
    $isM = in_array($gender, ['masculino']);
    $isF = in_array($gender, ['femenino']);

    $rows = array_filter([
        'Edad'          => $age ? $age . ' años' : null,
        'Orientación'   => $orientation ? ucfirst($orientation) : null,
        'Altura'        => $height ? $height . ' cm' : null,
        'Peso'          => $weight ? $weight . ' kg' : null,
        'Etnia'         => $ethnicity ? ucfirst($ethnicity) : null,
        'Nacionalidad'  => $nationality ?? null,
        $isM ? 'Long. del pene' : null  => $isM && $penisSize ? $penisSize . ' cm' : null,
        $isF ? 'Talla de pecho' : null  => $isF && $breastSize ? $breastSize : null,
        'Tatuajes'      => $tattoos ? ucfirst($tattoos) : null,
        'Piercings'     => $piercings ? ucfirst($piercings) : null,
        'Fuma'          => $smokes ? ucfirst($smokes) : null,
        'Bebe alcohol'  => $drinks ? ucfirst($drinks) : null,
        'Habla'         => !empty($langs) ? implode(', ', $langs) : null,
    ]);
@endphp

<table style="width:100%;font-size:.85rem;border-collapse:collapse;">
  @foreach($rows as $label => $value)
  @if($label && $value)
  <tr style="border-bottom:1px solid #f8fafc;">
    <td style="padding:.35rem 0;color:#9ca3af;width:45%;">{{ $label }}:</td>
    <td style="padding:.35rem 0;color:#374151;font-weight:500;">{{ $value }}</td>
  </tr>
  @endif
  @endforeach
</table>
BLADE;

// ══════════════════════════════════════════════════════
// CREAR ARCHIVOS
// ══════════════════════════════════════════════════════
$ok = 0; $fail = 0;
foreach ($files as $path => $content) {
    $fullPath = __DIR__ . '/' . $path;
    $dir = dirname($fullPath);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
    if (file_put_contents($fullPath, $content) !== false) {
        echo "✅ $path\n"; $ok++;
    } else {
        echo "❌ $path\n"; $fail++;
    }
}

echo "\n📊 Resultado: $ok OK · $fail errores\n";
echo "\nEjecuta: C:\\php\\php.exe fix_fase6_routes.php\n";
