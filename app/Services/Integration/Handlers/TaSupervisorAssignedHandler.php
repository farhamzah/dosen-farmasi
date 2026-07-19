<?php

namespace App\Services\Integration\Handlers;

class TaSupervisorAssignedHandler extends AcademicSourceEventHandler
{
    protected string $operation = 'assignment';

    protected string $domain = 'TA';

    protected string $portfolioEntity = 'ta.supervisor';

    protected string $eventType = 'TA_SUPERVISOR';

    protected string $defaultRole = 'PEMBIMBING';
}
