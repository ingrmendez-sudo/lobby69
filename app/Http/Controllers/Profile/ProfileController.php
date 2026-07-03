<?php
namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function setup()
    {
        $profile = DB::table('profiles')->where('user_id', auth()->id())->first();
        $user    = auth()->user();
        return view('profile.setup', compact('profile', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nickname'     => 'required|string|min:3|max:30|alpha_dash',
            'profile_type' => 'required|in:single,pareja,unicornio',
            'display_name' => 'required|string|max:100',
            'age'          => 'required|integer|min:18|max:99',
            'gender'       => 'required|in:masculino,femenino,otro',
            'bio'          => 'nullable|string|max:500',
            'state'        => 'nullable|string|max:100',
            'city'         => 'nullable|string|max:100',
            'orientation'  => 'nullable|string|max:50',
        ], [
            'nickname.required'     => 'El nick es obligatorio.',
            'nickname.alpha_dash'   => 'El nick solo puede contener letras, números, guiones y guiones bajos.',
            'nickname.min'          => 'El nick debe tener al menos 3 caracteres.',
            'age.min'               => 'Debes tener al menos 18 años.',
            'display_name.required' => 'El nombre es obligatorio.',
        ]);

        $userId  = auth()->id();
        $profile = DB::table('profiles')->where('user_id', $userId)->first();

        // Verificar nick unico (solo si es nuevo perfil o nick no asignado aun)
        $nickFijo = $profile?->nickname ?? null;
        if (!$nickFijo) {
            $nickExists = DB::table('profiles')
                ->where('nickname', $request->nickname)
                ->where('user_id', '!=', $userId)
                ->exists();
            if ($nickExists) {
                return back()->withErrors(['nickname' => 'Este nick ya está en uso. Elige otro.'])->withInput();
            }
        }

        $lookingFor      = $request->input('looking_for', []);
        $interests       = $request->input('interests', []);
        $privacySettings = $request->input('privacy_settings', []);
        $notifications   = $request->input('notifications', []);

        $data = [
            'nickname'             => $nickFijo ?? $request->nickname, // nick fijo tras primer guardado
            'profile_type'         => $request->profile_type,
            'display_name'         => $request->display_name,
            'age'                  => (int) $request->age,
            'gender'               => $request->gender,
            'bio'                  => $request->bio ?? '',
            'state'                => $request->state ?? '',
            'city'                 => $request->city ?? '',
            'country'              => $request->country ?? 'México',
            'orientation'          => $request->orientation ?? '',
            'looking_for'          => json_encode($lookingFor),
            'interests'            => json_encode($interests),
            'privacy_settings'     => json_encode($privacySettings),
            'notifications'        => json_encode($notifications),
            'profile_completed'    => true,
            'profile_completed_at' => Carbon::now()->toDateTimeString(),
            'updated_at'           => Carbon::now()->toDateTimeString(),
        ];

        // Campos de pareja solo si aplica
        if ($request->profile_type === 'pareja') {
            $data['partner_name']   = $request->partner_name ?? '';
            $data['partner_age']    = $request->partner_age ? (int)$request->partner_age : null;
            $data['partner_gender'] = $request->partner_gender ?? '';
            $data['partner_bio']    = $request->partner_bio ?? '';
        } else {
            $data['partner_name']   = null;
            $data['partner_age']    = null;
            $data['partner_gender'] = null;
            $data['partner_bio']    = null;
        }

        if ($profile) {
            DB::table('profiles')->where('user_id', $userId)->update($data);
        } else {
            $data['id']         = (string) Str::uuid();
            $data['user_id']    = $userId;
            $data['created_at'] = Carbon::now()->toDateTimeString();
            DB::table('profiles')->insert($data);
        }

        return redirect()->route('dashboard')
            ->with('success', '✅ Perfil guardado correctamente.');
    }

    public function edit()
    {
        $profile = DB::table('profiles')->where('user_id', auth()->id())->first();
        $user    = auth()->user();
        if (!$profile) return redirect()->route('profile.setup');
        return view('profile.edit', compact('profile', 'user'));
    }

    public function update(Request $request)
    {
        // Reutilizar store pero forzando nick fijo
        return $this->store($request);
    }

    public function publicShow($nickname)
    {
        $profile = \Illuminate\Support\Facades\DB::table('profiles')
            ->where('nickname', $nickname)
            ->where('profile_completed', true)
            ->first();

        if (!$profile) abort(404, 'Perfil no encontrado.');

        $user = \Illuminate\Support\Facades\DB::table('users')
            ->whereRaw('id::text = ?', [$profile->user_id])
            ->first();

        if (!$user || in_array($user->membership_type, ['banned', 'suspended'])) {
            abort(404);
        }

        $me = auth()->id();

        // ¿Es el propio perfil?
        $isOwnProfile = $me && (string)$me === (string)$profile->user_id;

        // ¿Ya lo sigue el usuario autenticado?
        $isFollowing = false;
        if ($me && !$isOwnProfile) {
            $isFollowing = \Illuminate\Support\Facades\DB::table('follows')
                ->where('follower_id', $me)
                ->where('following_id', $profile->user_id)
                ->exists();
        }

        // Contadores
        $followersCount = \Illuminate\Support\Facades\DB::table('follows')
            ->where('following_id', $profile->user_id)
            ->count();

        $followingCount = \Illuminate\Support\Facades\DB::table('follows')
            ->where('follower_id', $profile->user_id)
            ->count();

        return view('profile.show', compact(
            'profile',
            'user',
            'isOwnProfile',
            'isFollowing',
            'followersCount',
            'followingCount'
        ));
    }

}
