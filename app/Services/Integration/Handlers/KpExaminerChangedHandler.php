<?php

namespace App\Services\Integration\Handlers;

class KpExaminerChangedHandler extends KpExaminerAssignedHandler
{
    protected string $operation = 'assignment_changed';
}
