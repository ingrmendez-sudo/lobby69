<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminPendingComposer
{
    public function compose(View $view): void
    {
        try {
            $pendingPhotos = Schema::hasTable('photos') && Schema::hasColumn('photos', 'status')
                ? DB::table('photos')->where('status', 'pending')->count()
                : 0;
        } catch (\Exception $e) { $pendingPhotos = 0; }

        try {
            $pendingVideos = Schema::hasTable('videos') && Schema::hasColumn('videos', 'status')
                ? DB::table('videos')->where('status', 'pending')->count()
                : 0;
        } catch (\Exception $e) { $pendingVideos = 0; }

        try {
            $pendingVerifications = Schema::hasTable('verifications') && Schema::hasColumn('verifications', 'status')
                ? DB::table('verifications')->where('status', 'pending')->count()
                : 0;
        } catch (\Exception $e) { $pendingVerifications = 0; }

        try {
            $pendingInvitations = Schema::hasTable('invitation_requests') && Schema::hasColumn('invitation_requests', 'status')
                ? DB::table('invitation_requests')->where('status', 'pending')->count()
                : 0;
        } catch (\Exception $e) { $pendingInvitations = 0; }

        try {
            $pendingArticleComments = Schema::hasTable('article_comments') && Schema::hasColumn('article_comments', 'status')
                ? DB::table('article_comments')->where('status', 'pending')->count()
                : 0;
        } catch (\Exception $e) { $pendingArticleComments = 0; }

        $view->with([
            'pendingPhotos'         => $pendingPhotos,
            'pendingVideos'         => $pendingVideos,
            'pendingVerifications'  => $pendingVerifications,
            'pendingInvitations'    => $pendingInvitations,
            'pendingArticleComments'=> $pendingArticleComments,
        ]);
    }
}