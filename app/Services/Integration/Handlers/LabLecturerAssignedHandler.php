<?php

namespace App\Services\Integration\Handlers;

class LabLecturerAssignedHandler extends AcademicSourceEventHandler
{
    protected string $operation = 'assignment';

    protected string $domain = 'Lab Farmasi';

    protected string $portfolioEntity = 'lab.assignment';

    protected string $eventType = 'LAB_ASSIGNMENT';

    protected string $defaultRole = 'DOSEN_PRAKTIKUM';
}
