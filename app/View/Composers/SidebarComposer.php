<?php
namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SidebarComposer
{
    public function compose(View $view): void
    {
        if (!Auth::check()) return;

        $userId = (string) Auth::id();

        $whoViewedMe      = collect();
        $whoViewedMeCount = 0;
        $iViewed          = collect();
        $iViewedCount     = 0;

        try {
            $viewerIds = DB::table('profile_views')
                ->where('viewed_id', $userId)
                ->orderByDesc('viewed_at')
                ->limit(5)
                ->pluck('viewer_id')
                ->map(fn($id) => (string)$id)
                ->toArray();

            $whoViewedMeCount = DB::table('profile_views')
                ->where('viewed_id', $userId)
                ->count();

            if (!empty($viewerIds)) {
                $whoViewedMe = User::with('profile')
                    ->whereIn('id', $viewerIds)
                    ->where('role', '!=', 'admin')
                    ->get();
            }
        } catch (\Throwable $e) {}

        try {
            $viewedIds = DB::table('profile_views')
                ->where('viewer_id', $userId)
                ->orderByDesc('viewed_at')
                ->limit(5)
                ->pluck('viewed_id')
                ->map(fn($id) => (string)$id)
                ->toArray();

            $iViewedCount = DB::table('profile_views')
                ->where('viewer_id', $userId)
                ->count();

            if (!empty($viewedIds)) {
                $iViewed = User::with('profile')
                    ->whereIn('id', $viewedIds)
                    ->where('role', '!=', 'admin')
                    ->get();
            }
        } catch (\Throwable $e) {}

        $view->with(compact(
            'whoViewedMe', 'whoViewedMeCount',
            'iViewed', 'iViewedCount'
        ));
    }
}
