<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminVerificationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $verifications = DB::table('verifications as v')
            ->join('users as u', DB::raw('u.id::text'), '=', DB::raw('v.user_id::text'))
            ->leftJoin('profiles as p', DB::raw('p.user_id::text'), '=', DB::raw('v.user_id::text'))
            ->select(
                'v.*',
                'u.email',
                'u.name',
                'u.membership_type',
                'p.nickname',
                'p.profile_type',
                'p.city',
                'p.state'
            )
            ->whereRaw("v.status = ?", [$status])
            ->orderBy('v.created_at', 'asc')
            ->paginate(20);

        $counts = [
            'pending'  => DB::table('verifications')->whereRaw("status::text = 'pending'")->count(),
            'approved' => DB::table('verifications')->whereRaw("status::text = 'approved'")->count(),
            'rejected' => DB::table('verifications')->whereRaw("status::text = 'rejected'")->count(),
        ];

        return view('admin.verifications.index', compact('verifications', 'counts', 'status'));
    }

    public function show($id)
    {
        $verification = DB::table('verifications as v')
            ->join('users as u', DB::raw('u.id::text'), '=', DB::raw('v.user_id::text'))
            ->leftJoin('profiles as p', DB::raw('p.user_id::text'), '=', DB::raw('v.user_id::text'))
            ->select('v.*', 'u.email', 'u.name', 'u.membership_type',
                     'u.trial_started_at', 'p.nickname', 'p.display_name',
                     'p.profile_type', 'p.gender', 'p.age', 'p.city', 'p.state')
            ->where('v.id', $id)
            ->first();

        if (!$verification) abort(404);

        return view('admin.verifications.show', compact('verification'));
    }

    public function approve(Request $request, $id)
    {
        $verification = DB::table('verifications')->where('id', $id)->first();
        if (!$verification) abort(404);

        DB::table('verifications')->where('id', $id)->update([
            'status'      => 'approved',
            'admin_note'  => $request->input('note', ''),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);

        // Actualizar usuario: verificado
        DB::table('users')->where('id', $verification->user_id)->update([
            'verification_status' => 'approved',
            'verified_at'         => Carbon::now(),
            'membership_type'     => 'trial_verified',
            'updated_at'          => Carbon::now(),
        ]);

        // Sincronizar verified_profile en profiles
        DB::table('profiles')->whereRaw('user_id::text = ?', [(string) $verification->user_id])->update([
            'verified_profile' => true,
            'updated_at'       => Carbon::now(),
        ]);

        // Generar código de referido si no tiene
        $user = DB::table('users')->where('id', $verification->user_id)->first();
        if (!$user->referral_code) {
            $profile = DB::table('profiles')->where('user_id', $verification->user_id)->first();
            $nick    = $profile->nickname ?? 'user';
            $code    = strtoupper(substr($nick, 0, 4)) . rand(1000, 9999);
            DB::table('users')->where('id', $verification->user_id)
                ->update(['referral_code' => $code, 'updated_at' => Carbon::now()]);
        }

        return redirect()->route('admin.verifications.index')
            ->with('success', "✅ Verificación #{$id} aprobada. Usuario activado con mes gratuito.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|min:10',
        ], [
            'note.required' => 'Debes indicar el motivo del rechazo.',
            'note.min'      => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        $verification = DB::table('verifications')->where('id', $id)->first();
        if (!$verification) abort(404);

        DB::table('verifications')->where('id', $id)->update([
            'status'      => 'rejected',
            'admin_note'  => $request->input('note'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);

        DB::table('users')->where('id', $verification->user_id)->update([
            'verification_status' => 'rejected',
            'updated_at'          => Carbon::now(),
        ]);

        // Limpiar verified_profile en profiles
        DB::table('profiles')->whereRaw('user_id::text = ?', [(string) $verification->user_id])->update([
            'verified_profile' => false,
            'updated_at'       => Carbon::now(),
        ]);

        return redirect()->route('admin.verifications.index')
            ->with('success', "Verificación #{$id} rechazada. El usuario fue notificado.");
    }

    public function serveImage($id)
    {
        $verification = \Illuminate\Support\Facades\DB::table('verifications')
            ->where('id', $id)->first();

        if (!$verification) abort(404);

        $path = storage_path('app/private/' . $verification->selfie_path);

        if (!file_exists($path)) {
            abort(404, 'Imagen no encontrada');
        }

        $mimeType = mime_content_type($path);
        return response()->file($path, [
            'Content-Type'  => $mimeType,
            'Cache-Control' => 'no-store, no-cache',
            'X-Robots-Tag'  => 'noindex',
        ]);
    }
}