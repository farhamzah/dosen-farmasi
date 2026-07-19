<?php

namespace App\Services;

use App\Services\Integration\Handlers\KpExamCancelledHandler;
use App\Services\Integration\Handlers\KpExamCompletedHandler;
use App\Services\Integration\Handlers\KpExaminerAssignedHandler;
use App\Services\Integration\Handlers\KpExaminerChangedHandler;
use App\Services\Integration\Handlers\KpExamRescheduledHandler;
use App\Services\Integration\Handlers\KpExamScheduledHandler;
use App\Services\Integration\Handlers\KpPspaActivityCancelledHandler;
use App\Services\Integration\Handlers\KpPspaActivityCompletedHandler;
use App\Services\Integration\Handlers\KpPspaActivityScheduledHandler;
use App\Services\Integration\Handlers\KpPspaAssessmentFinalizedHandler;
use App\Services\Integration\Handlers\KpPspaExaminerAssignedHandler;
use App\Services\Integration\Handlers\KpPspaPreceptorAssignedHandler;
use App\Services\Integration\Handlers\KpPspaSupervisorAssignedHandler;
use App\Services\Integration\Handlers\KpSupervisorAssignedHandler;
use App\Services\Integration\Handlers\KpSupervisorChangedHandler;
use App\Services\Integration\Handlers\LabActivityCancelledHandler;
use App\Services\Integration\Handlers\LabActivityCompletedHandler;
use App\Services\Integration\Handlers\LabLecturerAssignedHandler;
use App\Services\Integration\Handlers\LabScheduleCreatedHandler;
use App\Services\Integration\Handlers\LabScheduleRescheduledHandler;
use App\Services\Integration\Handlers\TaExamCancelledHandler;
use App\Services\Integration\Handlers\TaExamCompletedHandler;
use App\Services\Integration\Handlers\TaExaminerAssignedHandler;
use App\Services\Integration\Handlers\TaExaminerChangedHandler;
use App\Services\Integration\Handlers\TaExamRescheduledHandler;
use App\Services\Integration\Handlers\TaExamScheduledHandler;
use App\Services\Integration\Handlers\TaSupervisorAssignedHandler;
use App\Services\Integration\Handlers\TaSupervisorChangedHandler;
use App\Services\Integration\Handlers\TuLetterAssignedHandler;
use App\Services\Integration\Handlers\TuLetterCancelledHandler;
use App\Services\Integration\Handlers\TuLetterPublishedHandler;

class IntegrationEventRegistry
{
    public function handlers(): array
    {
        return [
            'tu.letter.assigned' => TuLetterAssignedHandler::class,
            'tu.letter.published' => TuLetterPublishedHandler::class,
            'tu.letter.cancelled' => TuLetterCancelledHandler::class,
            'ta.exam.scheduled' => TaExamScheduledHandler::class,
            'ta.exam.rescheduled' => TaExamRescheduledHandler::class,
            'ta.exam.completed' => TaExamCompletedHandler::class,
            'ta.exam.cancelled' => TaExamCancelledHandler::class,
            'ta.supervisor.assigned' => TaSupervisorAssignedHandler::class,
            'ta.supervisor.changed' => TaSupervisorChangedHandler::class,
            'ta.examiner.assigned' => TaExaminerAssignedHandler::class,
            'ta.examiner.changed' => TaExaminerChangedHandler::class,
            'kp.supervisor.assigned' => KpSupervisorAssignedHandler::class,
            'kp.supervisor.changed' => KpSupervisorChangedHandler::class,
            'kp.examiner.assigned' => KpExaminerAssignedHandler::class,
            'kp.examiner.changed' => KpExaminerChangedHandler::class,
            'kp.exam.scheduled' => KpExamScheduledHandler::class,
            'kp.exam.rescheduled' => KpExamRescheduledHandler::class,
            'kp.exam.completed' => KpExamCompletedHandler::class,
            'kp.exam.cancelled' => KpExamCancelledHandler::class,
            'kpspa.supervisor.assigned' => KpPspaSupervisorAssignedHandler::class,
            'kpspa.preceptor.assigned' => KpPspaPreceptorAssignedHandler::class,
            'kpspa.examiner.assigned' => KpPspaExaminerAssignedHandler::class,
            'kpspa.activity.scheduled' => KpPspaActivityScheduledHandler::class,
            'kpspa.activity.completed' => KpPspaActivityCompletedHandler::class,
            'kpspa.activity.cancelled' => KpPspaActivityCancelledHandler::class,
            'kpspa.assessment.finalized' => KpPspaAssessmentFinalizedHandler::class,
            'lab.lecturer.assigned' => LabLecturerAssignedHandler::class,
            'lab.schedule.created' => LabScheduleCreatedHandler::class,
            'lab.schedule.rescheduled' => LabScheduleRescheduledHandler::class,
            'lab.activity.completed' => LabActivityCompletedHandler::class,
            'lab.activity.cancelled' => LabActivityCancelledHandler::class,
        ];
    }

    public function isKnown(string $eventType): bool
    {
        return array_key_exists($eventType, $this->handlers());
    }

    public function handlerFor(string $eventType): ?object
    {
        $class = $this->handlers()[$eventType] ?? null;

        return $class ? app($class) : null;
    }
}
