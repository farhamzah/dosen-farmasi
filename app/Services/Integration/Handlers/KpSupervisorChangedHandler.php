<?php

namespace App\Services\Integration\Handlers;

class KpSupervisorChangedHandler extends KpSupervisorAssignedHandler
{
    protected string $operation = 'assignment_changed';
}
