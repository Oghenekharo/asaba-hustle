<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyJobRequest;
use App\Http\Requests\CreateJobRequest;
use App\Http\Requests\RateWorkerRequest;
use App\Http\Resources\RatingResource;
use App\Http\Resources\ServiceJobResource;
use App\Http\Resources\UserResource;
use App\Models\Conversation;
use App\Models\ServiceJob;
use App\Models\Skill;
use App\Services\JobService;
use App\Services\WorkerDiscoveryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function __construct(
        protected JobService $jobService,
        protected WorkerDiscoveryService $workerDiscovery
    ) {}

    public function jobs(Request $request)
    {
        $user = $request->user();
        $query = ServiceJob::query()
            ->where('status', 'open')
            ->with(['client', 'worker', 'skill'])
            ->latest();

        $skills = Skill::query()
            ->orderBy('name')
            ->get();

        $skillId = $request->integer('skill_id');
        $status = trim((string) $request->query('status', ''));
        $term = trim((string) $request->query('q', ''));

        if ($user && $user->hasRole('worker')) {
            $skillIds = $user->loadMissing('skills')->relevantSkillIds();

            if (!empty($skillIds)) {
                $query->whereIn('skill_id', $skillIds);
            }
        }

        if ($skillId > 0) {
            $query->where('skill_id', $skillId);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($term !== '') {
            $query->where(function ($builder) use ($term) {
                $builder
                    ->where('title', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%')
                    ->orWhere('location', 'like', '%' . $term . '%')
                    ->orWhereHas('skill', function ($skillQuery) use ($term) {
                        $skillQuery->where('name', 'like', '%' . $term . '%');
                    })
                    ->orWhereHas('client', function ($clientQuery) use ($term) {
                        $clientQuery->where('name', 'like', '%' . $term . '%');
                    });
            });
        }

        $jobs = $query->paginate(9)->withQueryString();

        return view('web.joblist', [
            'jobs' => $jobs,
            'skills' => $skills,
            'selectedSkillId' => $skillId,
            'statusFilter' => $status,
            'searchTerm' => $term,
        ]);
    }

    public function showJob(ServiceJob $job)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $isOwner = $user && (int) $user->id === (int) $job->user_id;
        $isWorker = $user && $user->hasRole('worker');

        $job->load([
            'client',
            'worker.skill',
            'skill',
            'rating',
            'workerRating',
            'ratings',
            'payment',
            'negotiations' => function ($q) use ($isOwner, $isWorker, $user) {
                $q->with([
                    'worker' => function ($query) use ($isOwner) {
                        $query->select([
                            'id',
                            'name',
                            'bio',
                            'primary_skill_id',
                            'availability_status',
                            'created_at',
                            'is_verified',
                            'profile_photo',
                            'bank_name',
                            'account_name',
                            'account_number',
                        ])->with('skill');

                        if ($isOwner) {
                            $query
                                ->with('skills')
                                ->withCount('ratingsReceived')
                                ->withAvg('ratingsReceived', 'rating');
                        }
                    },
                ])->latest('id');

                if (!$isOwner && $isWorker && $user) {
                    $q->where('worker_id', $user->id);
                }
            },
        ]);

        if ($isWorker && $user) {
            $job->load([
                'applications' => fn($query) => $query
                    ->where('user_id', $user->id)
                    ->latest('id'),
            ]);
        }

        $existingApplication = null;
        $existingConversation = null;
        $visibleNegotiations = $job->negotiations;
        $acceptedNegotiation = $visibleNegotiations->firstWhere('status', 'accepted');

        if ($acceptedNegotiation) {
            if ($isOwner || ((int) $acceptedNegotiation->worker_id === (int) $user?->id)) {
                $visibleNegotiations = collect([$acceptedNegotiation]);
            } else {
                $visibleNegotiations = collect();
            }
        }

        $latestNegotiation = $visibleNegotiations->first();
        $latestMine = $isWorker ? $visibleNegotiations->first() : null;

        if ($isWorker && $user) {
            $existingApplication = $job->applications
                ->firstWhere('user_id', $user->id);
        }

        if ($job->assigned_to) {
            $existingConversation = Conversation::query()
                ->where('job_id', $job->id)
                ->where('client_id', $job->user_id)
                ->where('worker_id', $job->assigned_to)
                ->first();
        }

        return view('web.job-detail', compact(
            'job',
            'existingApplication',
            'existingConversation',
            'visibleNegotiations',
            'latestNegotiation',
            'latestMine'
        ));
    }

    public function myJobs(Request $request)
    {
        $user = $request->user();
        $isClient = $user->hasRole('client');
        $status = trim((string) $request->query('status', ''));
        $term = trim((string) $request->query('q', ''));

        $query = $isClient
            ? ServiceJob::query()->where('user_id', $user->id)
            : ServiceJob::query()->where('assigned_to', $user->id);

        $query
            ->with(['client', 'worker', 'skill'])
            ->when($status !== '', function ($builder) use ($status) {
                $builder->where('status', $status);
            })
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where(function ($searchQuery) use ($term) {
                    $searchQuery
                        ->where('title', 'like', '%' . $term . '%')
                        ->orWhere('description', 'like', '%' . $term . '%')
                        ->orWhere('location', 'like', '%' . $term . '%')
                        ->orWhereHas('skill', function ($skillQuery) use ($term) {
                            $skillQuery->where('name', 'like', '%' . $term . '%');
                        });
                });
            });

        $jobs = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if (!$request->expectsJson()) {
            $baseStatsQuery = $isClient
                ? ServiceJob::query()->where('user_id', $user->id)
                : ServiceJob::query()->where('assigned_to', $user->id);

            $jobCounts = [
                'total' => (clone $baseStatsQuery)->count(),
                'open' => (clone $baseStatsQuery)->where('status', ServiceJob::STATUS_OPEN)->count(),
                'assigned' => (clone $baseStatsQuery)->where('status', ServiceJob::STATUS_ASSIGNED)->count(),
                'in_progress' => (clone $baseStatsQuery)->where('status', ServiceJob::STATUS_IN_PROGRESS)->count(),
                'payment_pending' => (clone $baseStatsQuery)->where('status', ServiceJob::STATUS_PAYMENT_PENDING)->count(),
                'completed' => (clone $baseStatsQuery)->whereIn('status', [ServiceJob::STATUS_COMPLETED, ServiceJob::STATUS_RATED])->count(),
            ];

            return view('web.my-jobs', [
                'jobs' => $jobs,
                'jobCounts' => $jobCounts,
                'isClient' => $isClient,
                'statusFilter' => $status,
                'searchTerm' => $term,
            ]);
        }

        return $this->successResponse(
            ServiceJobResource::collection($jobs),
            'User jobs retrieved successfully.'
        );
    }

    public function storeJob(CreateJobRequest $request)
    {
        $this->authorize('create', ServiceJob::class);

        $job = $this->jobService->createJob(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            new ServiceJobResource($job->load(['client', 'worker', 'skill'])),
            'Job created successfully.',
            201
        );
    }

    public function apply(ApplyJobRequest $request, ServiceJob $job)
    {
        $this->authorize('apply', $job);

        $existing = $job->applications()
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($existing) {
            return $this->errorResponse('You already applied for this job', 409);
        }

        $application = $this->jobService->applyToJob(
            $request->user(),
            $job,
            $request->validated('message'),
            (float) $request->validated('amount') ?? (float) $job->budget
        );

        return $this->successResponse(
            $application,
            'Application submitted successfully.',
            201
        );
    }

    public function hire(Request $request, ServiceJob $job)
    {
        $this->authorize('hire', $job);

        $validator = Validator::make($request->all(), [
            'worker_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('The given data was invalid.', 422, $validator->errors()->toArray());
        }

        try {
            $job = $this->jobService->hireWorker($job, (int) $request->input('worker_id'));
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(
            new ServiceJobResource($job),
            'Worker hired successfully.'
        );
    }

    public function accept(Request $request, ServiceJob $job)
    {
        $this->authorize('accept', $job);

        try {
            $job = $this->jobService->workerAcceptJob($job, $request->user()->id);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(
            new ServiceJobResource($job->load(['client', 'worker', 'skill'])),
            'Job accepted successfully.'
        );
    }

    public function reject(Request $request, ServiceJob $job)
    {
        $this->authorize('rejectAssignment', $job);

        try {
            $job = $this->jobService->workerRejectJob($job, $request->user()->id);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(
            new ServiceJobResource($job->load(['client', 'worker', 'skill'])),
            'Job rejected successfully.'
        );
    }

    public function start(Request $request, ServiceJob $job)
    {
        $this->authorize('start', $job);

        try {
            $job = $this->jobService->startJob($job, $request->user()->id);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(
            new ServiceJobResource($job->load(['client', 'worker', 'skill'])),
            'Job started successfully.'
        );
    }

    public function complete(Request $request, ServiceJob $job)
    {
        $this->authorize('complete', $job);

        try {
            $job = $this->jobService->completeJob($job);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(
            new ServiceJobResource($job->load(['client', 'worker', 'skill'])),
            'Job marked as completed. Waiting for payment confirmation.'
        );
    }

    public function markPaid(Request $request, ServiceJob $job)
    {
        $this->authorize('markPaid', $job);

        try {
            $job = $this->jobService->markJobPaid($job, $request->user()->id);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(
            new ServiceJobResource($job->load(['client', 'worker', 'skill'])),
            'Payment marked as sent. Waiting for worker confirmation.'
        );
    }

    public function confirmPayment(Request $request, ServiceJob $job)
    {
        $this->authorize('confirmPayment', $job);

        try {
            $job = $this->jobService->confirmJobPayment($job, $request->user()->id);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(
            new ServiceJobResource($job->load(['client', 'worker', 'skill'])),
            'Payment confirmed. Job closed successfully.'
        );
    }

    public function rate(ServiceJob $job, RateWorkerRequest $request)
    {
        $this->authorize('rate', $job);

        try {
            $rating = $this->jobService->rateParticipant(
                $job,
                $request->user()->id,
                $request->validated()
            );
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(
            new RatingResource($rating->load(['client', 'worker', 'rater', 'ratee'])),
            'Rating submitted successfully.',
            201
        );
    }

    public function suggestedWorkers(ServiceJob $job, Request $request)
    {
        $this->authorize('suggestedWorkers', $job);

        return $this->successResponse(
            UserResource::collection($this->workerDiscovery->discover($job)),
            'Suggested workers retrieved successfully.'
        );
    }
}
