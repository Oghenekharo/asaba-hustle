<?php

namespace App\Notifications;

use App\Support\NotificationType;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class RatingReceivedNotification extends Notification
{
    public function __construct(
        public string $raterName,
        public float $score,
        public string $jobTitle,
        public string $url
    ) {}

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    // public function toDatabase($notifiable)
    // {
    //     return [
    //         'title' => 'You received a rating',
    //         'body' => "{$this->raterName} rated you {$this->score}⭐ for \"{$this->jobTitle}\".",
    //         'url' => $this->url,
    //         'type' => NotificationType::RATING,
    //         'score' => $this->score,
    //     ];
    // }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('⭐ New Rating Received')
            ->body("{$this->raterName} rated you {$this->score}⭐")
            ->data([
                'url' => $this->url,
                'type' => NotificationType::RATING,
            ]);
    }
}
