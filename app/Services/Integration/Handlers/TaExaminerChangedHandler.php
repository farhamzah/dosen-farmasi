<?php

namespace App\Services\Integration\Handlers;

class TaExaminerChangedHandler extends TaExaminerAssignedHandler
{
    protected string $operation = 'assignment_changed';
}
