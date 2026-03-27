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
    public function counterOffer(ServiceJob $job, User $user, float $amount, ?string $message = null): JobNegotiation
    {
        $latest = $this->getLatestNegotiation($job, $user);

        if (!$latest) {
            throw ValidationException::withMessages([
                'negotiation' => ['Only workers can initiate negotiation.']
            ]);
        }

        if ($job->status !== ServiceJob::STATUS_OPEN) {
            throw ValidationException::withMessages([
                'job' => ['This job is no longer open for negotiation.']
            ]);
        }

        if ($latest->status === 'accepted') {
            throw ValidationException::withMessages([
                'negotiation' => ['Negotiation already accepted.']
            ]);
        }

        if ($job->user_id === $user->id && !$latest) {
            throw ValidationException::withMessages([
                'negotiation' => ['No offer to respond to.']
            ]);
        }

        return DB::transaction(function () use ($job, $user, $amount, $message, $latest) {
            $receiver = $latest->created_by === 'client'
                ? $latest->worker
                : $job->client;

            $latest->update([
                'amount'     => $amount,
                'message'    => $message,
                'history'    => $this->appendHistoryEntry($latest),
                'status'     => 'pending',
                'created_by' => $this->resolveActor($job, $user),
            ]);
            $negotiation = $latest->fresh(['job', 'worker']);

            $this->activityLogService->log($user->id, 'negotiation_countered', [
                'job_id' => $job->id,
                'negotiation_id' => $negotiation->id,
                'amount' => $amount,
                'created_by' => $negotiation->created_by,
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
    public function acceptOffer(JobNegotiation $negotiation): JobNegotiation
    {
        return DB::transaction(function () use ($negotiation) {
            $negotiation = JobNegotiation::query()
                ->with(['job', 'worker'])
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

            $this->activityLogService->log($negotiation->client_id, 'negotiation_accepted', [
                'job_id' => $job->id,
                'negotiation_id' => $negotiation->id,
                'worker_id' => $negotiation->worker_id,
                'agreed_amount' => $negotiation->amount,
            ]);

            DB::afterCommit(function () use ($negotiation) {
                $this->notificationService->sendNegotiationUpdate(
                    $negotiation->worker,
                    $negotiation->job,
                    'accepted'
                );
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
    public function rejectOffer(JobNegotiation $negotiation, float $amount, string $message): JobNegotiation
    {
        return DB::transaction(function () use ($negotiation, $amount, $message) {
            $negotiation = JobNegotiation::query()
                ->with(['job', 'worker'])
                ->lockForUpdate()
                ->findOrFail($negotiation->id);

            if ($negotiation->status !== 'pending') {
                throw ValidationException::withMessages([
                    'negotiation' => ['Only pending offers can be rejected.']
                ]);
            }

            $negotiation->update([
                'amount' => $amount,
                'message' => $message,
                'history' => $this->appendHistoryEntry($negotiation),
                'status' => 'rejected',
                'created_by' => 'client',
            ]);

            $this->activityLogService->log($negotiation->client_id, 'negotiation_rejected', [
                'job_id' => $negotiation->job_id,
                'negotiation_id' => $negotiation->id,
                'worker_id' => $negotiation->worker_id,
                'counter_amount' => $amount,
                'message' => $message,
            ]);

            DB::afterCommit(function () use ($negotiation) {
                $this->notificationService->sendNegotiationUpdate(
                    $negotiation->worker,
                    $negotiation->job,
                    'rejected'
                );

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
