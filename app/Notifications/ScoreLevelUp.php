<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ScoreLevelUp extends Notification
{
    protected float $oldScore;
    protected float $newScore;

    public function __construct(float $oldScore, float $newScore)
    {
        $this->oldScore = $oldScore;
        $this->newScore = $newScore;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $oldStars = $this->starsLabel($this->oldScore);
        $newStars = $this->starsLabel($this->newScore);

        return [
            'type'      => 'score_level_up',
            'old_score' => $this->oldScore,
            'new_score' => $this->newScore,
            'old_stars' => $oldStars,
            'new_stars' => $newStars,
            'message'   => "Tu perfil subio de {$oldStars} a {$newStars}!",
        ];
    }

    // Sobreescribir buildPayload para agregar user_id al registro
    public function databaseType(object $notifiable): string
    {
        return 'score_level_up';
    }

    private function starsLabel(float $score): string
    {
        if ($score >= 4.5) return '5 estrellas';
        if ($score >= 3.5) return '4 estrellas';
        if ($score >= 2.5) return '3 estrellas';
        if ($score >= 1.5) return '2 estrellas';
        if ($score >= 0.5) return '1 estrella';
        return 'sin estrellas';
    }
}
