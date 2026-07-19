<?php

namespace App\Services\Integration\Handlers;

class KpPspaAssessmentFinalizedHandler extends KpPspaActivityScheduledHandler
{
    protected string $operation = 'completed';

    protected string $portfolioEntity = 'kpspa.assessment';

    protected string $eventType = 'PKPA_ASSESSMENT';
}
