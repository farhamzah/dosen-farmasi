<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\Document;
use App\Models\LecturerCertification;
use App\Models\LecturerEducation;
use App\Models\LecturerExpertiseArea;
use App\Models\LecturerExternalIdentifier;
use App\Models\LecturerFunctionalPosition;
use App\Models\LecturerSnapshot;
use App\Models\LecturerStructuralPosition;
use App\Models\PortfolioActivity;
use App\Models\ProfileVisibilitySetting;
use App\Services\ProfileCompletenessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request, ProfileCompletenessService $completeness)
    {
        $lecturerId = (string) $request->user()->core_lecturer_id;

        return view('profile.show', [
            'user' => $request->user(),
            'educations' => LecturerEducation::query()->where('lecturer_core_id', $lecturerId)->orderBy('sort_order')->orderByDesc('end_year')->get(),
            'functionalPositions' => LecturerFunctionalPosition::query()->where('lecturer_core_id', $lecturerId)->orderByDesc('is_active')->orderByDesc('effective_date')->get(),
            'structuralPositions' => LecturerStructuralPosition::query()->where('lecturer_core_id', $lecturerId)->orderByDesc('is_active')->orderByDesc('start_date')->get(),
            'expertiseAreas' => LecturerExpertiseArea::query()->where('lecturer_core_id', $lecturerId)->latest()->get(),
            'certifications' => LecturerCertification::query()->where('lecturer_core_id', $lecturerId)->orderByDesc('issued_at')->get(),
            'identifiers' => LecturerExternalIdentifier::query()->where('lecturer_core_id', $lecturerId)->orderBy('identifier_type')->get(),
            'recentAcademicActivities' => PortfolioActivity::query()->where('lecturer_core_id', $lecturerId)->latest()->limit(5)->get(),
            'visibilitySetting' => ProfileVisibilitySetting::query()->firstOrCreate(['lecturer_core_id' => $lecturerId]),
            'completeness' => $completeness->build($request->user()),
            'levels' => LecturerEducation::LEVELS,
            'identifierTypes' => LecturerExternalIdentifier::TYPES,
        ]);
    }

    public function storeEducation(Request $request)
    {
        Gate::authorize('create', LecturerEducation::class);

        $data = $this->educationData($request);
        $document = $this->ownedDocument($request, $data['document_id'] ?? null);

        $education = LecturerEducation::query()->create([
            ...$data,
            'document_id' => $document?->id,
            'lecturer_core_id' => (string) $request->user()->core_lecturer_id,
            'source_type' => 'MANUAL',
            'verification_status' => 'DRAFT',
            'visibility' => ($data['visibility'] ?? null) ?: LecturerEducation::defaultVisibilityForLevel($data['level']),
        ]);

        if ($document) {
            $document->update(['visibility' => 'PRIVATE']);
        }

        return redirect()->route('profile.show')->with('status', 'Riwayat pendidikan ditambahkan.');
    }

    public function updateEducation(Request $request, LecturerEducation $education)
    {
        Gate::authorize('update', $education);

        $data = $this->educationData($request);
        $document = $this->ownedDocument($request, $data['document_id'] ?? null);

        $education->update([
            ...$data,
            'document_id' => $document?->id,
            'visibility' => ($data['visibility'] ?? null) ?: LecturerEducation::defaultVisibilityForLevel($data['level']),
        ]);

        if ($document) {
            $document->update(['visibility' => 'PRIVATE']);
        }

        return redirect()->route('profile.show')->with('status', 'Riwayat pendidikan diperbarui.');
    }

    public function destroyEducation(Request $request, LecturerEducation $education)
    {
        Gate::authorize('delete', $education);

        $education->delete();

        return redirect()->route('profile.show')->with('status', 'Riwayat pendidikan dihapus.');
    }

    public function storeIdentifier(Request $request)
    {
        $data = $this->identifierData($request);

        LecturerExternalIdentifier::query()->updateOrCreate(
            [
                'lecturer_core_id' => (string) $request->user()->core_lecturer_id,
                'identifier_type' => $data['identifier_type'],
            ],
            [
                ...$data,
                'source_type' => 'MANUAL',
                'verification_status' => 'DRAFT',
            ],
        );

        return redirect()->route('profile.show')->with('status', 'Identitas ilmiah diperbarui.');
    }

    public function updateIdentifier(Request $request, LecturerExternalIdentifier $identifier)
    {
        $this->ownedIdentifier($request, $identifier);

        $data = $this->identifierData($request);

        $identifier->update([
            ...$data,
            'source_type' => 'MANUAL',
        ]);

        return redirect()->route('profile.show')->with('status', 'Identitas ilmiah diperbarui.');
    }

    public function destroyIdentifier(Request $request, LecturerExternalIdentifier $identifier)
    {
        $this->ownedIdentifier($request, $identifier);

        $identifier->delete();

        return redirect()->route('profile.show')->with('status', 'Identitas ilmiah dihapus.');
    }

    public function updateVisibility(Request $request)
    {
        $data = $request->validate([
            'public_profile_enabled' => ['nullable', 'boolean'],
            'section_visibility' => ['array'],
            'section_visibility.*' => ['in:PRIVATE,INTERNAL,PUBLIC'],
        ]);

        ProfileVisibilitySetting::query()->updateOrCreate(
            ['lecturer_core_id' => (string) $request->user()->core_lecturer_id],
            [
                'public_profile_enabled' => (bool) ($data['public_profile_enabled'] ?? false),
                'section_visibility' => $data['section_visibility'] ?? [],
                'field_visibility' => [
                    'nik' => 'PRIVATE',
                    'home_address' => 'PRIVATE',
                    'personal_phone' => 'PRIVATE',
                    'document_numbers' => 'PRIVATE',
                ],
            ],
        );

        return redirect()->route('profile.show')->with('status', 'Pengaturan visibilitas disimpan.');
    }

    public function publicProfile(Request $request, string $lecturerCoreId)
    {
        $visibility = ProfileVisibilitySetting::query()
            ->where('lecturer_core_id', $lecturerCoreId)
            ->where('public_profile_enabled', true)
            ->firstOrFail();

        $templates = [
            'akademik' => [
                'label' => 'Akademik',
                'description' => 'Format resmi untuk profil dosen, portofolio, dan kebutuhan institusi.',
            ],
            'impact' => [
                'label' => 'Impact',
                'description' => 'Tampilan editorial dengan aksen hijau-emas untuk dibagikan ke mitra.',
            ],
            'editorial' => [
                'label' => 'Editorial',
                'description' => 'Layout ringkas dua kolom untuk CV cepat dan mudah dicetak.',
            ],
        ];
        $selectedTemplate = $request->string('template')->lower()->toString();
        $selectedTemplate = array_key_exists($selectedTemplate, $templates) ? $selectedTemplate : 'akademik';

        return view('profile.public', [
            'visibility' => $visibility,
            'templates' => $templates,
            'selectedTemplate' => $selectedTemplate,
            'lecturer' => LecturerSnapshot::query()->where('core_lecturer_id', $lecturerCoreId)->first(),
            'user' => AppUser::query()->where('core_lecturer_id', $lecturerCoreId)->first(),
            'educations' => LecturerEducation::query()->where('lecturer_core_id', $lecturerCoreId)->where('visibility', 'PUBLIC')->orderByDesc('end_year')->get(),
            'functionalPositions' => LecturerFunctionalPosition::query()->where('lecturer_core_id', $lecturerCoreId)->where('visibility', 'PUBLIC')->orderByDesc('is_active')->orderByDesc('effective_date')->get(),
            'structuralPositions' => LecturerStructuralPosition::query()->where('lecturer_core_id', $lecturerCoreId)->where('visibility', 'PUBLIC')->orderByDesc('is_active')->orderByDesc('start_date')->get(),
            'certifications' => LecturerCertification::query()->where('lecturer_core_id', $lecturerCoreId)->where('visibility', 'PUBLIC')->orderByDesc('issued_at')->get(),
            'expertiseAreas' => LecturerExpertiseArea::query()->where('lecturer_core_id', $lecturerCoreId)->where('visibility', 'PUBLIC')->get(),
            'identifiers' => LecturerExternalIdentifier::query()->where('lecturer_core_id', $lecturerCoreId)->where('visibility', 'PUBLIC')->get(),
            'activities' => PortfolioActivity::query()->with('category')
                ->where('lecturer_core_id', $lecturerCoreId)
                ->where('visibility', 'PUBLIC')
                ->latest('start_date')
                ->latest()
                ->limit(12)
                ->get(),
        ]);
    }

    private function educationData(Request $request): array
    {
        return $request->validate([
            'level' => ['required', Rule::in(LecturerEducation::LEVELS)],
            'institution_name' => ['required', 'string', 'max:255'],
            'study_program' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'start_year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 5)],
            'end_year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 5)],
            'graduation_status' => ['required', 'in:LULUS,BERJALAN,TIDAK_SELESAI'],
            'degree' => ['nullable', 'string', 'max:255'],
            'certificate_number' => ['nullable', 'string', 'max:255'],
            'certificate_date' => ['nullable', 'date'],
            'thesis_title' => ['nullable', 'string', 'max:2000'],
            'document_id' => ['nullable', 'integer', 'exists:documents,id'],
            'visibility' => ['nullable', 'in:PRIVATE,INTERNAL,PUBLIC'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
    }

    private function identifierData(Request $request): array
    {
        return $request->validate([
            'identifier_type' => ['required', Rule::in(LecturerExternalIdentifier::TYPES)],
            'identifier_value' => ['required', 'string', 'max:255'],
            'profile_url' => ['nullable', 'url', 'max:1000', function (string $attribute, mixed $value, \Closure $fail): void {
                if (preg_match('/^\s*javascript:/i', (string) $value)) {
                    $fail('URL profil tidak aman.');
                }
            }],
            'visibility' => ['required', 'in:PRIVATE,INTERNAL,PUBLIC'],
        ]);
    }

    private function ownedIdentifier(Request $request, LecturerExternalIdentifier $identifier): LecturerExternalIdentifier
    {
        abort_unless((string) $identifier->lecturer_core_id === (string) $request->user()->core_lecturer_id, 403);

        return $identifier;
    }

    private function ownedDocument(Request $request, mixed $documentId): ?Document
    {
        if (! $documentId) {
            return null;
        }

        return Document::query()
            ->where('id', $documentId)
            ->where('lecturer_core_id', (string) $request->user()->core_lecturer_id)
            ->firstOrFail();
    }
}
