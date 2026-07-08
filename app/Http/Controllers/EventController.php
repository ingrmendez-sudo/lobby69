<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = DB::table('events')
            ->where('is_published', true)
            ->orderBy('starts_at', 'asc')
            ->get();

        return view('events.index', compact('events'));
    }

    public function show($id)
    {
        $event = DB::table('events')
            ->where('id', $id)
            ->where('is_published', true)
            ->first();

        if (!$event) abort(404);

        return view('events.show', compact('event'));
    }
}
