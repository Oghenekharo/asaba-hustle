<?php

namespace App\Policies;

use App\Models\ServiceJob;
use App\Models\User;

class ServiceJobPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('client');
    }

    public function apply(User $user, ServiceJob $job): bool
    {
        return $user->hasRole('worker')
            && $user->availability_status === 'available'
            && $user->id !== $job->user_id
            && $job->status === ServiceJob::STATUS_OPEN
            && $job->assigned_to === null;
    }

    public function hire(User $user, ServiceJob $job): bool
    {
        return $user->id === $job->user_id;
    }

    public function accept(User $user, ServiceJob $job): bool
    {
        return $user->id === $job->assigned_to
            && $job->status === ServiceJob::STATUS_ASSIGNED;
    }

    public function rejectAssignment(User $user, ServiceJob $job): bool
    {
        return $user->id === $job->assigned_to
            && $job->status === ServiceJob::STATUS_ASSIGNED;
    }

    public function start(User $user, ServiceJob $job): bool
    {
        return $user->id === $job->assigned_to;
    }

    public function complete(User $user, ServiceJob $job): bool
    {
        return $user->id === $job->assigned_to
            && $job->status === ServiceJob::STATUS_IN_PROGRESS;
    }

    public function markPaid(User $user, ServiceJob $job): bool
    {
        return $user->id === $job->user_id
            && $job->status === ServiceJob::STATUS_PAYMENT_PENDING;
    }

    public function confirmPayment(User $user, ServiceJob $job): bool
    {
        return $user->id === $job->assigned_to
            && $job->status === ServiceJob::STATUS_PAYMENT_PENDING
            && $job->paid_at !== null;
    }

    public function rate(User $user, ServiceJob $job): bool
    {
        return $user->id === $job->user_id
            && $job->status === ServiceJob::STATUS_COMPLETED
            && $job->rating === null;
    }

    public function message(User $user, ServiceJob $job): bool
    {
        if ($user->id === $job->user_id) {
            return $job->assigned_to !== null;
        }

        if ($user->hasRole('worker')) {
            return $job->assigned_to === $user->id
                || $job->applications()
                    ->where('user_id', $user->id)
                    ->exists();
        }

        return false;
    }

    public function suggestedWorkers(User $user, ServiceJob $job): bool
    {
        return $user->id === $job->user_id;
    }

    public function cancelByAdmin(User $user, ServiceJob $job): bool
    {
        return $user->hasRole('admin')
            && in_array($job->status, ServiceJob::adminCancellableStatuses(), true);
    }

    public function rollbackByAdmin(User $user, ServiceJob $job): bool
    {
        return $user->hasRole('admin')
            && !empty(ServiceJob::adminRollbackTargets($job->status));
    }
}
