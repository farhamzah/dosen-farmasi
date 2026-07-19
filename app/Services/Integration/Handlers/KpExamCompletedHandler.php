<?php

namespace App\Services\Integration\Handlers;

class KpExamCompletedHandler extends KpExamScheduledHandler
{
    protected string $operation = 'completed';
}
