<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminPhotoController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $photos = DB::table('photos as ph')
            ->join('users as u', DB::raw('u.id::text'), '=', DB::raw('ph.user_id::text'))
            ->leftJoin('profiles as p', DB::raw('p.user_id::text'), '=', DB::raw('ph.user_id::text'))
            ->select('ph.*', 'u.email', 'p.nickname', 'p.display_name', 'p.profile_type')
            ->whereRaw("ph.status::text = ?", [$status])
            ->orderBy('ph.created_at', 'asc')
            ->paginate(24);

        $counts = [
            'pending'  => DB::table('photos')->whereRaw("status::text = 'pending'")->count(),
            'approved' => DB::table('photos')->whereRaw("status::text = 'approved'")->count(),
            'rejected' => DB::table('photos')->whereRaw("status::text = 'rejected'")->count(),
        ];

        return view('admin.photos.index', compact('photos', 'counts', 'status'));
    }

    public function approve($id)
    {
        DB::table('photos')->where('id', $id)->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);
        return back()->with('success', "✅ Foto #{$id} aprobada.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['note' => 'required|min:5']);

        DB::table('photos')->where('id', $id)->update([
            'status'      => 'rejected',
            'admin_note'  => $request->input('note'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);
        return back()->with('success', "Foto #{$id} rechazada.");
    }

    public function serve($id)
    {
        $photo = DB::table('photos')->where('id', $id)->first();
        if (!$photo) abort(404);

        $path = storage_path('app/private/' . $photo->file_path);
        if (!file_exists($path)) abort(404);

        return response()->file($path, [
            'Content-Type'  => mime_content_type($path),
            'Cache-Control' => 'no-store',
            'X-Robots-Tag'  => 'noindex',
        ]);
    }
}