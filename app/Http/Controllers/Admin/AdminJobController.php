<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminJobIndexRequest;
use App\Http\Requests\Admin\AdminJobRollbackRequest;
use App\Models\ServiceJob;
use App\Services\JobService;

class AdminJobController extends Controller
{
    public function __construct(
        protected JobService $jobService
    ) {
    }

    public function index(AdminJobIndexRequest $request)
    {
        $jobs = ServiceJob::query()
            ->with(['client', 'worker', 'skill'])
            ->adminFilter($request->filters())
            ->latest()
            ->paginate(25);

        return view('admin.jobs.index', compact('jobs'));
    }

    public function show(ServiceJob $job)
    {
        $job->load([
            'client',
            'worker',
            'skill',
            'rating',
            'payment',
            'conversation.messages.sender',
            'applications' => function ($query) {
                $query->latest()->with([
                    'user.skill',
                    'user.skills',
                    'user.roles',
                ]);
            },
        ]);

        return view('admin.jobs.show', compact('job'));
    }

    public function cancel(ServiceJob $job)
    {
        $this->authorize('cancelByAdmin', $job);

        try {
            $this->jobService->cancelJobByAdmin($job, (int) auth()->id());
        } catch (\Exception $exception) {
            return redirect()
                ->back()
                ->withErrors(['job' => $exception->getMessage()]);
        }

        return redirect()
            ->back()
            ->with('status', 'Job cancelled successfully.');
    }

    public function rollback(AdminJobRollbackRequest $request, ServiceJob $job)
    {
        $this->authorize('rollbackByAdmin', $job);

        try {
            $this->jobService->rollbackJobStatusByAdmin(
                $job,
                $request->validated('target_status'),
                (int) auth()->id()
            );
        } catch (\Exception $exception) {
            return redirect()
                ->back()
                ->withErrors(['job' => $exception->getMessage()]);
        }

        return redirect()
            ->back()
            ->with('status', 'Job status rolled back successfully.');
    }
}
