@if ($isOwner || $isAssignedWorker)
    <section class="rounded-[2rem] h-[350px] overflow-y-auto bg-white border border-slate-100 shadow-sm p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Job Lifecycle</p>
                <h3 class="mt-2 text-lg font-black text-slate-900">Job Progress</h3>
                <p class="mt-2 text-sm font-medium text-slate-500">
                    Follow this job from acceptance to completion.
                </p>
            </div>
            <span
                class="inline-flex items-center rounded-full bg-[var(--surface-soft)] px-3 py-1 text-[10px] font-black uppercase tracking-widest text-[var(--brand)]">
                {{ str_replace('_', ' ', $job->status) }}
            </span>
        </div>

        <div class="mt-6 space-y-3">
            <div
                class="rounded-2xl border px-4 py-4 {{ $job->status === 'assigned' || in_array($job->status, $acceptedStages) ? 'border-orange-200 bg-orange-50/70' : 'border-slate-200 bg-slate-50' }}">
                <div class="flex items-start justify-between gap-3">
                    <p
                        class="text-[10px] font-black uppercase tracking-widest {{ $job->status === 'assigned' || in_array($job->status, $acceptedStages) ? 'text-orange-700' : 'text-slate-400' }}">
                        1. Job Accepted
                    </p>
                    @if (in_array($job->status, $acceptedStages))
                        <i data-lucide="circle-check-big" class="h-4 w-4 text-orange-600"></i>
                    @endif
                </div>
                <p
                    class="mt-2 text-sm font-medium {{ $job->status === 'assigned' || in_array($job->status, $acceptedStages) ? 'text-orange-900' : 'text-slate-500' }}">
                    The assigned worker confirms they are ready to take on the job.
                </p>
            </div>

            <div
                class="rounded-2xl border px-4 py-4 {{ in_array($job->status, ['worker_accepted']) || in_array($job->status, $inProgressStages) ? 'border-blue-200 bg-blue-50/70' : 'border-slate-200 bg-slate-50' }}">
                <div class="flex items-start justify-between gap-3">
                    <p
                        class="text-[10px] font-black uppercase tracking-widest {{ in_array($job->status, ['worker_accepted']) || in_array($job->status, $inProgressStages) ? 'text-blue-700' : 'text-slate-400' }}">
                        2. Work In Progress
                    </p>
                    @if (in_array($job->status, $inProgressStages))
                        <i data-lucide="circle-check-big" class="h-4 w-4 text-blue-600"></i>
                    @endif
                </div>
                <p
                    class="mt-2 text-sm font-medium {{ in_array($job->status, ['worker_accepted']) || in_array($job->status, $inProgressStages) ? 'text-blue-900' : 'text-slate-500' }}">
                    The worker marks the job as started once work begins.
                </p>
            </div>

            <div
                class="rounded-2xl border px-4 py-4 {{ in_array($job->status, $paymentPendingStages) ? 'border-amber-200 bg-amber-50/70' : 'border-slate-200 bg-slate-50' }}">
                <div class="flex items-start justify-between gap-3">
                    <p
                        class="text-[10px] font-black uppercase tracking-widest {{ in_array($job->status, $paymentPendingStages) ? 'text-amber-700' : 'text-slate-400' }}">
                        3. Payment Pending
                    </p>
                    @if (in_array($job->status, $paymentPendingStages))
                        <i data-lucide="circle-check-big" class="h-4 w-4 text-amber-600"></i>
                    @endif
                </div>
                <p
                    class="mt-2 text-sm font-medium {{ in_array($job->status, $paymentPendingStages) ? 'text-amber-900' : 'text-slate-500' }}">
                    Once the work is done, the client marks it as paid and the worker confirms payment receipt.
                </p>
                @if ($paymentLifecycleLabel)
                    <div
                        class="mt-4 inline-flex items-center rounded-full border border-amber-200 bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-amber-700">
                        {{ $paymentLifecycleLabel }}
                    </div>
                @endif
                @if ($showCashPaymentNote)
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-white/70 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-700">
                            Cash Payment
                        </p>
                        <p class="mt-2 text-sm font-medium text-amber-900">
                            This job is set to cash, so no bank transfer details are needed here.
                        </p>
                    </div>
                @endif
            </div>

            <div
                class="rounded-2xl border px-4 py-4 {{ in_array($job->status, $closedStages) ? 'border-emerald-200 bg-emerald-50/70' : 'border-slate-200 bg-slate-50' }}">
                <div class="flex items-start justify-between gap-3">
                    <p
                        class="text-[10px] font-black uppercase tracking-widest {{ in_array($job->status, $closedStages) ? 'text-emerald-700' : 'text-slate-400' }}">
                        4. Job Closed
                    </p>
                    @if (in_array($job->status, $closedStages))
                        <i data-lucide="circle-check-big" class="h-4 w-4 text-emerald-600"></i>
                    @endif
                </div>
                <p
                    class="mt-2 text-sm font-medium {{ in_array($job->status, $closedStages) ? 'text-emerald-900' : 'text-slate-500' }}">
                    The job is closed after the worker confirms payment has been received.
                </p>
            </div>

            <div
                class="rounded-2xl border px-4 py-4 {{ $clientRating || $workerRating ? 'border-violet-200 bg-violet-50/70' : 'border-slate-200 bg-slate-50' }}">
                <div class="flex items-start justify-between gap-3">
                    <p
                        class="text-[10px] font-black uppercase tracking-widest {{ $clientRating || $workerRating ? 'text-violet-700' : 'text-slate-400' }}">
                        5. Leave Rating
                    </p>
                    @if ($clientRating || $workerRating)
                        <i data-lucide="circle-check-big" class="h-4 w-4 text-violet-600"></i>
                    @endif
                </div>
                <p
                    class="mt-2 text-sm font-medium {{ $clientRating || $workerRating ? 'text-violet-900' : 'text-slate-500' }}">
                    Leave a rating after payment is confirmed so both sides can build trust on the platform.
                </p>
            </div>
        </div>

        <div class="mt-6">
            @if ($canAcceptJob)
                <div class="grid gap-3 sm:grid-cols-2">
                    <form id="job-reject-form" class="job-status-form"
                        action="{{ route('web.app.jobs.reject', $job) }}" method="POST"
                        data-success-target="#job-lifecycle-feedback" data-loading-text="Rejecting...">
                        @csrf
                        <x-button id="job-reject-submit" type="submit" color="black" variant="outline" class="w-full">
                            <x-slot:icon>
                                <i data-lucide="x-circle" class="h-4 w-4"></i>
                            </x-slot:icon>
                            Reject Offer
                        </x-button>
                    </form>
                    <form id="job-accept-form" class="job-status-form"
                        action="{{ route('web.app.jobs.accept', $job) }}" method="POST"
                        data-success-target="#job-lifecycle-feedback" data-loading-text="Accepting...">
                        @csrf
                        <x-button id="job-accept-submit" type="submit" class="w-full">
                            <x-slot:icon>
                                <i data-lucide="badge-check" class="h-4 w-4"></i>
                            </x-slot:icon>
                            Accept Job
                        </x-button>
                    </form>
                </div>
            @elseif($canStartJob)
                <form id="job-start-form" class="job-status-form" action="{{ route('web.app.jobs.start', $job) }}"
                    method="POST" data-success-target="#job-lifecycle-feedback" data-loading-text="Starting...">
                    @csrf
                    <x-button id="job-start-submit" type="submit" class="w-full">
                        <x-slot:icon>
                            <i data-lucide="play" class="h-4 w-4"></i>
                        </x-slot:icon>
                        Mark Job In Progress
                    </x-button>
                </form>
            @elseif($canCompleteJob)
                <form id="job-complete-form" class="job-status-form"
                    action="{{ route('web.app.jobs.complete', $job) }}" method="POST"
                    data-success-target="#job-lifecycle-feedback" data-loading-text="Updating...">
                    @csrf
                    <x-button id="job-complete-submit" type="submit" class="w-full">
                        <x-slot:icon>
                            <i data-lucide="circle-check-big" class="h-4 w-4"></i>
                        </x-slot:icon>
                        Mark Work Completed
                    </x-button>
                </form>
            @elseif($canMarkPaid)
                <div class="grid gap-3 sm:grid-cols-2">
                    @if ($canViewTransferDetails)
                        <button type="button" onclick="openModal('workerTransferDetailsModal')"
                            class="flex cursor-pointer h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-600 transition-all hover:border-[var(--brand)] hover:text-[var(--brand)] active:scale-95">
                            <i data-lucide="copy" class="h-4 w-4"></i>
                            View Account Details
                        </button>
                    @elseif ($showCashPaymentNote)
                        <div
                            class="flex min-h-14 items-center rounded-2xl border border-amber-200 bg-amber-50/70 px-4 py-3 text-left">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-700">
                                Cash Payment
                            </p>
                        </div>
                    @endif
                    <form id="job-mark-paid-form" class="job-status-form"
                        action="{{ route('web.app.jobs.mark-paid', $job) }}" method="POST"
                        data-success-target="#job-lifecycle-feedback" data-loading-text="Updating...">
                        @csrf
                        <x-button id="job-mark-paid-submit" type="submit" class="w-full">
                            <x-slot:icon>
                                <i data-lucide="wallet" class="h-4 w-4"></i>
                            </x-slot:icon>
                            Mark as Paid
                        </x-button>
                    </form>
                </div>
            @elseif($canConfirmPayment)
                <form id="job-confirm-payment-form" class="job-status-form"
                    action="{{ route('web.app.jobs.confirm-payment', $job) }}" method="POST"
                    data-success-target="#job-lifecycle-feedback" data-loading-text="Confirming...">
                    @csrf
                    <x-button id="job-confirm-payment-submit" type="submit" class="w-full">
                        <x-slot:icon>
                            <i data-lucide="badge-check" class="h-4 w-4"></i>
                        </x-slot:icon>
                        Confirm Payment Received
                    </x-button>
                </form>
            @elseif($isOwner && $job->status === 'in_progress')
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Work in Progress</p>
                    <p class="mt-2 text-sm font-medium text-slate-600">
                        The worker is still handling this job. You can mark it as paid once they say the work is
                        complete.
                    </p>
                </div>
            @elseif($isOwner && $job->status === 'assigned')
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Waiting for Acceptance
                    </p>
                    <p class="mt-2 text-sm font-medium text-slate-600">
                        The assigned worker still needs to accept this job before work can begin.
                    </p>
                </div>
            @elseif($isOwner && $job->status === 'worker_accepted')
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Ready to Start</p>
                    <p class="mt-2 text-sm font-medium text-slate-600">
                        The worker has accepted this job and can now mark it as in progress when work begins.
                    </p>
                </div>
            @elseif($isAssignedWorker && $job->status === 'payment_pending' && $job->paid_at === null)
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                        Awaiting Client Confirmation
                    </p>
                    <p class="mt-2 text-sm font-medium text-slate-600">
                        You marked the work as completed. The client still needs to review the work and mark this job as
                        paid.
                    </p>
                </div>
            @elseif($isOwner && $job->status === 'payment_pending' && $job->paid_at !== null)
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Payment Sent</p>
                    <p class="mt-2 text-sm font-medium text-slate-600">
                        You marked this job as paid. The worker still needs to confirm payment receipt.
                    </p>
                </div>
            @elseif(in_array($job->status, ['completed', 'rated']))
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-700">Job Closed</p>
                    <p class="mt-2 text-sm font-medium text-emerald-800">
                        Payment has been confirmed and this job is now closed.
                    </p>
                </div>
            @endif

            <div id="job-lifecycle-feedback" class="mt-4 hidden rounded-2xl border px-4 py-3 text-sm"></div>
        </div>
    </section>
@endif
