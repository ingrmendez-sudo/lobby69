<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $userId = (string) $user->id;
        $cat    = $request->get('cat', 'all');

        // Tipos por categoría
        $typeMap = [
            'messages' => ['new_message'],
            'social'   => ['friend_request', 'friend_accepted', 'follow'],
            'activity' => ['like', 'article_like'],
        ];

        // Notificaciones del sistema filtradas por categoría
        $query = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50);

        if (isset($typeMap[$cat])) {
            $query->whereIn('type', $typeMap[$cat]);
        } elseif ($cat === 'all') {
            // todas excepto comment (que viene de photo_comments)
        }
        // comments y reviews se cargan por separado

        $notifs = $query->get()->map(function($n) {
            $n->data = json_decode($n->data, true);
            return $n;
        });

        // Comentarios recibidos en fotos
        $photoComments = collect();
        if (in_array($cat, ['all', 'comments'])) {
            $photoComments = DB::table('photo_comments as pc')
                ->join('photos as ph', DB::raw('ph.photo_uuid::text'), '=', DB::raw('pc.photo_id::text'))
                ->join('users as u',   DB::raw('u.id::text'),          '=', DB::raw('pc.user_id::text'))
                ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
                ->whereRaw('ph.user_id::text = ?', [$userId])
                ->where('pc.status', 'approved')
                ->orderByDesc('pc.created_at')
                ->select([
                    'pc.id', 'pc.body', 'pc.created_at', 'pc.read_at',
                    'ph.photo_uuid', 'ph.caption',
                    DB::raw('COALESCE(pr.display_name, u.username) AS commenter_name'),
                    DB::raw('pr.nickname AS commenter_nick'),
                ])
                ->limit(30)
                ->get();
        }

        // Recomendaciones recibidas
        $reviewsReceived = collect();
        if (in_array($cat, ['all', 'reviews'])) {
            $reviewsReceived = DB::table('profile_reviews as r')
                ->join('users as u',  DB::raw('u.id::text'), '=', DB::raw('r.reviewer_id::text'))
                ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
                ->whereRaw('r.reviewed_id::text = ?', [$userId])
                ->orderByDesc('r.created_at')
                ->select([
                    'r.id', 'r.type', 'r.body', 'r.created_at',
                    DB::raw('COALESCE(pr.display_name, u.username) AS reviewer_name'),
                    DB::raw('pr.nickname AS reviewer_nick'),
                ])
                ->limit(20)
                ->get();
        }

        // Contadores sin leer por tipo
        $unreadByType = [
            'messages' => DB::table('notifications')
                ->where('user_id', $user->id)->whereNull('read_at')
                ->whereIn('type', ['new_message'])->count(),
            'social'   => DB::table('notifications')
                ->where('user_id', $user->id)->whereNull('read_at')
                ->whereIn('type', ['friend_request', 'friend_accepted', 'follow'])->count(),
            'activity' => DB::table('notifications')
                ->where('user_id', $user->id)->whereNull('read_at')
                ->whereIn('type', ['like', 'article_like'])->count(),
            'comments' => DB::table('photo_comments as pc')
                ->join('photos as ph', DB::raw('ph.photo_uuid::text'), '=', DB::raw('pc.photo_id::text'))
                ->whereRaw('ph.user_id::text = ?', [$userId])
                ->whereNull('pc.read_at')->where('pc.status', 'approved')->count(),
        ];

        $totalUnread = DB::table('notifications')
            ->whereNull('read_at')
            ->where(function($q) use ($user) {
                $q->whereRaw('user_id::text = ?', [(string)$user->id])
                  ->orWhereRaw('notifiable_id::text = ?', [(string)$user->id]);
            })->count();

        $myNick = DB::table('profiles')
            ->where('user_id', $user->id)->value('nickname') ?? '';

        return view('notifications.index', compact(
            'notifs', 'myNick', 'photoComments',
            'reviewsReceived', 'unreadByType', 'totalUnread'
        ));
    }


    public function unreadCount()
    {
        $user  = auth()->user();
        $count = DB::table('notifications')
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markRead(Request $request)
    {
        $user = auth()->user();
        DB::table('notifications')
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return response()->json(['success' => true]);
    }

        public function markOne(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return response()->json(['success' => true]);
    }

    // ── Helper estático para crear notificaciones desde otros controladores ──
    public static function create(string $userId, string $type, array $data): void
    {
        try {
            DB::table('notifications')->insert([
                'id'         => (string) Str::uuid(),
                'user_id'    => $userId,
                'type'       => $type,
                'data'       => json_encode($data),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } catch(\Exception $e) {
            // Silencioso — las notificaciones no deben romper el flujo principal
        }
    }
}




