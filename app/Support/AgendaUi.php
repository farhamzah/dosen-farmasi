<?php

namespace App\Support;

class AgendaUi
{
    public static function typeLabel(?string $type): string
    {
        return match ($type) {
            'MEETING' => 'Rapat',
            'EXAM' => 'Ujian',
            'SERVICE' => 'Pengabdian',
            'WORKSHOP' => 'Lokakarya',
            default => str((string) $type)->replace('_', ' ')->title()->toString(),
        };
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'SCHEDULED' => 'Terjadwal',
            'COMPLETED' => 'Selesai',
            'CANCELLED' => 'Dibatalkan',
            default => str((string) $status)->replace('_', ' ')->title()->toString(),
        };
    }
}
