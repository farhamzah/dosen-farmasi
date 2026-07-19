<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DosenStorageCheckCommand extends Command
{
    protected $signature = 'dosen:storage-check';

    protected $description = 'Check configured private document storage for read/write availability.';

    public function handle(): int
    {
        $diskName = (string) config('dosen_farmasi.documents.disk', 'shared_private');
        $path = 'health/'.Str::uuid().'.txt';
        $disk = Storage::disk($diskName);

        if (! $disk->put($path, 'ok')) {
            $this->error("Disk {$diskName} is not writable.");

            return self::FAILURE;
        }

        $content = $disk->get($path);
        $disk->delete($path);

        if ($content !== 'ok') {
            $this->error("Disk {$diskName} write/read check failed.");

            return self::FAILURE;
        }

        $this->info("Disk {$diskName} is readable and writable.");

        return self::SUCCESS;
    }
}
