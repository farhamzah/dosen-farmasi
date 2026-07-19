<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\PortfolioActivity;
use App\Services\AuditLogger;
use App\Services\DocumentFileValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::query()->with('activities')->latest();

        if (! $request->user()->isAdmin()) {
            $query->where('lecturer_core_id', $request->user()->core_lecturer_id);
        }

        return view('documents.index', ['documents' => $query->paginate(10)]);
    }

    public function create()
    {
        return view('documents.create', ['activities' => PortfolioActivity::query()
            ->where('lecturer_core_id', auth()->user()->core_lecturer_id)
            ->whereIn('verification_status', ['DRAFT', 'REVISION_REQUIRED'])
            ->orderBy('title')
            ->get()]);
    }

    public function store(Request $request, AuditLogger $audit, DocumentFileValidator $fileValidator)
    {
        $allowed = implode(',', config('dosen_farmasi.documents.allowed_extensions'));
        $data = $request->validate([
            'portfolio_activity_id' => ['nullable', 'exists:portfolio_activities,id'],
            'document_type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:255'],
            'document_date' => ['nullable', 'date'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'visibility' => ['nullable', 'in:PRIVATE,INTERNAL,PUBLIC'],
            'file' => ['required', 'file', 'max:'.config('dosen_farmasi.documents.max_kb'), 'extensions:'.$allowed],
        ]);
        $data['document_type'] = strtoupper($data['document_type']);
        $data['visibility'] = $data['visibility'] ?? 'PRIVATE';

        if (! in_array($data['document_type'], config('dosen_farmasi.documents.types'), true)) {
            throw ValidationException::withMessages(['document_type' => 'Jenis dokumen tidak dikenal.']);
        }

        $activity = null;
        if (! empty($data['portfolio_activity_id'])) {
            $activity = PortfolioActivity::query()->findOrFail($data['portfolio_activity_id']);
            Gate::authorize('update', $activity);
        }

        $file = $request->file('file');
        $fileInfo = $fileValidator->validate($file);
        $extension = $fileInfo['extension'];
        $mimeType = $fileInfo['mime_type'];

        $storedFilename = (string) Str::uuid().'.'.$extension;
        $path = 'documents/'.now()->format('Y/m').'/'.$storedFilename;
        $disk = (string) config('dosen_farmasi.documents.disk');
        $contents = $fileInfo['contents'];

        Storage::disk($disk)->put($path, $contents);

        try {
            $document = DB::transaction(function () use ($request, $data, $disk, $path, $file, $fileInfo, $storedFilename, $extension, $mimeType, $activity, $audit): Document {
                $document = Document::query()->create([
                    'lecturer_core_id' => (string) $request->user()->core_lecturer_id,
                    'document_type' => $data['document_type'],
                    'title' => $data['title'],
                    'document_number' => $data['document_number'] ?? null,
                    'document_date' => $data['document_date'] ?? null,
                    'issuer' => $data['issuer'] ?? null,
                    'disk' => $disk,
                    'path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_filename' => $storedFilename,
                    'extension' => $extension,
                    'mime_type' => $mimeType,
                    'size_bytes' => $file->getSize(),
                    'sha256_checksum' => $fileInfo['sha256'],
                    'uploaded_by_app_user_id' => $request->user()->id,
                    'uploaded_by_core_user_id' => (string) $request->user()->core_user_id,
                    'verification_status' => 'PENDING',
                    'visibility' => $data['visibility'],
                ]);

                DocumentVersion::query()->create([
                    'document_id' => $document->id,
                    'version_number' => 1,
                    'disk' => $disk,
                    'path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'size_bytes' => $file->getSize(),
                    'sha256_checksum' => $document->sha256_checksum,
                    'uploaded_by_app_user_id' => $request->user()->id,
                    'created_at' => now(),
                ]);

                if ($activity) {
                    $activity->documents()->attach($document->id);
                }

                $audit->record('document.uploaded', $request->user(), $document, [], $request);

                return $document;
            });
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($path);
            throw $exception;
        }

        return redirect()->route('dosen.documents.index');
    }

    public function destroy(Request $request, Document $document, AuditLogger $audit)
    {
        Gate::authorize('delete', $document);

        $document->update(['delete_reason' => $request->string('reason')->toString() ?: 'Dihapus pengguna.']);
        $document->delete();
        $audit->record('document.deleted', $request->user(), $document, ['reason' => $document->delete_reason], $request);

        return redirect()->route('dosen.documents.index');
    }
}
