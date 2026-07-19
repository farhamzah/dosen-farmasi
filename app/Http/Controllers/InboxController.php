<?php

namespace App\Http\Controllers;

use App\Models\InboxItem;
use App\Services\InboxWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InboxController extends Controller
{
    public function index(Request $request)
    {
        $query = InboxItem::query()->latest('occurred_at')->latest();

        if (! $request->user()->isAdmin()) {
            $query->where('lecturer_core_id', $request->user()->core_lecturer_id);
        }

        $query
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')));

        return view('inbox.index', ['items' => $query->paginate(15)->withQueryString()]);
    }

    public function transition(Request $request, InboxItem $inboxItem, InboxWorkflowService $workflow)
    {
        Gate::authorize('update', $inboxItem);

        $data = $request->validate(['status' => ['required', 'in:UNREAD,READ,ACCEPTED,DECLINED,COMPLETED,ARCHIVED']]);
        $workflow->transition($inboxItem, $request->user(), $data['status']);

        return redirect()->route('dosen.inbox.index');
    }

    public function markRead(Request $request, InboxItem $inboxItem, InboxWorkflowService $workflow)
    {
        Gate::authorize('update', $inboxItem);
        $workflow->transition($inboxItem, $request->user(), 'READ');

        return redirect()->route('dosen.inbox.index');
    }
}
