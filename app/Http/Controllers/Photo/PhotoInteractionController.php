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
            'status'   => 'approved',
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
    /**
     * El dueño de la foto responde un comentario.
     */
    public function replyComment(Request $request, $photoId, $commentId)
    {
        $request->validate(['body' => 'required|string|max:500']);

        if (self::contieneContacto($request->body)) {
            return response()->json(
                ['error' => 'La respuesta no puede contener datos de contacto.'], 422
            );
        }

        $photo = DB::table('photos')
            ->where(function($q) use ($photoId) {
                $q->whereRaw('photo_uuid::text = ?', [$photoId])
                  ->orWhereRaw('id::text = ?', [$photoId]);
            })
            ->select(['photo_uuid', 'user_id'])
            ->first();

        if (!$photo) {
            return response()->json(['error' => 'Foto no encontrada.'], 404);
        }

        if ((string) auth()->id() !== (string) $photo->user_id) {
            return response()->json(['error' => 'Solo el dueño de la foto puede responder.'], 403);
        }

        $reply = \App\Models\PhotoComment::create([
            'id'        => (string) \Illuminate\Support\Str::uuid(),
            'photo_id'  => (string) $photo->photo_uuid,
            'user_id'   => (string) auth()->id(),
            'parent_id' => $commentId,
            'body'      => strip_tags($request->body),
            'status'    => 'approved',
        ]);

        $user = auth()->user();

        return response()->json([
            'success' => true,
            'reply'   => [
                'id'       => $reply->id,
                'body'     => $reply->body,
                'nickname' => $user->profile->nickname ?? $user->name,
                'avatar_photo_id' => null,
            ]
        ]);
    }
}
