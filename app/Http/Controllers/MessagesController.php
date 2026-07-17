<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MessagesController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $userId = (string) $user->id;
        $tab    = $request->get('tab', 'inbox');

        // ── Tab 1: Conversaciones ──
        $conversations = collect();
        $unreadTotal   = 0;
        if ($tab === 'inbox') {
            $conversations = DB::select("
                SELECT
                    p.id          AS partner_id,
                    prof.nickname,
                    COALESCE(prof.display_name, p.username) AS display_name,
                    prof.profile_type,
                    prof.verified_profile,
                    m.body        AS last_message,
                    m.created_at  AS last_at,
                    m.sender_id,
                    COUNT(m2.id) FILTER (WHERE m2.read = false AND m2.receiver_id::text = ?) AS unread_count,
                    (SELECT ap.id FROM photos ap
                     WHERE ap.user_id::text = p.id::text
                       AND ap.is_profile_photo = true
                       AND ap.status = 'approved'
                     LIMIT 1) AS avatar_photo_id
                FROM (
                    SELECT DISTINCT ON (
                        LEAST(sender_id::text, receiver_id::text) ||
                        GREATEST(sender_id::text, receiver_id::text)
                    )
                        id, sender_id, receiver_id, body, created_at, read
                    FROM messages
                    WHERE sender_id::text = ? OR receiver_id::text = ?
                    ORDER BY
                        LEAST(sender_id::text, receiver_id::text) ||
                        GREATEST(sender_id::text, receiver_id::text),
                        created_at DESC
                ) m
                JOIN users p ON p.id::text = CASE
                    WHEN m.sender_id::text = ? THEN m.receiver_id::text
                    ELSE m.sender_id::text
                END
                LEFT JOIN profiles prof ON prof.user_id::text = p.id::text
                LEFT JOIN messages m2
                    ON m2.sender_id::text = p.id::text
                   AND m2.receiver_id::text = ?
                GROUP BY
                    p.id, p.username,
                    prof.nickname, prof.display_name, prof.profile_type, prof.verified_profile,
                    m.body, m.created_at, m.sender_id
                ORDER BY m.created_at DESC
            ", [$userId, $userId, $userId, $userId, $userId]);

            $unreadTotal = DB::table('messages')
                ->whereRaw('receiver_id::text = ?', [$userId])
                ->where('read', false)
                ->count();
        }

        // ── Tab 2: Comentarios recibidos ──
        $photoComments = collect();
        $videoComments = collect();
        if ($tab === 'comments') {
            $photoComments = DB::table('photo_comments as pc')
                ->join('photos as ph', DB::raw('ph.photo_uuid::text'), '=', DB::raw('pc.photo_id::text'))
                ->join('users as u',   DB::raw('u.id::text'),          '=', DB::raw('pc.user_id::text'))
                ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
                ->whereRaw('ph.user_id::text = ?', [$userId])
                ->where('pc.status', 'approved')
                ->orderByDesc('pc.created_at')
                ->select([
                    'pc.id', 'pc.body', 'pc.created_at',
                    'ph.photo_uuid', 'ph.caption',
                    DB::raw('COALESCE(pr.display_name, u.username) AS commenter_name'),
                    DB::raw('pr.nickname AS commenter_nick'),
                    DB::raw('pr.profile_type AS commenter_type'),
                    DB::raw("(SELECT ap.id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = 'approved' LIMIT 1) AS commenter_avatar_id"),
                ])
                ->limit(50)
                ->get();

            try {
                $videoComments = DB::table('video_comments as vc')
                    ->join('videos as v',    DB::raw('v.id::text'),  '=', DB::raw('vc.video_id::text'))
                    ->join('users as u',     DB::raw('u.id::text'),  '=', DB::raw('vc.user_id::text'))
                    ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
                    ->whereRaw('v.user_id::text = ?', [$userId])
                    ->where('vc.status', 'approved')
                    ->orderByDesc('vc.created_at')
                    ->select([
                        'vc.id', 'vc.body', 'vc.created_at',
                        'v.id as video_id', 'v.title',
                        DB::raw('COALESCE(pr.display_name, u.username) AS commenter_name'),
                        DB::raw('pr.nickname AS commenter_nick'),
                    ])
                    ->limit(50)
                    ->get();
            } catch (\Throwable $e) {}
        }

        // ── Tab 3: Amistades ──
        $friendsPending  = collect();
        $friendsSent     = collect();
        $friendsAccepted = collect();
        if ($tab === 'friends') {
            $friendsPending = DB::table('friendships as f')
                ->join('users as u',  DB::raw('u.id::text'), '=', DB::raw('f.sender_id::text'))
                ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
                ->whereRaw('f.receiver_id::text = ?', [$userId])
                ->where('f.status', 'pending')
                ->select([
                    'f.id AS friendship_id',
                    'u.id AS user_id', 'u.username',
                    DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
                    'pr.nickname', 'pr.profile_type', 'pr.verified_profile', 'pr.city',
                    DB::raw("(SELECT ap.id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = 'approved' LIMIT 1) AS avatar_photo_id"),
                    'f.created_at',
                ])
                ->orderByDesc('f.created_at')
                ->get();

            $friendsSent = DB::table('friendships as f')
                ->join('users as u',  DB::raw('u.id::text'), '=', DB::raw('f.receiver_id::text'))
                ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
                ->whereRaw('f.sender_id::text = ?', [$userId])
                ->where('f.status', 'pending')
                ->select([
                    'f.id AS friendship_id',
                    'u.id AS user_id',
                    DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
                    'pr.nickname', 'pr.profile_type', 'pr.city',
                    DB::raw("(SELECT ap.id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = 'approved' LIMIT 1) AS avatar_photo_id"),
                    'f.created_at',
                ])
                ->orderByDesc('f.created_at')
                ->get();

            $friendsAccepted = DB::table('friendships as f')
                ->join('users as u',
                    DB::raw('u.id::text'),
                    '=',
                    DB::raw("CASE WHEN f.sender_id::text = '{$userId}' THEN f.receiver_id::text ELSE f.sender_id::text END")
                )
                ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
                ->whereRaw('(f.sender_id::text = ? OR f.receiver_id::text = ?)', [$userId, $userId])
                ->where('f.status', 'accepted')
                ->whereRaw('u.id::text != ?', [$userId])
                ->select([
                    'f.id AS friendship_id',
                    'u.id AS user_id',
                    DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
                    'pr.nickname', 'pr.profile_type', 'pr.verified_profile', 'pr.city',
                    DB::raw("(SELECT ap.id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = 'approved' LIMIT 1) AS avatar_photo_id"),
                ])
                ->get();
        }

        // ── Tab 4: Recomendaciones ──
        $reviewsReceived = collect();
        $reviewsGiven    = collect();
        $canReview       = collect();
        if ($tab === 'reviews') {
            $reviewsReceived = DB::table('profile_reviews as r')
                ->join('users as u',  DB::raw('u.id::text'), '=', DB::raw('r.reviewer_id::text'))
                ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
                ->whereRaw('r.reviewed_id::text = ?', [$userId])
                ->select([
                    'r.id', 'r.type', 'r.body', 'r.created_at',
                    DB::raw('COALESCE(pr.display_name, u.username) AS reviewer_name'),
                    'pr.nickname AS reviewer_nick',
                    DB::raw("(SELECT ap.id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = 'approved' LIMIT 1) AS avatar_photo_id"),
                ])
                ->orderByDesc('r.created_at')
                ->get();

            $reviewsGiven = DB::table('profile_reviews as r')
                ->join('users as u',  DB::raw('u.id::text'), '=', DB::raw('r.reviewed_id::text'))
                ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
                ->whereRaw('r.reviewer_id::text = ?', [$userId])
                ->select([
                    'r.id', 'r.type', 'r.body', 'r.created_at',
                    DB::raw('COALESCE(pr.display_name, u.username) AS reviewed_name'),
                    'pr.nickname AS reviewed_nick',
                    DB::raw("(SELECT ap.id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = 'approved' LIMIT 1) AS avatar_photo_id"),
                ])
                ->orderByDesc('r.created_at')
                ->get();

            $reviewedIds = DB::table('profile_reviews')
                ->whereRaw('reviewer_id::text = ?', [$userId])
                ->pluck('reviewed_id')
                ->map(fn($id) => (string) $id)
                ->toArray();

            $canReview = DB::table('friendships as f')
                ->join('users as u',
                    DB::raw('u.id::text'),
                    '=',
                    DB::raw("CASE WHEN f.sender_id::text = '{$userId}' THEN f.receiver_id::text ELSE f.sender_id::text END")
                )
                ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
                ->whereRaw('(f.sender_id::text = ? OR f.receiver_id::text = ?)', [$userId, $userId])
                ->where('f.status', 'accepted')
                ->whereRaw('u.id::text != ?', [$userId])
                ->whereNotIn(DB::raw('u.id::text'), count($reviewedIds) ? $reviewedIds : ['__none__'])
                ->select([
                    'u.id AS user_id',
                    DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
                    'pr.nickname',
                    DB::raw("(SELECT ap.id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = 'approved' LIMIT 1) AS avatar_photo_id"),
                ])
                ->get();
        }

        // ── Tab 5: Anuncios ──
        $announcements   = collect();
        $myAnnouncements = collect();
        if ($tab === 'announcements') {
            $now = Carbon::now();

            $announcements = DB::table('announcements as a')
                ->join('users as u',  DB::raw('u.id::text'), '=', DB::raw('a.user_id::text'))
                ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
                ->whereRaw('a.user_id::text != ?', [$userId])
                ->where('a.status', 'active')
                ->orderByDesc('a.created_at')
                ->select([
                    'a.id', 'a.title', 'a.looking_for', 'a.event_date',
                    'a.proposal', 'a.created_at', 'a.directed_to', 'a.what_looking',
                    'a.user_id',
                    DB::raw("COALESCE(a.expires_at, a.created_at + INTERVAL '4 days') AS expires_at"),
                    DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
                    'pr.nickname', 'pr.profile_type', 'pr.city', 'pr.verified_profile',
                    DB::raw("(SELECT ap.id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = 'approved' LIMIT 1) AS avatar_photo_id"),
                ])
                ->limit(30)
                ->get()
                ->map(function ($a) use ($now) {
                    $a->is_expired = Carbon::parse($a->expires_at)->lt($now);
                    $a->directed_to  = $a->directed_to  ? json_decode($a->directed_to,  true) : [];
                    $a->what_looking = $a->what_looking ? json_decode($a->what_looking, true) : [];
                    return $a;
                });

            $myAnnouncements = DB::table('announcements')
                ->whereRaw('user_id::text = ?', [$userId])
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($a) use ($now) {
                    $expires = $a->expires_at
                        ? Carbon::parse($a->expires_at)
                        : Carbon::parse($a->created_at)->addDays(4);
                    $a->is_expired   = $expires->lt($now);
                    $a->directed_to  = $a->directed_to  ? json_decode($a->directed_to,  true) : [];
                    $a->what_looking = $a->what_looking ? json_decode($a->what_looking, true) : [];
                    return $a;
                });
        }

        return view('messages.index', compact(
            'tab', 'conversations', 'unreadTotal',
            'photoComments', 'videoComments',
            'friendsPending', 'friendsSent', 'friendsAccepted',
            'reviewsReceived', 'reviewsGiven', 'canReview',
            'announcements', 'myAnnouncements'
        ));
    }

    // ── Abrir conversación (AJAX) ──
    public function conversation(Request $request, string $partnerId)
    {
        $userId = (string) Auth::id();

        DB::table('messages')
            ->whereRaw('sender_id::text = ?',   [$partnerId])
            ->whereRaw('receiver_id::text = ?',  [$userId])
            ->where('read', false)
            ->update(['read' => true, 'read_at' => now()]);

        $messages = DB::table('messages as m')
            ->join('users as u',  DB::raw('u.id::text'), '=', DB::raw('m.sender_id::text'))
            ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
            ->whereRaw(
                '(m.sender_id::text = ? AND m.receiver_id::text = ?) OR (m.sender_id::text = ? AND m.receiver_id::text = ?)',
                [$userId, $partnerId, $partnerId, $userId]
            )
            ->orderBy('m.created_at')
            ->select([
                'm.id', 'm.body', 'm.created_at', 'm.sender_id', 'm.read',
                DB::raw('COALESCE(pr.display_name, u.username) AS sender_name'),
                'pr.nickname AS sender_nick',
                DB::raw("(SELECT ap.id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = 'approved' LIMIT 1) AS avatar_photo_id"),
            ])
            ->get();

        $partner = DB::table('users as u')
            ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
            ->whereRaw('u.id::text = ?', [$partnerId])
            ->select([
                'u.id',
                DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
                'pr.nickname', 'pr.profile_type', 'pr.verified_profile',
                DB::raw("(SELECT ap.id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = 'approved' LIMIT 1) AS avatar_photo_id"),
            ])
            ->first();

        return response()->json(['messages' => $messages, 'partner' => $partner]);
    }

    // ── Enviar mensaje ──
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|uuid',
            'body'        => 'required|string|max:1000',
        ]);
        $userId = (string) Auth::id();

        DB::table('messages')->insert([
            'id'          => Str::uuid(),
            'sender_id'   => $userId,
            'receiver_id' => $request->receiver_id,
            'body'        => $request->body,
            'read'        => false,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        DB::table('notifications')->insert([
            'id'         => Str::uuid(),
            'user_id'    => $request->receiver_id,
            'type'       => 'new_message',
            'data'       => json_encode(['sender_id' => $userId, 'preview' => substr($request->body, 0, 80)]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── Amistad: aceptar / rechazar ──
    public function friendAction(Request $request, string $friendshipId)
    {
        $request->validate(['action' => 'required|in:accept,reject']);
        $userId = (string) Auth::id();

        $friendship = DB::table('friendships')
            ->whereRaw('id::text = ?', [$friendshipId])
            ->whereRaw('receiver_id::text = ?', [$userId])
            ->first();

        if (! $friendship) {
            return response()->json(['error' => 'No encontrado'], 404);
        }

        if ($request->action === 'accept') {
            DB::table('friendships')
                ->whereRaw('id::text = ?', [$friendshipId])
                ->update(['status' => 'accepted', 'updated_at' => now()]);
        } else {
            DB::table('friendships')
                ->whereRaw('id::text = ?', [$friendshipId])
                ->delete();
        }

        return response()->json(['ok' => true]);
    }

    // ── Dejar recomendación ──
    public function review(Request $request)
    {
        $request->validate([
            'reviewed_id' => 'required|uuid',
            'type'        => 'required|in:positive,negative',
            'body'        => 'nullable|string|max:500',
        ]);
        $userId = (string) Auth::id();

        $isFriend = DB::table('friendships')
            ->whereRaw(
                '(sender_id::text = ? AND receiver_id::text = ?) OR (sender_id::text = ? AND receiver_id::text = ?)',
                [$userId, $request->reviewed_id, $request->reviewed_id, $userId]
            )
            ->where('status', 'accepted')
            ->exists();

        if (! $isFriend) {
            return response()->json(['error' => 'Solo puedes recomendar amigos'], 403);
        }

        $exists = DB::table('profile_reviews')
            ->whereRaw('reviewer_id::text = ?', [$userId])
            ->whereRaw('reviewed_id::text = ?',  [$request->reviewed_id])
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Ya dejaste una recomendación'], 409);
        }

        DB::table('profile_reviews')->insert([
            'id'          => Str::uuid(),
            'reviewer_id' => $userId,
            'reviewed_id' => $request->reviewed_id,
            'type'        => $request->type,
            'body'        => $request->body,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── Crear anuncio ──
    public function storeAnnouncement(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:120',
            'directed_to'  => 'nullable|array',
            'directed_to.*'=> 'in:singles,parejas,unicornio',
            'what_looking' => 'nullable|array',
            'what_looking.*'=> 'in:intercambios,cuckold,fiesta,trio_mhm,trio_hmh,gangbang,cita_soft,reunion_swinger,encuentro_casual,voyeurismo,jugar,conocernos',
            'event_date'   => 'nullable|date|after:today|before:' . now()->addDays(4)->toDateString(),
            'proposal'     => 'nullable|string|max:600',
        ]);

        $expiresAt = Carbon::now()->addDays(4);

        DB::table('announcements')->insert([
            'id'           => Str::uuid(),
            'user_id'      => (string) Auth::id(),
            'title'        => $request->title,
            'looking_for'  => implode(', ', $request->input('what_looking', [])),
            'directed_to'  => json_encode($request->input('directed_to',  [])),
            'what_looking' => json_encode($request->input('what_looking', [])),
            'event_date'   => $request->event_date,
            'proposal'     => $request->proposal,
            'status'       => 'active',
            'expires_at'   => $expiresAt,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── Cerrar anuncio ──
    public function closeAnnouncement(string $id)
    {
        DB::table('announcements')
            ->whereRaw('id::text = ?', [$id])
            ->whereRaw('user_id::text = ?', [(string) Auth::id()])
            ->update(['status' => 'closed', 'updated_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
