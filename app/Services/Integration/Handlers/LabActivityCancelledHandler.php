<?php

namespace App\Services\Integration\Handlers;

class LabActivityCancelledHandler extends LabScheduleCreatedHandler
{
    protected string $operation = 'cancelled';
}
