<?php

namespace App\Services\Integration\Handlers;

class LabScheduleRescheduledHandler extends LabScheduleCreatedHandler
{
    protected string $operation = 'reschedule';
}
