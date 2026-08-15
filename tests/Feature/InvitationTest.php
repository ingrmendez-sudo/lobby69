<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class InvitationTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function invitation_request_stores_as_pending(): void
    {
        $payload = [
            'nombre_completo'     => 'Test User Feature',
            'email'               => 'testfeature_' . time() . '@example.com',
            'edad'                => 25,
            'pais'                => 'Mexico',
            'estado'              => 'Ciudad de Mexico',
            'municipio'           => 'Cuauhtemoc',
            'tipo_perfil'         => 'single',
            'motivo'              => 'Quiero unirme al club para conocer personas afines en un ambiente discreto.',
            'invitation_code'     => '',
            'terminos_aceptados'  => '1',
            'privacidad_aceptada' => '1',
        ];

        $response = $this->post('/invitacion', $payload);

        $response->assertRedirect('/invitacion/gracias');

        $this->assertDatabaseHas('invitation_requests', [
            'email'  => $payload['email'],
            'status' => 'pending',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invitation_request_fails_without_required_fields(): void
    {
        $response = $this->post('/invitacion', []);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'nombre_completo',
            'email',
            'edad',
            'pais',
            'estado',
            'municipio',
            'tipo_perfil',
            'motivo',
            'terminos_aceptados',
            'privacidad_aceptada',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invitation_request_fails_if_underage(): void
    {
        $payload = [
            'nombre_completo'     => 'Test Menor',
            'email'               => 'menor_' . time() . '@example.com',
            'edad'                => 16,
            'pais'                => 'Mexico',
            'estado'              => 'Ciudad de Mexico',
            'municipio'           => 'Cuauhtemoc',
            'tipo_perfil'         => 'single',
            'motivo'              => 'Quiero unirme al club para conocer personas afines en un ambiente discreto.',
            'invitation_code'     => '',
            'terminos_aceptados'  => '1',
            'privacidad_aceptada' => '1',
        ];

        $response = $this->post('/invitacion', $payload);

        $response->assertSessionHasErrors(['edad']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invitation_request_fails_with_invalid_tipo_perfil(): void
    {
        $payload = [
            'nombre_completo'     => 'Test Invalid',
            'email'               => 'invalid_' . time() . '@example.com',
            'edad'                => 25,
            'pais'                => 'Mexico',
            'estado'              => 'Ciudad de Mexico',
            'municipio'           => 'Cuauhtemoc',
            'tipo_perfil'         => 'tipo_invalido',
            'motivo'              => 'Quiero unirme al club para conocer personas afines en un ambiente discreto.',
            'invitation_code'     => '',
            'terminos_aceptados'  => '1',
            'privacidad_aceptada' => '1',
        ];

        $response = $this->post('/invitacion', $payload);

        $response->assertSessionHasErrors(['tipo_perfil']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invitation_success_page_loads(): void
    {
        $response = $this->get('/invitacion/gracias');
        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invitation_with_ref_code_captures_referral(): void
    {
        $code = DB::table('referral_codes')->value('code');

        if (!$code) {
            $this->markTestSkipped('No hay referral codes en la base de datos.');
        }

        $response = $this->get('/invitacion?ref=' . $code);
        $response->assertStatus(200);
        $response->assertSee($code);
    }
}
