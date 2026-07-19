<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $query = CalendarEvent::query()->orderBy('starts_at');

        if (! $request->user()->isAdmin()) {
            $query->where('lecturer_core_id', $request->user()->core_lecturer_id);
        }

        $events = $query->paginate(15);

        return view('calendar.index', ['events' => $events]);
    }
}
