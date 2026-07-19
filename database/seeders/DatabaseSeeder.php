<?php

namespace Database\Seeders;

use App\Models\IntegrationClient;
use App\Models\PortfolioCategory;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Pendidikan dan Pengajaran',
            'Penelitian dan Pengembangan',
            'Pengabdian kepada Masyarakat',
            'Penunjang',
            'Pengembangan Kompetensi',
            'Penghargaan',
            'Organisasi dan Kepanitiaan',
        ];

        foreach ($categories as $index => $name) {
            PortfolioCategory::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $index + 1, 'is_active' => true],
            );
        }

        foreach (['BKD', 'SISTER', 'Publikasi', 'HKI', 'Akreditasi', 'Kolaborasi'] as $name) {
            Tag::query()->updateOrCreate(
                ['normalized_name' => Tag::normalize($name)],
                ['name' => $name, 'is_active' => true],
            );
        }

        foreach (config('dosen_farmasi.integration.clients') as $code) {
            IntegrationClient::query()->updateOrCreate(
                ['app_code' => $code],
                [
                    'code' => $code,
                    'name' => str($code)->replace('-', ' ')->title()->toString(),
                    'allowed_abilities' => config('dosen_farmasi.integration.abilities'),
                    'abilities' => config('dosen_farmasi.integration.abilities'),
                    'is_active' => false,
                ],
            );
        }
    }
}
