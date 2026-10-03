<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DocumentDownloadController extends Controller
{
    public function __invoke(Request $request, Document $document, AuditLogger $audit)
    {
        Gate::authorize('view', $document);

        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        $audit->record('document_downloaded', $request->user(), $document, [], $request);

        return Storage::disk($document->disk)->download($document->path, $document->original_filename);
    }
}
