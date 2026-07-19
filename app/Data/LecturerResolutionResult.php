<?php

namespace App\Data;

class LecturerResolutionResult
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $lecturerCoreId = null,
        public readonly ?string $message = null,
        public readonly array $candidates = [],
    ) {}

    public function resolved(): bool
    {
        return $this->status === 'resolved' && filled($this->lecturerCoreId);
    }
}
