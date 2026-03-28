<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyJobRequest;
use App\Http\Requests\CreateJobRequest;
use App\Http\Requests\RateWorkerRequest;
use App\Http\Resources\RatingResource;
use App\Http\Resources\ServiceJobResource;
use App\Http\Resources\UserResource;
use App\Traits\LogActivity;
use Illuminate\Http\Request;
use App\Models\ServiceJob;
use App\Models\JobApplication;
use App\Services\JobService;
use App\Services\WorkerDiscoveryService;
use Exception;
use Illuminate\Support\Facades\Validator;


class ServiceJobController extends Controller
{
    use LogActivity;
    protected $jobService;
    protected $workerDiscovery;

    public function __construct(
        JobService $jobService,
        WorkerDiscoveryService $workerDiscovery
    ) {
        $this->jobService = $jobService;
        $this->workerDiscovery = $workerDiscovery;
    }
    /**
     * List jobs
     */
    public function index()
    {
        $jobs = ServiceJob::with(['client', 'skill'])
            ->latest()
            ->paginate(10);

        return $this->successResponse(
            ServiceJobResource::collection($jobs),
            'Jobs retrieved successfully.'
        );
    }

    /**
     * Create job
     */
    public function store(CreateJobRequest $request)
    {
        $this->authorize('create', ServiceJob::class);

        $data = $request->validated();

        $job = $this->jobService->createJob($request->user(), $data);

        $this->activityLog()->log(
            $request->user()->id,
            'job_posted',
            ['date' => now()],
            $request->ip()
        );

        return $this->successResponse(
            new ServiceJobResource($job),
            'Job created successfully.',
            201
        );
    }

    /**
     * View job details
     */
    public function show(ServiceJob $job)
    {
        $job->load(['client', 'skill', 'applications.user']);

        return $this->successResponse(
            new ServiceJobResource($job),
            'Job retrieved successfully.'
        );
    }

    /**
     * Worker applies for job
     */
    public function apply(ApplyJobRequest $request, ServiceJob $job)
    {
        $this->authorize('apply', $job);

        $existing = JobApplication::where('job_id', $job->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($existing) {
            return $this->errorResponse(
                'You already applied for this job',
                409
            );
        }

        $application = $this->jobService->applyToJob(
            $request->user(),
            $job,
            $request->validated('message'),
            (float) $request->validated('amount')
        );

        return $this->successResponse(
            $application,
            'Application submitted successfully.',
            201
        );
    }

    /**
     * Client hires worker
     */
    public function hire(Request $request, ServiceJob $job)
    {
        $this->authorize('hire', $job);

        $validator = Validator::make($request->all(), [
            'worker_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {

            $job = $this->jobService->hireWorker($job, $request->worker_id);

            return $this->successResponse(
                new ServiceJobResource($job),
                'Worker hired successfully.'
            );
        } catch (Exception $e) {

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function accept(ServiceJob $job, Request $request)
    {
        $this->authorize('accept', $job);

        try {

            $job = $this->jobService->workerAcceptJob(
                $job,
                $request->user()->id
            );

            return $this->successResponse(
                new ServiceJobResource($job),
                'Job accepted successfully.'
            );
        } catch (Exception $e) {

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function reject(ServiceJob $job, Request $request)
    {
        $this->authorize('rejectAssignment', $job);

        try {

            $job = $this->jobService->workerRejectJob(
                $job,
                $request->user()->id
            );

            return $this->successResponse(
                new ServiceJobResource($job),
                'Job rejected successfully.'
            );
        } catch (Exception $e) {

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function start(ServiceJob $job, Request $request)
    {
        $this->authorize('start', $job);

        try {

            $job = $this->jobService->startJob(
                $job,
                $request->user()->id
            );

            return $this->successResponse(
                new ServiceJobResource($job),
                'Job started successfully.'
            );
        } catch (Exception $e) {

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Mark job completed
     */
    public function complete(Request $request, ServiceJob $job)
    {
        $this->authorize('complete', $job);

        try {
            $job = $this->jobService->completeJob($job);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }

        return $this->successResponse(
            new ServiceJobResource($job->load(['client', 'worker', 'skill', 'rating'])),
            'Job marked as completed. Waiting for payment confirmation.'
        );
    }

    public function markPaid(Request $request, ServiceJob $job)
    {
        $this->authorize('markPaid', $job);

        try {
            $job = $this->jobService->markJobPaid(
                $job,
                $request->user()->id
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }

        return $this->successResponse(
            new ServiceJobResource($job->load(['client', 'worker', 'skill', 'rating'])),
            'Payment marked as sent. Waiting for worker confirmation.'
        );
    }

    public function confirmPayment(Request $request, ServiceJob $job)
    {
        $this->authorize('confirmPayment', $job);

        try {
            $job = $this->jobService->confirmJobPayment(
                $job,
                $request->user()->id
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }

        return $this->successResponse(
            new ServiceJobResource($job->load(['client', 'worker', 'skill', 'rating'])),
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

            return $this->successResponse(
                new RatingResource($rating->load(['client', 'worker', 'rater', 'ratee'])),
                'Rating submitted successfully.',
                201
            );
        } catch (Exception $e) {

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function cancel(ServiceJob $job, Request $request)
    {
        $this->authorize('cancelByAdmin', $job);

        try {
            $job = $this->jobService->cancelJobByAdmin($job, $request->user()->id);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }

        return $this->successResponse(
            new ServiceJobResource($job),
            'Job cancelled successfully.'
        );
    }

    public function rollback(ServiceJob $job, Request $request)
    {
        $this->authorize('rollbackByAdmin', $job);

        $validator = Validator::make($request->all(), [
            'target_status' => 'required|in:worker_accepted,in_progress,payment_pending,completed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('The given data was invalid.', 422, $validator->errors()->toArray());
        }

        try {
            $job = $this->jobService->rollbackJobStatusByAdmin(
                $job,
                (string) $request->input('target_status'),
                (int) $request->user()->id
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }

        return $this->successResponse(
            new ServiceJobResource($job),
            'Job status rolled back successfully.'
        );
    }

    /**
     * My jobs (client or worker)
     */
    public function myJobs(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('client')) {
            $jobs = ServiceJob::where('user_id', $user->id)
                ->with(['client', 'worker', 'skill'])
                ->latest()
                ->paginate(10);
        } else {
            $jobs = ServiceJob::where('assigned_to', $user->id)
                ->with(['client', 'worker', 'skill'])
                ->latest()
                ->paginate(10);
        }

        return $this->successResponse(
            ServiceJobResource::collection($jobs),
            'User jobs retrieved successfully.'
        );
    }

    public function search(Request $request)
    {
        $query = ServiceJob::query()
            ->with(['client', 'skill']);

        if ($request->skill_id) {
            $query->where('skill_id', $request->skill_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->latitude && $request->longitude) {

            $radius = $request->radius ?? 10;

            $lat = $request->latitude;
            $lng = $request->longitude;

            $query->select('*')
                ->selectRaw("
                (6371 *
                acos(
                    cos(radians(?)) *
                    cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(latitude))
                )
            ) AS distance", [$lat, $lng, $lat])
                ->having("distance", "<=", $radius)
                ->orderBy("distance");
        }

        $jobs = $query->paginate(10);

        return $this->successResponse(
            ServiceJobResource::collection($jobs),
            'Job search completed successfully.'
        );
    }

    public function suggestedWorkers(ServiceJob $job)
    {
        $this->authorize('suggestedWorkers', $job);

        $workers = $this->workerDiscovery->discover($job);

        return $this->successResponse(
            UserResource::collection($workers),
            'Suggested workers retrieved successfully.'
        );
    }
}
