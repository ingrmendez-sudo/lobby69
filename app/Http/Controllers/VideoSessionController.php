<?php

namespace App\Http\Controllers;

use App\Events\VideoSignal;
use App\Models\User;
use App\Models\VideoSession;
use App\Services\VideoQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoSessionController extends Controller
{
    public function __construct(private VideoQuotaService $quota) {}

    public function initiate(Request $request)
    {
        $request->validate(['to_user_id' => 'required|string|exists:users,id']);

        $caller = auth()->user();
        $target = User::findOrFail($request->to_user_id);

        if ($caller->id === $target->id) {
            return response()->json(['error' => 'invalid_target'], 422);
        }

        $quota = $this->quota->canInitiateCall($caller);

        if (!$quota['allowed']) {
            return response()->json([
                'error'        => $quota['reason'],
                'message'      => $quota['message'],
                'wait_seconds' => $quota['wait_seconds'] ?? null,
            ], 403);
        }

        $token = Str::random(64);

        VideoSession::create([
            'initiator_id'         => $caller->id,
            'receiver_id'          => $target->id,
            'session_token'        => $token,
            'max_duration_minutes' => $quota['max_duration_minutes'],
            'started_at'           => now(),
        ]);

        broadcast(new VideoSignal(
            toUserId:           (int) $target->id,
            fromUserId:         (int) $caller->id,
            type:               'call-request',
            payload:            [
                'caller_name'   => $caller->username ?? $caller->name,
                'caller_avatar' => $caller->avatar_url ?? null,
            ],
            token:              $token,
            maxDurationSeconds: $quota['max_duration_seconds'],
        ));

        return response()->json([
            'token'                => $token,
            'max_duration_seconds' => $quota['max_duration_seconds'],
            'max_duration_minutes' => $quota['max_duration_minutes'],
        ]);
    }

    public function respond(Request $request)
    {
        $request->validate([
            'token'  => 'required|string',
            'action' => 'required|in:accept,reject',
        ]);

        $session = VideoSession::where('session_token', $request->token)
            ->whereNull('ended_at')
            ->firstOrFail();

        $responder = auth()->user();

        if ($session->receiver_id !== $responder->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        if ($request->action === 'reject') {
            $this->quota->closeSession($request->token, 'rejected');

            broadcast(new VideoSignal(
                toUserId:   (int) $session->initiator_id,
                fromUserId: (int) $responder->id,
                type:       'call-rejected',
                payload:    [],
            ));

            return response()->json(['status' => 'rejected']);
        }

        broadcast(new VideoSignal(
            toUserId:   (int) $session->initiator_id,
            fromUserId: (int) $responder->id,
            type:       'call-accepted',
            payload:    [],
            token:      $request->token,
        ));

        return response()->json(['status' => 'accepted']);
    }

    public function signal(Request $request)
    {
        $request->validate([
            'token'      => 'required|string',
            'to_user_id' => 'required|string',
            'type'       => 'required|in:offer,answer,ice-candidate',
            'payload'    => 'required',
        ]);

        $session = VideoSession::where('session_token', $request->token)
            ->whereNull('ended_at')
            ->firstOrFail();

        $sender = auth()->user();

        if (!in_array($sender->id, [$session->initiator_id, $session->receiver_id])) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        broadcast(new VideoSignal(
            toUserId:   (int) $request->to_user_id,
            fromUserId: (int) $sender->id,
            type:       $request->type,
            payload:    $request->payload,
            token:      $request->token,
        ));

        return response()->json(['status' => 'relayed']);
    }

    public function end(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $user    = auth()->user();
        $session = VideoSession::where('session_token', $request->token)
            ->whereNull('ended_at')
            ->firstOrFail();

        if (!in_array($user->id, [$session->initiator_id, $session->receiver_id])) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        $endedBy = ($user->id === $session->initiator_id) ? 'initiator' : 'receiver';
        $this->quota->closeSession($request->token, $endedBy);

        $otherId = ($user->id === $session->initiator_id)
            ? $session->receiver_id
            : $session->initiator_id;

        broadcast(new VideoSignal(
            toUserId:   (int) $otherId,
            fromUserId: (int) $user->id,
            type:       'call-ended',
            payload:    [],
        ));

        return response()->json(['status' => 'ended']);
    }
}
