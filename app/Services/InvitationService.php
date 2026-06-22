<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationService
{
    public function approveInvitation(object $solicitud, string $adminId, ?string $notes = null): array
    {
        $existing = DB::table('users')->where('email', $solicitud->email)->first();
        if ($existing) {
            throw new \Exception("El email {$solicitud->email} ya tiene una cuenta.");
        }

        $tempPassword = $this->generateTempPassword();
        $userId       = (string) Str::uuid();

        DB::beginTransaction();
        try {
            DB::table('users')->insert([
                'id'                => $userId,
                'email'             => $solicitud->email,
                'username'          => $this->generateUsername($solicitud->nombre),
                'name'              => $solicitud->nombre,
                'password'          => Hash::make($tempPassword),
                'role'              => 'user',
                'active'            => true,
                'age_verified'      => true,
                'terms_accepted'    => true,
                'terms_accepted_at' => Carbon::now(),
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]);

            DB::table('invitation_requests')->where('id', $solicitud->id)->update([
                'status'      => 'approved',
                'reviewed_by' => $adminId,
                'reviewed_at' => Carbon::now(),
                'approved_at' => Carbon::now(),
                'admin_notes' => $notes,
                'updated_at'  => Carbon::now(),
            ]);

            DB::commit();

            $this->sendWelcomeMail($solicitud->email, $solicitud->nombre, $tempPassword);

            Log::info('Usuario creado desde invitacion', [
                'user_id'       => $userId,
                'email'         => $solicitud->email,
                'temp_password' => $tempPassword,
            ]);

            return ['success' => true, 'user_id' => $userId, 'temp_password' => $tempPassword];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function generateTempPassword(): string
    {
        $words   = ['Lobby', 'Noche', 'Fiesta', 'Club', 'Elite', 'Vivid'];
        $special = ['!', '@', '#', '$'];
        return $words[array_rand($words)] . rand(100, 999) . $special[array_rand($special)];
    }

    private function generateUsername(string $nombre): string
    {
        $base     = strtolower(substr(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $nombre)), 0, 15));
        $username = $base;
        $counter  = 1;
        while (DB::table('users')->where('username', $username)->exists()) {
            $username = $base . $counter++;
        }
        return $username;
    }

    private function sendWelcomeMail(string $email, string $nombre, string $tempPassword): void
    {
        try {
            Mail::send('emails.invitation-approved', compact('nombre', 'email', 'tempPassword'), function ($mail) use ($email, $nombre) {
                $mail->to($email, $nombre)->subject('Tu acceso a LOBBY69 ha sido aprobado');
            });
        } catch (\Exception $e) {
            Log::warning('Mail no enviado: ' . $e->getMessage());
        }
    }
}