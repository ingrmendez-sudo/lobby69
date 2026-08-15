<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class AdminPanelTest extends TestCase
{
    private function getAdminUser()
    {
        return DB::table('users')
            ->where('role', 'admin')
            ->first();
    }

    private function getNonAdminUser()
    {
        return DB::table('users')
            ->where('role', '!=', 'admin')
            ->first();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_boost_panel_requires_authentication(): void
    {
        $response = $this->get('/admin/boost');
        $response->assertRedirect('/login');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_boost_panel_loads_for_admin(): void
    {
        $admin = $this->getAdminUser();

        if (!$admin) {
            $this->markTestSkipped('No hay usuario admin en la base de datos.');
        }

        $response = $this->actingAs(
            \App\Models\User::find($admin->id)
        )->get('/admin/boost');

        $response->assertStatus(200);
        $response->assertSee('Boost');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_invitations_panel_loads_for_admin(): void
    {
        $admin = $this->getAdminUser();

        if (!$admin) {
            $this->markTestSkipped('No hay usuario admin en la base de datos.');
        }

        $response = $this->actingAs(
            \App\Models\User::find($admin->id)
        )->get('/admin/invitaciones');

        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function non_admin_cannot_access_boost_panel(): void
    {
        $user = $this->getNonAdminUser();

        if (!$user) {
            $this->markTestSkipped('No hay usuario no-admin en la base de datos.');
        }

        $response = $this->actingAs(
            \App\Models\User::find($user->id)
        )->get('/admin/boost');

        $response->assertStatus(403);
    }
}
