<?php

namespace App\Listeners;

use App\Models\ScheduleLog;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Events\Dispatcher;

class ScheduleLogSubscriber
{
    public function handleTaskFinished(ScheduledTaskFinished $event): void
    {
        ScheduleLog::create([
            'command_name'   => $this->formatCommandName($event->task),
            'status'         => 'Success',
            'error_message'  => null,
            'execution_date' => now()->toDateString(),
        ]);
    }

    public function handleTaskFailed(ScheduledTaskFailed $event): void
    {
        $errorMessage = $event->exception ? $event->exception->getMessage() : 'Unknown Error';

        ScheduleLog::create([
            'command_name'   => $this->formatCommandName($event->task),
            'status'         => 'Failed',
            'error_message'  => $errorMessage,
            'execution_date' => now()->toDateString(),
        ]);
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            ScheduledTaskFinished::class,
            [ScheduleLogSubscriber::class, 'handleTaskFinished']
        );

        $events->listen(
            ScheduledTaskFailed::class,
            [ScheduleLogSubscriber::class, 'handleTaskFailed']
        );
    }

    private function formatCommandName($task): string
    {
        if (!empty($task->description)) {
            return $task->description;
        }

        if ($task->command) {
            preg_match('/\'artisan\'\s+([a-zA-Z0-9:-]+)/', $task->command, $matches);
            if (isset($matches[1])) {
                return $matches[1];
            }
        }

        return 'Closure Task';
    }
}