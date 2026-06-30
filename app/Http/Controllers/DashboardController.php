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

    public function feedAjax(Request $request)
    {
        $user = auth()->user();
        $tab  = $request->get('tab','new');
        $page = (int)$request->get('page',1);
        $feed = $this->getFeed((string)$user->id,$tab,$page);
        $html = '';
        foreach ($feed as $photo) {
            $src  = asset('storage/'.($photo->thumbnail_path?:$photo->file_path));
            $nick = optional(optional($photo->user)->profile)->nickname ?? 'Usuario';
            $av   = optional(optional($photo->user)->profile)->avatar_url ? asset('storage/'.optional(optional($photo->user)->profile)->avatar_url) : asset('img/default-avatar.svg');
            $l = (int)$photo->likes_count; $co = (int)$photo->comments_count; $pid = $photo->id;
            $html .= '<div class="feed-card" data-photo-id="' . $pid . '"><div class="feed-card-img-wrap"><img src="' . $src . '" loading="lazy" onclick=\'openPhotoModal("' . $pid . '")\'/></div><div class="feed-card-footer"><a href="/perfil/' . $nick . '" class="feed-card-user"><img src="' . $av . '" class="feed-card-avatar"/><span>' . $nick . '</span></a><div class="feed-card-actions"><button onclick=\'toggleLike("' . $pid . '",this)\' class="btn-like">&#x2665; <span class="like-count">' . $l . '</span></button><button onclick=\'openPhotoModal("' . $pid . '")\' class="btn-comment">&#x1F4AC; ' . $co . '</button></div></div></div>';
        }
        return response()->json(['html'=>$html,'hasMore'=>$feed->hasMorePages(),'nextPage'=>$feed->currentPage()+1]);
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

    public function photoModal(Request $request, string $photoId)
    {
        try {
            $photo = Photo::with(['user.profile'])->where(DB::raw('id::text'),$photoId)->firstOrFail();
            $photo->likes_count    = DB::table('photo_likes')->where(DB::raw('photo_id::text'),$photoId)->count();
            $photo->comments_count = DB::table('photo_comments')->where(DB::raw('photo_id::text'),$photoId)->where('status','approved')->count();
            $comments = DB::table('photo_comments')->join('users',DB::raw('photo_comments.user_id::text'),'=',DB::raw('users.id::text'))->join('profiles',DB::raw('users.id::text'),'=',DB::raw('profiles.user_id::text'))->select('photo_comments.id','photo_comments.body','photo_comments.created_at','profiles.nickname','profiles.avatar_url')->where(DB::raw('photo_comments.photo_id::text'),$photoId)->where('photo_comments.status','approved')->orderBy('photo_comments.created_at')->get();
            $userLiked = DB::table('photo_likes')->where(DB::raw('photo_id::text'),$photoId)->where(DB::raw('user_id::text'),(string)auth()->id())->exists();
            return response()->json(['photo'=>['id'=>$photo->id,'url'=>asset('storage/'.$photo->file_path),'caption'=>$photo->caption,'likes'=>(int)$photo->likes_count,'comments'=>(int)$photo->comments_count,'userLiked'=>$userLiked,'nick'=>optional(optional($photo->user)->profile)->nickname,'avatar'=>optional(optional($photo->user)->profile)->avatar_url ? asset('storage/'.optional(optional($photo->user)->profile)->avatar_url) : asset('img/default-avatar.svg')],'comments'=>$comments]);
        } catch (\Exception $e) { return response()->json(['error'=>(string)$e->getMessage()],500); }
    }
}
