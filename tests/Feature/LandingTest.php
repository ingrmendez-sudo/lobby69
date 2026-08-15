<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function landing_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('LOBBY69');
        $response->assertSee('Solicitar Acceso');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function landing_page_contains_invitation_link(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('invitacion');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invitation_form_page_loads(): void
    {
        $response = $this->get('/invitacion');
        $response->assertStatus(200);
        $response->assertSee('invitation_code');
    }
}
