<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;
use App\Models\JobNegotiation;
use App\Models\ServiceJob;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('conversation.{conversationUuid}', function ($user, $conversationUuid) {
    $conversation = Conversation::query()
        ->where('uuid', $conversationUuid)
        ->first();

    if (!$conversation) {
        return false;
    }

    return $conversation->client_id === $user->id ||
        $conversation->worker_id === $user->id;
});

Broadcast::channel('job.{jobId}', function ($user, $jobId) {
    $job = ServiceJob::query()->find($jobId);

    if (!$job) {
        return false;
    }

    $isNegotiating = JobNegotiation::where('job_id', $jobId)
        ->where('worker_id', $user->id)
        ->exists();

    return (int) $job->user_id === (int) $user->id
        || (int) $job->assigned_to === (int) $user->id || $isNegotiating;
});
