<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Photo;
use App\Models\ProfileView;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user    = auth()->user();
        $profile = $user->profile;
        $tab     = $request->get('tab', 'new');
        $feed    = $this->getFeed((string)$user->id, $tab, 1);
        $whoViewedMe = collect(); $whoViewedMeCount = 0;
        $iViewed = collect(); $iViewedCount = 0;
        $onlineUsers = collect(); $newUsers = collect();
        try {
            $uid = (string) $user->id;
            $whoViewedMe = ProfileView::with('viewer.profile')->where(DB::raw('viewed_id::text'), $uid)->where(DB::raw('viewer_id::text'), '!=', $uid)->orderByDesc('viewed_at')->limit(5)->get();
            $whoViewedMeCount = ProfileView::where(DB::raw('viewed_id::text'), $uid)->where(DB::raw('viewer_id::text'), '!=', $uid)->count();
            $iViewed = ProfileView::with('viewed.profile')->where(DB::raw('viewer_id::text'), $uid)->orderByDesc('viewed_at')->limit(5)->get();
            $iViewedCount = ProfileView::where(DB::raw('viewer_id::text'), $uid)->count();
        } catch (\Exception $e) { Log::error('Views:'.(string)$e->getMessage()); }
        try {
            $uid = (string) $user->id;
            $onlineUsers = DB::table('users')->join('profiles', DB::raw('users.id::text'), '=', DB::raw('profiles.user_id::text'))->select('profiles.nickname','profiles.avatar_url','users.last_seen_at')->where('users.last_seen_at', '>=', now()->subMinutes(10))->where(DB::raw('users.id::text'), '!=', $uid)->orderByDesc('users.last_seen_at')->limit(12)->get();
        } catch (\Exception $e) { Log::error('Online:'.(string)$e->getMessage()); }
        try {
            $uid = (string) $user->id;
            $newUsers = DB::table('users')->join('profiles', DB::raw('users.id::text'), '=', DB::raw('profiles.user_id::text'))->select('profiles.nickname','profiles.avatar_url','profiles.profile_type','users.created_at')->where('profiles.profile_completed', true)->where(DB::raw('users.id::text'), '!=', $uid)->orderByDesc('users.created_at')->limit(5)->get();
        } catch (\Exception $e) { Log::error('New:'.(string)$e->getMessage()); }
        return view('dashboard.index', compact('user','profile','feed','tab','whoViewedMe','whoViewedMeCount','iViewed','iViewedCount','onlineUsers','newUsers'));
    }

    private function getFeed(string $userId, string $tab, int $page): LengthAwarePaginator
    {
        try {
           $photos = Photo::with(['user.profile'])->approved()->where('album_type','public')->orderByDesc('created_at')->paginate(12,['*'],'page',$page);
            foreach ($photos as $photo) {
                $pid = (string)$photo->id;
                $photo->likes_count    = DB::table('photo_likes')->where(DB::raw('photo_id::text'),$pid)->count();
                $photo->comments_count = DB::table('photo_comments')->where(DB::raw('photo_id::text'),$pid)->where('status','approved')->count();
            }
            return $photos;
        } catch (\Exception $e) {
            Log::error('getFeed:'.(string)$e->getMessage());
            return new LengthAwarePaginator([],0,12,$page);
        }
    }

    public function toggleLike(Request $request, string $photoId)
    {
        $userId = (string)auth()->id();
        $exists = DB::table('photo_likes')->where(DB::raw('photo_id::text'),$photoId)->where(DB::raw('user_id::text'),$userId)->exists();
        if ($exists) { DB::table('photo_likes')->where(DB::raw('photo_id::text'),$photoId)->where(DB::raw('user_id::text'),$userId)->delete(); $liked=false; }
        else { DB::table('photo_likes')->insert(['photo_id'=>$photoId,'user_id'=>$userId,'created_at'=>now()]); $liked=true; }
        $count = DB::table('photo_likes')->where(DB::raw('photo_id::text'),$photoId)->count();
        return response()->json(['liked'=>$liked,'count'=>$count]);
    }

    
    public function feedAjax(Request $request)
    {
        $user = auth()->user();
        $tab  = $request->get('tab', 'new');
        $page = (int) $request->get('page', 1);
        $feed = $this->getFeed((string) $user->id, $tab, $page);

        $html = '';
        foreach ($feed as $photo) {
            $pid      = (string) $photo->id;
            $imgUrl   = route('photo.serve', ['path' => $photo->file_path]);
            $nick     = optional(optional($photo->user)->profile)->nickname ?? 'Usuario';
            $avRaw    = optional(optional($photo->user)->profile)->avatar_url;
            $av       = $avRaw ? route('photo.serve', ['path' => $avRaw]) : asset('img/default-avatar.svg');
            $l        = (int) $photo->likes_count;
            $co       = (int) $photo->comments_count;
            $isLiked  = DB::table('photo_likes')
                          ->where(DB::raw('photo_id::text'), $pid)
                          ->where(DB::raw('user_id::text'), (string) $user->id)
                          ->exists();
            $likedClass = $isLiked ? ' is-liked' : '';
            $heartIcon  = $isLiked ? 'fas' : 'far';

            $html .= '
<div class="dsb-photo-card" data-photo-id="' . $pid . '">
  <div class="dsb-photo-card__header">
    <a href="/perfil/' . htmlspecialchars($nick) . '" class="dsb-photo-card__owner">
      <img src="' . $av . '" onerror="this.src=\'' . asset('img/default-avatar.svg') . '\'">
      <div><span class="dsb-photo-card__owner-nick">' . htmlspecialchars($nick) . '</span></div>
    </a>
  </div>
  <div class="dsb-photo-card__img-wrap">
    <img src="' . $imgUrl . '" class="dsb-photo-card__img" loading="lazy"
         onerror="this.parentElement.style.background=\'#1a1028\'">
  </div>
  <div class="dsb-photo-card__footer">
    <div class="dsb-photo-card__actions">
      <button class="dsb-like-btn' . $likedClass . '" data-photo-id="' . $pid . '">
        <i class="' . $heartIcon . ' fa-heart"></i>
        <span>' . $l . '</span>
      </button>
      <button class="dsb-comment-btn" data-photo-id="' . $pid . '">
        <i class="far fa-comment"></i>
        <span>' . $co . '</span>
      </button>
      <a href="/perfil/' . htmlspecialchars($nick) . '" class="dsb-profile-btn">
        <i class="fas fa-user"></i>
      </a>
    </div>
  </div>
</div>';
        }

        return response()->json([
            'html'     => $html,
            'hasMore'  => $feed->hasMorePages(),
            'nextPage' => $feed->currentPage() + 1,
        ]);
    }

    public function photoModal(Request $request, string $photoId)
    {
        try {
            $photo = Photo::with(['user.profile'])
                          ->where(DB::raw('id::text'), $photoId)
                          ->firstOrFail();

            $likesCount    = DB::table('photo_likes')
                               ->where(DB::raw('photo_id::text'), $photoId)
                               ->count();
            $commentsCount = DB::table('photo_comments')
                               ->where(DB::raw('photo_id::text'), $photoId)
                               ->where('status', 'approved')
                               ->count();
            $userLiked = DB::table('photo_likes')
                           ->where(DB::raw('photo_id::text'), $photoId)
                           ->where(DB::raw('user_id::text'), (string) auth()->id())
                           ->exists();

            $comments = DB::table('photo_comments')
                ->join('users',    DB::raw('photo_comments.user_id::text'), '=', DB::raw('users.id::text'))
                ->join('profiles', DB::raw('users.id::text'),               '=', DB::raw('profiles.user_id::text'))
                ->select(
                    'photo_comments.id',
                    'photo_comments.body',
                    'photo_comments.created_at',
                    'profiles.nickname as user_nick',
                    'profiles.avatar_url'
                )
                ->where(DB::raw('photo_comments.photo_id::text'), $photoId)
                ->where('photo_comments.status', 'approved')
                ->orderBy('photo_comments.created_at')
                ->get()
                ->map(function ($c) {
                    $avRaw = $c->avatar_url;
                    return [
                        'user_nick'   => $c->user_nick ?? 'Usuario',
                        'user_avatar' => $avRaw
                            ? route('photo.serve', ['path' => $avRaw])
                            : asset('img/default-avatar.svg'),
                        'body'        => $c->body,
                        'created_at'  => \Carbon\Carbon::parse($c->created_at)->diffForHumans(),
                    ];
                });

            $profile  = optional($photo->user)->profile;
            $avRaw    = $profile?->avatar_url;

            return response()->json([
                'photo' => [
                    'id'           => (string) $photo->id,
                    'url'          => route('photo.serve', ['path' => $photo->file_path]),
                    'caption'      => $photo->caption ?? '',
                    'likes_count'  => $likesCount,
                    'comments_count' => $commentsCount,
                    'liked'        => $userLiked,
                ],
                'owner' => [
                    'nick'         => $profile?->nickname ?? 'Usuario',
                    'avatar'       => $avRaw
                        ? route('photo.serve', ['path' => $avRaw])
                        : asset('img/default-avatar.svg'),
                    'profile_type' => $profile?->profile_type ?? 'single',
                    'city'         => $profile?->city ?? '',
                    'profile_url'  => $profile?->nickname
                        ? route('profile.show', $profile->nickname)
                        : '#',
                ],
                'comments' => $comments,
            ]);

        } catch (\Exception $e) {
            Log::error('photoModal: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeComment(Request $request, string $photoId)
    {
        $request->validate(['body' => 'required|string|max:500']);

        try {
            DB::table('photo_comments')->insert([
                'id'         => \Illuminate\Support\Str::uuid(),
                'photo_id'   => $photoId,
                'user_id'    => (string) auth()->id(),
                'body'       => $request->input('body'),
                'status'     => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'message' => 'Comentario enviado, pendiente de revisión.',
            ]);

        } catch (\Exception $e) {
            Log::error('storeComment: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
