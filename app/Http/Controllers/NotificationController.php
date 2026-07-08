<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $notifs = DB::table('notifications')
            ->whereRaw('user_id::text = ?', [(string) $user->id])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function($n) {
                $n->data = json_decode($n->data, true);
                return $n;
            });

        // Marcar todas como leídas
        DB::table('notifications')
            ->whereRaw('user_id::text = ?', [(string) $user->id])
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return view('notifications.index', compact('notifs'));
    }

    public function unreadCount()
    {
        $user  = auth()->user();
        $count = DB::table('notifications')
            ->whereRaw('user_id::text = ?', [(string) $user->id])
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markRead(Request $request)
    {
        $user = auth()->user();
        DB::table('notifications')
            ->whereRaw('user_id::text = ?', [(string) $user->id])
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
