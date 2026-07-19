<?php

namespace App\Http\Controllers;

use App\Models\PortfolioActivity;
use App\Models\Tag;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PortfolioTagController extends Controller
{
    public function attach(Request $request, PortfolioActivity $activity, AuditLogger $audit)
    {
        Gate::authorize('attach', [Tag::class, $activity]);

        $data = $request->validate(['tag_id' => ['required', 'exists:tags,id']]);
        $activity->tags()->syncWithoutDetaching([$data['tag_id']]);
        $audit->record('tag.attached', $request->user(), $activity, ['tag_id' => $data['tag_id']], $request);

        return redirect()->route('dosen.portfolio.show', $activity);
    }

    public function detach(Request $request, PortfolioActivity $activity, Tag $tag, AuditLogger $audit)
    {
        Gate::authorize('attach', [Tag::class, $activity]);

        $activity->tags()->detach($tag->id);
        $audit->record('tag.detached', $request->user(), $activity, ['tag_id' => $tag->id], $request);

        return redirect()->route('dosen.portfolio.show', $activity);
    }
}
