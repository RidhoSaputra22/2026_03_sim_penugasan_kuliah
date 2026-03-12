<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::where('user_id', auth()->id())->get();

        if (request()->wantsJson()) {
            return response()->json($events);
        }

        return view('events.index', compact('events'));
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start'       => 'required|date',
            'end'         => 'nullable|date|after_or_equal:start',
            'location'    => 'nullable|string|max:255',
            'color' => 'nullable|in:primary,secondary,accent,info,success,warning,error,neutral',

        ]);
        $validated['user_id'] = auth()->id();
        // dd($validated);
        $event = Event::create($validated);

        if ($request->wantsJson()) {
            return response()->json($event, 201);
        }
        return redirect()->route('events.index');
    }

    public function show(Event $event)
    {
        abort_unless($event->user_id === auth()->id(), 403);
        return response()->json($event);
    }

    public function edit(Event $event)
    {
        abort_unless($event->user_id === auth()->id(), 403);
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        abort_unless($event->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start'       => 'required|date',
            'end'         => 'nullable|date|after_or_equal:start',
            'location'    => 'nullable|string|max:255',
            'color' => 'nullable|in:primary,secondary,accent,info,success,warning,error,neutral',

        ]);
        $event->update($validated);

        if ($request->wantsJson()) {
            return response()->json($event);
        }

        return redirect()->route('events.index');
    }

    public function destroy(Event $event)
    {
        abort_unless($event->user_id === auth()->id(), 403);
        $event->delete();

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('events.index');
    }
}
