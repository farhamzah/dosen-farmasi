<?php

namespace App\Services\Integration\Handlers;

class LabScheduleCreatedHandler extends AcademicSourceEventHandler
{
    protected string $operation = 'schedule';

    protected string $domain = 'Lab Farmasi';

    protected string $portfolioEntity = 'lab.activity';

    protected string $eventType = 'PRAKTIKUM';

    protected string $defaultRole = 'DOSEN_PRAKTIKUM';
}
