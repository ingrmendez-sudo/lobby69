<?php
/**
 * make_fase7a.php
 * Sub-fase 7A: BD + Modelos + Middleware + SearchController
 * Sub-fase 7B: Dashboard nuevo con feed + Modal + Likes/Comentarios
 * + Navbar con barra de búsqueda expandible
 *
 * Ejecutar: C:\php\php.exe make_fase7a.php
 */

$base = __DIR__;

// ============================================================
// HELPER
// ============================================================
function writeFile(string $path, string $content): void {
    $dir = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($path, $content);
    echo "✓ $path\n";
}

// ============================================================
// 1. MIGRACIONES
// ============================================================

// --- profile_views ---
writeFile($base . '/database/migrations/2025_01_01_000010_create_profile_views_table.php', <<<'PHP'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('profile_views', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('viewer_id');
            $table->uuid('viewed_id');
            $table->timestamp('viewed_at')->useCurrent();
            $table->index(['viewed_id', 'viewed_at']);
            $table->index(['viewer_id', 'viewed_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('profile_views'); }
};
PHP);

// --- photo_likes ---
writeFile($base . '/database/migrations/2025_01_01_000011_create_photo_likes_table.php', <<<'PHP'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('photo_likes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id');
            $table->uuid('photo_id');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'photo_id']);
            $table->index('photo_id');
        });
    }
    public function down(): void { Schema::dropIfExists('photo_likes'); }
};
PHP);

// --- photo_comments ---
writeFile($base . '/database/migrations/2025_01_01_000012_create_photo_comments_table.php', <<<'PHP'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('photo_comments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('photo_id');
            $table->uuid('user_id');
            $table->text('body');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            $table->index(['photo_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('photo_comments'); }
};
PHP);

// --- friendships ---
writeFile($base . '/database/migrations/2025_01_01_000013_create_friendships_table.php', <<<'PHP'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('friendships', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sender_id');
            $table->uuid('receiver_id');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'blocked'])->default('pending');
            $table->timestamps();
            $table->unique(['sender_id', 'receiver_id']);
            $table->index(['receiver_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('friendships'); }
};
PHP);

// --- messages ---
writeFile($base . '/database/migrations/2025_01_01_000014_create_messages_table.php', <<<'PHP'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sender_id');
            $table->uuid('receiver_id');
            $table->text('body');
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['receiver_id', 'read']);
            $table->index(['sender_id', 'receiver_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('messages'); }
};
PHP);

// --- last_seen en users ---
writeFile($base . '/database/migrations/2025_01_01_000015_add_last_seen_to_users.php', <<<'PHP'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('remember_token');
            }
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_seen_at');
        });
    }
};
PHP);

// ============================================================
// 2. MODELOS
// ============================================================

writeFile($base . '/app/Models/ProfileView.php', <<<'PHP'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProfileView extends Model
{
    use HasUuids;
    public $timestamps = false;
    protected $table = 'profile_views';
    protected $fillable = ['viewer_id', 'viewed_id', 'viewed_at'];
    protected $casts = ['viewed_at' => 'datetime'];

    public function viewer() { return $this->belongsTo(User::class, 'viewer_id'); }
    public function viewed() { return $this->belongsTo(User::class, 'viewed_id'); }
}
PHP);

writeFile($base . '/app/Models/PhotoLike.php', <<<'PHP'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PhotoLike extends Model
{
    use HasUuids;
    public $timestamps = false;
    protected $table = 'photo_likes';
    protected $fillable = ['user_id', 'photo_id'];
    protected $casts = ['created_at' => 'datetime'];

    public function user()  { return $this->belongsTo(User::class); }
    public function photo() { return $this->belongsTo(Photo::class); }
}
PHP);

writeFile($base . '/app/Models/PhotoComment.php', <<<'PHP'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PhotoComment extends Model
{
    use HasUuids;
    protected $table = 'photo_comments';
    protected $fillable = ['photo_id', 'user_id', 'body', 'status'];

    public function user()  { return $this->belongsTo(User::class); }
    public function photo() { return $this->belongsTo(Photo::class); }

    public function scopeApproved($q) { return $q->where('status', 'approved'); }
}
PHP);

writeFile($base . '/app/Models/Friendship.php', <<<'PHP'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Friendship extends Model
{
    use HasUuids;
    protected $table = 'friendships';
    protected $fillable = ['sender_id', 'receiver_id', 'status'];

    public function sender()   { return $this->belongsTo(User::class, 'sender_id'); }
    public function receiver() { return $this->belongsTo(User::class, 'receiver_id'); }

    public static function statusBetween(string $userA, string $userB): ?string {
        $f = static::where(function($q) use ($userA, $userB) {
            $q->where('sender_id', $userA)->where('receiver_id', $userB);
        })->orWhere(function($q) use ($userA, $userB) {
            $q->where('sender_id', $userB)->where('receiver_id', $userA);
        })->first();
        return $f?->status;
    }
}
PHP);

writeFile($base . '/app/Models/Message.php', <<<'PHP'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Message extends Model
{
    use HasUuids;
    protected $table = 'messages';
    protected $fillable = ['sender_id', 'receiver_id', 'body', 'read', 'read_at'];
    protected $casts = ['read' => 'boolean', 'read_at' => 'datetime'];

    public function sender()   { return $this->belongsTo(User::class, 'sender_id'); }
    public function receiver() { return $this->belongsTo(User::class, 'receiver_id'); }
}
PHP);

// Photo model (completo)
writeFile($base . '/app/Models/Photo.php', <<<'PHP'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Photo extends Model
{
    use HasUuids;
    protected $table = 'photos';
    protected $fillable = [
        'user_id','album_type','file_path','thumbnail_path',
        'is_profile_photo','status','admin_note',
        'reviewed_by','reviewed_at','sort_order','caption',
    ];
    protected $casts = [
        'is_profile_photo' => 'boolean',
        'reviewed_at'      => 'datetime',
        'sort_order'       => 'integer',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function likes()    { return $this->hasMany(PhotoLike::class); }
    public function comments() { return $this->hasMany(PhotoComment::class)->where('status','approved'); }

    public function scopeApproved($q) { return $q->where('status', 'approved'); }
    public function scopePublic($q)   { return $q->where('album_type', 'public')->where('status', 'approved'); }
    public function scopePending($q)  { return $q->where('status', 'pending'); }

    public function isLikedBy(string $userId): bool {
        return $this->likes()->where('user_id', $userId)->exists();
    }
}
PHP);

// ============================================================
// 3. MIDDLEWARE TrackLastSeen
// ============================================================
writeFile($base . '/app/Http/Middleware/TrackLastSeen.php', <<<'PHP'
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TrackLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $userId = Auth::id();
            // Actualizar solo cada 2 minutos para no saturar la BD
            $cacheKey = "last_seen_{$userId}";
            if (!Cache::has($cacheKey)) {
                DB::table('users')
                    ->where('id', $userId)
                    ->update(['last_seen_at' => now()]);
                Cache::put($cacheKey, true, 120); // 2 minutos
            }
        }
        return $next($request);
    }
}
PHP);

// ============================================================
// 4. CONTROLLERS
// ============================================================

// DashboardController
writeFile($base . '/app/Http/Controllers/DashboardController.php', <<<'PHP'
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Photo;
use App\Models\ProfileView;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user    = auth()->user();
        $profile = $user->profile;

        // ── Feed de fotos (12 por página, excluye las propias) ──
        $tab    = $request->get('tab', 'new');
        $feedQuery = Photo::with(['user.profile'])
            ->approved()
            ->where('album_type', 'public')
            ->where('user_id', '!=', $user->id)
            ->withCount(['likes', 'comments']);

        if ($tab === 'likes') {
            $feedQuery->orderByDesc('likes_count');
        } else {
            $feedQuery->orderByDesc('created_at');
        }

        $feed = $feedQuery->paginate(12);

        // ── Quién vio mi perfil (últimas 5) ──
        $whoViewedMe = ProfileView::with('viewer.profile')
            ->where('viewed_id', $user->id)
            ->where('viewer_id', '!=', $user->id)
            ->orderByDesc('viewed_at')
            ->limit(5)
            ->get();

        $whoViewedMeCount = ProfileView::where('viewed_id', $user->id)
            ->where('viewer_id', '!=', $user->id)
            ->count();

        // ── A quién vi (últimas 5) ──
        $iViewed = ProfileView::with('viewed.profile')
            ->where('viewer_id', $user->id)
            ->orderByDesc('viewed_at')
            ->limit(5)
            ->get();

        $iViewedCount = ProfileView::where('viewer_id', $user->id)->count();

        // ── Usuarios en línea (visto hace menos de 10 min) ──
        $onlineUsers = DB::table('users')
            ->join('profiles', 'users.id', '=', DB::raw('profiles.user_id::text'))
            ->select('profiles.nickname', 'profiles.avatar_url', 'users.last_seen_at')
            ->where('users.last_seen_at', '>=', now()->subMinutes(10))
            ->where('users.id', '!=', $user->id)
            ->orderByDesc('users.last_seen_at')
            ->limit(12)
            ->get();

        // ── Nuevos usuarios (últimos 5 registrados y verificados) ──
        $newUsers = DB::table('users')
            ->join('profiles', 'users.id', '=', DB::raw('profiles.user_id::text'))
            ->select('profiles.nickname', 'profiles.avatar_url',
                     'profiles.profile_type', 'users.created_at')
            ->where('profiles.profile_completed', true)
            ->orderByDesc('users.created_at')
            ->where('users.id', '!=', $user->id)
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'user', 'profile', 'feed', 'tab',
            'whoViewedMe', 'whoViewedMeCount',
            'iViewed', 'iViewedCount',
            'onlineUsers', 'newUsers'
        ));
    }

    // ── Feed AJAX (scroll infinito) ──
    public function feedAjax(Request $request)
    {
        $user = auth()->user();
        $tab  = $request->get('tab', 'new');

        $feedQuery = Photo::with(['user.profile'])
            ->approved()
            ->where('album_type', 'public')
            ->where('user_id', '!=', $user->id)
            ->withCount(['likes', 'comments']);

        if ($tab === 'likes') {
            $feedQuery->orderByDesc('likes_count');
        } else {
            $feedQuery->orderByDesc('created_at');
        }

        $feed = $feedQuery->paginate(12);

        // Renderizar solo las tarjetas para AJAX
        $html = view('dashboard._feed_items', [
            'feed' => $feed,
            'user' => $user,
        ])->render();

        return response()->json([
            'html'     => $html,
            'nextPage' => $feed->currentPage() + 1,
            'hasMore'  => $feed->hasMorePages(),
        ]);
    }
}
PHP);

// PhotoInteractionController — Likes y Comentarios
writeFile($base . '/app/Http/Controllers/Photo/PhotoInteractionController.php', <<<'PHP'
<?php
namespace App\Http\Controllers\Photo;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\PhotoLike;
use App\Models\PhotoComment;
use Illuminate\Http\Request;

class PhotoInteractionController extends Controller
{
    // ── Toggle Like ──
    public function toggleLike(Request $request, string $photoId)
    {
        $user  = auth()->user();
        $photo = Photo::findOrFail($photoId);

        $existing = PhotoLike::where('user_id', $user->id)
                             ->where('photo_id', $photoId)
                             ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            PhotoLike::create(['user_id' => $user->id, 'photo_id' => $photoId]);
            $liked = true;
        }

        $count = PhotoLike::where('photo_id', $photoId)->count();

        return response()->json(['liked' => $liked, 'count' => $count]);
    }

    // ── Agregar Comentario ──
    public function addComment(Request $request, string $photoId)
    {
        $request->validate(['body' => 'required|string|max:500|min:2']);

        $user  = auth()->user();
        $photo = Photo::findOrFail($photoId);

        $comment = PhotoComment::create([
            'photo_id' => $photoId,
            'user_id'  => $user->id,
            'body'     => $request->body,
            'status'   => 'pending', // moderación admin
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comentario enviado. Será visible tras revisión.',
            'comment' => [
                'id'         => $comment->id,
                'body'       => $comment->body,
                'user_nick'  => $user->profile?->nickname ?? $user->name,
                'user_avatar'=> $user->profile?->avatar_url ?? asset('img/default-avatar.svg'),
                'created_at' => $comment->created_at->diffForHumans(),
            ],
        ]);
    }

    // ── Obtener datos completos de una foto (para modal) ──
    public function show(string $photoId)
    {
        $user  = auth()->user();
        $photo = Photo::with([
            'user.profile',
            'likes',
            'comments.user.profile',
        ])->findOrFail($photoId);

        $liked = $photo->likes()->where('user_id', $user->id)->exists();

        return response()->json([
            'photo'    => [
                'id'          => $photo->id,
                'url'         => asset('storage/' . $photo->file_path),
                'caption'     => $photo->caption,
                'likes_count' => $photo->likes->count(),
                'liked'       => $liked,
            ],
            'owner'    => [
                'nick'         => $photo->user->profile?->nickname ?? $photo->user->name,
                'avatar'       => $photo->user->profile?->avatar_url ?? asset('img/default-avatar.svg'),
                'profile_type' => $photo->user->profile?->profile_type ?? 'single',
                'city'         => $photo->user->profile?->city ?? '',
                'profile_url'  => route('profile.show', $photo->user->profile?->nickname ?? ''),
            ],
            'comments' => $photo->comments->map(fn($c) => [
                'id'          => $c->id,
                'body'        => $c->body,
                'user_nick'   => $c->user->profile?->nickname ?? $c->user->name,
                'user_avatar' => $c->user->profile?->avatar_url ?? asset('img/default-avatar.svg'),
                'created_at'  => $c->created_at->diffForHumans(),
            ]),
        ]);
    }
}
PHP);

// SearchController
writeFile($base . '/app/Http/Controllers/SearchController.php', <<<'PHP'
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    // ── Búsqueda live (AJAX navbar) ──
    public function live(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = DB::table('profiles')
            ->join('users', DB::raw('profiles.user_id::text'), '=', 'users.id')
            ->select(
                'profiles.nickname',
                'profiles.avatar_url',
                'profiles.profile_type',
                'profiles.city',
                'users.last_seen_at'
            )
            ->where('profiles.profile_completed', true)
            ->where(function($query) use ($q) {
                $query->whereRaw('LOWER(profiles.nickname) LIKE ?', ['%' . strtolower($q) . '%'])
                      ->orWhereRaw('LOWER(profiles.display_name) LIKE ?', ['%' . strtolower($q) . '%']);
            })
            ->limit(6)
            ->get()
            ->map(fn($p) => [
                'nick'         => $p->nickname,
                'avatar'       => $p->avatar_url ?? asset('img/default-avatar.svg'),
                'profile_type' => $p->profile_type ?? 'single',
                'city'         => $p->city ?? '',
                'url'          => route('profile.show', $p->nickname),
                'online'       => $p->last_seen_at
                                  && now()->diffInMinutes($p->last_seen_at) <= 10,
            ]);

        return response()->json($results);
    }

    // ── Página de resultados completos ──
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $results = collect();
        if (strlen($q) >= 2) {
            $results = DB::table('profiles')
                ->join('users', DB::raw('profiles.user_id::text'), '=', 'users.id')
                ->select('profiles.*', 'users.last_seen_at', 'users.created_at as joined_at')
                ->where('profiles.profile_completed', true)
                ->where(function($query) use ($q) {
                    $query->whereRaw('LOWER(profiles.nickname) LIKE ?', ['%' . strtolower($q) . '%'])
                          ->orWhereRaw('LOWER(profiles.bio) LIKE ?', ['%' . strtolower($q) . '%']);
                })
                ->orderByDesc('users.last_seen_at')
                ->paginate(20);
        }

        return view('search.index', compact('q', 'results'));
    }
}
PHP);

// ============================================================
// 5. VISTAS
// ============================================================

// --- Dashboard principal ---
writeFile($base . '/resources/views/dashboard/index.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('title', 'Inicio')

{{-- ══ SIDEBAR IZQUIERDO ══ --}}
@push('sidebar-left')
@php
    $sbUser    = auth()->user();
    $sbProfile = $sbUser->profile ?? null;
    $sbAvatar  = $sbProfile?->avatar_url ?? asset('img/default-avatar.svg');
    $sbNick    = $sbProfile?->nickname ?? $sbUser->name ?? 'Usuario';
    $sbMember  = $sbUser->membership_type ?? 'trial';
    $memberLabels = [
        'trial'      => ['label'=>'Trial',      'icon'=>'fa-clock',   'color'=>'#9ca3af'],
        'explorer'   => ['label'=>'Explorer',   'icon'=>'fa-compass', 'color'=>'#60a5fa'],
        'connectors' => ['label'=>'Connectors', 'icon'=>'fa-link',    'color'=>'#34d399'],
        'influencer' => ['label'=>'Influencer', 'icon'=>'fa-star',    'color'=>'#a78bfa'],
        'vip_elite'  => ['label'=>'VIP Elite',  'icon'=>'fa-gem',     'color'=>'#fbbf24'],
        'vitalicio'  => ['label'=>'Vitalicio',  'icon'=>'fa-crown',   'color'=>'#e056a0'],
    ];
    $mInfo = $memberLabels[$sbMember] ?? $memberLabels['trial'];
    $lastSeen = $sbUser->last_seen_at
        ? \Carbon\Carbon::parse($sbUser->last_seen_at)->diffForHumans()
        : 'Primera vez';
@endphp

{{-- Tarjeta Mi Perfil --}}
<div class="l69-sb-card">
    <div class="l69-sb-profile">
        <div class="l69-sb-avatar-wrap">
            <img src="{{ $sbAvatar }}" alt="{{ $sbNick }}"
                 class="l69-sb-avatar"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            @if($sbUser->identity_verified ?? false)
                <span class="l69-sb-verified" title="Verificado">
                    <i class="fas fa-check"></i>
                </span>
            @endif
        </div>
        <div class="l69-sb-nick">{{ $sbNick }}</div>
        <div class="l69-sb-type">
            @if($sbProfile?->profile_type === 'pareja') <i class="fas fa-heart"></i> Pareja
            @elseif($sbProfile?->profile_type === 'unicornio') <i class="fas fa-star"></i> Unicornio
            @else <i class="fas fa-user"></i> Single @endif
        </div>
        <span class="l69-sb-badge" style="background:rgba(255,255,255,.06);color:{{ $mInfo['color'] }};border:1px solid {{ $mInfo['color'] }}44;">
            <i class="fas {{ $mInfo['icon'] }}"></i> {{ $mInfo['label'] }}
        </span>
        <div class="l69-sb-lastseen">
            <i class="fas fa-clock"></i> {{ $lastSeen }}
        </div>
    </div>
</div>

{{-- Quién vio mi perfil --}}
<div class="l69-sb-card">
    <div class="l69-sb-card-title">
        <i class="fas fa-eye"></i> Vieron mi perfil
        @if($whoViewedMeCount > 0)
            <span class="l69-sb-count">{{ $whoViewedMeCount }}</span>
        @endif
    </div>
    @forelse($whoViewedMe as $view)
    @php $vp = $view->viewer?->profile; @endphp
    <div class="l69-sb-user-row">
        <a href="{{ $vp?->nickname ? route('profile.show', $vp->nickname) : '#' }}">
            <img src="{{ $vp?->avatar_url ?? asset('img/default-avatar.svg') }}"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
        </a>
        <div class="l69-sb-user-info">
            <span class="l69-sb-user-nick">{{ $vp?->nickname ?? 'Usuario' }}</span>
            <span class="l69-sb-user-time">{{ \Carbon\Carbon::parse($view->viewed_at)->diffForHumans() }}</span>
        </div>
    </div>
    @empty
    <p class="l69-sb-empty">Aún nadie ha visitado tu perfil</p>
    @endforelse
    @if($whoViewedMeCount > 5)
    <a href="#" class="l69-sb-see-more">Ver todos ({{ $whoViewedMeCount }}) →</a>
    @endif
</div>

{{-- A quién vi --}}
<div class="l69-sb-card">
    <div class="l69-sb-card-title">
        <i class="fas fa-walking"></i> Perfiles que visité
    </div>
    @forelse($iViewed as $view)
    @php $vp = $view->viewed?->profile; @endphp
    <div class="l69-sb-user-row">
        <a href="{{ $vp?->nickname ? route('profile.show', $vp->nickname) : '#' }}">
            <img src="{{ $vp?->avatar_url ?? asset('img/default-avatar.svg') }}"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
        </a>
        <div class="l69-sb-user-info">
            <span class="l69-sb-user-nick">{{ $vp?->nickname ?? 'Usuario' }}</span>
            <span class="l69-sb-user-time">{{ \Carbon\Carbon::parse($view->viewed_at)->diffForHumans() }}</span>
        </div>
    </div>
    @empty
    <p class="l69-sb-empty">No has visitado perfiles aún</p>
    @endforelse
    @if($iViewedCount > 5)
    <a href="#" class="l69-sb-see-more">Ver historial completo →</a>
    @endif
</div>
@endpush

{{-- ══ SIDEBAR DERECHO ══ --}}
@push('sidebar-right')

{{-- Usuarios en línea --}}
<div class="l69-sb-card">
    <div class="l69-sb-card-title">
        <i class="fas fa-circle" style="color:#22c55e;font-size:.55rem;"></i>
        En línea ahora
        @if($onlineUsers->count() > 0)
            <span class="l69-sb-count" style="background:rgba(34,197,94,.15);color:#22c55e;">
                {{ $onlineUsers->count() }}
            </span>
        @endif
    </div>
    @forelse($onlineUsers->take(8) as $ou)
    <div class="l69-sb-user-row">
        <div style="position:relative;">
            <img src="{{ $ou->avatar_url ?? asset('img/default-avatar.svg') }}"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            <span style="position:absolute;bottom:0;right:0;width:9px;height:9px;
                         background:#22c55e;border-radius:50%;border:2px solid #0f0a1a;"></span>
        </div>
        <div class="l69-sb-user-info">
            <a href="{{ route('profile.show', $ou->nickname) }}"
               class="l69-sb-user-nick">{{ $ou->nickname }}</a>
        </div>
    </div>
    @empty
    <p class="l69-sb-empty">No hay usuarios en línea ahora</p>
    @endforelse
</div>

{{-- Nuevos usuarios --}}
<div class="l69-sb-card">
    <div class="l69-sb-card-title">
        <i class="fas fa-user-plus"></i> Nuevos miembros
    </div>
    @forelse($newUsers as $nu)
    <div class="l69-sb-user-row">
        <a href="{{ route('profile.show', $nu->nickname) }}">
            <img src="{{ $nu->avatar_url ?? asset('img/default-avatar.svg') }}"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
        </a>
        <div class="l69-sb-user-info">
            <a href="{{ route('profile.show', $nu->nickname) }}"
               class="l69-sb-user-nick">{{ $nu->nickname }}</a>
            <span class="l69-sb-user-time">
                @if($nu->profile_type === 'pareja') 👫
                @elseif($nu->profile_type === 'unicornio') ⭐
                @else 👤 @endif
                {{ \Carbon\Carbon::parse($nu->created_at)->diffForHumans() }}
            </span>
        </div>
    </div>
    @empty
    <p class="l69-sb-empty">Sin nuevos miembros recientes</p>
    @endforelse
</div>
@endpush

{{-- ══ CONTENIDO CENTRAL ══ --}}
@section('content')
<div class="l69-feed">

    {{-- Tabs --}}
    <div class="l69-feed__tabs">
        <a href="?tab=new"
           class="l69-feed__tab {{ $tab === 'new' ? 'is-active' : '' }}">
            <i class="fas fa-clock"></i> Fotos Nuevas
        </a>
        <a href="?tab=likes"
           class="l69-feed__tab {{ $tab === 'likes' ? 'is-active' : '' }}">
            <i class="fas fa-fire"></i> Más Populares
        </a>
    </div>

    {{-- Grid de fotos --}}
    <div class="l69-feed__grid" id="feedGrid">
        @include('dashboard._feed_items', ['feed' => $feed, 'user' => $user])
    </div>

    {{-- Cargar más --}}
    @if($feed->hasMorePages())
    <div class="l69-feed__load-more" id="loadMoreWrap">
        <button class="l69-feed__load-btn" id="loadMoreBtn"
                data-page="2" data-tab="{{ $tab }}">
            <i class="fas fa-chevron-down"></i> Cargar más fotos
        </button>
    </div>
    @endif

</div>

{{-- ══ MODAL DE FOTO ══ --}}
<div class="l69-photo-modal" id="photoModal" style="display:none;">
    <div class="l69-photo-modal__overlay" id="photoModalOverlay"></div>
    <div class="l69-photo-modal__container">
        <button class="l69-photo-modal__close" id="photoModalClose">
            <i class="fas fa-times"></i>
        </button>
        <div class="l69-photo-modal__body">

            {{-- Foto --}}
            <div class="l69-photo-modal__img-wrap">
                <img src="" alt="" id="modalPhoto" class="l69-photo-modal__img">
                {{-- Like sobre la foto --}}
                <div class="l69-photo-modal__img-actions">
                    <button class="l69-like-btn" id="modalLikeBtn" data-photo-id="">
                        <i class="far fa-heart"></i>
                        <span id="modalLikeCount">0</span>
                    </button>
                    <span class="l69-comment-count-badge">
                        <i class="far fa-comment"></i>
                        <span id="modalCommentCount">0</span>
                    </span>
                </div>
            </div>

            {{-- Panel lateral --}}
            <div class="l69-photo-modal__panel">

                {{-- Dueño --}}
                <div class="l69-photo-modal__owner" id="modalOwner">
                    <a href="#" id="modalOwnerLink">
                        <img src="" alt="" id="modalOwnerAvatar"
                             class="l69-photo-modal__owner-avatar">
                    </a>
                    <div>
                        <div class="l69-photo-modal__owner-nick" id="modalOwnerNick"></div>
                        <div class="l69-photo-modal__owner-meta" id="modalOwnerMeta"></div>
                    </div>
                </div>

                <div class="l69-photo-modal__caption" id="modalCaption"></div>

                {{-- Comentarios --}}
                <div class="l69-photo-modal__comments" id="modalComments">
                    <div class="l69-photo-modal__comments-list" id="commentsList">
                        <div class="l69-sb-empty">Cargando comentarios...</div>
                    </div>
                </div>

                {{-- Formulario comentario --}}
                <form class="l69-photo-modal__comment-form" id="commentForm">
                    @csrf
                    <input type="hidden" id="commentPhotoId" name="photo_id">
                    <div class="l69-photo-modal__comment-input-wrap">
                        <img src="{{ auth()->user()->profile?->avatar_url ?? asset('img/default-avatar.svg') }}"
                             alt="" class="l69-photo-modal__comment-avatar"
                             onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
                        <input type="text" name="body" id="commentBody"
                               placeholder="Escribe un comentario..."
                               class="l69-photo-modal__comment-input"
                               maxlength="500" autocomplete="off">
                        <button type="submit" class="l69-photo-modal__comment-send">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <p class="l69-photo-modal__comment-note">
                        <i class="fas fa-info-circle"></i>
                        Los comentarios se publican tras revisión
                    </p>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
// ── Abrir modal ──
document.addEventListener('click', function(e) {
    var card = e.target.closest('[data-photo-id]');
    if (!card || e.target.closest('.l69-like-btn') || e.target.closest('.l69-card__owner')) return;
    var photoId = card.dataset.photoId;
    if (photoId) openPhotoModal(photoId);
});

// ── Like en feed (sin abrir modal) ──
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.l69-like-btn[data-photo-id]');
    if (!btn) return;
    e.preventDefault(); e.stopPropagation();
    toggleLike(btn.dataset.photoId, btn);
});

function toggleLike(photoId, btn) {
    fetch('/fotos/' + photoId + '/like', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        var icon = btn.querySelector('i');
        var count = btn.querySelector('span');
        if (data.liked) {
            icon.className = 'fas fa-heart';
            btn.classList.add('is-liked');
        } else {
            icon.className = 'far fa-heart';
            btn.classList.remove('is-liked');
        }
        if (count) count.textContent = data.count;
        // Sync modal si está abierto
        if (document.getElementById('photoModal').style.display !== 'none') {
            var modalBtn = document.getElementById('modalLikeBtn');
            if (modalBtn && modalBtn.dataset.photoId === photoId) {
                var mIcon = modalBtn.querySelector('i');
                if (data.liked) { mIcon.className = 'fas fa-heart'; modalBtn.classList.add('is-liked'); }
                else { mIcon.className = 'far fa-heart'; modalBtn.classList.remove('is-liked'); }
                document.getElementById('modalLikeCount').textContent = data.count;
            }
        }
    });
}

function openPhotoModal(photoId) {
    var modal = document.getElementById('photoModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Reset
    document.getElementById('modalPhoto').src = '';
    document.getElementById('commentsList').innerHTML =
        '<div class="l69-sb-empty"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';

    fetch('/fotos/' + photoId + '/info', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        // Foto
        document.getElementById('modalPhoto').src = data.photo.url;
        document.getElementById('modalLikeCount').textContent = data.photo.likes_count;
        document.getElementById('modalCommentCount').textContent = data.comments.length;
        var likeBtn = document.getElementById('modalLikeBtn');
        likeBtn.dataset.photoId = data.photo.id;
        likeBtn.querySelector('i').className = data.photo.liked ? 'fas fa-heart' : 'far fa-heart';
        if (data.photo.liked) likeBtn.classList.add('is-liked');
        else likeBtn.classList.remove('is-liked');
        if (data.photo.caption) {
            document.getElementById('modalCaption').textContent = data.photo.caption;
            document.getElementById('modalCaption').style.display = 'block';
        } else {
            document.getElementById('modalCaption').style.display = 'none';
        }
        // Owner
        document.getElementById('modalOwnerAvatar').src = data.owner.avatar;
        document.getElementById('modalOwnerNick').textContent = data.owner.nick;
        document.getElementById('modalOwnerMeta').textContent =
            (data.owner.profile_type === 'pareja' ? '👫 Pareja' :
             data.owner.profile_type === 'unicornio' ? '⭐ Unicornio' : '👤 Single') +
            (data.owner.city ? ' · ' + data.owner.city : '');
        document.getElementById('modalOwnerLink').href = data.owner.profile_url;
        // Comentarios
        var list = document.getElementById('commentsList');
        if (data.comments.length === 0) {
            list.innerHTML = '<p class="l69-sb-empty">Sé el primero en comentar</p>';
        } else {
            list.innerHTML = data.comments.map(c =>
                '<div class="l69-comment-item">' +
                '<img src="' + c.user_avatar + '" onerror="this.src=\'/img/default-avatar.svg\'">' +
                '<div><span class="l69-comment-nick">' + c.user_nick + '</span>' +
                '<span class="l69-comment-time">' + c.created_at + '</span>' +
                '<p class="l69-comment-body">' + c.body + '</p></div></div>'
            ).join('');
        }
        document.getElementById('commentPhotoId').value = data.photo.id;
    });
}

// Like desde modal
document.getElementById('modalLikeBtn').addEventListener('click', function() {
    toggleLike(this.dataset.photoId, this);
});

// Cerrar modal
document.getElementById('photoModalClose').addEventListener('click', closeModal);
document.getElementById('photoModalOverlay').addEventListener('click', closeModal);
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
function closeModal() {
    document.getElementById('photoModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Enviar comentario
document.getElementById('commentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var photoId = document.getElementById('commentPhotoId').value;
    var body    = document.getElementById('commentBody').value.trim();
    if (!body) return;

    fetch('/fotos/' + photoId + '/comentario', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ body: body })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('commentBody').value = '';
        var note = document.querySelector('.l69-photo-modal__comment-note');
        note.style.color = '#34d399';
        note.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
        setTimeout(() => {
            note.style.color = '';
            note.innerHTML = '<i class="fas fa-info-circle"></i> Los comentarios se publican tras revisión';
        }, 3000);
    });
});

// Cargar más fotos
var loadBtn = document.getElementById('loadMoreBtn');
if (loadBtn) {
    loadBtn.addEventListener('click', function() {
        var page = this.dataset.page;
        var tab  = this.dataset.tab;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
        this.disabled = true;

        fetch('/dashboard/feed?tab=' + tab + '&page=' + page, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('feedGrid').insertAdjacentHTML('beforeend', data.html);
            if (data.hasMore) {
                loadBtn.dataset.page = data.nextPage;
                loadBtn.innerHTML = '<i class="fas fa-chevron-down"></i> Cargar más fotos';
                loadBtn.disabled = false;
            } else {
                document.getElementById('loadMoreWrap').style.display = 'none';
            }
        });
    });
}
})();
</script>
@endpush
BLADE);

// --- Partial _feed_items ---
writeFile($base . '/resources/views/dashboard/_feed_items.blade.php', <<<'BLADE'
@foreach($feed as $photo)
@php
    $owner   = $photo->user?->profile;
    $ownerNick = $owner?->nickname ?? $photo->user?->name ?? 'Usuario';
    $ownerAvatar = $owner?->avatar_url ?? asset('img/default-avatar.svg');
    $isLiked = $photo->isLikedBy($user->id);
@endphp
<div class="l69-feed-card" data-photo-id="{{ $photo->id }}">
    {{-- Foto --}}
    <div class="l69-feed-card__img-wrap">
        <img src="{{ asset('storage/' . $photo->file_path) }}"
             alt="{{ $owner?->nickname ?? 'Foto' }}"
             class="l69-feed-card__img"
             loading="lazy"
             onerror="this.parentElement.style.background='#1a1028'">
        {{-- Overlay con acciones --}}
        <div class="l69-feed-card__overlay">
            <button class="l69-like-btn {{ $isLiked ? 'is-liked' : '' }}"
                    data-photo-id="{{ $photo->id }}">
                <i class="{{ $isLiked ? 'fas' : 'far' }} fa-heart"></i>
                <span>{{ $photo->likes_count }}</span>
            </button>
            <span class="l69-feed-card__comments">
                <i class="far fa-comment"></i>
                {{ $photo->comments_count }}
            </span>
        </div>
    </div>
    {{-- Footer de la tarjeta --}}
    <div class="l69-feed-card__footer">
        <a href="{{ $owner?->nickname ? route('profile.show', $owner->nickname) : '#' }}"
           class="l69-card__owner" title="Ver perfil de {{ $ownerNick }}">
            <img src="{{ $ownerAvatar }}"
                 alt="{{ $ownerNick }}"
                 class="l69-feed-card__owner-avatar"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            <span class="l69-feed-card__owner-nick">{{ $ownerNick }}</span>
        </a>
        @if($photo->caption)
        <p class="l69-feed-card__caption">{{ Str::limit($photo->caption, 60) }}</p>
        @endif
    </div>
</div>
@endforeach
BLADE);

// --- Search view ---
writeFile($base . '/resources/views/search/index.blade.php', <<<'BLADE'
@extends('layouts.app')
@section('title', $q ? "Búsqueda: {$q}" : 'Buscar')

@section('content')
<div class="l69-page-content">
    <h1 style="font-size:1.4rem;font-weight:700;color:#fff;margin-bottom:1.25rem;">
        <i class="fas fa-search"></i>
        @if($q) Resultados para "<span style="color:#e056a0;">{{ $q }}</span>"
        @else Explorar miembros @endif
    </h1>

    @if($q && $results instanceof \Illuminate\Pagination\LengthAwarePaginator && $results->total() === 0)
    <div class="l69-sb-card" style="text-align:center;padding:2.5rem;">
        <i class="fas fa-user-slash" style="font-size:2rem;color:#4b5563;margin-bottom:1rem;display:block;"></i>
        <p style="color:#9ca3af;">No encontramos perfiles con "<strong>{{ $q }}</strong>"</p>
        <a href="{{ route('explore') }}" class="l69-quick-btn" style="display:inline-flex;margin-top:1rem;">
            Explorar todos los perfiles →
        </a>
    </div>
    @elseif($results instanceof \Illuminate\Pagination\LengthAwarePaginator && $results->count() > 0)
    <div class="l69-feed__grid">
        @foreach($results as $profile)
        <div class="l69-profile-card">
            <a href="{{ route('profile.show', $profile->nickname) }}">
                <img src="{{ $profile->avatar_url ?? asset('img/default-avatar.svg') }}"
                     alt="{{ $profile->nickname }}"
                     class="l69-profile-card__avatar"
                     onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            </a>
            <a href="{{ route('profile.show', $profile->nickname) }}"
               class="l69-profile-card__nick">{{ $profile->nickname }}</a>
            <span class="l69-profile-card__type">
                @if($profile->profile_type === 'pareja') 👫 Pareja
                @elseif($profile->profile_type === 'unicornio') ⭐ Unicornio
                @else 👤 Single @endif
            </span>
        </div>
        @endforeach
    </div>
    {{ $results->appends(['q' => $q])->links() }}
    @endif
</div>
@endsection
BLADE);

// ============================================================
// 6. NAVBAR — Agregar barra de búsqueda
// ============================================================
$navbarPath = $base . '/resources/views/components/navbar.blade.php';
$navbarContent = file_get_contents($navbarPath);

// Agregar meta csrf si no existe en app.blade.php
$appLayoutPath = $base . '/resources/views/layouts/app.blade.php';
$appContent = file_get_contents($appLayoutPath);
if (!str_contains($appContent, 'csrf-token')) {
    $appContent = str_replace(
        '<meta name="description"',
        '<meta name="csrf-token" content="{{ csrf_token() }}">' . "\n    " . '<meta name="description"',
        $appContent
    );
    file_put_contents($appLayoutPath, $appContent);
    echo "✓ CSRF meta tag añadido a app.blade.php\n";
}

// Insertar búsqueda en navbar — después del spacer y antes de los links
$searchBarCode = <<<'NAVBAR_SEARCH'

        {{-- ── Barra de búsqueda expandible ── --}}
        @auth
        <div class="l69-nav__search" id="navSearch">
            <button class="l69-nav__search-icon" id="navSearchToggle" title="Buscar">
                <i class="fas fa-search"></i>
            </button>
            <div class="l69-nav__search-wrap" id="navSearchWrap">
                <input type="text"
                       id="navSearchInput"
                       placeholder="Buscar miembros..."
                       class="l69-nav__search-input"
                       autocomplete="off"
                       maxlength="50">
                <div class="l69-nav__search-results" id="navSearchResults"></div>
            </div>
        </div>
        @endauth

NAVBAR_SEARCH;

// Insertar después del spacer
$navbarContent = str_replace(
    '<div class="l69-nav__spacer"></div>',
    '<div class="l69-nav__spacer"></div>' . $searchBarCode,
    $navbarContent
);

// Agregar CSS de búsqueda y JS antes del último </style> del navbar
$searchCSS = <<<'CSS'

/* ── Search navbar ── */
.l69-nav__search {
    position: relative;
    display: flex;
    align-items: center;
    margin-right: .5rem;
}
.l69-nav__search-icon {
    background: none;
    border: none;
    color: var(--nav-text);
    cursor: pointer;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .15s, color .15s;
    flex-shrink: 0;
}
.l69-nav__search-icon:hover { background: var(--nav-hover-bg); color: #fff; }
.l69-nav__search-wrap {
    position: absolute;
    right: 40px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    overflow: hidden;
    transition: width .3s cubic-bezier(.4,0,.2,1);
}
.l69-nav__search.is-open .l69-nav__search-wrap {
    width: 260px;
}
.l69-nav__search-input {
    width: 100%;
    padding: .45rem .9rem;
    background: rgba(255,255,255,.07);
    border: 1px solid var(--nav-border);
    border-radius: 20px;
    color: #fff;
    font-size: .875rem;
    outline: none;
    transition: border-color .2s, background .2s;
}
.l69-nav__search-input:focus {
    border-color: rgba(180,60,120,.5);
    background: rgba(255,255,255,.1);
}
.l69-nav__search-input::placeholder { color: rgba(226,217,243,.4); }
.l69-nav__search-results {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: var(--nav-dropdown-bg);
    border: 1px solid var(--nav-border);
    border-radius: 12px;
    box-shadow: 0 16px 48px rgba(0,0,0,.5);
    overflow: hidden;
    display: none;
    z-index: 9200;
}
.l69-nav__search-results.has-results { display: block; }
.l69-search-result-item {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .6rem .9rem;
    text-decoration: none;
    color: var(--nav-text);
    transition: background .15s;
    border-bottom: 1px solid rgba(255,255,255,.04);
}
.l69-search-result-item:last-child { border-bottom: none; }
.l69-search-result-item:hover { background: var(--nav-hover-bg); color: #fff; }
.l69-search-result-item img {
    width: 32px; height: 32px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid rgba(180,60,120,.3);
}
.l69-search-result-nick { font-weight: 600; font-size: .86rem; }
.l69-search-result-meta { font-size: .74rem; color: var(--nav-text-muted); }
.l69-search-online-dot {
    width: 7px; height: 7px;
    background: #22c55e;
    border-radius: 50%;
    flex-shrink: 0;
}
.l69-search-result-all {
    display: block;
    text-align: center;
    padding: .55rem;
    font-size: .8rem;
    color: var(--nav-active-color);
    text-decoration: none;
    background: rgba(180,60,120,.06);
}
.l69-search-result-all:hover { background: rgba(180,60,120,.12); }
@media (max-width: 767px) {
    .l69-nav__search { display: none; }
}

CSS;

$navbarContent = str_replace('</style>', $searchCSS . '</style>', $navbarContent);

// Agregar JS de búsqueda al final del componente
$searchJS = <<<'NAVJS'

@auth
<script>
(function(){
    var toggle  = document.getElementById('navSearchToggle');
    var wrap    = document.getElementById('navSearch');
    var input   = document.getElementById('navSearchInput');
    var results = document.getElementById('navSearchResults');
    var timer;

    if (!toggle) return;

    toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        wrap.classList.toggle('is-open');
        if (wrap.classList.contains('is-open')) {
            setTimeout(function(){ input.focus(); }, 320);
        } else {
            results.classList.remove('has-results');
            results.innerHTML = '';
            input.value = '';
        }
    });

    input.addEventListener('input', function() {
        clearTimeout(timer);
        var q = this.value.trim();
        if (q.length < 2) {
            results.classList.remove('has-results');
            results.innerHTML = '';
            return;
        }
        timer = setTimeout(function() {
            fetch('/buscar/live?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.length === 0) {
                    results.innerHTML = '<div style="padding:.75rem 1rem;font-size:.82rem;color:#9b8aaa;">Sin resultados</div>';
                } else {
                    results.innerHTML = data.map(p =>
                        '<a href="' + p.url + '" class="l69-search-result-item">' +
                        '<img src="' + p.avatar + '" onerror="this.src=\'/img/default-avatar.svg\'">' +
                        '<div style="flex:1;min-width:0;">' +
                        '<div class="l69-search-result-nick">' + p.nick + '</div>' +
                        '<div class="l69-search-result-meta">' +
                        (p.profile_type === 'pareja' ? '👫' : p.profile_type === 'unicornio' ? '⭐' : '👤') +
                        (p.city ? ' · ' + p.city : '') + '</div></div>' +
                        (p.online ? '<span class="l69-search-online-dot" title="En línea"></span>' : '') +
                        '</a>'
                    ).join('') +
                    '<a href="/buscar?q=' + encodeURIComponent(q) + '" class="l69-search-result-all">' +
                    'Ver todos los resultados →</a>';
                }
                results.classList.add('has-results');
            });
        }, 280);
    });

    document.addEventListener('click', function(e) {
        if (!wrap.contains(e.target)) {
            wrap.classList.remove('is-open');
            results.classList.remove('has-results');
            results.innerHTML = '';
            input.value = '';
        }
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            wrap.classList.remove('is-open');
            results.classList.remove('has-results');
            input.value = '';
        }
        if (e.key === 'Enter') {
            var q = input.value.trim();
            if (q) window.location.href = '/buscar?q=' + encodeURIComponent(q);
        }
    });
})();
</script>
@endauth
NAVJS;

$navbarContent .= $searchJS;
file_put_contents($navbarPath, $navbarContent);
echo "✓ resources/views/components/navbar.blade.php (búsqueda añadida)\n";

// ============================================================
// 7. APP.BLADE.PHP — Sistema @stack para sidebars dinámicos
// ============================================================
$appContent = file_get_contents($appLayoutPath);

// Reemplazar el sistema actual de sidebars por @stack
$oldSidebar = <<<'OLD'
        @auth
        {{-- ── Layout 3 columnas para usuarios autenticados ── --}}
        @if(!in_array(request()->route()?->getName(), [
            'login', 'landing', 'invitation.show', 'invitation.request',
            'password.request', 'password.reset', 'password.change',
            'verification.show', 'verification.pending',
            'admin.invitations.index', 'admin.invitations.show',
            'admin.verifications.index', 'admin.verifications.show',
            'admin.photos.index',
        ]))
        <div class="l69-layout">

            {{-- ── SIDEBAR IZQUIERDO ── --}}
            <aside class="l69-sidebar l69-sidebar--left">
                @include('layouts.sidebar-left')
            </aside>

            {{-- ── CONTENIDO CENTRAL ── --}}
            <div class="l69-layout__content">
                @yield('content')
            </div>

            {{-- ── SIDEBAR DERECHO (contextual) ── --}}
            <aside class="l69-sidebar l69-sidebar--right">
                @include('layouts.sidebar-right')
            </aside>

        </div>
        @else
            @yield('content')
        @endif

        @else
        {{-- ── Layout simple para guests ── --}}
        @yield('content')
        @endauth
OLD;

$newSidebar = <<<'NEW'
        @auth
        @php
            $noLayout = in_array(request()->route()?->getName(), [
                'login','landing','invitation.show','invitation.request',
                'password.request','password.reset','password.change',
                'verification.show','verification.pending',
                'admin.invitations.index','admin.invitations.show',
                'admin.verifications.index','admin.verifications.show',
                'admin.photos.index',
            ]);
            $hasLeftSidebar  = $__env->hasStack('sidebar-left');
            $hasRightSidebar = $__env->hasStack('sidebar-right');
        @endphp

        @if($noLayout)
            @yield('content')
        @else
        <div class="l69-layout">

            {{-- ── SIDEBAR IZQUIERDO ── --}}
            <aside class="l69-sidebar l69-sidebar--left">
                @stack('sidebar-left')
            </aside>

            {{-- ── CONTENIDO CENTRAL ── --}}
            <div class="l69-layout__content">
                @yield('content')
            </div>

            {{-- ── SIDEBAR DERECHO ── --}}
            <aside class="l69-sidebar l69-sidebar--right">
                @stack('sidebar-right')
            </aside>

        </div>
        @endif

        @else
        @yield('content')
        @endauth
NEW;

if (str_contains($appContent, '@include(\'layouts.sidebar-left\')')) {
    $appContent = str_replace($oldSidebar, $newSidebar, $appContent);
} else {
    // Si ya tiene @stack, no hacer nada
    echo "  app.blade.php ya tiene sistema @stack\n";
}

// Agregar CSS del feed y sidebar cards
$feedCSS = <<<'FEEDCSS'

    /* ══════════════════════════════════════
       SIDEBAR CARDS (sistema de tarjetas)
       ══════════════════════════════════════ */
    .l69-sb-card {
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(180,60,120,.18);
        border-radius: 16px;
        padding: 1.1rem;
        margin-bottom: .85rem;
    }
    .l69-sb-card-title {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: rgba(224,86,160,.85);
        margin-bottom: .75rem;
        display: flex;
        align-items: center;
        gap: .45rem;
    }
    .l69-sb-count {
        margin-left: auto;
        background: rgba(180,60,120,.2);
        color: #e056a0;
        border-radius: 10px;
        padding: .1rem .5rem;
        font-size: .72rem;
        font-weight: 700;
    }
    .l69-sb-profile {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: .4rem;
    }
    .l69-sb-avatar-wrap { position: relative; }
    .l69-sb-avatar {
        width: 76px; height: 76px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(180,60,120,.45);
    }
    .l69-sb-verified {
        position: absolute; bottom: 2px; right: 2px;
        width: 20px; height: 20px;
        background: #27ae60; border-radius: 50%;
        border: 2px solid #0f0a1a;
        display: flex; align-items: center; justify-content: center;
        font-size: .55rem; color: #fff;
    }
    .l69-sb-nick { font-weight: 700; font-size: .95rem; color: #fff; }
    .l69-sb-type { font-size: .76rem; color: rgba(180,60,120,.9); }
    .l69-sb-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .18rem .6rem; border-radius: 20px;
        font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .04em;
    }
    .l69-sb-lastseen {
        font-size: .73rem; color: rgba(226,217,243,.45);
        display: flex; align-items: center; gap: .3rem;
    }
    .l69-sb-user-row {
        display: flex; align-items: center; gap: .6rem;
        padding: .4rem 0;
        border-bottom: 1px solid rgba(255,255,255,.04);
    }
    .l69-sb-user-row:last-of-type { border-bottom: none; }
    .l69-sb-user-row img {
        width: 32px; height: 32px;
        border-radius: 50%; object-fit: cover;
        border: 1px solid rgba(180,60,120,.3);
        flex-shrink: 0;
    }
    .l69-sb-user-info { display: flex; flex-direction: column; min-width: 0; }
    .l69-sb-user-nick {
        font-size: .83rem; font-weight: 600; color: #e2d9f3;
        text-decoration: none; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis;
    }
    .l69-sb-user-nick:hover { color: #e056a0; }
    .l69-sb-user-time { font-size: .72rem; color: rgba(226,217,243,.4); }
    .l69-sb-empty { font-size: .8rem; color: rgba(226,217,243,.4); text-align: center; padding: .5rem 0; }
    .l69-sb-see-more {
        display: block; text-align: right;
        font-size: .78rem; color: #e056a0;
        text-decoration: none; margin-top: .5rem;
        font-weight: 600;
    }
    .l69-sb-see-more:hover { text-decoration: underline; }

    /* ══════════════════════════════════════
       FEED DE FOTOS
       ══════════════════════════════════════ */
    .l69-feed { width: 100%; }
    .l69-feed__tabs {
        display: flex; gap: .5rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid rgba(180,60,120,.15);
        padding-bottom: .75rem;
    }
    .l69-feed__tab {
        display: flex; align-items: center; gap: .4rem;
        padding: .5rem 1.1rem;
        border-radius: 8px;
        color: rgba(226,217,243,.6);
        text-decoration: none;
        font-size: .88rem; font-weight: 600;
        transition: all .18s;
        border: 1px solid transparent;
    }
    .l69-feed__tab:hover { color: #fff; background: rgba(180,60,120,.1); }
    .l69-feed__tab.is-active {
        color: #e056a0;
        background: rgba(180,60,120,.15);
        border-color: rgba(180,60,120,.3);
    }
    .l69-feed__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: .85rem;
    }
    .l69-feed__load-more { text-align: center; margin: 1.5rem 0; }
    .l69-feed__load-btn {
        display: inline-flex; align-items: center; gap: .5rem;
        padding: .65rem 1.75rem;
        background: rgba(180,60,120,.12);
        border: 1px solid rgba(180,60,120,.3);
        border-radius: 10px;
        color: #e2d9f3; font-size: .9rem; font-weight: 600;
        cursor: pointer; transition: all .18s;
    }
    .l69-feed__load-btn:hover {
        background: rgba(180,60,120,.22);
        color: #fff;
    }

    /* Tarjeta de foto del feed */
    .l69-feed-card {
        border-radius: 12px;
        overflow: hidden;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(180,60,120,.12);
        cursor: pointer;
        transition: transform .2s, box-shadow .2s;
    }
    .l69-feed-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(0,0,0,.4);
        border-color: rgba(180,60,120,.35);
    }
    .l69-feed-card__img-wrap {
        position: relative;
        aspect-ratio: 1;
        overflow: hidden;
        background: #0f0a1a;
    }
    .l69-feed-card__img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform .3s;
    }
    .l69-feed-card:hover .l69-feed-card__img { transform: scale(1.04); }
    .l69-feed-card__overlay {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        padding: .6rem .7rem;
        background: linear-gradient(to top, rgba(0,0,0,.75), transparent);
        display: flex; align-items: center; gap: .6rem;
        opacity: 0; transition: opacity .2s;
    }
    .l69-feed-card:hover .l69-feed-card__overlay { opacity: 1; }
    .l69-feed-card__footer {
        padding: .55rem .7rem;
    }
    .l69-card__owner {
        display: flex; align-items: center; gap: .45rem;
        text-decoration: none;
    }
    .l69-feed-card__owner-avatar {
        width: 24px; height: 24px;
        border-radius: 50%; object-fit: cover;
        border: 1px solid rgba(180,60,120,.3);
    }
    .l69-feed-card__owner-nick {
        font-size: .8rem; font-weight: 600; color: #e2d9f3;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .l69-card__owner:hover .l69-feed-card__owner-nick { color: #e056a0; }
    .l69-feed-card__caption {
        font-size: .75rem; color: rgba(226,217,243,.5);
        margin: .2rem 0 0; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis;
    }
    .l69-feed-card__comments {
        color: rgba(255,255,255,.8); font-size: .82rem;
        display: flex; align-items: center; gap: .3rem;
    }

    /* Like button */
    .l69-like-btn {
        display: flex; align-items: center; gap: .3rem;
        background: rgba(255,255,255,.15);
        border: none; border-radius: 20px;
        padding: .3rem .65rem;
        color: #fff; font-size: .82rem; font-weight: 600;
        cursor: pointer; transition: all .15s;
    }
    .l69-like-btn:hover { background: rgba(239,68,68,.3); }
    .l69-like-btn.is-liked { background: rgba(239,68,68,.35); color: #fca5a5; }
    .l69-like-btn.is-liked i { color: #ef4444; }
    .l69-comment-count-badge {
        color: rgba(255,255,255,.8); font-size: .82rem;
        display: flex; align-items: center; gap: .3rem;
    }

    /* ══════════════════════════════════════
       MODAL DE FOTO
       ══════════════════════════════════════ */
    .l69-photo-modal {
        position: fixed; inset: 0;
        z-index: 99990;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .l69-photo-modal__overlay {
        position: absolute; inset: 0;
        background: rgba(0,0,0,.88);
        backdrop-filter: blur(6px);
    }
    .l69-photo-modal__container {
        position: relative;
        z-index: 1;
        background: #12091e;
        border: 1px solid rgba(180,60,120,.25);
        border-radius: 20px;
        max-width: 960px;
        width: 100%;
        max-height: 92vh;
        overflow: hidden;
        box-shadow: 0 32px 80px rgba(0,0,0,.6);
    }
    .l69-photo-modal__close {
        position: absolute; top: .75rem; right: .75rem;
        width: 34px; height: 34px;
        background: rgba(255,255,255,.08);
        border: none; border-radius: 50%;
        color: #fff; cursor: pointer; z-index: 10;
        display: flex; align-items: center; justify-content: center;
        font-size: .9rem; transition: background .15s;
    }
    .l69-photo-modal__close:hover { background: rgba(239,68,68,.3); }
    .l69-photo-modal__body {
        display: grid;
        grid-template-columns: 1fr 320px;
        max-height: 92vh;
    }
    .l69-photo-modal__img-wrap {
        position: relative;
        background: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 400px;
    }
    .l69-photo-modal__img {
        max-width: 100%; max-height: 80vh;
        object-fit: contain;
    }
    .l69-photo-modal__img-actions {
        position: absolute; bottom: 1rem; left: 1rem;
        display: flex; gap: .65rem; align-items: center;
    }
    .l69-photo-modal__panel {
        display: flex; flex-direction: column;
        border-left: 1px solid rgba(180,60,120,.15);
        max-height: 92vh; overflow: hidden;
    }
    .l69-photo-modal__owner {
        display: flex; align-items: center; gap: .65rem;
        padding: 1rem 1.1rem;
        border-bottom: 1px solid rgba(180,60,120,.12);
    }
    .l69-photo-modal__owner-avatar {
        width: 42px; height: 42px;
        border-radius: 50%; object-fit: cover;
        border: 2px solid rgba(180,60,120,.4);
    }
    .l69-photo-modal__owner-nick {
        font-weight: 700; font-size: .95rem; color: #fff;
    }
    .l69-photo-modal__owner-meta {
        font-size: .78rem; color: rgba(226,217,243,.5);
    }
    .l69-photo-modal__caption {
        padding: .65rem 1.1rem;
        font-size: .85rem; color: rgba(226,217,243,.7);
        border-bottom: 1px solid rgba(180,60,120,.1);
    }
    .l69-photo-modal__comments {
        flex: 1; overflow-y: auto;
        padding: .75rem 1.1rem;
        scrollbar-width: thin;
        scrollbar-color: rgba(180,60,120,.3) transparent;
    }
    .l69-photo-modal__comment-form {
        padding: .75rem 1.1rem;
        border-top: 1px solid rgba(180,60,120,.12);
    }
    .l69-photo-modal__comment-input-wrap {
        display: flex; align-items: center; gap: .5rem;
    }
    .l69-photo-modal__comment-avatar {
        width: 28px; height: 28px;
        border-radius: 50%; object-fit: cover;
        flex-shrink: 0;
    }
    .l69-photo-modal__comment-input {
        flex: 1; padding: .45rem .75rem;
        background: rgba(255,255,255,.07);
        border: 1px solid rgba(180,60,120,.2);
        border-radius: 20px; color: #fff;
        font-size: .84rem; outline: none;
        transition: border-color .2s;
    }
    .l69-photo-modal__comment-input:focus {
        border-color: rgba(180,60,120,.5);
    }
    .l69-photo-modal__comment-send {
        width: 32px; height: 32px;
        background: linear-gradient(135deg, #c0392b, #8e44ad);
        border: none; border-radius: 50%;
        color: #fff; cursor: pointer; font-size: .82rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; transition: opacity .15s;
    }
    .l69-photo-modal__comment-send:hover { opacity: .85; }
    .l69-photo-modal__comment-note {
        font-size: .72rem; color: rgba(226,217,243,.4);
        margin: .4rem 0 0; display: flex; gap: .3rem; align-items: center;
    }
    .l69-comment-item {
        display: flex; gap: .55rem;
        margin-bottom: .75rem;
    }
    .l69-comment-item img {
        width: 28px; height: 28px;
        border-radius: 50%; object-fit: cover; flex-shrink: 0;
    }
    .l69-comment-nick {
        font-size: .8rem; font-weight: 700; color: #e2d9f3;
        margin-right: .4rem;
    }
    .l69-comment-time {
        font-size: .72rem; color: rgba(226,217,243,.4);
    }
    .l69-comment-body {
        font-size: .83rem; color: rgba(226,217,243,.8);
        margin: .2rem 0 0; line-height: 1.4;
    }
    @media (max-width: 640px) {
        .l69-photo-modal__body { grid-template-columns: 1fr; }
        .l69-photo-modal__panel { max-height: 50vh; }
    }

FEEDCSS;

$appContent = str_replace(
    '    </style>' . "\n" . '<style>[x-cloak]',
    $feedCSS . '    </style>' . "\n" . '<style>[x-cloak]',
    $appContent
);

file_put_contents($appLayoutPath, $appContent);
echo "✓ resources/views/layouts/app.blade.php actualizado\n";

// ============================================================
// 8. RUTAS
// ============================================================
$routesPath = $base . '/routes/web.php';
$routesContent = file_get_contents($routesPath);

$newRoutes = <<<'ROUTES'

// ── Dashboard con feed ──
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware('auth')->name('dashboard');
Route::get('/dashboard/feed', [\App\Http\Controllers\DashboardController::class, 'feedAjax'])
    ->middleware('auth')->name('dashboard.feed');

// ── Búsqueda ──
Route::get('/buscar', [\App\Http\Controllers\SearchController::class, 'index'])
    ->middleware('auth')->name('search.index');
Route::get('/buscar/live', [\App\Http\Controllers\SearchController::class, 'live'])
    ->middleware('auth')->name('search.live');

// ── Interacciones de fotos ──
Route::middleware('auth')->group(function() {
    Route::post('/fotos/{photo}/like',       [\App\Http\Controllers\Photo\PhotoInteractionController::class, 'toggleLike'])->name('photos.like');
    Route::post('/fotos/{photo}/comentario', [\App\Http\Controllers\Photo\PhotoInteractionController::class, 'addComment'])->name('photos.comment');
    Route::get('/fotos/{photo}/info',        [\App\Http\Controllers\Photo\PhotoInteractionController::class, 'show'])->name('photos.info');
});

ROUTES;

if (!str_contains($routesContent, 'DashboardController')) {
    // Reemplazar ruta dashboard existente o agregar al final
    if (str_contains($routesContent, "name('dashboard')")) {
        $routesContent = preg_replace(
            "/Route::get\('\/dashboard'.*?name\('dashboard'\);/s",
            "// dashboard reemplazado por DashboardController",
            $routesContent
        );
    }
    $routesContent .= $newRoutes;
    file_put_contents($routesPath, $routesContent);
    echo "✓ routes/web.php actualizado\n";
} else {
    echo "  routes/web.php ya tiene DashboardController\n";
}

// ============================================================
// RESUMEN
// ============================================================
echo "\n";
echo "════════════════════════════════════════════════════\n";
echo "  Sub-fases 7A + 7B listas. Ejecuta en orden:\n";
echo "\n";
echo "  1. C:\\php\\php.exe artisan migrate\n";
echo "  2. C:\\php\\php.exe artisan view:clear\n";
echo "  3. C:\\php\\php.exe artisan route:clear\n";
echo "  4. C:\\php\\php.exe artisan config:clear\n";
echo "  5. C:\\php\\php.exe artisan serve\n";
echo "\n";
echo "  Luego registra el middleware en bootstrap/app.php:\n";
echo "  'track.seen' => \\App\\Http\\Middleware\\TrackLastSeen::class\n";
echo "  Y añádelo al grupo 'web' o en las rutas auth.\n";
echo "════════════════════════════════════════════════════\n";
