<?php

namespace App\Services;

use App\Events\NotificationBroadcasted;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;

class UserNotificationService
{

    public function create($userId, $title, $message, $type = null, $actionUrl = null, $actionLabel = null)
    {
        return DB::transaction(function () use ($userId, $title, $message, $type, $actionUrl, $actionLabel) {
            $notification = UserNotification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'action_url' => $actionUrl,
                'action_label' => $actionLabel,
            ]);

            DB::afterCommit(function () use ($notification) {
                event(new NotificationBroadcasted($notification));
            });

            return $notification;
        });
    }

    public function sendNegotiationUpdate($user, $job, $type, $data = [])
    {
        return $this->create(
            $user->id,
            match ($type) {
                'new_offer' => 'New Offer Received',
                'counter_offer' => 'Offer Updated',
                'accepted' => 'Offer Accepted',
                'counter_offer_sent' => 'Counter Offer Sent',
                'rejected' => 'Offer Rejected',
                default => 'Negotiation Update',
            },
            match ($type) {
                'new_offer' => 'You have received a new offer on a job.',
                'counter_offer' => 'The offer has been updated.',
                'accepted' => 'Your offer has been accepted.',
                'counter_offer_sent' => 'A counter offer was sent on this negotiation.',
                'rejected' => 'Your offer was rejected.',
                default => 'There is an update on your negotiation.',
            },
            'negotiation',
            // array_merge([
            //     'job_id' => $job->id,
            // ], $data),
        );
    }
}
