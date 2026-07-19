<?php

namespace App\Services\Integration\Handlers;

class KpPspaActivityCompletedHandler extends KpPspaActivityScheduledHandler
{
    protected string $operation = 'completed';
}
