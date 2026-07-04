<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminEventController extends Controller
{
    public function index()
    {
        $events = DB::table('events')->orderByDesc('event_date')->paginate(20);
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.form', ['event' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string',
            'event_date'  => 'required|date',
            'location'    => 'nullable|string|max:200',
            'is_online'   => 'boolean',
            'image_url'   => 'nullable|url|max:500',
        ]);

        DB::table('events')->insert([
            ...$data,
            'is_online'  => $request->boolean('is_online'),
            'created_by' => (string) auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.events.index')->with('success', 'Evento creado.');
    }

    public function edit($id)
    {
        $event = DB::table('events')->where('id', $id)->first();
        abort_if(!$event, 404);
        return view('admin.events.form', compact('event'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string',
            'event_date'  => 'required|date',
            'location'    => 'nullable|string|max:200',
            'is_online'   => 'boolean',
            'image_url'   => 'nullable|url|max:500',
        ]);

        DB::table('events')->where('id', $id)->update([
            ...$data,
            'is_online'  => $request->boolean('is_online'),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.events.index')->with('success', 'Evento actualizado.');
    }

    public function destroy($id)
    {
        DB::table('events')->where('id', $id)->delete();
        return back()->with('success', 'Evento eliminado.');
    }
}
