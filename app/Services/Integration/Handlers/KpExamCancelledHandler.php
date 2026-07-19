<?php

namespace App\Services\Integration\Handlers;

class KpExamCancelledHandler extends KpExamScheduledHandler
{
    protected string $operation = 'cancelled';
}
