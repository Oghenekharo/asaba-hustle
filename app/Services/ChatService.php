<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ChatMessage;
use App\Models\ServiceJob;
use App\Events\ChatMessageBroadcasted;
use App\Services\UserNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChatService
{
    public function __construct(
        protected UserNotificationService $notificationService,
    ) {}

    public function startConversation(ServiceJob $job, $user)
    {
        $workerId = null;

        if ($user->hasRole('worker')) {
            $workerId = $user->id;
        } elseif ((int) $user->id === (int) $job->user_id && $job->assigned_to) {
            $workerId = $job->assigned_to;
        }

        if (!in_array($job->status, ServiceJob::chatEligibleStatuses(), true)) {
            throw ValidationException::withMessages([
                'chat' => ['Chat is only available after a worker has been hired.']
            ]);
        }

        if (!$workerId) {
            throw new \RuntimeException('Conversation cannot be started for this job yet.');
        }

        return Conversation::firstOrCreate([
            'job_id' => $job->id,
            'client_id' => $job->user_id,
            'worker_id' => $workerId,
        ]);
    }

    public function sendMessage($user, ServiceJob $job, $message, ?Conversation $conversation = null)
    {
        $conversation = $conversation ?: $this->startConversation($job, $user);
        $conversation->loadMissing('job');

        if (!in_array($conversation->job->status, ServiceJob::chatEligibleStatuses(), true)) {
            throw ValidationException::withMessages([
                'chat' => ['This conversation is not active yet.']
            ]);
        }

        return DB::transaction(function () use ($user, $job, $message, $conversation) {
            $chatMessage = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'message' => $message
            ]);

            $chatMessage->load(['sender', 'conversation']);

            DB::afterCommit(function () use ($chatMessage) {
                event(new ChatMessageBroadcasted($chatMessage));

                $conversation = $chatMessage->conversation;
                $receiverId = (int) $chatMessage->sender_id === (int) $conversation->client_id
                    ? $conversation->worker_id
                    : $conversation->client_id;

                if ($receiverId) {
                    $this->notificationService->create(
                        $receiverId,
                        'New message',
                        $chatMessage->message,
                        'message',
                        route('web.app.conversations', ['conversation' => $conversation->uuid]),
                        'Open Chat',
                    );
                }
            });

            return $chatMessage;
        });
    }
}
