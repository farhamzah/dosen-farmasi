<?php

namespace App\Services\Integration\Handlers;

class KpPspaExaminerAssignedHandler extends KpPspaSupervisorAssignedHandler
{
    protected string $portfolioEntity = 'kpspa.examiner';

    protected string $eventType = 'KPSPA_EXAMINER';

    protected string $defaultRole = 'PENGUJI_PKPA';
}
