<?php

namespace App\Console\Commands;

use App\Models\AppUser;
use App\Models\CalendarEvent;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\InboxItem;
use App\Models\LecturerCertification;
use App\Models\LecturerEducation;
use App\Models\LecturerExpertiseArea;
use App\Models\LecturerExternalIdentifier;
use App\Models\LecturerFunctionalPosition;
use App\Models\PortfolioActivity;
use App\Models\PortfolioCategory;
use App\Models\PortfolioParticipant;
use App\Models\PortfolioVerificationHistory;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeedUiDemoCommand extends Command
{
    protected $signature = 'dosen:seed-ui-demo
        {--clear : Hapus data demo M8 sebelum membuat ulang}
        {--only-clear : Hapus data demo M8 tanpa membuat ulang}';

    protected $description = 'Seed data UI demo lokal untuk screenshot M8 premium.';

    private const SOURCE = 'm8-ui-demo';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Command demo UI hanya boleh berjalan pada environment local/testing.');

            return self::FAILURE;
        }

        DB::transaction(function (): void {
            if ($this->option('clear') || $this->option('only-clear')) {
                $this->clearDemoData();
            }

            if ($this->option('only-clear')) {
                return;
            }

            $user = $this->demoUser();
            $lecturerId = (string) $user->core_lecturer_id;
            $categories = $this->categories();
            $documents = $this->documents($user, $lecturerId);
            $activities = $this->activities($user, $lecturerId, $categories);

            foreach ($activities as $index => $activity) {
                if (isset($documents[$index % count($documents)])) {
                    $activity->documents()->syncWithoutDetaching([$documents[$index % count($documents)]->id]);
                }

                PortfolioVerificationHistory::query()->create([
                    'portfolio_activity_id' => $activity->id,
                    'from_status' => null,
                    'to_status' => $activity->verification_status,
                    'actor_app_user_id' => $user->id,
                    'actor_core_user_id' => (string) $user->core_user_id,
                    'actor_role' => 'dosen',
                    'notes' => 'Data demo UI M8.',
                    'metadata' => ['source' => self::SOURCE],
                ]);
            }

            $this->participants($activities);
            $this->academicProfile($lecturerId);
            $this->inbox($lecturerId);
            $this->agenda($lecturerId);
            $this->notifications($user);
        });

        $this->info($this->option('only-clear') ? 'Data demo UI M8 berhasil dibersihkan.' : 'Data demo UI M8 berhasil dibuat.');

        return self::SUCCESS;
    }

    private function clearDemoData(): void
    {
        $demoActivities = PortfolioActivity::withTrashed()->where('source_app', self::SOURCE)->pluck('id');
        DB::table('activity_documents')->whereIn('portfolio_activity_id', $demoActivities)->delete();
        DB::table('portfolio_activity_tag')->whereIn('portfolio_activity_id', $demoActivities)->delete();
        PortfolioParticipant::query()->whereIn('portfolio_activity_id', $demoActivities)->delete();
        PortfolioVerificationHistory::query()->whereIn('portfolio_activity_id', $demoActivities)->delete();
        PortfolioActivity::withTrashed()->where('source_app', self::SOURCE)->forceDelete();

        DocumentVersion::query()->where('source_app', self::SOURCE)->delete();
        Document::withTrashed()->where('source_app', self::SOURCE)->forceDelete();
        Storage::disk((string) config('dosen_farmasi.documents.disk'))->deleteDirectory('demo/m8');
        InboxItem::query()->where('source_app', self::SOURCE)->delete();
        CalendarEvent::query()->where('source_app', self::SOURCE)->delete();
        LecturerEducation::withTrashed()->where('source_app', self::SOURCE)->forceDelete();
        LecturerFunctionalPosition::withTrashed()->where('source_app', self::SOURCE)->forceDelete();
        LecturerExpertiseArea::withTrashed()->where('source_app', self::SOURCE)->forceDelete();
        LecturerCertification::withTrashed()->where('source_app', self::SOURCE)->forceDelete();
        LecturerExternalIdentifier::withTrashed()->where('source_app', self::SOURCE)->forceDelete();
        DatabaseNotification::query()->where('data', 'like', '%'.self::SOURCE.'%')->delete();
    }

    private function demoUser(): AppUser
    {
        $preferredEmail = config('dosen_farmasi.ui_demo.preferred_email');

        $user = filled($preferredEmail)
            ? AppUser::query()
                ->where('role', 'dosen')
                ->where('email', $preferredEmail)
                ->whereNotNull('core_lecturer_id')
                ->first()
            : null;

        if ($user) {
            return $user;
        }

        $user = AppUser::query()
            ->where('role', 'dosen')
            ->whereNotNull('core_lecturer_id')
            ->orderBy('id')
            ->first();

        if ($user) {
            return $user;
        }

        return AppUser::query()->updateOrCreate(
            ['core_user_id' => 'm8-demo-user'],
            [
                'core_lecturer_id' => 'm8-demo-lecturer',
                'name' => 'Dosen Demo Farmasi',
                'email' => 'dosen.demo@example.test',
                'lecturer_number' => 'DF-0001',
                'nip' => '0000000000000000',
                'nidn' => '0000000000',
                'role' => 'dosen',
                'is_active' => true,
            ],
        );
    }

    private function categories(): array
    {
        $names = [
            'pendidikan' => 'Pendidikan dan Pengajaran',
            'penelitian' => 'Penelitian dan Pengembangan',
            'pengabdian' => 'Pengabdian kepada Masyarakat',
        ];

        return collect($names)
            ->map(fn (string $name, string $key): PortfolioCategory => PortfolioCategory::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => array_search($key, array_keys($names), true) + 1, 'is_active' => true],
            ))
            ->all();
    }

    private function documents(AppUser $user, string $lecturerId): array
    {
        $disk = (string) config('dosen_farmasi.documents.disk');
        $content = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF";
        $rows = [
            ['SK Mengajar Farmakologi Klinik', 'SURAT_KEPUTUSAN', 'sk-mengajar-farmakologi.pdf'],
            ['Bukti Publikasi Fitokimia', 'BUKTI_PUBLIKASI', 'publikasi-fitokimia.pdf'],
            ['Laporan Pengabdian Posyandu', 'LAPORAN', 'laporan-pengabdian-posyandu.pdf'],
            ['Sertifikat Kompetensi Apoteker', 'SERTIFIKAT', 'sertifikat-kompetensi.pdf'],
        ];

        return collect($rows)->map(function (array $row, int $index) use ($content, $disk, $lecturerId, $user): Document {
            $path = 'demo/m8/'.$row[2];
            Storage::disk($disk)->put($path, $content);

            $document = Document::query()->create([
                'lecturer_core_id' => $lecturerId,
                'document_type' => $row[1],
                'title' => $row[0],
                'document_number' => 'DEMO/M8/'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'document_date' => now()->subMonths($index + 1)->toDateString(),
                'issuer' => 'Fakultas Farmasi UBP',
                'disk' => $disk,
                'path' => $path,
                'original_filename' => $row[2],
                'stored_filename' => $row[2],
                'extension' => 'pdf',
                'mime_type' => 'application/pdf',
                'size_bytes' => strlen($content),
                'sha256_checksum' => hash('sha256', $content.$row[2]),
                'uploaded_by_app_user_id' => $user->id,
                'uploaded_by_core_user_id' => (string) $user->core_user_id,
                'source_app' => self::SOURCE,
                'source_record_id' => 'document-'.$index,
                'verification_status' => $index === 0 ? 'OFFICIAL' : 'PENDING',
                'visibility' => $index === 3 ? 'PRIVATE' : 'INTERNAL',
            ]);

            DocumentVersion::query()->create([
                'document_id' => $document->id,
                'version_number' => 1,
                'disk' => $disk,
                'path' => $path,
                'original_filename' => $row[2],
                'mime_type' => 'application/pdf',
                'size_bytes' => strlen($content),
                'sha256_checksum' => $document->sha256_checksum,
                'uploaded_by_app_user_id' => $user->id,
                'source_app' => self::SOURCE,
                'source_record_id' => 'document-version-'.$index,
            ]);

            return $document;
        })->all();
    }

    private function activities(AppUser $user, string $lecturerId, array $categories): array
    {
        $rows = [
            ['pendidikan', 'perkuliahan', 'Mengampu Farmakologi Klinik', 'Koordinator Mata Kuliah', 'ADMIN_VERIFIED', 'MANUAL', null],
            ['pendidikan', 'praktikum', 'Praktikum Teknologi Sediaan Steril', 'Dosen Praktikum', 'SUBMITTED', 'MANUAL', null],
            ['pendidikan', 'pembimbing-tugas-akhir', 'Pembimbing Tugas Akhir Formulasi Gel Herbal', 'Pembimbing Utama', 'SYSTEM_VERIFIED', 'SYSTEM', 'ta-farmasi'],
            ['pendidikan', 'penguji-kp', 'Penguji Seminar Kerja Praktik Industri Farmasi', 'Penguji', 'SYSTEM_VERIFIED', 'SYSTEM', 'kp-farmasi'],
            ['pendidikan', 'bahan-ajar', 'Penyusunan Modul Farmasi Fisik', 'Penulis', 'DRAFT', 'MANUAL', null],
            ['pendidikan', 'pembimbing-akademik', 'Pembimbing Akademik Angkatan 2023', 'Pembimbing Akademik', 'ADMIN_VERIFIED', 'MANUAL', null],
            ['pendidikan', 'penguji-tugas-akhir', 'Penguji Sidang Skripsi Analisis Metabolit', 'Penguji', 'SYSTEM_VERIFIED', 'SYSTEM', 'ta-farmasi'],
            ['pendidikan', 'dosen-tamu', 'Kuliah Tamu Farmakovigilans', 'Narasumber', 'REVISION_REQUIRED', 'MANUAL', null],
            ['penelitian', 'penelitian', 'Riset Ekstrak Daun Lokal sebagai Antioksidan', 'Ketua Peneliti', 'ADMIN_VERIFIED', 'MANUAL', null],
            ['penelitian', 'publikasi-jurnal', 'Publikasi Jurnal Fitokimia Terapan', 'Penulis Pertama', 'SYSTEM_VERIFIED', 'SYSTEM', 'sinta'],
            ['penelitian', 'hki-paten', 'Draft HKI Formula Gel Herbal', 'Inventor', 'SUBMITTED', 'MANUAL', null],
            ['penelitian', 'hibah', 'Proposal Hibah Riset Farmasi Bahan Alam', 'Ketua Pengusul', 'DRAFT', 'MANUAL', null],
            ['penelitian', 'kolaborasi', 'Kolaborasi Riset Farmasi Klinis RS Mitra', 'Koordinator', 'ADMIN_VERIFIED', 'MANUAL', null],
            ['pengabdian', 'kegiatan-pengabdian', 'Edukasi Penggunaan Obat Rasional di Posyandu', 'Ketua Pelaksana', 'ADMIN_VERIFIED', 'MANUAL', null],
            ['pengabdian', 'mitra', 'Pendampingan Kader Kesehatan Desa', 'Fasilitator', 'SUBMITTED', 'MANUAL', null],
            ['pengabdian', 'luaran', 'Booklet Edukasi DAGUSIBU untuk Masyarakat', 'Penulis', 'DRAFT', 'MANUAL', null],
        ];

        return collect($rows)->map(function (array $row, int $index) use ($categories, $lecturerId, $user): PortfolioActivity {
            return PortfolioActivity::query()->create([
                'lecturer_core_id' => $lecturerId,
                'category_id' => $categories[$row[0]]->id,
                'activity_type' => $row[1],
                'title' => $row[2],
                'description' => 'Data demo fiktif untuk mengevaluasi visual M8 pada mode populated.',
                'personal_notes' => 'Catatan demo internal.',
                'lecturer_role' => $row[3],
                'academic_year' => now()->year.'/'.(now()->year + 1),
                'semester' => now()->month >= 8 || now()->month <= 1 ? 'Ganjil' : 'Genap',
                'start_date' => now()->subDays(45 - $index)->toDateString(),
                'end_date' => now()->subDays(40 - $index)->toDateString(),
                'institution_name' => $row[0] === 'pengabdian' ? 'Mitra Kesehatan Karawang' : 'Universitas Buana Perjuangan Karawang',
                'location' => $row[0] === 'pengabdian' ? 'Karawang' : 'Kampus UBP',
                'verification_status' => $row[4],
                'revision_reason' => $row[4] === 'REVISION_REQUIRED' ? 'Mohon lengkapi bukti kegiatan dan peran dosen.' : null,
                'verified_at' => str_contains($row[4], 'VERIFIED') ? now()->subDays(5) : null,
                'verified_by_app_user_id' => str_contains($row[4], 'VERIFIED') ? $user->id : null,
                'source_type' => $row[5],
                'source_app' => self::SOURCE,
                'source_entity' => $row[6] ?: $row[0],
                'source_record_id' => self::SOURCE.'-activity-'.$index,
                'visibility' => 'INTERNAL',
                'created_by_core_user_id' => (string) $user->core_user_id,
                'created_at' => now()->subDays(20 - $index),
                'updated_at' => now()->subDays(2),
            ]);
        })->all();
    }

    private function participants(array $activities): void
    {
        foreach (array_slice($activities, 0, 8) as $index => $activity) {
            PortfolioParticipant::query()->create([
                'portfolio_activity_id' => $activity->id,
                'participant_type' => $index % 2 === 0 ? 'STUDENT' : 'INSTITUTION',
                'student_identifier' => $index % 2 === 0 ? 'MHS'.(202300 + $index) : null,
                'student_name' => $index % 2 === 0 ? 'Mahasiswa Demo '.($index + 1) : null,
                'institution_name' => $index % 2 === 1 ? 'Mitra Demo '.($index + 1) : null,
                'role' => $index % 2 === 0 ? 'Mahasiswa bimbingan' : 'Mitra kegiatan',
                'is_primary' => $index === 0,
                'sort_order' => $index + 1,
                'participant_fingerprint' => hash('sha256', self::SOURCE.'-'.$activity->id.'-'.$index),
            ]);
        }
    }

    private function academicProfile(string $lecturerId): void
    {
        foreach ([
            ['S1', 'Universitas Padjadjaran', 'Farmasi', 'S.Farm', 2009],
            ['Profesi', 'Universitas Padjadjaran', 'Profesi Apoteker', 'Apt.', 2010],
            ['S2', 'Institut Teknologi Bandung', 'Farmasi Klinik', 'M.Farm', 2014],
            ['S3', 'Universitas Indonesia', 'Ilmu Farmasi', 'Dr.', 2021],
        ] as $index => $row) {
            LecturerEducation::query()->create([
                'lecturer_core_id' => $lecturerId,
                'level' => $row[0],
                'institution_name' => $row[1],
                'study_program' => $row[2],
                'degree' => $row[3],
                'start_year' => $row[4] - 4,
                'end_year' => $row[4],
                'graduation_status' => 'LULUS',
                'thesis_title' => $index >= 2 ? 'Kajian fiktif keamanan dan efektivitas sediaan farmasi.' : null,
                'source_type' => 'MANUAL',
                'source_app' => self::SOURCE,
                'source_record_id' => 'education-'.$index,
                'verification_status' => 'VERIFIED',
                'visibility' => 'INTERNAL',
                'sort_order' => $index + 1,
            ]);
        }

        foreach ([['Asisten Ahli', 2016, 150], ['Lektor', 2022, 300]] as $index => $row) {
            LecturerFunctionalPosition::query()->create([
                'lecturer_core_id' => $lecturerId,
                'position_name' => $row[0],
                'effective_date' => $row[1].'-04-01',
                'credit_score' => $row[2],
                'unit' => 'Program Studi Farmasi',
                'is_active' => $index === 1,
                'source_type' => 'MANUAL',
                'source_app' => self::SOURCE,
                'source_record_id' => 'functional-'.$index,
                'verification_status' => 'VERIFIED',
                'visibility' => 'INTERNAL',
            ]);
        }

        LecturerExpertiseArea::query()->create([
            'lecturer_core_id' => $lecturerId,
            'knowledge_family' => 'Kesehatan',
            'primary_expertise' => 'Farmakologi Klinik',
            'specializations' => ['Farmakovigilans', 'Penggunaan Obat Rasional', 'Farmasi Bahan Alam'],
            'research_topics' => ['Keamanan Obat', 'Antioksidan Herbal', 'Edukasi DAGUSIBU'],
            'practical_skills' => ['Konseling Obat', 'Review Literatur', 'Desain Modul Edukasi'],
            'collaboration_interests' => ['Rumah Sakit', 'Puskesmas', 'Industri Farmasi'],
            'is_primary' => true,
            'source_type' => 'MANUAL',
            'source_app' => self::SOURCE,
            'source_record_id' => 'expertise-0',
            'verification_status' => 'VERIFIED',
            'visibility' => 'PUBLIC',
        ]);

        LecturerCertification::query()->create([
            'lecturer_core_id' => $lecturerId,
            'category' => 'profesi',
            'name' => 'Sertifikat Kompetensi Apoteker',
            'issuer' => 'Komite Farmasi Demo',
            'issued_at' => now()->subYears(2)->toDateString(),
            'status' => 'AKTIF',
            'source_type' => 'MANUAL',
            'source_app' => self::SOURCE,
            'source_record_id' => 'certification-0',
            'verification_status' => 'VERIFIED',
            'visibility' => 'INTERNAL',
        ]);

        foreach ([
            ['SINTA', '6789012', 'https://sinta.kemdikbud.go.id/authors/profile/6789012'],
            ['ORCID', '0000-0002-1234-5678', 'https://orcid.org/0000-0002-1234-5678'],
            ['Google Scholar', 'demo-scholar-id', 'https://scholar.google.com/citations?user=demo'],
        ] as $row) {
            LecturerExternalIdentifier::query()->updateOrCreate(
                ['lecturer_core_id' => $lecturerId, 'identifier_type' => $row[0]],
                [
                    'identifier_value' => $row[1],
                    'profile_url' => $row[2],
                    'source_type' => 'MANUAL',
                    'source_app' => self::SOURCE,
                    'source_record_id' => Str::slug($row[0]),
                    'verification_status' => 'VERIFIED',
                    'visibility' => 'PUBLIC',
                ],
            );
        }
    }

    private function inbox(string $lecturerId): void
    {
        foreach ([
            ['INVITATION', 'Undangan Penguji Seminar KP', 'Mahasiswa Farmasi Industri membutuhkan konfirmasi penguji.', 'HIGH'],
            ['REVISION', 'Revisi Portofolio Kuliah Tamu', 'Lengkapi bukti undangan dan dokumentasi kegiatan.', 'HIGH'],
            ['INFO', 'Sinkronisasi TA Berhasil', 'Dua kegiatan bimbingan TA sudah masuk otomatis.', 'NORMAL'],
            ['TASK', 'Lengkapi Identitas Ilmiah', 'Hubungkan profil Google Scholar untuk memperkaya profil publik.', 'NORMAL'],
        ] as $index => $row) {
            InboxItem::query()->create([
                'lecturer_core_id' => $lecturerId,
                'type' => $row[0],
                'title' => $row[1],
                'summary' => $row[2],
                'priority' => $row[3],
                'status' => $index === 2 ? 'READ' : 'UNREAD',
                'read_at' => $index === 2 ? now()->subDay() : null,
                'due_at' => now()->addDays($index + 1),
                'occurred_at' => now()->subHours($index + 3),
                'source_app' => self::SOURCE,
                'source_record_id' => 'inbox-'.$index,
                'metadata' => ['demo' => true],
            ]);
        }
    }

    private function agenda(string $lecturerId): void
    {
        foreach ([
            ['Rapat Koordinasi Praktikum', 'MEETING', 'Ruang Rapat Farmasi', 1],
            ['Seminar Proposal TA', 'EXAM', 'Lab Komputer', 2],
            ['Kunjungan Pengabdian Posyandu', 'SERVICE', 'Desa Mitra Karawang', 4],
            ['Workshop Penulisan Artikel Ilmiah', 'WORKSHOP', 'Aula Fakultas', 7],
        ] as $index => $row) {
            CalendarEvent::query()->create([
                'lecturer_core_id' => $lecturerId,
                'title' => $row[0],
                'description' => 'Agenda demo fiktif untuk visual QA.',
                'event_type' => $row[1],
                'status' => 'SCHEDULED',
                'starts_at' => now()->addDays($row[3])->setTime(9 + $index, 0),
                'ends_at' => now()->addDays($row[3])->setTime(10 + $index, 30),
                'location' => $row[2],
                'source_type' => 'SYSTEM',
                'source_app' => self::SOURCE,
                'source_record_id' => 'agenda-'.$index,
                'metadata' => ['demo' => true],
            ]);
        }
    }

    private function notifications(AppUser $user): void
    {
        foreach ([
            ['Portofolio terverifikasi', 'Mengampu Farmakologi Klinik sudah diverifikasi admin.'],
            ['Agenda baru', 'Seminar Proposal TA ditambahkan ke agenda Anda.'],
            ['Dokumen tersimpan', 'SK Mengajar Farmakologi Klinik berhasil ditautkan.'],
            ['Identitas ilmiah lengkap', 'ORCID dan SINTA sudah terhubung.'],
            ['Perlu revisi', 'Kuliah tamu membutuhkan bukti tambahan.'],
            ['Sinkronisasi berhasil', 'Data TA terbaru masuk ke Tridharma.'],
        ] as $index => $row) {
            DatabaseNotification::query()->create([
                'id' => (string) Str::uuid(),
                'type' => 'm8.ui.demo',
                'notifiable_type' => AppUser::class,
                'notifiable_id' => $user->id,
                'data' => [
                    'title' => $row[0],
                    'message' => $row[1],
                    'source' => self::SOURCE,
                ],
                'read_at' => $index > 3 ? null : now()->subHours($index + 1),
                'created_at' => now()->subHours($index + 1),
                'updated_at' => now()->subHours($index + 1),
            ]);
        }
    }
}
