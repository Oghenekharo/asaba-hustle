<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CounterNegotiationRequest;
use App\Http\Requests\CreateNegotiationRequest;
use App\Http\Requests\RejectNegotiationRequest;
use App\Models\JobNegotiation;
use App\Models\ServiceJob;
use App\Services\NegotiationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JobNegotiationController extends Controller
{
    public function __construct(
        protected NegotiationService $negotiationService
    ) {}

    public function store(CreateNegotiationRequest $request, ServiceJob $job): JsonResponse
    {
        $user = $request->user();
        $latest = $job->negotiations()
            ->where('worker_id', $user->id)
            ->latest('id')
            ->first();

        try {
            if (!$latest) {
                $negotiation = $this->negotiationService->createInitialOffer(
                    $job,
                    $user,
                    $request->validated('amount'),
                    $request->validated('message')
                );
            } else {
                $negotiation = $this->negotiationService->counterOffer(
                    $latest,
                    $user,
                    $request->validated('amount'),
                    $request->validated('message')
                );
            }
        } catch (ValidationException $exception) {
            return $this->errorResponse(
                'The given data was invalid.',
                422,
                $exception->errors()
            );
        }

        return $this->successResponse(
            $negotiation,
            'Offer sent successfully.'
        );
    }

    public function accept(Request $request, ServiceJob $job, JobNegotiation $negotiation): JsonResponse
    {
        if ((int) $negotiation->job_id !== (int) $job->id) {
            return $this->errorResponse('Negotiation does not belong to this job.', 404);
        }

        if (!in_array((int) $request->user()->id, [(int) $negotiation->client_id, (int) $negotiation->worker_id], true)) {
            return $this->errorResponse('Only negotiation participants can accept offers.', 403);
        }

        try {
            $negotiation = $this->negotiationService->acceptOffer($negotiation, $request->user());
        } catch (ValidationException $exception) {
            return $this->errorResponse(
                'The given data was invalid.',
                422,
                $exception->errors()
            );
        }

        return $this->successResponse(
            $negotiation,
            'Offer accepted.'
        );
    }

    public function counter(CounterNegotiationRequest $request, ServiceJob $job, JobNegotiation $negotiation): JsonResponse
    {
        if ((int) $negotiation->job_id !== (int) $job->id) {
            return $this->errorResponse('Negotiation does not belong to this job.', 404);
        }

        if (!in_array((int) $request->user()->id, [(int) $negotiation->client_id, (int) $negotiation->worker_id], true)) {
            return $this->errorResponse('Only negotiation participants can counter offers.', 403);
        }

        try {
            $negotiation = $this->negotiationService->counterOffer(
                $negotiation,
                $request->user(),
                (float) $request->validated('amount'),
                $request->validated('message')
            );
        } catch (ValidationException $exception) {
            return $this->errorResponse(
                'The given data was invalid.',
                422,
                $exception->errors()
            );
        }

        return $this->successResponse(
            $negotiation,
            'Counter offer sent.'
        );
    }

    public function reject(RejectNegotiationRequest $request, ServiceJob $job, JobNegotiation $negotiation): JsonResponse
    {
        if ((int) $negotiation->job_id !== (int) $job->id) {
            return $this->errorResponse('Negotiation does not belong to this job.', 404);
        }

        if (!in_array((int) $request->user()->id, [(int) $negotiation->client_id, (int) $negotiation->worker_id], true)) {
            return $this->errorResponse('Only negotiation participants can reject offers.', 403);
        }

        try {
            $this->negotiationService->rejectOffer(
                $negotiation,
                $request->user(),
                $request->validated('message')
            );
        } catch (ValidationException $exception) {
            return $this->errorResponse(
                'The given data was invalid.',
                422,
                $exception->errors()
            );
        }

        return $this->successResponse(
            null,
            'Offer rejected.'
        );
    }
}
