<?php

namespace App\Services;

use App\Events\NegotiationUpdated;
use App\Models\JobNegotiation;
use App\Models\ServiceJob;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NegotiationService
{
    public function __construct(
        protected UserNotificationService $notificationService,
        protected ActivityLogService $activityLogService,
    ) {}
    /*
    |--------------------------------------------------------------------------
    | Create Initial Offer (on apply)
    |--------------------------------------------------------------------------
    */
    public function createInitialOffer(ServiceJob $job, User $worker, float $amount, ?string $message = null): JobNegotiation
    {
        if ($job->status !== ServiceJob::STATUS_OPEN) {
            throw ValidationException::withMessages([
                'job' => ['This job is no longer open for negotiation.']
            ]);
        }

        if ($job->user_id === $worker->id) {
            throw ValidationException::withMessages([
                'negotiation' => ['Clients cannot initiate negotiation.']
            ]);
        }

        $alreadyExists = JobNegotiation::where('job_id', $job->id)
            ->where('worker_id', $worker->id)
            ->exists();

        if ($alreadyExists) {
            throw ValidationException::withMessages([
                'negotiation' => ['You have already submitted an offer.']
            ]);
        }

        return DB::transaction(function () use ($job, $worker, $amount, $message) {
            $negotiation = JobNegotiation::create([
                'job_id'     => $job->id,
                'client_id'  => $job->user_id,
                'worker_id'  => $worker->id,
                'amount'     => $amount,
                'message'    => $message,
                'history'    => [],
                'status'     => 'pending',
                'created_by' => 'worker',
            ]);

            $this->activityLogService->log($worker->id, 'negotiation_created', [
                'job_id' => $job->id,
                'negotiation_id' => $negotiation->id,
                'client_id' => $job->user_id,
                'amount' => $amount,
                'created_by' => 'worker',
            ]);

            DB::afterCommit(function () use ($job, $negotiation) {
                $this->notificationService->sendNegotiationUpdate(
                    $job->client,
                    $job,
                    'new_offer'
                );
                event(new NegotiationUpdated($negotiation));
            });

            return $negotiation;
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Counter Offer
    |--------------------------------------------------------------------------
    */
    public function counterOffer(JobNegotiation $negotiation, User $user, float $amount, ?string $message = null): JobNegotiation
    {
        $negotiation->loadMissing(['job', 'worker', 'client']);
        $job = $negotiation->job;

        if ($job->status !== ServiceJob::STATUS_OPEN) {
            throw ValidationException::withMessages([
                'job' => ['This job is no longer open for negotiation.']
            ]);
        }

        if ($negotiation->status !== 'pending') {
            throw ValidationException::withMessages([
                'negotiation' => ['Only pending offers can be countered.']
            ]);
        }

        $actor = $this->resolveActor($job, $user);

        if (!in_array($actor, ['client', 'worker'], true)) {
            throw ValidationException::withMessages([
                'negotiation' => ['You are not part of this negotiation.']
            ]);
        }

        if ($negotiation->created_by === $actor) {
            throw ValidationException::withMessages([
                'negotiation' => ['Wait for the other party before sending another counter offer.']
            ]);
        }

        return DB::transaction(function () use ($job, $user, $amount, $message, $negotiation, $actor) {
            $receiver = $actor === 'client' ? $negotiation->worker : $job->client;

            $negotiation->update([
                'amount'     => $amount,
                'message'    => $message,
                'history'    => $this->appendHistoryEntry($negotiation),
                'status'     => 'pending',
                'created_by' => $actor,
            ]);
            $negotiation = $negotiation->fresh(['job', 'worker', 'client']);

            $this->activityLogService->log($user->id, 'negotiation_countered', [
                'job_id' => $job->id,
                'negotiation_id' => $negotiation->id,
                'amount' => $amount,
                'created_by' => $actor,
                'counterparty_id' => $receiver?->id,
            ]);

            DB::afterCommit(function () use ($job, $receiver, $negotiation) {
                $this->notificationService->sendNegotiationUpdate(
                    $receiver,
                    $job,
                    'counter_offer'
                );

                event(new NegotiationUpdated($negotiation));
            });

            return $negotiation;
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Accept Offer (THIS IS CRITICAL)
    |--------------------------------------------------------------------------
    */
    public function acceptOffer(JobNegotiation $negotiation, User $user): JobNegotiation
    {
        return DB::transaction(function () use ($negotiation, $user) {
            $negotiation = JobNegotiation::query()
                ->with(['job', 'worker', 'client'])
                ->lockForUpdate()
                ->findOrFail($negotiation->id);

            $job = ServiceJob::query()
                ->lockForUpdate()
                ->findOrFail($negotiation->job_id);

            if ($job->status !== ServiceJob::STATUS_OPEN) {
                throw ValidationException::withMessages([
                    'job' => ['This job is no longer open for negotiation.']
                ]);
            }

            if ($negotiation->status !== 'pending') {
                throw ValidationException::withMessages([
                    'negotiation' => ['Only pending offers can be accepted.']
                ]);
            }

            $actor = $this->resolveActor($job, $user);

            if (!in_array($actor, ['client', 'worker'], true)) {
                throw ValidationException::withMessages([
                    'negotiation' => ['You are not part of this negotiation.']
                ]);
            }

            if ($negotiation->created_by === $actor) {
                throw ValidationException::withMessages([
                    'negotiation' => ['You cannot accept your own latest offer.']
                ]);
            }

            // Mark accepted
            $negotiation->update([
                'status' => 'accepted'
            ]);

            // Reject others
            JobNegotiation::where('job_id', $negotiation->job_id)
                ->where('id', '!=', $negotiation->id)
                ->update(['status' => 'rejected']);

            // 🔥 IMPORTANT: Transition Job
            $job->update([
                'status'       => ServiceJob::STATUS_ASSIGNED,
                'assigned_to'  => $negotiation->worker_id,
                'agreed_amount' => $negotiation->amount,
            ]);

            $this->activityLogService->log($user->id, 'negotiation_accepted', [
                'job_id' => $job->id,
                'negotiation_id' => $negotiation->id,
                'worker_id' => $negotiation->worker_id,
                'agreed_amount' => $negotiation->amount,
                'accepted_by' => $actor,
            ]);

            DB::afterCommit(function () use ($negotiation) {
                $recipient = $negotiation->created_by === 'worker' ? $negotiation->worker : $negotiation->client;
                $this->notificationService->sendNegotiationUpdate($recipient, $negotiation->job, 'accepted');
                event(new NegotiationUpdated($negotiation));
            });

            return $negotiation->fresh(['job', 'worker']);
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Reject Offer
    |--------------------------------------------------------------------------
    */
    public function rejectOffer(JobNegotiation $negotiation, User $user, ?string $message = null): JobNegotiation
    {
        return DB::transaction(function () use ($negotiation, $user, $message) {
            $negotiation = JobNegotiation::query()
                ->with(['job', 'worker', 'client'])
                ->lockForUpdate()
                ->findOrFail($negotiation->id);

            if ($negotiation->status !== 'pending') {
                throw ValidationException::withMessages([
                    'negotiation' => ['Only pending offers can be rejected.']
                ]);
            }

            $actor = $this->resolveActor($negotiation->job, $user);

            if (!in_array($actor, ['client', 'worker'], true)) {
                throw ValidationException::withMessages([
                    'negotiation' => ['You are not part of this negotiation.']
                ]);
            }

            if ($negotiation->created_by === $actor) {
                throw ValidationException::withMessages([
                    'negotiation' => ['You cannot reject your own latest offer.']
                ]);
            }

            $negotiation->update([
                'message' => $message,
                'history' => $this->appendHistoryEntry($negotiation),
                'status' => 'rejected',
            ]);

            $this->activityLogService->log($user->id, 'negotiation_rejected', [
                'job_id' => $negotiation->job_id,
                'negotiation_id' => $negotiation->id,
                'worker_id' => $negotiation->worker_id,
                'message' => $message,
                'rejected_by' => $actor,
            ]);

            DB::afterCommit(function () use ($negotiation) {
                $recipient = $negotiation->created_by === 'worker' ? $negotiation->worker : $negotiation->client;
                $this->notificationService->sendNegotiationUpdate($recipient, $negotiation->job, 'rejected');

                event(new NegotiationUpdated($negotiation));
            });

            return $negotiation->fresh(['job', 'worker']);
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function getLatestNegotiation(ServiceJob $job, User $user): ?JobNegotiation
    {
        return JobNegotiation::where('job_id', $job->id)
            ->where(function ($q) use ($user) {
                $q->where('client_id', $user->id)
                    ->orWhere('worker_id', $user->id);
            })
            ->latest('id')
            ->first();
    }


    protected function resolveActor(ServiceJob $job, User $user): string
    {
        return $job->user_id === $user->id ? 'client' : 'worker';
    }

    protected function appendHistoryEntry(JobNegotiation $negotiation): array
    {
        $history = $negotiation->history ?? [];
        $history[] = [
            'amount' => (float) $negotiation->amount,
            'message' => $negotiation->message,
            'status' => $negotiation->status,
            'created_by' => $negotiation->created_by,
            'recorded_at' => now()->toIso8601String(),
            'previous_updated_at' => optional($negotiation->updated_at)->toIso8601String(),
        ];

        return $history;
    }
}
