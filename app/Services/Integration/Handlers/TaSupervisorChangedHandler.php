<?php

namespace App\Services\Integration\Handlers;

class TaSupervisorChangedHandler extends TaSupervisorAssignedHandler
{
    protected string $operation = 'assignment_changed';
}
