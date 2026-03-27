<?php

namespace App\Services;

use App\Events\JobStatusUpdated;
use App\Events\WorkerHired;
use App\Models\ServiceJob;
use App\Models\JobApplication;
use App\Models\JobNegotiation;
use App\Models\Payment;
use App\Models\Rating;
use App\Models\User;
use App\Services\UserNotificationService;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JobService
{
    public function __construct(
        protected UserNotificationService $notificationService,
        protected NegotiationService $negotiationService,
        protected ActivityLogService $activityLogService,
    ) {}

    public function createJob($user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            $job = ServiceJob::create([
                'user_id' => $user->id,
                ...$data
            ]);

            $this->activityLogService->log($user->id, 'job_created', [
                'job_id' => $job->id,
                'title' => $job->title,
                'budget' => $job->budget,
                'payment_method' => $job->payment_method,
                'status' => $job->status,
            ]);

            DB::afterCommit(function () {
                Cache::forget(CacheKeys::ADMIN_DASHBOARD_METRICS);
            });

            return $job;
        });
    }

    protected function broadcastJobStatusUpdate(ServiceJob $job): void
    {
        DB::afterCommit(function () use ($job) {
            event(new JobStatusUpdated($job->fresh(['client', 'worker', 'skill', 'rating'])));
        });
    }

    public function updateAvailability($user, $status)
    {
        $user->update([
            'availability_status' => $status
        ]);

        return $user;
    }

    public function applyToJob($user, ServiceJob $job, $message = null, float $amount = 0)
    {
        return DB::transaction(function () use ($user, $job, $message, $amount) {
            $application = JobApplication::create([
                'job_id' => $job->id,
                'user_id' => $user->id,
                'message' => $message
            ]);

            $this->negotiationService->createInitialOffer($job, $user, $amount, $message);

            $this->activityLogService->log($user->id, 'job_application_submitted', [
                'job_id' => $job->id,
                'application_id' => $application->id,
                'client_id' => $job->user_id,
                'offered_amount' => $amount,
            ]);

            DB::afterCommit(function () use ($job, $user) {
                $this->notificationService->create(
                    $job->user_id,
                    'New job application',
                    $user->name . ' applied for "' . $job->title . '".',
                    'job_application',
                    route('web.app.jobs.show', $job),
                    'View Job'
                );
            });

            return $application;
        });
    }

    // public function hireWorker(ServiceJob $job, $workerId)
    // {
    //     $job->update([
    //         'assigned_to' => $workerId,
    //         'status' => 'assigned'
    //     ]);



    //     return $job;
    // }

    public function hireWorker(ServiceJob $job, int $workerId)
    {
        return DB::transaction(function () use ($job, $workerId) {

            $job = ServiceJob::where('id', $job->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($job->status !== ServiceJob::STATUS_OPEN) {
                throw new \Exception('Job is no longer available for hiring.');
            }

            $worker = User::where('id', $workerId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($worker->availability_status === 'offline') {
                throw new \Exception('Worker is currently offline.');
            }

            if ($worker->availability_status === 'busy') {
                throw new \Exception('Worker is currently busy.');
            }

            // Prevent worker having multiple active jobs
            $activeJobExists = ServiceJob::where('assigned_to', $worker->id)
                ->whereIn('status', [
                    ServiceJob::STATUS_ASSIGNED,
                    ServiceJob::STATUS_WORKER_ACCEPTED,
                    ServiceJob::STATUS_IN_PROGRESS,
                    ServiceJob::STATUS_PAYMENT_PENDING,
                ])
                ->exists();

            if ($activeJobExists) {
                throw new \Exception('Worker already has an active job.');
            }

            $negotiation = JobNegotiation::query()
                ->where('job_id', $job->id)
                ->where('worker_id', $workerId)
                ->where('status', 'pending')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$negotiation) {
                throw new \Exception('No pending negotiation was found for the selected worker.');
            }

            $this->negotiationService->acceptOffer($negotiation);

            $job->applications()->where('user_id', $worker->id)->update([
                'status' => 'accepted',
            ]);

            $job->applications()->where('user_id', '!=', $worker->id)->update([
                'status' => 'rejected',
            ]);

            $worker->update([
                'availability_status' => 'busy'
            ]);

            $this->activityLogService->log($job->user_id, 'worker_hired', [
                'job_id' => $job->id,
                'worker_id' => $worker->id,
                'negotiation_id' => $negotiation->id,
                'agreed_amount' => $negotiation->amount,
            ]);

            DB::afterCommit(function () use ($job, $worker) {
                Cache::forget(CacheKeys::ADMIN_DASHBOARD_METRICS);
                event(new WorkerHired($job));
                $this->notificationService->create(
                    $worker->id,
                    'You were hired!',
                    'You have been hired for "' . $job->title . '".',
                    'job_hired',
                    route('web.app.jobs.show', $job),
                    'View Job'
                );
            });

            $this->broadcastJobStatusUpdate($job);

            return $job->fresh(['client', 'worker', 'skill']);
        });
    }

    /*public function hireWorker(ServiceJob $job, int $workerId)
    {
        return DB::transaction(function () use ($job, $workerId) {

            // Lock the job row to prevent concurrent updates
            $job = ServiceJob::where('id', $job->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Ensure job is still open
            if ($job->status !== 'open') {
                throw new \Exception('Job is no longer available for hiring.');
            }

            // Fetch worker
            $worker = User::findOrFail($workerId);

            // Worker availability checks
            if ($worker->availability_status === 'offline') {
                throw new \Exception('Worker is currently offline.');
            }

            if ($worker->availability_status === 'busy') {
                throw new \Exception('Worker is currently busy.');
            }

            // Assign worker
            $job->update([
                'assigned_to' => $worker->id,
                'status' => 'assigned'
            ]);



            // Mark worker as busy
            $worker->update([
                'availability_status' => 'busy'
            ]);

            return $job->fresh(['client', 'worker', 'skill']);
        });

    }*/

    public function workerAcceptJob(ServiceJob $job, $workerId)
    {
        return DB::transaction(function () use ($job, $workerId) {

            $job = ServiceJob::where('id', $job->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($job->assigned_to !== $workerId) {
                throw new \Exception('You are not assigned to this job.');
            }

            if ($job->status !== ServiceJob::STATUS_ASSIGNED) {
                throw new \Exception('Job cannot be accepted.');
            }

            $job->update([
                'status' => ServiceJob::STATUS_WORKER_ACCEPTED
            ]);

            $this->activityLogService->log($workerId, 'job_assignment_accepted', [
                'job_id' => $job->id,
                'status' => $job->status,
            ]);

            $this->broadcastJobStatusUpdate($job);

            return $job->fresh();
        });
    }

    public function workerRejectJob(ServiceJob $job, int $workerId)
    {
        return DB::transaction(function () use ($job, $workerId) {
            $job = ServiceJob::where('id', $job->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $job->assigned_to !== $workerId) {
                throw new \Exception('You are not assigned to this job.');
            }

            if ($job->status !== ServiceJob::STATUS_ASSIGNED) {
                throw new \Exception('Only assigned jobs can be rejected.');
            }

            $negotiation = JobNegotiation::query()
                ->where('job_id', $job->id)
                ->where('worker_id', $workerId)
                ->where('status', 'accepted')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($negotiation) {
                $history = $negotiation->history ?? [];
                $history[] = [
                    'amount' => (float) $negotiation->amount,
                    'message' => $negotiation->message,
                    'status' => $negotiation->status,
                    'created_by' => $negotiation->created_by,
                    'recorded_at' => now()->toIso8601String(),
                    'event' => 'worker_rejected_assignment',
                ];

                $negotiation->update([
                    'history' => $history,
                    'status' => 'rejected',
                    'created_by' => 'worker',
                    'message' => 'Worker rejected the assigned offer.',
                ]);
            }

            $job->applications()
                ->where('user_id', $workerId)
                ->update(['status' => 'rejected']);

            $job->update([
                'status' => ServiceJob::STATUS_OPEN,
                'assigned_to' => null,
                'agreed_amount' => null,
            ]);

            User::whereKey($workerId)->update([
                'availability_status' => 'available',
            ]);

            $this->activityLogService->log($workerId, 'job_assignment_rejected', [
                'job_id' => $job->id,
                'client_id' => $job->user_id,
                'negotiation_id' => $negotiation?->id,
            ]);

            DB::afterCommit(function () use ($job) {
                Cache::forget(CacheKeys::ADMIN_DASHBOARD_METRICS);
                $this->notificationService->create(
                    $job->user_id,
                    'Assigned worker declined the job',
                    'The assigned worker declined "' . $job->title . '". The job is open again for negotiation.',
                    'job_assignment_rejected',
                    route('web.app.jobs.show', $job),
                    'Review Job'
                );
            });

            $this->broadcastJobStatusUpdate($job);

            return $job->fresh(['client', 'worker', 'skill']);
        });
    }

    public function startJob(ServiceJob $job, $workerId)
    {
        return DB::transaction(function () use ($job, $workerId) {

            $job = ServiceJob::where('id', $job->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($job->assigned_to !== $workerId) {
                throw new \Exception('Unauthorized worker.');
            }

            if ($job->status !== ServiceJob::STATUS_WORKER_ACCEPTED) {
                throw new \Exception('Job cannot be started.');
            }

            $job->update([
                'status' => ServiceJob::STATUS_IN_PROGRESS
            ]);

            $this->activityLogService->log($workerId, 'job_started', [
                'job_id' => $job->id,
                'status' => $job->status,
            ]);

            $this->broadcastJobStatusUpdate($job);

            return $job->fresh();
        });
    }
    public function completeJob(ServiceJob $job)
    {
        return DB::transaction(function () use ($job) {

            $job = ServiceJob::where('id', $job->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($job->status !== ServiceJob::STATUS_IN_PROGRESS) {
                throw new \Exception('Job must be in progress to complete.');
            }

            $job->update([
                'status' => ServiceJob::STATUS_PAYMENT_PENDING
            ]);

            $payment = Payment::where('job_id', $job->id)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($payment) {
                $payment->update([
                    'user_id' => $job->user_id,
                    'amount' => $job->agreed_amount ?? $job->budget,
                    'payment_method' => $job->payment_method,
                    'status' => Payment::STATUS_AWAITING_CONFIRMATION,
                    'verified_at' => null,
                ]);
            } else {
                Payment::create([
                    'job_id' => $job->id,
                    'user_id' => $job->user_id,
                    'amount' => $job->agreed_amount ?? $job->budget,
                    'payment_method' => $job->payment_method,
                    'reference' => $this->generateReference('JOB'),
                    'status' => Payment::STATUS_AWAITING_CONFIRMATION,
                    'idempotency_key' => $this->generateIdempotencyKey(),
                    'verified_at' => null,
                    'provider_payload' => null,
                ]);
            }

            $this->activityLogService->log($job->assigned_to, 'job_marked_completed', [
                'job_id' => $job->id,
                'client_id' => $job->user_id,
                'status' => $job->status,
            ]);

            DB::afterCommit(function () use ($job) {
                Cache::forget(CacheKeys::ADMIN_DASHBOARD_METRICS);
                $this->notificationService->create(
                    $job->user_id,
                    'Job marked completed',
                    $job->worker->name . ' marked "' . $job->title . '" as completed. Please proceed to make payment after confirming the work is completed.',
                    'job_completed',
                    route('web.app.jobs.show', $job),
                    'Review Job'
                );
            });

            $this->broadcastJobStatusUpdate($job);

            return $job->fresh();
        });
    }

    public function markJobPaid(ServiceJob $job, int $clientId)
    {
        return DB::transaction(function () use ($job, $clientId) {

            $job = ServiceJob::where('id', $job->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $job->user_id !== $clientId) {
                throw new \Exception('Only the client can mark this job as paid.');
            }

            if ($job->status !== ServiceJob::STATUS_PAYMENT_PENDING) {
                throw new \Exception('Job is not awaiting payment confirmation.');
            }

            $job->update([
                'paid_at' => now(),
            ]);

            $payment = Payment::where('job_id', $job->id)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($payment) {
                $payment->update([
                    'user_id' => $job->user_id,
                    'amount' => $job->agreed_amount ?? $job->budget,
                    'payment_method' => $job->payment_method,
                    'status' => Payment::STATUS_PENDING,
                    'verified_at' => null,
                ]);
            } else {
                $payment = Payment::create([
                    'job_id' => $job->id,
                    'user_id' => $job->user_id,
                    'amount' => $job->agreed_amount ?? $job->budget,
                    'payment_method' => $job->payment_method,
                    'reference' => $this->generateReference('JOB'),
                    'status' => Payment::STATUS_PENDING,
                    'idempotency_key' => $this->generateIdempotencyKey(),
                    'verified_at' => null,
                    'provider_payload' => null,
                ]);
            }

            $this->activityLogService->log($clientId, 'job_payment_marked_sent', [
                'job_id' => $job->id,
                'payment_id' => $payment->id,
                'worker_id' => $job->assigned_to,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'paid_at' => optional($job->paid_at)->toIso8601String(),
            ]);

            DB::afterCommit(function () use ($job) {
                Cache::forget(CacheKeys::ADMIN_DASHBOARD_METRICS);
                if ($job->assigned_to) {
                    $this->notificationService->create(
                        $job->assigned_to,
                        'Payment marked as sent',
                        'The client marked "' . $job->title . '" as paid. Please confirm payment receipt.',
                        'job_paid',
                        route('web.app.jobs.show', $job),
                        'Review Job'
                    );
                }
            });

            $this->broadcastJobStatusUpdate($job);

            return $job->fresh();
        });
    }

    public function confirmJobPayment(ServiceJob $job, int $workerId)
    {
        return DB::transaction(function () use ($job, $workerId) {

            $job = ServiceJob::where('id', $job->id)
                ->lockForUpdate()
                ->firstOrFail();

            $payment = Payment::where('job_id', $job->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $job->assigned_to !== $workerId) {
                throw new \Exception('Only the assigned worker can confirm payment.');
            }

            if ($job->status !== ServiceJob::STATUS_PAYMENT_PENDING) {
                throw new \Exception('Job is not awaiting payment confirmation.');
            }

            if ($job->paid_at === null) {
                throw new \Exception('The client has not marked this job as paid yet.');
            }

            $job->update([
                'status' => ServiceJob::STATUS_COMPLETED
            ]);

            $payment->update([
                'status' => Payment::STATUS_SUCCESSFUL,
                'verified_at' => now()
            ]);

            $worker = User::find($job->assigned_to);

            if ($worker) {
                $worker->update([
                    'availability_status' => 'available'
                ]);
            }

            $this->activityLogService->log($workerId, 'job_payment_confirmed', [
                'job_id' => $job->id,
                'payment_id' => $payment->id,
                'client_id' => $job->user_id,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
            ]);

            DB::afterCommit(function () use ($job) {
                Cache::forget(CacheKeys::ADMIN_DASHBOARD_METRICS);
                $this->notificationService->create(
                    $job->user_id,
                    'Payment confirmed',
                    'The worker confirmed payment for "' . $job->title . '". The job is now closed.',
                    'payment_confirmed',
                    route('web.app.jobs.show', $job),
                    'View Job'
                );
            });

            $this->broadcastJobStatusUpdate($job);

            return $job->fresh();
        });
    }

    public function rateWorker(ServiceJob $job, $clientId, $data)
    {
        return DB::transaction(function () use ($job, $clientId, $data) {

            $job = ServiceJob::where('id', $job->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($job->user_id !== $clientId) {
                throw new \Exception('Only the job owner can rate.');
            }

            if ($job->status !== ServiceJob::STATUS_COMPLETED) {
                throw new \Exception('Job must be completed before rating.');
            }

            if ($job->rating) {
                throw new \Exception('Job already rated.');
            }

            $rating = Rating::create([

                'job_id' => $job->id,
                'client_id' => $clientId,
                'worker_id' => $job->assigned_to,
                'rating' => $data['rating'],
                'review' => $data['review'] ?? null

            ]);

            $worker = $job->worker()->first();

            if ($worker) {
                $worker->syncAverageRating();
            }

            $job->update([
                'status' => ServiceJob::STATUS_RATED
            ]);

            $this->activityLogService->log($clientId, 'worker_rated', [
                'job_id' => $job->id,
                'worker_id' => $job->assigned_to,
                'rating_id' => $rating->id,
                'score' => $rating->rating,
            ]);

            DB::afterCommit(function () {
                Cache::forget(CacheKeys::ADMIN_DASHBOARD_METRICS);
            });

            $this->broadcastJobStatusUpdate($job);

            return $rating;
        });
    }

    public function cancelJobByAdmin(ServiceJob $job, int $adminId): ServiceJob
    {
        return DB::transaction(function () use ($job, $adminId) {
            $job = ServiceJob::query()
                ->whereKey($job->id)
                ->lockForUpdate()
                ->firstOrFail();

            $previousStatus = $job->status;

            if (!in_array($previousStatus, ServiceJob::adminCancellableStatuses(), true)) {
                throw new \Exception('Admin can only cancel jobs that are open, assigned, worker accepted, or in progress.');
            }

            $assignedWorkerId = $job->assigned_to;

            $job->update([
                'status' => ServiceJob::STATUS_CANCELLED,
            ]);

            if ($assignedWorkerId) {
                User::whereKey($assignedWorkerId)->update([
                    'availability_status' => 'available',
                ]);
            }

            $this->activityLogService->log($adminId, 'job_cancelled_by_admin', [
                'job_id' => $job->id,
                'previous_status' => $previousStatus,
                'assigned_to' => $assignedWorkerId,
            ]);

            DB::afterCommit(function () use ($job, $assignedWorkerId) {
                Cache::forget(CacheKeys::ADMIN_DASHBOARD_METRICS);

                $this->notificationService->create(
                    $job->user_id,
                    'Job cancelled by admin',
                    'Your job "' . $job->title . '" was cancelled by an administrator.',
                    'job_cancelled',
                    route('web.app.jobs.show', $job),
                    'View Job'
                );

                if ($assignedWorkerId) {
                    $this->notificationService->create(
                        $assignedWorkerId,
                        'Assigned job cancelled by admin',
                        'The job "' . $job->title . '" was cancelled by an administrator.',
                        'job_cancelled',
                        route('web.app.jobs.show', $job),
                        'View Job'
                    );
                }
            });

            $this->broadcastJobStatusUpdate($job);

            return $job->fresh(['client', 'worker', 'skill']);
        });
    }

    public function rollbackJobStatusByAdmin(ServiceJob $job, string $targetStatus, int $adminId): ServiceJob
    {
        return DB::transaction(function () use ($job, $targetStatus, $adminId) {
            $job = ServiceJob::query()
                ->whereKey($job->id)
                ->lockForUpdate()
                ->firstOrFail();

            $previousStatus = $job->status;
            $allowedTargets = ServiceJob::adminRollbackTargets($previousStatus);

            if (!in_array($targetStatus, $allowedTargets, true)) {
                throw new \Exception('This rollback target is not allowed for the current job status.');
            }

            $payment = Payment::query()
                ->where('job_id', $job->id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            $ratingDeleted = false;

            if ($job->rating && in_array($targetStatus, [
                ServiceJob::STATUS_WORKER_ACCEPTED,
                ServiceJob::STATUS_IN_PROGRESS,
                ServiceJob::STATUS_PAYMENT_PENDING,
                ServiceJob::STATUS_COMPLETED,
            ], true)) {
                $job->rating->delete();
                $ratingDeleted = true;
            }

            if (in_array($targetStatus, [ServiceJob::STATUS_WORKER_ACCEPTED, ServiceJob::STATUS_IN_PROGRESS], true)) {
                $job->paid_at = null;

                if ($payment) {
                    $payment->update([
                        'status' => Payment::STATUS_PENDING,
                        'verified_at' => null,
                    ]);
                }
            }

            if ($targetStatus === ServiceJob::STATUS_PAYMENT_PENDING) {
                if ($payment) {
                    $payment->update([
                        'status' => $job->paid_at ? Payment::STATUS_PENDING : Payment::STATUS_AWAITING_CONFIRMATION,
                        'verified_at' => null,
                    ]);
                }
            }

            if ($targetStatus === ServiceJob::STATUS_COMPLETED) {
                if (!$job->paid_at) {
                    $job->paid_at = now();
                }

                if ($payment) {
                    $payment->update([
                        'status' => Payment::STATUS_SUCCESSFUL,
                        'verified_at' => $payment->verified_at ?? now(),
                    ]);
                }
            }

            $job->status = $targetStatus;
            $job->save();

            if ($job->worker) {
                $job->worker->syncAverageRating();
            }

            $this->activityLogService->log($adminId, 'job_status_rolled_back_by_admin', [
                'job_id' => $job->id,
                'previous_status' => $previousStatus,
                'target_status' => $targetStatus,
                'payment_id' => $payment?->id,
                'rating_deleted' => $ratingDeleted,
            ]);

            DB::afterCommit(function () use ($job, $previousStatus, $targetStatus) {
                Cache::forget(CacheKeys::ADMIN_DASHBOARD_METRICS);

                $message = 'An administrator rolled back "' . $job->title . '" from ' .
                    str_replace('_', ' ', $previousStatus) . ' to ' . str_replace('_', ' ', $targetStatus) . '.';

                $this->notificationService->create(
                    $job->user_id,
                    'Job status corrected by admin',
                    $message,
                    'job_status_rollback',
                    route('web.app.jobs.show', $job),
                    'View Job'
                );

                if ($job->assigned_to) {
                    $this->notificationService->create(
                        $job->assigned_to,
                        'Job status corrected by admin',
                        $message,
                        'job_status_rollback',
                        route('web.app.jobs.show', $job),
                        'View Job'
                    );
                }
            });

            $this->broadcastJobStatusUpdate($job);

            return $job->fresh(['client', 'worker', 'skill', 'payment', 'rating']);
        });
    }

    private function generateIdempotencyKey(): string
    {
        /**
         * UUID v4 is ideal:
         * - practically collision-free
         * - standard for idempotency
         */
        return (string) Str::uuid();
    }

    private function generateReference(string $prefix = 'REF'): string
    {
        /**
         * Format example:
         * JOB-20260324-8F3K9X
         *
         * Combines:
         * - prefix (context)
         * - date (traceability)
         * - random segment (uniqueness)
         */

        do {
            $reference = sprintf(
                '%s-%s-%s',
                strtoupper($prefix),
                now()->format('Ymd'),
                strtoupper(Str::random(6))
            );

            // Ensure uniqueness at DB level
            $exists = DB::table('payments')
                ->where('reference', $reference)
                ->exists();
        } while ($exists);

        return $reference;
    }
}
