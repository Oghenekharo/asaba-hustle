<?php

namespace App\Listeners;

use App\Services\UserNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentNotification implements ShouldQueue
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
        $payment = $event->payment;

        $this->notificationService->create(
            $payment->user_id,
            'Payment successful',
            'Your payment was completed successfully.',
            'payment'
        );
    }
}
