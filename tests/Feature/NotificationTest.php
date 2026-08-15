<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Notifications\ScoreLevelUp;
use App\Models\User;

class NotificationTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function score_level_up_notification_can_be_instantiated(): void
    {
        $notif = new ScoreLevelUp(75.0, 90.0);
        $this->assertInstanceOf(ScoreLevelUp::class, $notif);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notification_via_database_channel(): void
    {
        $user = User::first();

        if (!$user) {
            $this->markTestSkipped('No hay usuarios en la base de datos.');
        }

        $notif = new ScoreLevelUp(75.0, 90.0);
        $this->assertContains('database', $notif->via($user));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notification_to_database_has_required_keys(): void
    {
        $user = User::first();

        if (!$user) {
            $this->markTestSkipped('No hay usuarios en la base de datos.');
        }

        $notif  = new ScoreLevelUp(75.0, 90.0);
        $array  = $notif->toDatabase($user);

        $this->assertArrayHasKey('old_score', $array);
        $this->assertArrayHasKey('new_score', $array);
        $this->assertArrayHasKey('old_stars', $array);
        $this->assertArrayHasKey('new_stars', $array);
        $this->assertArrayHasKey('message',   $array);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unread_notifications_endpoint_requires_auth(): void
    {
        $response = $this->get('/notificaciones');
        $response->assertRedirect('/login');
    }
}
