<?php

namespace App\Services\Integration\Handlers;

class TaExaminerAssignedHandler extends TaSupervisorAssignedHandler
{
    protected string $portfolioEntity = 'ta.examiner';

    protected string $eventType = 'TA_EXAMINER';

    protected string $defaultRole = 'PENGUJI';
}
