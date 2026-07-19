<?php

namespace App\Services\Integration\Handlers;

class KpPspaSupervisorAssignedHandler extends AcademicSourceEventHandler
{
    protected string $operation = 'assignment';

    protected string $domain = 'KP PSPA';

    protected string $portfolioEntity = 'kpspa.supervisor';

    protected string $eventType = 'KPSPA_SUPERVISOR';

    protected string $defaultRole = 'PEMBIMBING_PKPA';
}
