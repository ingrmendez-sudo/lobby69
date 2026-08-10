<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\Photo\PhotoInteractionController;
use App\Services\MembershipAccessService;

class MessagesController extends Controller
{
    public function __construct(
        private MembershipAccessService $access
    ) {}

    public function index(Request $request)
    {
        $user   = Auth::user();
        $userId = (string) $user->id;
        $tab    = $request->get('tab', 'chats');

        // ── Tab Chats ──
        $conversations = collect();
        $unreadTotal   = 0;
        if ($tab === 'chats') {
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
                ->whereRaw('"read" = false')
                ->count();
        }

        // ── Tab Amigos ──
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
                ->orderByDesc('f.created_at')->get();

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
                ->orderByDesc('f.created_at')->get();

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
                ])->get();
        }

        // ── Tab Anuncios ──
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
                    'a.proposal', 'a.created_at', 'a.directed_to', 'a.what_looking', 'a.user_id',
                    DB::raw("COALESCE(a.expires_at, a.created_at + INTERVAL '4 days') AS expires_at"),
                    DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
                    'pr.nickname', 'pr.profile_type', 'pr.city', 'pr.verified_profile',
                    DB::raw("(SELECT ap.id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = 'approved' LIMIT 1) AS avatar_photo_id"),
                ])
                ->limit(30)->get()
                ->map(function ($a) use ($now) {
                    $a->is_expired   = Carbon::parse($a->expires_at)->lt($now);
                    $a->directed_to  = $a->directed_to  ? json_decode($a->directed_to,  true) : [];
                    $a->what_looking = $a->what_looking ? json_decode($a->what_looking, true) : [];
                    return $a;
                });

            $myAnnouncements = DB::table('announcements')
                ->whereRaw('user_id::text = ?', [$userId])
                ->orderByDesc('created_at')->get()
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

        // ── Datos de membresía para la vista ──
        $userTier      = $this->access->tier($user);
        $canChat       = $this->access->can($user, 'chat_private');
        $dailyLimit    = $this->access->limit($user, 'daily_messages');
        $sentToday     = $canChat ? DB::table('messages')
            ->whereRaw('sender_id::text = ?', [$userId])
            ->whereDate('created_at', today())
            ->count() : 0;
        $messagesLeft  = $dailyLimit !== null ? max(0, $dailyLimit - $sentToday) : null;

        $userId = (string) Auth::id();
        return view('messages.index', compact(
            'userId', 'tab', 'conversations', 'unreadTotal',
            'friendsPending', 'friendsSent', 'friendsAccepted',
            'announcements', 'myAnnouncements',
            'userTier', 'canChat', 'dailyLimit', 'messagesLeft'
        ));
    }


    // ── Abrir conversación (AJAX) ──
    public function conversation(Request $request, string $partnerId)
    {
        $userId = (string) Auth::id();

        DB::table('messages')
            ->whereRaw('sender_id::text = ?',   [$partnerId])
            ->whereRaw('receiver_id::text = ?',  [$userId])
            ->whereRaw('"read" = false')
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

        $user   = Auth::user();
        $userId = (string) $user->id;

        // ── Control de acceso por membresía ──
        if (!$this->access->can($user, 'chat_private')) {
            return response()->json([
                'error'       => 'membership_required',
                'message'     => 'Necesitas al menos membresía Explorer para enviar mensajes.',
                'upgrade_url' => '/membresias',
                'tier'        => $this->access->tier($user),
            ], 403);
        }

        // ── Límite diario de mensajes ──
        $dailyLimit = $this->access->limit($user, 'daily_messages');
        if ($dailyLimit !== null) {
            $sentToday = DB::table('messages')
                ->whereRaw('sender_id::text = ?', [$userId])
                ->whereDate('created_at', today())
                ->count();
            if ($sentToday >= $dailyLimit) {
                return response()->json([
                    'error'       => 'daily_limit_reached',
                    'message'     => 'Alcanzaste tu límite de ' . $dailyLimit . ' mensajes por día.',
                    'limit'       => $dailyLimit,
                    'sent_today'  => $sentToday,
                    'upgrade_url' => '/membresias',
                    'tier'        => $this->access->tier($user),
                ], 429);
            }
        }
            $msgId  = (string) \Illuminate\Support\Str::uuid();
            $now    = now();

            // 1. Guardar mensaje
            DB::table('messages')->insert([
                'id'          => $msgId,
                'sender_id'   => $userId,
                'receiver_id' => $request->receiver_id,
                'body'        => $request->body,
                'read'        => false,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            // 2. Notificación persistente
            DB::table('notifications')->insert([
                'id'         => (string) \Illuminate\Support\Str::uuid(),
                'user_id'    => $request->receiver_id,
                'type'       => 'new_message',
                'data'       => json_encode([
                    'sender_id' => $userId,
                    'preview'   => substr($request->body, 0, 80),
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 3. Obtener datos del sender para el broadcast
            $senderProfile = DB::table('profiles')
                ->whereRaw('user_id::text = ?', [$userId])
                ->select('nickname')
                ->first();

            $avatarPhotoId = DB::table('photos')
                ->whereRaw('user_id::text = ?', [$userId])
                ->whereRaw('is_profile_photo = true')
                ->where('status', 'approved')
                ->value('id');

            // 4. Broadcast en tiempo real al receptor
            broadcast(new \App\Events\MessageSent(
                senderId:      $userId,
                receiverId:    $request->receiver_id,
                messageId:     $msgId,
                body:          $request->body,
                createdAt:     $now->toISOString(),
                senderNick:    $senderProfile?->nickname,
                avatarPhotoId: $avatarPhotoId ? (string) $avatarPhotoId : null,
            ));

            return response()->json(['ok' => true, 'message_id' => $msgId]);
        }


    // ── Amistad: enviar solicitud ──
    public function sendFriendRequest(Request $request)
    {
        $request->validate(['target_id' => 'required|uuid']);
        $userId   = (string) Auth::id();
        $targetId = (string) $request->target_id;

        if ($userId === $targetId) {
            return response()->json(['error' => 'No puedes agregarte a ti mismo'], 422);
        }

        $exists = DB::table('friendships')
            ->where(function($q) use ($userId, $targetId) {
                $q->whereRaw('sender_id::text = ?',   [$userId])
                  ->whereRaw('receiver_id::text = ?', [$targetId]);
            })
            ->orWhere(function($q) use ($userId, $targetId) {
                $q->whereRaw('sender_id::text = ?',   [$targetId])
                  ->whereRaw('receiver_id::text = ?', [$userId]);
            })
            ->first();

        if ($exists) {
            return response()->json([
                'error'  => 'Ya existe una solicitud o amistad',
                'status' => $exists->status,
            ], 409);
        }

        $id = (string) \Illuminate\Support\Str::uuid();
        DB::table('friendships')->insert([
            'id'          => $id,
            'sender_id'   => $userId,
            'receiver_id' => $targetId,
            'status'      => 'pending',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);


        // Notificar al receptor de la solicitud de amistad
        $senderNick = DB::table('profiles')
            ->whereRaw('user_id::text = ?', [$userId])
            ->value('nickname');
        \App\Http\Controllers\NotificationController::create($targetId, 'friend_request', [
            'from_nick' => $senderNick ?? 'Alguien',
            'sender_id' => $userId,
        ]);
        return response()->json(['ok' => true, 'friendship_id' => $id]);
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

            // Notificar al que envió la solicitud
            $acceptedFriendship = DB::table('friendships')->whereRaw('id::text = ?', [$friendshipId])->first();
            if ($acceptedFriendship) {
                $accepterNick = DB::table('profiles')->whereRaw('user_id::text = ?', [$userId])->value('nickname');
                \App\Http\Controllers\NotificationController::create((string)$acceptedFriendship->sender_id, 'friend_accepted', [
                    'from_nick' => $accepterNick ?? 'Alguien',
                ]);
            }
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









