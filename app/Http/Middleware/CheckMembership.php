<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MembershipAccessService;

/**
 * Uso en rutas:
 *   ->middleware('membership:chat_private')
 *   ->middleware('membership:video_chat')
 *   ->middleware('membership:publish_videos')
 */
class CheckMembership
{
    public function __construct(
        private MembershipAccessService $access
    ) {}

    public function handle(Request $request, Closure $next, string $feature): mixed
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        if (!$this->access->can($user, $feature)) {
            if ($request->expectsJson()) {
                return $this->access->denyResponse($feature);
            }
            return redirect()->route('membresias.index')
                ->with('upgrade_required', $feature)
                ->with('required_tier', $this->access->requiredTierFor($feature));
        }

        return $next($request);
    }
}