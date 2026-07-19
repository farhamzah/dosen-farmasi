<?php

namespace App\Services\Integration\Handlers;

class KpPspaActivityCancelledHandler extends KpPspaActivityScheduledHandler
{
    protected string $operation = 'cancelled';
}
