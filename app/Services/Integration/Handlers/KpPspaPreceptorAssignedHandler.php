<?php

namespace App\Services\Integration\Handlers;

class KpPspaPreceptorAssignedHandler extends KpPspaSupervisorAssignedHandler
{
    protected string $portfolioEntity = 'kpspa.preceptor';

    protected string $eventType = 'KPSPA_PRECEPTOR';

    protected string $defaultRole = 'PRECEPTOR';
}
