<?php

namespace App\Console\Commands;

use App\Events\VideoSignal;
use App\Models\VideoSession;
use Illuminate\Console\Command;

class CleanOrphanVideoSessions extends Command
{
    protected $signature   = 'video:clean-orphans';
    protected $description = 'Cierra sesiones de video que superaron su duracion maxima';

    public function handle(): void
    {
        $orphans = VideoSession::whereNull('ended_at')
            ->whereRaw("started_at < NOW() - (max_duration_minutes * INTERVAL '1 minute')")
            ->get();

        if ($orphans->isEmpty()) {
            $this->info('Sin sesiones huerfanas.');
            return;
        }

        foreach ($orphans as $session) {
            $actualMinutes = (int) ceil(
                $session->started_at->diffInSeconds(now()) / 60
            );

            $session->update([
                'ended_at'       => now(),
                'actual_minutes' => $actualMinutes,
                'ended_by'       => 'timeout',
            ]);

            foreach ([$session->initiator_id, $session->receiver_id] as $uid) {
                try {
                    broadcast(new VideoSignal(
                        toUserId:   (int) $uid,
                        fromUserId: 0,
                        type:       'call-ended',
                        payload:    ['reason' => 'timeout'],
                    ));
                } catch (\Exception $e) {
                    $this->warn("No se pudo notificar al usuario {$uid}");
                }
            }

            $this->info("Sesion ID {$session->id} cerrada por timeout ({$actualMinutes} min).");
        }
    }
}
