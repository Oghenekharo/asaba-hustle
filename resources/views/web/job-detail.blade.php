@extends('layouts.app', ['title' => $job->title . ' | Asaba Hustle'])

@php
    use App\Models\Payment;

    $viewer = auth()->user();
    $isOwner = $viewer->id === $job->user_id;
    $isWorker = $viewer->hasRole('worker');
    $hasApplied = filled($existingApplication ?? null);
    $workerNeedsPayoutDetails =
        $isWorker && !$isOwner && (!filled($viewer->bank_name) || !filled($viewer->account_name) || !filled($viewer->account_number));

    $canApply =
        $isWorker &&
        !$isOwner &&
        !$hasApplied &&
        !$workerNeedsPayoutDetails &&
        $job->status === 'open' &&
        $job->assigned_to === null &&
        $viewer->availability_status === 'available';

    $canChatAsWorker = $isWorker && !$isOwner && $hasApplied && $job->status === 'assigned';
    $isAssignedWorker = (int) $job->assigned_to === (int) $viewer->id;
    $canSeeAssignedWorker = $job->worker && ($isOwner || $isAssignedWorker);
    $canAcceptJob = $isAssignedWorker && $job->status === 'assigned';
    $canRejectJob = $isAssignedWorker && $job->status === 'assigned';
    $canStartJob = $isAssignedWorker && $job->status === 'worker_accepted';
    $canCompleteJob = $isAssignedWorker && $job->status === 'in_progress';
    $canMarkPaid = $isOwner && $job->status === 'payment_pending' && $job->paid_at === null;
    $canConfirmPayment = $isAssignedWorker && $job->status === 'payment_pending' && $job->paid_at !== null;
    $canRateWorker = $isOwner && $job->status === 'completed' && !$job->rating && $job->worker;
    $canViewTransferDetails =
        $isOwner &&
        $job->worker &&
        $job->status === 'payment_pending' &&
        $job->payment_method === 'transfer';
    $showCashPaymentNote =
        $isOwner &&
        $job->worker &&
        $job->status === 'payment_pending' &&
        $job->payment_method === 'cash';
    $acceptedStages = ['worker_accepted', 'in_progress', 'payment_pending', 'completed', 'rated'];
    $inProgressStages = ['in_progress', 'payment_pending', 'completed', 'rated'];
    $paymentPendingStages = ['payment_pending', 'completed', 'rated'];
    $closedStages = ['completed', 'rated'];

    $visibleNegotiations = $visibleNegotiations ?? collect();
    $latest = $latestNegotiation ?? $visibleNegotiations->first();
    $myNegotiations = $visibleNegotiations;
    $latestMine = $latestMine ?? $myNegotiations->first();
    $viewerNegotiationCount = $isOwner ? $visibleNegotiations->count() : $myNegotiations->count();
    $showNegotiationSection = $isOwner
        ? $job->status === 'open' || $visibleNegotiations->isNotEmpty()
        : $job->status === 'open' || $viewerNegotiationCount > 0;
    $agreedAmount = $job->agreed_amount;
    $paymentLifecycleStatus = match (true) {
        $job->status !== 'payment_pending' => $job->payment?->status,
        $job->paid_at === null => Payment::STATUS_AWAITING_CONFIRMATION,
        default => $job->payment?->status ?? Payment::STATUS_PENDING,
    };
    $paymentLifecycleLabel = $paymentLifecycleStatus ? str_replace('_', ' ', $paymentLifecycleStatus) : null;
@endphp

@section('content')
    <div class="max-w-5xl mx-auto pt-20" id="job-detail-page" data-job-id="{{ $job->id }}"
        data-job-status="{{ $job->status }}">
        <a href="{{ route('web.app.jobs') }}"
            class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest opacity-40 hover:opacity-100 hover:text-[var(--brand)] transition-all mb-8">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
            Back to Explore
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-8 space-y-6">
                <section
                    class="p-8 md:p-12 rounded-[3rem] bg-white border border-[var(--brand)]/5 shadow-sm relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-[var(--brand)]/5 blur-3xl rounded-full"></div>

                    <div class="relative">
                        <div class="flex items-center gap-3 mb-6">
                            <span
                                class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                {{ $job->status }}
                            </span>
                            <span class="text-[10px] font-bold opacity-30 uppercase tracking-tighter italic">Ref:
                                #AH-{{ $job->id }}</span>
                        </div>

                        <h1 class="text-4xl md:text-5xl font-black tracking-tighter text-[var(--ink)] leading-[1.1] mb-6">
                            {{ $job->title }}
                        </h1>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 py-8 border-y border-slate-50">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest opacity-30 mb-1">
                                    {{ $agreedAmount !== null ? 'Listed Budget' : 'Budget' }}
                                </p>
                                <p class="text-xl font-black text-emerald-600">&#8358;{{ number_format($job->budget) }}</p>
                            </div>
                            @if ($agreedAmount !== null)
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest opacity-30 mb-1">Agreed Fee
                                    </p>
                                    <p class="text-xl font-black text-[var(--ink)]">
                                        &#8358;{{ number_format($agreedAmount) }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest opacity-30 mb-1">Category</p>
                                <p class="text-xl font-black text-[var(--ink)]">{{ $job->skill->name }}</p>
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <p class="text-[10px] font-black uppercase tracking-widest opacity-30 mb-1">Location</p>
                                <p class="text-sm font-bold text-[var(--ink)] truncate">{{ $job->location }}</p>
                            </div>
                        </div>

                        <div class="mt-8">
                            <h2 class="text-xs font-black uppercase tracking-widest mb-4 opacity-40">Description</h2>
                            <p class="text-slate-600 font-medium leading-relaxed italic text-lg">
                                "{{ $job->description }}"
                            </p>
                        </div>
                    </div>
                </section>

                @if ($isOwner || $isAssignedWorker)
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
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
                                    Once the work is done, the client marks it as paid and the worker confirms payment
                                    receipt.
                                </p>
                                @if ($paymentLifecycleLabel)
                                    <div
                                        class="mt-4 inline-flex items-center rounded-full border border-amber-200 bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-amber-700">
                                        {{ $paymentLifecycleLabel }}
                                    </div>
                                @endif
                                @if ($canViewTransferDetails)
                                    <button type="button" onclick="openModal('workerTransferDetailsModal')"
                                        class="mt-4 inline-flex items-center gap-2 rounded-2xl border border-amber-200 bg-white px-4 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-amber-700 transition-all hover:border-amber-300 hover:bg-amber-100">
                                        <i data-lucide="copy" class="h-4 w-4"></i>
                                        View Worker Account Details
                                    </button>
                                @elseif ($showCashPaymentNote)
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

                            @if ($isOwner)
                                <div
                                    class="rounded-2xl border px-4 py-4 {{ $job->rating ? 'border-violet-200 bg-violet-50/70' : 'border-slate-200 bg-slate-50' }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <p
                                            class="text-[10px] font-black uppercase tracking-widest {{ $job->rating ? 'text-violet-700' : 'text-slate-400' }}">
                                            5. Rate Worker
                                        </p>
                                        @if ($job->rating)
                                            <i data-lucide="circle-check-big" class="h-4 w-4 text-violet-600"></i>
                                        @endif
                                    </div>
                                    <p
                                        class="mt-2 text-sm font-medium {{ $job->rating ? 'text-violet-900' : 'text-slate-500' }}">
                                        Leave a rating after payment is confirmed so the worker's profile reflects this job.
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="mt-6">
                            @if ($canAcceptJob)
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <form id="job-reject-form" class="job-status-form"
                                        action="{{ route('web.app.jobs.reject', $job) }}" method="POST"
                                        data-success-target="#job-lifecycle-feedback" data-loading-text="Rejecting...">
                                        @csrf
                                        <x-button id="job-reject-submit" type="submit" color="black"
                                            variant="outline" class="w-full">
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
                                <form id="job-start-form" class="job-status-form"
                                    action="{{ route('web.app.jobs.start', $job) }}" method="POST"
                                    data-success-target="#job-lifecycle-feedback" data-loading-text="Starting...">
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
                                            class="flex h-14 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-600 transition-all hover:border-[var(--brand)] hover:text-[var(--brand)] active:scale-95">
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
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Work in
                                        Progress</p>
                                    <p class="mt-2 text-sm font-medium text-slate-600">
                                        The worker is still handling this job. You can mark it as paid once they say the
                                        work
                                        is complete.
                                    </p>
                                </div>
                            @elseif($isOwner && $job->status === 'assigned')
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Waiting
                                        for Acceptance</p>
                                    <p class="mt-2 text-sm font-medium text-slate-600">
                                        The assigned worker still needs to accept this job before work can begin.
                                    </p>
                                </div>
                            @elseif($isOwner && $job->status === 'worker_accepted')
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Ready to
                                        Start</p>
                                    <p class="mt-2 text-sm font-medium text-slate-600">
                                        The worker has accepted this job and can now mark it as in progress when work
                                        begins.
                                    </p>
                                </div>
                            @elseif($isAssignedWorker && $job->status === 'payment_pending' && $job->paid_at === null)
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                        Awaiting Client Confirmation
                                    </p>
                                    <p class="mt-2 text-sm font-medium text-slate-600">
                                        You marked the work as completed. The client still needs to review the work and mark this job as paid.
                                    </p>
                                </div>
                            @elseif($isOwner && $job->status === 'payment_pending' && $job->paid_at !== null)
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Payment Sent
                                    </p>
                                    <p class="mt-2 text-sm font-medium text-slate-600">
                                        You marked this job as paid. The worker still needs to confirm payment receipt.
                                    </p>
                                </div>
                            @elseif(in_array($job->status, ['completed', 'rated']))
                                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-4">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-700">
                                        Job Closed</p>
                                    <p class="mt-2 text-sm font-medium text-emerald-800">
                                        Payment has been confirmed and this job is now closed.
                                    </p>
                                </div>
                            @endif

                            <div id="job-lifecycle-feedback" class="mt-4 hidden rounded-2xl border px-4 py-3 text-sm">
                            </div>
                        </div>
                    </section>
                @endif
            </div>

            <aside class="lg:col-span-4 space-y-6">
                <section class="p-6 rounded-[2.5rem] bg-white border border-slate-100 shadow-sm text-center">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-30 mb-6">Posted By</p>

                    <div class="inline-flex relative mb-4">
                        <x-avatar :user="$job->client" size="h-20 w-20" rounded="rounded-3xl" text="text-3xl"
                            class="shadow-xl" />
                        @if ($job->client->is_verified)
                            <div
                                class="absolute -bottom-1 -right-1 h-7 w-7 bg-green-500 border-4 border-white rounded-full flex items-center justify-center text-white">
                                <i data-lucide="check" class="w-3 h-3"></i>
                            </div>
                        @endif
                    </div>

                    <h3 class="text-xl font-black tracking-tight text-[var(--ink)]">{{ $job->client->name }}</h3>
                    <p class="text-xs font-medium opacity-50 mb-6 line-clamp-2">"{{ $job->client->bio }}"</p>

                    <div class="flex items-center justify-center gap-4 py-4 border-t border-slate-50">
                        <div>
                            <p class="text-[10px] font-black opacity-30 uppercase">Rating</p>
                            <p class="font-black text-sm">
                                {{ $job->client->average_rating > 0 ? $job->client->average_rating : 'New' }}</p>
                        </div>
                        <div class="w-px h-6 bg-slate-100"></div>
                        <div>
                            <p class="text-[10px] font-black opacity-30 uppercase">Joined</p>
                            <p class="font-black text-sm">
                                {{ \Carbon\Carbon::parse($job->client->created_at)->format('M Y') }}</p>
                        </div>
                    </div>

                    @if ($isOwner && $job->worker)
                        <button type="button" onclick="openModal('assignedWorkerModal')"
                            class="w-full mt-4 flex items-center justify-center gap-3 py-4 rounded-2xl bg-[var(--surface-soft)] text-[var(--brand)] font-black text-[10px] uppercase tracking-widest hover:bg-[var(--brand)] hover:text-white transition-all">
                            <i data-lucide="user-round-search" class="w-4 h-4"></i>
                            View Assigned Worker
                        </button>
                    @elseif($isOwner)
                        <div
                            class="w-full mt-4 rounded-2xl border border-dashed border-slate-200 px-5 py-4 text-left bg-slate-50/80">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Hiring Flow</p>
                            <p class="mt-2 text-xs font-medium text-slate-500">
                                Review negotiation offers below to inspect worker profiles and hire directly from the offer
                                cards.
                            </p>
                        </div>
                    @elseif($canChatAsWorker)
                        @if ($existingConversation)
                            <a href="{{ route('web.app.conversations', ['conversation' => $existingConversation->uuid]) }}"
                                class="w-full mt-4 flex items-center justify-center gap-3 py-4 rounded-2xl bg-[var(--surface-soft)] text-[var(--brand)] font-black text-[10px] uppercase tracking-widest hover:bg-[var(--brand)] hover:text-white transition-all">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                Chat with Client
                            </a>
                        @else
                            <button type="button" onclick="openModal('workerChatModal')"
                                class="w-full mt-4 flex items-center justify-center gap-3 py-4 rounded-2xl bg-[var(--surface-soft)] text-[var(--brand)] font-black text-[10px] uppercase tracking-widest hover:bg-[var(--brand)] hover:text-white transition-all">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                Start Chat with Client
                            </button>
                        @endif
                    @else
                        <div
                            class="w-full mt-4 rounded-2xl border border-dashed border-slate-200 px-5 py-4 text-left bg-slate-50/80">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Chat Access</p>
                            <p class="mt-2 text-xs font-medium text-slate-500">
                                Workers need to be assigned the job first before chat with the client is unlocked.
                            </p>
                        </div>
                    @endif
                </section>

                @if ($isOwner && $canSeeAssignedWorker)
                    <section class="p-6 rounded-[2.5rem] bg-[var(--ink)] text-white shadow-xl shadow-black/10">
                        <p class="text-[10px] font-black uppercase tracking-widest opacity-40 mb-6">Assigned Worker</p>
                        <div class="flex items-center gap-4">
                            <x-avatar :user="$job->worker" size="h-12 w-12" rounded="rounded-xl" text="text-sm"
                                class="bg-white/10" />
                            <div>
                                <h4 class="text-sm font-black">{{ $job->worker->name }}</h4>
                                <div class="flex items-center gap-1 text-orange-400">
                                    <i data-lucide="star" class="w-3 h-3 fill-current"></i>
                                    <span class="text-[10px] font-bold">{{ $job->worker->rating }}</span>
                                </div>
                            </div>
                            <button type="button" onclick="openModal('assignedWorkerModal')"
                                class="rounded-2xl border border-slate-200 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-200 transition hover:border-[var(--brand)] hover:text-[var(--brand)]">
                                View Details
                            </button>
                        </div>
                    </section>
                @endif

                @if ($isWorker && !$isOwner && $job->status === 'open' && $job->assigned_to === null && $workerNeedsPayoutDetails)
                    <section class="rounded-[2rem] bg-white border border-amber-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-amber-500 mb-3">Account Details Required
                        </p>
                        <div class="rounded-2xl bg-amber-50 border border-amber-100 px-4 py-4">
                            <p class="text-xs font-black uppercase tracking-widest text-amber-700">Update payment profile</p>
                            <p class="mt-2 text-sm font-medium text-amber-900">
                                Before you apply for this job, add your bank name, account name, and account number so the client can pay you by transfer if hired.
                            </p>
                            <a href="{{ route('web.app.me') }}"
                                class="mt-4 inline-flex items-center gap-2 rounded-2xl bg-amber-500 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-amber-600">
                                <i data-lucide="badge-info" class="h-4 w-4"></i>
                                Update Profile
                            </a>
                        </div>
                    </section>
                @elseif($canApply)
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Application</p>
                        <form id="job-apply-form" action="{{ route('web.app.jobs.apply', $job) }}" method="POST"
                            class="space-y-4">
                            @csrf
                            <h4 class="text-base">Budget: <span
                                    class="font-semibold">₦{{ number_format($job->budget, 2) }}</span></h4>
                            <div class="space-y-2">
                                <x-input type="number" value="{{ $job->budget }}" label="Amount" name="amount"
                                    placeholder="Enter your offer" icon="dollar-sign" required />
                                <p class="text-[0.7rem] text-red-500">Enter a couter offer or leave empty to accept the
                                    client's offer
                                </p>
                            </div>
                            <x-input type="textarea" name="message" id="job_application_message" rows="4"
                                icon="file-pen-line" label="Message to Client"
                                placeholder="Tell the client why you are a good fit for this job..." />
                            <x-error />
                            <x-button id="job-apply-submit" type="submit" class="w-full">
                                <x-slot:icon>
                                    <i data-lucide="send" class="h-4 w-4"></i>
                                </x-slot:icon>
                                Apply for this Job
                            </x-button>
                        </form>
                    </section>
                @elseif($isAssignedWorker)
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Application Status
                        </p>
                        <div class="rounded-2xl bg-blue-50 border border-blue-100 px-4 py-4">
                            <p class="text-xs font-black uppercase tracking-widest text-blue-700">You were selected</p>
                            <p class="mt-2 text-sm font-medium text-blue-800">
                                You are the assigned worker on this job. Use the job progress section to move it forward.
                            </p>
                        </div>
                    </section>
                @elseif($isWorker && !$isOwner && $hasApplied && !$job->assigned_to)
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Application Status
                        </p>
                        <div class="rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-4">
                            <p class="text-xs font-black uppercase tracking-widest text-emerald-700">Applied</p>
                            <p class="mt-2 text-sm font-medium text-emerald-800">
                                Your application has been sent. You can now continue the conversation with the client.
                            </p>
                        </div>
                    </section>
                @elseif($isWorker && !$isOwner && $hasApplied && $job->assigned_to && !$isAssignedWorker)
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Application Status
                        </p>
                        <div class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-4">
                            <p class="text-xs font-black uppercase tracking-widest text-slate-700">Not Selected</p>
                            <p class="mt-2 text-sm font-medium text-slate-600">
                                This job has already been assigned to another worker.
                            </p>
                        </div>
                    </section>
                @elseif($isWorker && !$isOwner && $job->status !== 'open')
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Job Status</p>
                        <div class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-4">
                            <p class="text-xs font-black uppercase tracking-widest text-slate-700">
                                {{ $isAssignedWorker ? 'Assigned to You' : 'No Longer Open' }}
                            </p>
                            <p class="mt-2 text-sm font-medium text-slate-600">
                                {{ $isAssignedWorker ? 'This job has been assigned to you already.' : 'This job has already been assigned and is no longer accepting applications.' }}
                            </p>
                        </div>
                    </section>
                @endif

                {{-- @if ($job->status === 'open' || $job->negotiations->where('worker_id', auth()->id())->count() > 1)
                    <div class="mt-8 rounded-2xl border border-slate-100 bg-white p-6">

                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">
                            Negotiation
                        </h3>
                        @if ($isOwner || $hasApplied)
                            {{-- EXISTING NEGOTIATIONS
                            <div id="negotiation-list" class="space-y-3 mb-6">

                                @forelse($job->negotiations as $negotiation)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">

                                        <div>
                                            <p class="text-xs font-bold text-slate-700">
                                                ₦{{ number_format($negotiation->amount) }}
                                            </p>

                                            <p class="text-[11px] text-slate-400">
                                                {{ ucfirst($negotiation->created_by) }} •
                                                {{ $negotiation->created_at->diffForHumans() }}
                                            </p>

                                            @if ($negotiation->message)
                                                <p class="text-xs text-slate-500 mt-1">
                                                    {{ $negotiation->message }}
                                                </p>
                                            @endif
                                        </div>

                                        <span class="text-[10px] font-black uppercase text-slate-400">
                                            {{ $negotiation->status }}
                                        </span>

                                    </div>

                                @empty

                                    <p class="text-xs text-slate-400">
                                        No offers yet.
                                    </p>
                                @endforelse

                            </div>
                        @endif

                        @if (!$isOwner && $job->negotiations->where('worker_id', auth()->id())->count() < 1)
                            {{-- ACTIONS
                            <form id="negotiation-form" method="POST"
                                action="{{ route('web.app.negotiate.submit', $job) }}" class="space-y-3">
                                @csrf
                                {{ $job->negotiations->where('worker_id', auth()->id())->count() }}
                                <x-input type="number" name="amount" placeholder="Enter your offer" icon="dollar-sign"
                                    required />

                                <x-input type="textarea" name="message" placeholder="Add a message (optional)"
                                    icon="mail" rows="4" />

                                <x-error />
                                <x-button class="w-full mt-2" id="negotiation-submit" type="submit">
                                    <x-slot:icon>
                                        <i data-lucide="paper-plane" class="h-4 w-4"></i>
                                    </x-slot:icon>
                                    Send Offer
                                </x-button>

                            </form>
                        @endif

                        @if ($isOwner && $job->negotiations->count() === 0)
                            <div class="text-xs text-slate-400">
                                Waiting for a worker to submit an offer.
                            </div>
                        @endif

                        {{-- ACCEPT / REJECT (ONLY FOR CLIENT)
                        @if (auth()->id() === $job->user_id && $job->negotiations->count())
                            @if ($latest && $latest->status === 'pending')
                                <div class="flex gap-3 mt-4">

                                    <button id="accept-offer"
                                        data-url="{{ url('/api/jobs/' . $job->id . '/negotiate/accept') }}"
                                        class="flex-1 py-3 rounded-xl bg-green-600 text-white text-xs font-black uppercase">
                                        Accept
                                    </button>

                                    <button id="reject-offer"
                                        data-url="{{ url('/api/jobs/' . $job->id . '/negotiate/reject') }}"
                                        class="flex-1 py-3 rounded-xl bg-red-500 text-white text-xs font-black uppercase">
                                        Reject
                                    </button>

                                </div>
                            @endif
                        @endif

                    </div>
                @endif --}}

                @if ($showNegotiationSection)

                    <div class="mt-8 rounded-2xl border border-slate-100 bg-white p-6">

                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">
                            Negotiation
                        </h3>

                        {{-- ================= NEGOTIATION LIST ================= --}}
                        @if ($isOwner || $hasApplied)
                            <div id="negotiation-list" class="space-y-3 mb-6">

                                @php
                                    $negotiations = $visibleNegotiations;
                                @endphp

                                <div class="space-y-3">
                                    @forelse($negotiations as $negotiation)
                                        <article
                                            class="group relative overflow-hidden rounded-2xl border border-white bg-white/60 p-4 transition-all hover:bg-white hover:shadow-lg hover:shadow-black/5">
                                            <!-- Header: Compact Amount & Status -->
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div
                                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--ink)] text-[var(--brand)] shadow-sm">
                                                        <span class="text-[10px] font-black font-mono">₦</span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h4
                                                            class="text-sm font-black tracking-tight text-[var(--ink)] truncate">
                                                            {{ number_format($negotiation->amount) }}
                                                        </h4>
                                                        <p
                                                            class="text-[9px] font-bold uppercase tracking-tighter text-slate-400 truncate">
                                                            {{ $negotiation->worker->name }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <span
                                                    class="shrink-0 rounded-lg border px-2 py-0.5 text-[8px] font-black uppercase tracking-widest
                    {{ $negotiation->status === 'pending'
                        ? 'bg-amber-50 text-amber-600 border-amber-100'
                        : ($negotiation->status === 'accepted'
                            ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                            : 'bg-slate-50 text-slate-400 border-slate-100') }}">
                                                    {{ $negotiation->status }}
                                                </span>
                                            </div>

                                            <!-- Compact Message -->
                                            @if ($negotiation->message)
                                                <div class="mt-3 pl-3 border-l-2 border-slate-100">
                                                    <p
                                                        class="text-[11px] font-medium leading-relaxed text-slate-500 line-clamp-1 italic">
                                                        "{{ $negotiation->message }}"
                                                    </p>
                                                </div>
                                            @endif

                                            <!-- ✅ SLIM OWNER ACTIONS -->
                                            @if ($isOwner)
                                                <div class="mt-4 flex items-center justify-end">
                                                    <button type="button"
                                                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-[9px] font-black uppercase tracking-widest text-slate-600 transition hover:border-[var(--brand)] hover:text-[var(--brand)]"
                                                        onclick="openModal('negotiationWorkerModal{{ $negotiation->id }}')">
                                                        <i data-lucide="eye" class="w-3 h-3"></i>
                                                        View Worker
                                                    </button>
                                                </div>
                                            @endif

                                            @if ($isOwner && $negotiation->status === 'pending')
                                                <div class="mt-4 grid grid-cols-2 gap-2">
                                                    <button type="button"
                                                        class="reject-offer flex h-9 items-center justify-center gap-1.5 rounded-xl border border-rose-100 bg-rose-50 text-[9px] font-black uppercase tracking-widest text-rose-600 transition-all hover:bg-rose-600 hover:text-white"
                                                        onclick="openNegotiationDecisionModal({
                                                            action: 'reject',
                                                            url: @js(route('web.app.negotiate.reject', $negotiation)),
                                                            amount: @js($negotiation->amount),
                                                            workerName: @js($negotiation->worker->name)
                                                        })">
                                                        <i data-lucide="x" class="w-3 h-3"></i>
                                                        Reject
                                                    </button>

                                                    <button type="button"
                                                        class="accept-offer flex h-9 items-center justify-center gap-1.5 rounded-xl bg-emerald-600 text-[9px] font-black uppercase tracking-widest text-white shadow-md shadow-emerald-500/10 transition-all hover:bg-emerald-700"
                                                        onclick="openNegotiationDecisionModal({
                                                            action: 'accept',
                                                            url: @js(route('web.app.negotiate.accept', $negotiation))
                                                        })">
                                                        <i data-lucide="check" class="w-3 h-3"></i>
                                                        Accept
                                                    </button>
                                                </div>
                                            @endif
                                        </article>
                                    @empty
                                        <p
                                            class="py-4 text-center text-[10px] font-black uppercase tracking-widest text-slate-300 italic">
                                            No offers yet</p>
                                    @endforelse
                                </div>


                            </div>
                        @endif


                        {{-- ================= NEGOTIATION FORM ================= --}}
                        @if (!$isOwner && $hasApplied && (!$latestMine || $latestMine->status === 'rejected'))
                            <form id="negotiation-form" method="POST"
                                action="{{ route('web.app.negotiate.submit', $job) }}" class="space-y-3">
                                @csrf

                                <x-input type="number" name="amount" placeholder="Enter your offer" icon="dollar-sign"
                                    value="{{ $latestMine?->status === 'rejected' ? $latestMine->amount : $job->budget }}"
                                    required />

                                <x-input type="textarea" name="message"
                                    placeholder="{{ $latestMine?->status === 'rejected' ? 'Respond to the client and submit your counter amount...' : 'Add a message (optional)' }}"
                                    icon="mail" rows="4" />

                                <x-error />

                                <x-button class="w-full mt-2" id="negotiation-submit" type="submit">
                                    <x-slot:icon>
                                        <i data-lucide="paper-plane" class="h-4 w-4"></i>
                                    </x-slot:icon>
                                    {{ $latestMine?->status === 'rejected' ? 'Send Counter Offer' : 'Send Offer' }}
                                </x-button>

                            </form>
                        @endif
                        {{-- ================= EMPTY STATE FOR CLIENT ================= --}}
                        @if ($isOwner && $visibleNegotiations->count() === 0)
                            <div class="text-xs text-slate-400">
                                Waiting for a worker to submit an offer.
                            </div>
                        @endif
                    </div>

                @endif

                @if ($canRateWorker)
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Rate Worker</p>
                        <form id="job-rate-form" action="{{ route('web.app.jobs.rate', $job) }}" method="POST"
                            class="space-y-4">
                            @csrf
                            <x-input type="number" name="rating" id="job_worker_rating" min="1" max="5"
                                icon="star" label="Star Rating" placeholder="Give a score from 1 to 5" />
                            <x-input type="textarea" name="review" id="job_worker_review" rows="4"
                                icon="message-square-quote" label="Review"
                                placeholder="Share how the worker handled this job..." />
                            <x-error />
                            <x-button id="job-rate-submit" type="submit" class="w-full">
                                <x-slot:icon>
                                    <i data-lucide="star" class="h-4 w-4"></i>
                                </x-slot:icon>
                                Submit Rating
                            </x-button>
                        </form>
                    </section>
                @elseif($isOwner && $job->rating)
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Worker Rating</p>
                        <div class="rounded-2xl border border-violet-100 bg-violet-50 px-4 py-4">
                            <div class="flex items-center gap-2 text-violet-700">
                                <i data-lucide="star" class="h-4 w-4 fill-current"></i>
                                <p class="text-sm font-black">{{ number_format($job->rating->rating, 1) }}/5</p>
                            </div>
                            <p class="mt-3 text-sm font-medium text-violet-900">
                                {{ $job->rating->review ?: 'You rated this worker after the job was closed.' }}
                            </p>
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
    <x-modal id="negotiationDecisionModal" title="Confirm Negotiation">
        <div class="">
            <div class="flex flex-col items-center text-center space-y-4">
                <div id="negotiationDecisionIcon"
                    class="h-16 w-16 rounded-[2rem] bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-inner">
                    <i data-lucide="handshake" class="h-8 w-8"></i>
                </div>
                <div>
                    <h3 id="negotiationDecisionHeading" class="text-xl font-black tracking-tighter text-[var(--ink)]">
                        Confirm Selection
                    </h3>
                    <p id="negotiationDecisionText"
                        class="mt-2 text-sm font-medium leading-relaxed text-slate-500 max-w-xs mx-auto">
                        By accepting this negotiation, the worker will be officially assigned and the job status will shift
                        to <span class="text-emerald-600 font-bold italic">Assigned</span>.
                    </p>
                </div>
            </div>

            <form id="negotiationDecisionForm" method="POST" action="" class="mt-10 space-y-4">
                @csrf
                <div id="negotiationDecisionFields" class="hidden rounded-[1.75rem] border p-4 space-y-4">
                    <div class="text-left">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Counter Terms</p>
                        <p class="mt-2 text-sm font-medium text-slate-500">
                            Enter the amount you are willing to pay and explain what should change before the worker
                            responds.
                        </p>
                    </div>
                    <div class="grid gap-4">
                        <x-input type="number" name="amount" id="negotiation_reject_amount" label="Counter Amount"
                            icon="dollar-sign" placeholder="Enter the amount you are willing to pay" />
                        <x-input type="textarea" name="message" id="negotiation_reject_message" rows="4"
                            label="Reason / Counter Note" icon="message-square-warning"
                            placeholder="Tell the worker why you rejected the offer and what you want adjusted..." />
                    </div>
                </div>
                <x-error class="mb-3" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button type="button" onclick="closeModal('negotiationDecisionModal')"
                        class="flex h-14 items-center justify-center rounded-2xl border-2 border-slate-100 bg-white px-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 transition-all hover:border-slate-200 hover:text-slate-600 active:scale-95">
                        Cancel
                    </button>
                    <button type="submit" id="negotiationDecisionSubmit"
                        class="flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 text-[10px] font-black uppercase tracking-[0.2em] text-white shadow-xl shadow-emerald-500/20 transition-all hover:bg-emerald-700 active:scale-95 group">
                        <span id="negotiationDecisionSubmitText">Confirm Action</span>
                        <i id="negotiationDecisionSubmitIcon" data-lucide="check-circle"
                            class="h-4 w-4 transition-transform group-hover:scale-110"></i>
                    </button>
                </div>
            </form>
        </div>
    </x-modal>


    @if ($isOwner)
        @foreach ($visibleNegotiations as $negotiation)
            @php
                $applicant = $negotiation->worker;
                $primarySkillName = $applicant?->skill?->name;
                $additionalSkills =
                    $applicant?->skills?->reject(fn($skill) => $skill->id === $applicant->primary_skill_id) ??
                    collect();
                $workerRating = $applicant?->ratings_received_avg_rating
                    ? round((float) $applicant->ratings_received_avg_rating, 1)
                    : null;
                $completedJobs = (int) ($applicant?->ratings_received_count ?? 0);
            @endphp
            <x-modal id="negotiationWorkerModal{{ $negotiation->id }}"
                title="{{ ($applicant?->name ?? 'Worker') . ' Profile' }}" size="max-w-2xl">
                <div class="space-y-6">
                    <div class="flex flex-col gap-5 rounded-[2rem] bg-slate-50/80 p-6 sm:flex-row sm:items-start">
                        <div class="shrink-0">
                            <x-avatar :user="$applicant" name="{{ $applicant->name ?? 'Unknown worker' }}"
                                size="h-20 w-20" rounded="rounded-[1.75rem]" text="text-2xl" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-2xl font-black tracking-tight text-slate-900">
                                    {{ $applicant->name ?? 'Unknown worker' }}
                                </h3>
                                @if ($applicant?->is_verified)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-700">
                                        <i data-lucide="badge-check" class="h-3.5 w-3.5"></i>
                                        Verified
                                    </span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm font-medium leading-relaxed text-slate-500">
                                {{ $applicant->bio ?: 'This worker has not added a bio yet.' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Average Rating</p>
                            <p class="mt-3 text-2xl font-black text-slate-900">
                                {{ $workerRating !== null ? number_format($workerRating, 1) : 'New' }}
                            </p>
                        </div>
                        <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Completed Jobs</p>
                            <p class="mt-3 text-2xl font-black text-slate-900">{{ $completedJobs }}</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Availability</p>
                            <p class="mt-3 text-lg font-black text-slate-900">
                                {{ ucfirst($applicant->availability_status ?? 'unknown') }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-[1.5rem] border border-slate-100 bg-white px-5 py-5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Primary Skill</p>
                            <p class="mt-3 text-lg font-black text-slate-900">{{ $primarySkillName ?? 'Not set' }}</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-slate-100 bg-white px-5 py-5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Joined</p>
                            <p class="mt-3 text-lg font-black text-slate-900">
                                {{ $applicant?->created_at?->format('M Y') ?? 'Unknown' }}
                            </p>
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] border border-slate-100 bg-white px-5 py-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Additional Skills</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse ($additionalSkills as $skill)
                                <span
                                    class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-slate-700">
                                    {{ $skill->name }}
                                </span>
                            @empty
                                <p class="text-sm font-medium text-slate-500">No additional skills listed.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50/80 px-5 py-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Latest Offer</p>
                        <p class="mt-3 text-2xl font-black text-slate-900">
                            &#8358;{{ number_format($negotiation->amount) }}
                        </p>
                        <p class="mt-3 text-sm font-medium leading-relaxed text-slate-600">
                            {{ $negotiation->message ?: 'No offer message was included.' }}
                        </p>
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <x-button type="button" color="black" variant="outline"
                            onclick="closeModal('negotiationWorkerModal{{ $negotiation->id }}')">
                            <x-slot:icon>
                                <i data-lucide="x" class="h-4 w-4"></i>
                            </x-slot:icon>
                            Close
                        </x-button>

                        @if ($negotiation->status === 'pending')
                            <x-button type="button" color="orange"
                                onclick="openNegotiationDecisionModal({
                                    action: 'accept',
                                    url: @js(route('web.app.negotiate.accept', $negotiation))
                                })">
                                <x-slot:icon>
                                    <i data-lucide="user-check" class="h-4 w-4"></i>
                                </x-slot:icon>
                                Accept Offer
                            </x-button>
                        @endif
                    </div>
                </div>
            </x-modal>
        @endforeach
    @endif
    @if ($isOwner && $job->worker)
        <x-modal id="assignedWorkerModal" title="Assigned Worker" size="max-w-2xl">
            <div class="space-y-6">
                <div class="flex flex-col gap-5 rounded-[2rem] bg-slate-50/80 p-6 sm:flex-row sm:items-start">
                    <div class="shrink-0">
                        <x-avatar :user="$job->worker" name="{{ $job->worker->name ?? 'Assigned worker' }}" size="h-20 w-20"
                            rounded="rounded-[1.75rem]" text="text-2xl" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="text-2xl font-black tracking-tight text-slate-900">
                                {{ $job->worker->name ?? 'Assigned worker' }}
                            </h3>
                            @if ($job->worker->is_verified)
                                <span
                                    class="inline-flex items-center gap-1 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-700">
                                    <i data-lucide="badge-check" class="h-3.5 w-3.5"></i>
                                    Verified
                                </span>
                            @endif
                        </div>
                        <p class="mt-2 text-sm font-medium leading-relaxed text-slate-500">
                            {{ $job->worker->bio ?: 'This worker has not added a bio yet.' }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Rating</p>
                        <p class="mt-3 text-2xl font-black text-slate-900">
                            {{ number_format($job->worker->average_rating, 1) }}</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Primary Skill</p>
                        <p class="mt-3 text-lg font-black text-slate-900">{{ $job->worker->skill->name ?? 'Not set' }}</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Availability</p>
                        <p class="mt-3 text-lg font-black text-slate-900">
                            {{ ucfirst($job->worker->availability_status ?? 'unknown') }}</p>
                    </div>
                </div>

            </div>
        </x-modal>
    @endif
    @if ($canViewTransferDetails)
        <x-modal id="workerTransferDetailsModal" title="Worker Account Details" size="max-w-2xl">
            <div class="space-y-5">
                <div class="rounded-[2rem] border border-amber-100 bg-amber-50/80 p-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-amber-600">Transfer Details</p>
                    <h4 class="mt-2 text-lg font-black text-amber-950">Use these details for manual transfer</h4>
                    <p class="mt-2 text-sm font-medium text-amber-900">
                        Copy the worker's account details below before marking this job as paid.
                    </p>
                </div>

                <div id="assignedWorkerTransferFeedback">
                    <x-error />
                </div>

                @if ($job->worker->bank_name && $job->worker->account_name && $job->worker->account_number)
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-[1.5rem] border border-amber-100 bg-white px-4 py-5">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-300">Bank</p>
                            <div class="mt-1 flex items-center justify-between gap-2">
                                <p class="text-sm font-bold text-slate-900">{{ $job->worker->bank_name }}</p>
                                <button type="button" onclick="copyTransferDetail(@js($job->worker->bank_name), 'Bank name')"
                                    class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-slate-500 transition hover:border-[var(--brand)] hover:text-[var(--brand)]">
                                    <i data-lucide="copy" class="h-3.5 w-3.5"></i>
                                    Copy
                                </button>
                            </div>
                        </div>
                        <div class="rounded-[1.5rem] border border-amber-100 bg-white px-4 py-5">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-300">Account Name</p>
                            <div class="mt-1 flex items-center justify-between gap-2">
                                <p class="text-sm font-bold text-slate-900">{{ $job->worker->account_name }}</p>
                                <button type="button"
                                    onclick="copyTransferDetail(@js($job->worker->account_name), 'Account name')"
                                    class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-slate-500 transition hover:border-[var(--brand)] hover:text-[var(--brand)]">
                                    <i data-lucide="copy" class="h-3.5 w-3.5"></i>
                                    Copy
                                </button>
                            </div>
                        </div>
                        <div class="rounded-[1.5rem] border border-amber-100 bg-white px-4 py-5">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-300">Account Number</p>
                            <div class="mt-1 flex items-center justify-between gap-2">
                                <p class="text-sm font-bold text-slate-900">{{ $job->worker->account_number }}</p>
                                <button type="button"
                                    onclick="copyTransferDetail(@js($job->worker->account_number), 'Account number')"
                                    class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-slate-500 transition hover:border-[var(--brand)] hover:text-[var(--brand)]">
                                    <i data-lucide="copy" class="h-3.5 w-3.5"></i>
                                    Copy
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-[1.5rem] border border-slate-100 bg-white px-5 py-5">
                        <p class="text-sm font-medium text-slate-500">
                            This worker has not added payment account details yet.
                        </p>
                    </div>
                @endif
            </div>
        </x-modal>
    @endif

    @if ($canChatAsWorker && !$existingConversation)
        <x-modal id="workerChatModal" title="Start Chat with Client" size="max-w-xl">
            <form id="job-chat-starter-form" action="{{ route('web.app.messages.send') }}" method="POST"
                class="space-y-5">
                @csrf
                <input type="hidden" name="job_id" value="{{ $job->id }}">

                <div>
                    <p class="text-xs font-medium text-slate-500">
                        Your application is already on this job. Send the first message to open the conversation with
                        {{ $job->client->name }}.
                    </p>
                </div>

                <x-input type="textarea" name="message" id="job_chat_message" rows="5" icon="message-square-text"
                    label="Message" placeholder="Introduce yourself and tell the client how you can help..." />

                <x-error />

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <x-button type="button" color="black" variant="outline" onclick="closeModal('workerChatModal')">
                        <x-slot:icon>
                            <i data-lucide="x" class="h-4 w-4"></i>
                        </x-slot:icon>
                        Cancel
                    </x-button>

                    <x-button id="job-chat-starter-submit" type="submit">
                        <x-slot:icon>
                            <i data-lucide="send" class="h-4 w-4"></i>
                        </x-slot:icon>
                        Send Message
                    </x-button>
                </div>
            </form>
        </x-modal>
    @endif
@endsection
