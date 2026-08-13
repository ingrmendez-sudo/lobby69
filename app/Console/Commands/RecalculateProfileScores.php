<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecalculateProfileScores extends Command
{
    protected $signature   = 'profiles:recalculate-scores {--user= : UUID de usuario especifico}';
    protected $description = 'Recalcula el score de recomendacion de todos los perfiles';

    public function handle(): int
    {
        $this->info('Iniciando recalculo de scores...');
        $query = DB::table('profiles')
            ->join('users', DB::raw('profiles.user_id::text'), '=', DB::raw('users.id::text'))
            ->where('users.role', '!=', 'admin')
            ->select('profiles.user_id');
        if ($userId = $this->option('user')) {
            $query->where('profiles.user_id', $userId);
        }
        $profiles = $query->get();
        $count = 0;
        $bar = $this->output->createProgressBar($profiles->count());
        $bar->start();
        foreach ($profiles as $profile) {
            $this->recalculate($profile->user_id);
            $count++;
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info("Scores actualizados: {$count} perfiles.");
        return Command::SUCCESS;
    }

    private function recalculate(string $userId): void
    {
        $now = Carbon::now();

        // Factor 1: Fotos aprobadas (max 10 = 1.5 pts)
        $photos  = DB::table('photos')->whereRaw('user_id::text = ?', [$userId])->where('status', 'approved')->count();
        $fPhotos = min(1.5, ($photos / 10) * 1.5);

        // Factor 2: Visitas ultimos 30 dias (max 50 = 1.25 pts)
        $visits  = DB::table('profile_views')->whereRaw('viewed_id::text = ?', [$userId])->where('viewed_at', '>', $now->copy()->subDays(30))->count();
        $fVisits = min(1.25, ($visits / 50) * 1.25);

        // Factor 3: Actividad reciente (max 1.0 pts)
        $user      = DB::table('users')->whereRaw('id::text = ?', [$userId])->first();
        $lastSeen  = $user?->last_seen_at ? Carbon::parse($user->last_seen_at) : null;
        $fActivity = 0.0;
        if ($lastSeen) {
            $days = $lastSeen->diffInDays($now);
            if ($days <= 7)      $fActivity = 1.0;
            elseif ($days <= 30) $fActivity = 0.5;
            else                 $fActivity = 0.1;
        }

        // Factor 4: Mensajes enviados (max 10 = 0.75 pts)
        $responses  = DB::table('messages')->whereRaw('sender_id::text = ?', [$userId])->where('created_at', '>', $now->copy()->subDays(30))->count();
        $fResponses = min(0.75, ($responses / 10) * 0.75);

        // Factor 5: Completitud del perfil (max 0.5 pts)
        $profile = DB::table('profiles')->whereRaw('user_id::text = ?', [$userId])->first();
        $checks  = [
            !empty($profile?->bio),
            !empty($profile?->avatar_url),
            !empty($profile?->city),
            !empty($profile?->interests),
            !empty($profile?->looking_for),
            !empty($profile?->nickname),
            !empty($profile?->profile_type),
        ];
        $complete  = count(array_filter($checks));
        $fComplete = round(($complete / count($checks)) * 0.5, 2);

        // Factor 6: Invitaciones exitosas (max 3 = 0.5 pts)
        $invites  = DB::table('users')
            ->whereRaw('referral_code IN (SELECT code FROM referral_codes WHERE owner_user_id::text = ?)', [$userId])
            ->count();
        $fInvites = min(0.5, ($invites / 3) * 0.5);

        // Score final + boost admin
        $baseScore  = round($fPhotos + $fVisits + $fActivity + $fResponses + $fComplete + $fInvites, 2);
        $boost      = 0.0;
        if ($profile?->boost_until && Carbon::parse($profile->boost_until)->isFuture()) {
            $boost = (float)($profile->boost_amount ?? 0);
        }
        $finalScore = min(5.0, round($baseScore + $boost, 2));

        // Guardar en profiles
        DB::table('profiles')->whereRaw('user_id::text = ?', [$userId])->update([
            'recommendation_score' => $finalScore,
            'score_updated_at'     => $now,
            'updated_at'           => $now,
        ]);

        // Historial
        DB::statement(
            "INSERT INTO profile_score_history
                (id, user_id, score, factor_photos, factor_visits,
                 factor_activity, factor_responses, factor_completeness, calculated_at)
             VALUES (gen_random_uuid(), ?, ?, ?, ?, ?, ?, ?, ?)",
            [$userId, $finalScore, round($fPhotos,2), round($fVisits,2),
             round($fActivity,2), round($fResponses,2), round($fComplete,2), $now]
        );
    }
}