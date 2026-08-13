<?php
namespace App\Services;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvitationService
{
    public function approve(string $id, string $adminId): array
    {
        $inv = DB::table('invitation_requests')->where('id', $id)->first();
        if (!$inv) return ['success'=>false,'message'=>'Solicitud no encontrada.'];
        if ($inv->status === 'approved') return ['success'=>false,'message'=>'Ya fue aprobada.'];

        if (DB::table('users')->where('email', $inv->email)->exists()) {
            return ['success'=>false,'message'=>'Ya existe un usuario con ese email.'];
        }

        $tempPassword = $this->generateTempPassword();
        $username     = $this->generateUsername($inv->nombre);

        DB::beginTransaction();
        try {
            $userId    = (string) Str::uuid();
            $hash      = Hash::make($tempPassword);
            $now       = Carbon::now()->toDateTimeString();

            // Raw SQL para evitar el cast boolean de PostgreSQL via pgBouncer
            DB::statement("
                INSERT INTO users (id, email, username, password, name, role,
                                   active, terms_accepted,
                                   terms_accepted_at, email_verified_at,
                                   created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'user',
                        true, true,
                        ?, ?, ?, ?)
            ", [$userId, $inv->email, $username, $hash, $inv->nombre,
                $now, $now, $now, $now]);

            DB::table('invitation_requests')->where('id', $id)->update([
                'status'      => 'approved',
                'reviewed_by' => $adminId,
                'reviewed_at' => $now,
                'approved_at' => $now,
                'updated_at'  => $now,
            ]);

            DB::commit();

            Log::channel('single')->info('=== LOBBY69 INVITACION APROBADA ===', [
                'email'         => $inv->email,
                'username'      => $username,
                'temp_password' => $tempPassword,
                'user_id'       => $userId,
                'approved_at'   => $now,
                'message'       => "Credenciales para {$inv->email}: usuario={$username} pass={$tempPassword}",
            ]);

            return ['success'=>true,'email'=>$inv->email,'temp_password'=>$tempPassword];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error aprobando invitacion: '.$e->getMessage());
            return ['success'=>false,'message'=>'Error: '.$e->getMessage()];
        }
    }

    public function reject(string $id, string $adminId, string $reason): array
    {
        $inv = DB::table('invitation_requests')->where('id', $id)->first();
        if (!$inv) return ['success'=>false,'message'=>'Solicitud no encontrada.'];

        DB::table('invitation_requests')->where('id', $id)->update([
            'status'      => 'rejected',
            'reviewed_by' => $adminId,
            'reviewed_at' => Carbon::now(),
            'admin_notes' => $reason,
            'updated_at'  => Carbon::now(),
        ]);

        return ['success'=>true];
    }

    private function generateTempPassword(): string
    {
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#';
        $pass  = '';
        for ($i = 0; $i < 12; $i++) {
            $pass .= $chars[random_int(0, strlen($chars)-1)];
        }
        return $pass;
    }

    private function generateUsername(string $nombre): string
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', iconv('UTF-8','ASCII//TRANSLIT',$nombre)));
        $base = substr($base, 0, 12) ?: 'user';
        $username = $base . random_int(100,999);
        while (DB::table('users')->where('username', $username)->exists()) {
            $username = $base . random_int(1000,9999);
        }
        return $username;
    }
}