<?php

namespace App\Services\Integration\Handlers;

class KpExaminerAssignedHandler extends AcademicSourceEventHandler
{
    protected string $operation = 'assignment';

    protected string $domain = 'KP';

    protected string $portfolioEntity = 'kp.examiner';

    protected string $eventType = 'KP_EXAMINER';

    protected string $defaultRole = 'PENGUJI';
}
