<?php

namespace App\Services\Integration\Handlers;

class KpPspaActivityScheduledHandler extends AcademicSourceEventHandler
{
    protected string $operation = 'schedule';

    protected string $domain = 'KP PSPA';

    protected string $portfolioEntity = 'kpspa.activity';

    protected string $eventType = 'PKPA_ACTIVITY';

    protected string $defaultRole = 'PEMBIMBING_PKPA';
}
