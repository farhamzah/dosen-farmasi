<?php

namespace App\Services\Integration\Handlers;

class KpSupervisorAssignedHandler extends AcademicSourceEventHandler
{
    protected string $operation = 'assignment';

    protected string $domain = 'KP';

    protected string $portfolioEntity = 'kp.supervisor';

    protected string $eventType = 'KP_SUPERVISOR';

    protected string $defaultRole = 'PEMBIMBING_DALAM';
}
