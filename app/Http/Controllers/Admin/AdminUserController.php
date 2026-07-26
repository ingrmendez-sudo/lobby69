<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $search      = $request->get('q');
        $membership  = $request->get('membresia');
        $status      = $request->get('estado');
        $verified    = $request->get('verificado');

        $query = DB::table('users')
            ->leftJoin(
                DB::raw('(SELECT user_id::text as pid, display_name, nickname, city, state,
                          gender, age, verified_profile, profile_completed,
                          orientation, profile_type FROM profiles) as p'),
                DB::raw('users.id::text'), '=', 'p.pid'
            )
            ->leftJoin(
                DB::raw('(SELECT user_id::text as phid, count(*) as photo_count
                          FROM photos GROUP BY user_id) as ph'),
                DB::raw('users.id::text'), '=', 'ph.phid'
            )
            ->where('users.role', '!=', 'admin')
            ->select(
                'users.id', 'users.username', 'users.email',
                'users.membership_type', 'users.membership_expires_at',
                'users.active', 'users.created_at', 'users.last_seen_at',
                'users.verification_status', 'users.referral_count',
                'users.trial_started_at',
                'p.display_name', 'p.nickname', 'p.city', 'p.state',
                'p.gender', 'p.age', 'p.verified_profile',
                'p.profile_completed', 'p.orientation', 'p.profile_type',
                DB::raw('COALESCE(ph.photo_count, 0) as photo_count')
            );

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('users.username',  'ilike', "%$search%")
                  ->orWhere('users.email',   'ilike', "%$search%")
                  ->orWhere('p.nickname',    'ilike', "%$search%")
                  ->orWhere('p.display_name','ilike', "%$search%")
                  ->orWhere('p.city',        'ilike', "%$search%");
            });
        }
        if ($membership)              $query->where('users.membership_type', $membership);
        if ($status === 'activo')     $query->where('users.active', true);
        if ($status === 'suspendido') $query->where('users.active', false);
        if ($verified === 'si')       $query->where('p.verified_profile', true);
        if ($verified === 'no')       $query->where('p.verified_profile', false);

        $users = $query->orderByDesc('users.created_at')->paginate(30);

        $stats = DB::table('users')->where('role', '!=', 'admin')
            ->selectRaw("
                count(*) as total,
                sum(case when active = true then 1 else 0 end) as activos,
                sum(case when active = false then 1 else 0 end) as suspendidos,
                sum(case when membership_type = 'premium' then 1 else 0 end) as premium,
                sum(case when membership_type = 'vip' then 1 else 0 end) as vip,
                sum(case when membership_type = 'trial' then 1 else 0 end) as trial,
                sum(case when membership_type = 'free' then 1 else 0 end) as free,
                sum(case when created_at >= now() - interval '7 days' then 1 else 0 end) as nuevos_semana
            ")->first();

        return view('admin.users.index', compact('users', 'stats', 'search', 'membership', 'status', 'verified'));
    }

    public function show($id)
    {
        $user = DB::table('users')
            ->leftJoin(
                DB::raw('(SELECT user_id::text as pid, display_name, nickname, city, state,
                          gender, age, bio, orientation, profile_type, verified_profile,
                          interests, looking_for, tattoos, piercings, smokes, drinks,
                          height, weight, ethnicity, nationality, marital_status,
                          profile_completed, last_active_at FROM profiles) as p'),
                DB::raw('users.id::text'), '=', 'p.pid'
            )
            ->where(DB::raw('users.id::text'), $id)
            ->select('users.*', 'p.display_name', 'p.nickname', 'p.city', 'p.state',
                     'p.gender', 'p.age', 'p.bio', 'p.orientation', 'p.profile_type',
                     'p.verified_profile', 'p.interests', 'p.looking_for',
                     'p.tattoos', 'p.piercings', 'p.smokes', 'p.drinks',
                     'p.height', 'p.weight', 'p.ethnicity', 'p.nationality',
                     'p.marital_status', 'p.profile_completed', 'p.last_active_at')
            ->first();

        if (!$user) abort(404);

        $photoCount   = DB::table('photos')->whereRaw('user_id::text = ?', [$id])->count();
        $likeCount    = DB::table('photo_likes')->whereRaw('user_id::text = ?', [$id])->count();
        $commentCount = DB::table('photo_comments')->whereRaw('user_id::text = ?', [$id])->count();
        $followCount  = DB::table('follows')->whereRaw('follower_id::text = ?', [$id])->count();
        $followerCount= DB::table('follows')->whereRaw('following_id::text = ?', [$id])->count();

        return response()->json([
            'user'          => $user,
            'photo_count'   => $photoCount,
            'like_count'    => $likeCount,
            'comment_count' => $commentCount,
            'follow_count'  => $followCount,
            'follower_count'=> $followerCount,
        ]);
    }

    public function suspend(Request $request, $id)
    {
        DB::table('users')->whereRaw('id::text = ?', [$id])->update(['active' => false]);
        return back()->with('success', 'Usuario suspendido.');
    }

    public function activate(Request $request, $id)
    {
        DB::table('users')->whereRaw('id::text = ?', [$id])->update(['active' => true]);
        return back()->with('success', 'Usuario activado.');
    }

    public function changeMembership(Request $request, $id)
    {
        $request->validate(['membership_type' => 'required|string']);
        $expires = null;
        if (in_array($request->membership_type, ['basic','premium','vip'])) {
            $expires = now()->addDays(30)->toDateTimeString();
        }
        DB::table('users')->whereRaw('id::text = ?', [$id])->update([
            'membership_type'       => $request->membership_type,
            'membership_expires_at' => $expires,
            'membership_started_at' => now(),
        ]);
        return back()->with('success', 'Membresía actualizada.');
    }

    public function resetPassword(Request $request, $id)
    {
        $newPass = Str::random(10);
        DB::table('users')->whereRaw('id::text = ?', [$id])->update([
            'password'          => Hash::make($newPass),
            'password_changed'  => false,
        ]);
        return back()->with('success', "Contraseña reseteada. Nueva contraseña temporal: {$newPass}");
    }

    public function destroy(Request $request, $id)
    {
        DB::table('photo_likes')->whereRaw('user_id::text = ?', [$id])->delete();
        DB::table('photo_comments')->whereRaw('user_id::text = ?', [$id])->delete();
        DB::table('follows')->whereRaw('follower_id::text = ?', [$id])->delete();
        DB::table('follows')->whereRaw('following_id::text = ?', [$id])->delete();
        DB::table('photos')->whereRaw('user_id::text = ?', [$id])->delete();
        DB::table('profiles')->whereRaw('user_id::text = ?', [$id])->delete();
        DB::table('users')->whereRaw('id::text = ?', [$id])->delete();
        return redirect()->route('admin.users.index')->with('success', 'Cuenta eliminada correctamente.');
    }

    public function exportCsv(Request $request)
    {
        $users = DB::table('users')
            ->leftJoin(
                DB::raw('(SELECT user_id::text as pid, display_name, nickname, city FROM profiles) as p'),
                DB::raw('users.id::text'), '=', 'p.pid'
            )
            ->where('users.role', '!=', 'admin')
            ->select('users.username','users.email','users.membership_type',
                     'users.active','users.created_at','p.nickname','p.city')
            ->orderByDesc('users.created_at')
            ->get();

        $csv = "Username,Email,Nickname,Ciudad,Membresía,Estado,Registro\n";
        foreach ($users as $u) {
            $csv .= implode(',', [
                $u->username, $u->email, $u->nickname ?? '',
                $u->city ?? '', $u->membership_type,
                $u->active ? 'Activo' : 'Suspendido',
                $u->created_at
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="usuarios_' . date('Y-m-d') . '.csv"',
        ]);
    }

    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = trim($request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $users = \App\Models\User::where('role', '!=', 'admin')
            ->where(function ($query) use ($q) {
                $query->where('email',    'ilike', "%{$q}%")
                    ->orWhere('username', 'ilike', "%{$q}%");
            })
            ->select('id', 'username', 'email', 'membership_type')
            ->limit(8)
            ->get();

        return response()->json($users);
    }

}
