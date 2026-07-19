<?php

namespace App\Services\Integration\Handlers;

class KpExamScheduledHandler extends AcademicSourceEventHandler
{
    protected string $operation = 'schedule';

    protected string $domain = 'KP';

    protected string $portfolioEntity = 'kp.exam';

    protected string $eventType = 'UJIAN_KP';

    protected string $defaultRole = 'PENGUJI';
}
