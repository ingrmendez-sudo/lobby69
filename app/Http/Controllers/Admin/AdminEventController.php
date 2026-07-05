<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminEventController extends Controller
{
    public function index()
    {
        $events = DB::table('events')->orderByDesc('starts_at')->paginate(20);
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.form', ['event' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:200',
            'description'  => 'nullable|string',
            'address'      => 'nullable|string|max:300',
            'organized_by' => 'nullable|string|max:200',
            'starts_at'    => 'required|date',
            'ends_at'      => 'nullable|date|after:starts_at',
            'is_online'    => 'nullable',
            'is_published' => 'nullable',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $imagePath = null;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('events', 'public');
        }

        DB::table('events')->insert([
            'title'        => $request->title,
            'description'  => $request->description,
            'address'      => $request->address,
            'organized_by' => $request->organized_by,
            'starts_at'    => $request->starts_at,
            'ends_at'      => $request->ends_at,
            'image_path'   => $imagePath,
            'is_online'    => $request->boolean('is_online'),
            'is_published' => $request->boolean('is_published'),
            'created_by'   => (string) auth()->id(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('admin.events.index')
                         ->with('success', 'Evento creado correctamente.');
    }

    public function edit($id)
    {
        $event = DB::table('events')->where('id', $id)->first();
        abort_if(!$event, 404);
        return view('admin.events.form', compact('event'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'        => 'required|string|max:200',
            'description'  => 'nullable|string',
            'address'      => 'nullable|string|max:300',
            'organized_by' => 'nullable|string|max:200',
            'starts_at'    => 'required|date',
            'ends_at'      => 'nullable|date|after:starts_at',
            'is_online'    => 'nullable',
            'is_published' => 'nullable',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $event = DB::table('events')->where('id', $id)->first();
        abort_if(!$event, 404);

        $imagePath = $event->image_path;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // Eliminar imagen anterior si existe
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('events', 'public');
        }

        // Eliminar imagen si se marcó el checkbox
        if ($request->has('remove_image') && $imagePath) {
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = null;
        }

        DB::table('events')->where('id', $id)->update([
            'title'        => $request->title,
            'description'  => $request->description,
            'address'      => $request->address,
            'organized_by' => $request->organized_by,
            'starts_at'    => $request->starts_at,
            'ends_at'      => $request->ends_at,
            'image_path'   => $imagePath,
            'is_online'    => $request->boolean('is_online'),
            'is_published' => $request->boolean('is_published'),
            'updated_at'   => now(),
        ]);

        return redirect()->route('admin.events.index')
                         ->with('success', 'Evento actualizado correctamente.');
    }

    public function destroy($id)
    {
        $event = DB::table('events')->where('id', $id)->first();
        if ($event && $event->image_path) {
            Storage::disk('public')->delete($event->image_path);
        }
        DB::table('events')->where('id', $id)->delete();
        return back()->with('success', 'Evento eliminado.');
    }
}
