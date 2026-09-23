<?php

namespace App\Http\Controllers;

use App\Models\LecturerCertification;
use App\Models\LecturerExpertiseArea;
use App\Models\LecturerFunctionalPosition;
use App\Models\LecturerStructuralPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AcademicProfileRecordController extends Controller
{
    public function storeExpertise(Request $request): RedirectResponse
    {
        LecturerExpertiseArea::query()->create([
            ...$this->expertiseData($request),
            ...$this->manualIdentity($request),
        ]);

        return $this->back('Bidang kepakaran ditambahkan.', 'keilmuan');
    }

    public function updateExpertise(Request $request, LecturerExpertiseArea $expertise): RedirectResponse
    {
        $this->authorizeManualRecord($request, $expertise);
        $expertise->update($this->expertiseData($request));

        return $this->back('Bidang kepakaran diperbarui.', 'keilmuan');
    }

    public function destroyExpertise(Request $request, LecturerExpertiseArea $expertise): RedirectResponse
    {
        $this->authorizeManualRecord($request, $expertise);
        $expertise->delete();

        return $this->back('Bidang kepakaran dihapus.', 'keilmuan');
    }

    public function storeCertification(Request $request): RedirectResponse
    {
        LecturerCertification::query()->create([
            ...$this->certificationData($request),
            ...$this->manualIdentity($request),
        ]);

        return $this->back('Sertifikasi ditambahkan.', 'sertifikasi');
    }

    public function updateCertification(Request $request, LecturerCertification $certification): RedirectResponse
    {
        $this->authorizeManualRecord($request, $certification);
        $certification->update($this->certificationData($request));

        return $this->back('Sertifikasi diperbarui.', 'sertifikasi');
    }

    public function destroyCertification(Request $request, LecturerCertification $certification): RedirectResponse
    {
        $this->authorizeManualRecord($request, $certification);
        $certification->delete();

        return $this->back('Sertifikasi dihapus.', 'sertifikasi');
    }

    public function storeFunctionalPosition(Request $request): RedirectResponse
    {
        $data = $this->functionalPositionData($request);
        $this->savePosition($request, LecturerFunctionalPosition::class, null, $data);

        return $this->back('Jabatan fungsional ditambahkan.', 'karier');
    }

    public function updateFunctionalPosition(Request $request, LecturerFunctionalPosition $position): RedirectResponse
    {
        $this->authorizeManualRecord($request, $position);
        $this->savePosition($request, LecturerFunctionalPosition::class, $position, $this->functionalPositionData($request));

        return $this->back('Jabatan fungsional diperbarui.', 'karier');
    }

    public function destroyFunctionalPosition(Request $request, LecturerFunctionalPosition $position): RedirectResponse
    {
        $this->authorizeManualRecord($request, $position);
        $position->delete();

        return $this->back('Jabatan fungsional dihapus.', 'karier');
    }

    public function storeStructuralPosition(Request $request): RedirectResponse
    {
        $data = $this->structuralPositionData($request);
        $this->savePosition($request, LecturerStructuralPosition::class, null, $data);

        return $this->back('Tugas tambahan ditambahkan.', 'karier');
    }

    public function updateStructuralPosition(Request $request, LecturerStructuralPosition $position): RedirectResponse
    {
        $this->authorizeManualRecord($request, $position);
        $this->savePosition($request, LecturerStructuralPosition::class, $position, $this->structuralPositionData($request));

        return $this->back('Tugas tambahan diperbarui.', 'karier');
    }

    public function destroyStructuralPosition(Request $request, LecturerStructuralPosition $position): RedirectResponse
    {
        $this->authorizeManualRecord($request, $position);
        $position->delete();

        return $this->back('Tugas tambahan dihapus.', 'karier');
    }

    private function expertiseData(Request $request): array
    {
        $data = $request->validate([
            'primary_expertise' => ['required', 'string', 'max:255'],
            'specializations' => ['nullable', 'string', 'max:2000'],
            'research_topics' => ['nullable', 'string', 'max:2000'],
            'visibility' => ['required', Rule::in(['PRIVATE', 'INTERNAL', 'PUBLIC'])],
        ]);

        foreach (['specializations', 'research_topics'] as $field) {
            $data[$field] = collect(preg_split('/\r\n|\r|\n/', (string) ($data[$field] ?? '')))
                ->map(fn (string $value) => trim($value))
                ->filter()
                ->unique()
                ->take(15)
                ->values()
                ->all();
        }

        return $data;
    }

    private function certificationData(Request $request): array
    {
        return $request->validate([
            'category' => ['required', Rule::in(['akademik', 'profesi', 'pelatihan'])],
            'name' => ['required', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'status' => ['required', Rule::in(['AKTIF', 'KEDALUWARSA', 'TIDAK_AKTIF'])],
            'visibility' => ['required', Rule::in(['PRIVATE', 'INTERNAL', 'PUBLIC'])],
        ]);
    }

    private function functionalPositionData(Request $request): array
    {
        $data = $request->validate([
            'position_name' => ['required', 'string', 'max:255'],
            'effective_date' => ['nullable', 'date'],
            'credit_score' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'unit' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'visibility' => ['required', Rule::in(['PRIVATE', 'INTERNAL', 'PUBLIC'])],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }

    private function structuralPositionData(Request $request): array
    {
        $data = $request->validate([
            'position_name' => ['required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'visibility' => ['required', Rule::in(['PRIVATE', 'INTERNAL', 'PUBLIC'])],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }

    private function savePosition(Request $request, string $modelClass, ?Model $position, array $data): void
    {
        $lecturerId = $this->lecturerId($request);

        DB::transaction(function () use ($modelClass, $position, $data, $lecturerId, $request): void {
            if ($data['is_active']) {
                $modelClass::query()
                    ->where('lecturer_core_id', $lecturerId)
                    ->where('source_type', 'MANUAL')
                    ->update(['is_active' => false]);
            }

            if ($position) {
                $position->update($data);
            } else {
                $modelClass::query()->create([...$data, ...$this->manualIdentity($request)]);
            }
        });
    }

    private function manualIdentity(Request $request): array
    {
        return [
            'lecturer_core_id' => $this->lecturerId($request),
            'source_type' => 'MANUAL',
            'verification_status' => 'ADMIN_VERIFIED',
        ];
    }

    private function lecturerId(Request $request): string
    {
        $lecturerId = (string) $request->user()->core_lecturer_id;
        abort_if($lecturerId === '', 403);

        return $lecturerId;
    }

    private function authorizeManualRecord(Request $request, Model $record): void
    {
        abort_unless(
            (string) $record->lecturer_core_id === $this->lecturerId($request)
            && $record->source_type === 'MANUAL',
            403,
        );
    }

    private function back(string $message, string $anchor): RedirectResponse
    {
        return redirect()->to(route('profile.show').'#'.$anchor)->with('status', $message);
    }
}
