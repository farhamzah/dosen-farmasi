<?php

namespace App\Services\Integration\Handlers;

class LabActivityCompletedHandler extends LabScheduleCreatedHandler
{
    protected string $operation = 'completed';
}
