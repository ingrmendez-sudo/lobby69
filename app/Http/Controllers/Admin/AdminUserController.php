<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $search      = $request->get('q');
        $membership  = $request->get('membresia');
        $status      = $request->get('estado');

        $query = DB::table('users')
            ->leftJoinSub(
                DB::table('profiles')->selectRaw('"user_id"::text as pid, "display_name", "nickname", "city", "verified_profile"'),
                'p', 'users.id::text', '=', 'p.pid'
            )
            ->where('users.role', '!=', 'admin')
            ->select('users.id', 'users.username', 'users.email', 'users.membership_type',
                     'users.active', 'users.created_at', 'users.last_seen_at',
                     'p.display_name', 'p.nickname', 'p.city', 'p.verified_profile');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('users.username', 'ilike', "%$search%")
                  ->orWhere('users.email',   'ilike', "%$search%")
                  ->orWhere('p.nickname',    'ilike', "%$search%");
            });
        }
        if ($membership) $query->where('users.membership_type', $membership);
        if ($status === 'activo')    $query->where('users.active', true);
        if ($status === 'suspendido') $query->where('users.active', false);

        $users = $query->orderByDesc('users.created_at')->paginate(30);

        $memberships = DB::table('users')
            ->where('role', '!=', 'admin')
            ->selectRaw('membership_type, count(*) as total')
            ->groupBy('membership_type')
            ->pluck('total', 'membership_type');

        return view('admin.users.index', compact('users', 'memberships', 'search', 'membership', 'status'));
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
        DB::table('users')->whereRaw('id::text = ?', [$id])
            ->update(['membership_type' => $request->membership_type]);
        return back()->with('success', 'Membresía actualizada.');
    }
}
