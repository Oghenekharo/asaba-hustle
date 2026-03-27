<?php

namespace App\Listeners;

use App\Services\UserNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWorkerHiredNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    protected $notificationService;

    public function __construct(UserNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $job = $event->job;

        $this->notificationService->create(
            $job->assigned_to,
            'You were hired!',
            'You have been hired for job: ' . $job->title,
            'job_hired'
        );
    }
}
