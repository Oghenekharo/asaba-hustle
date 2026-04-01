@extends('layouts.app', ['title' => $job->title . ' | Asaba Hustle'])

@php
    use App\Models\Payment;
    use App\Models\ServiceJob;

    $viewer = auth()->user();
    $isOwner = $viewer->id === $job->user_id;
    $isWorker = $viewer->hasRole('worker');
    $hasApplied = filled($existingApplication ?? null);
    $clientRating = $job->rating;
    $workerRating = $job->workerRating;
    $workerNeedsPayoutDetails =
        $isWorker &&
        !$isOwner &&
        (!filled($viewer->bank_name) || !filled($viewer->account_name) || !filled($viewer->account_number));
    $workerNeedsVerification = $isWorker && (!$viewer->is_verified || !filled($viewer->id_document));

    $canApply =
        $isWorker &&
        !$isOwner &&
        !$hasApplied &&
        !$workerNeedsPayoutDetails &&
        !$workerNeedsVerification &&
        $job->status === 'open' &&
        $job->assigned_to === null &&
        $viewer->availability_status === 'available';

    $isAssignedWorker = (int) $job->assigned_to === (int) $viewer->id;
    $canSeeAssignedWorker = $job->worker && ($isOwner || $isAssignedWorker);
    $canChatOnJob =
        $job->worker &&
        in_array($job->status, ServiceJob::chatEligibleStatuses(), true) &&
        ($isOwner || $isAssignedWorker);
    $chatPartnerLabel = $isOwner ? Str::before($job->worker?->name, ' ') : Str::before($job->client?->name, ' ');
    $chatPartnerName = $isOwner ? $job->worker?->name : $job->client?->name;
    $canAcceptJob = $isAssignedWorker && $job->status === 'assigned';
    $canRejectJob = $isAssignedWorker && $job->status === 'assigned';
    $canStartJob = $isAssignedWorker && $job->status === 'worker_accepted';
    $canCompleteJob = $isAssignedWorker && $job->status === 'in_progress';
    $canMarkPaid = $isOwner && $job->status === 'payment_pending' && $job->paid_at === null;
    $canConfirmPayment = $isAssignedWorker && $job->status === 'payment_pending' && $job->paid_at !== null;
    $canRateWorker = $isOwner && in_array($job->status, ['completed', 'rated']) && !$clientRating && $job->worker;
    $canRateClient =
        $isAssignedWorker && in_array($job->status, ['completed', 'rated']) && !$workerRating && $job->client;
    $canOpenRatingModal = $canRateWorker || $canRateClient;
    $ratingModalId = $canOpenRatingModal ? 'jobRatingModal' : null;
    $canViewTransferDetails =
        $isOwner && $job->worker && $job->status === 'payment_pending' && $job->payment_method === 'transfer';
    $showCashPaymentNote =
        $isOwner && $job->worker && $job->status === 'payment_pending' && $job->payment_method === 'cash';
    $paymentPayload = $job->payment?->provider_payload ?? [];
    $paymentReceiptUrl = data_get($paymentPayload, 'receipt_url');
    $paymentReceiptOriginalName = data_get($paymentPayload, 'receipt_original_name');
    $paymentReceiptUploadedAt = data_get($paymentPayload, 'receipt_uploaded_at');
    $canUploadTransferReceipt =
        $canMarkPaid && $job->payment_method === 'transfer';
    $canViewTransferReceipt =
        $paymentReceiptUrl &&
        (
            ($isOwner && in_array($job->status, ['payment_pending', 'completed', 'rated'])) ||
            ($isAssignedWorker && $job->status === 'payment_pending' && $job->paid_at !== null) ||
            ($isAssignedWorker && in_array($job->status, ['completed', 'rated']))
        );
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
    $agreedAmount = $job->agreed_amount ?? null;
    $paymentLifecycleStatus = match (true) {
        $job->status !== 'payment_pending' => $job->payment?->status,
        $job->paid_at === null => Payment::STATUS_AWAITING_CONFIRMATION,
        default => $job->payment?->status ?? Payment::STATUS_PENDING,
    };
    $paymentLifecycleLabel = $paymentLifecycleStatus ? str_replace('_', ' ', $paymentLifecycleStatus) : null;
    $clientLatitude = $job->client?->latitude ?? $job->latitude;
    $clientLongitude = $job->client?->longitude ?? $job->longitude;
    $workerRouteSourceLatitude = $viewer->latitude;
    $workerRouteSourceLongitude = $viewer->longitude;
    $showWorkerToClientMap = $isWorker && !$isOwner && $job->status === ServiceJob::STATUS_OPEN;
@endphp



@section('content')
    <div class="max-w-5xl mx-auto pt-20" id="job-detail-page" data-job-id="{{ $job->id }}"
        data-job-status="{{ $job->status }}"
        data-can-open-rating-modal="{{ $canOpenRatingModal ? 'true' : 'false' }}"
        data-rating-modal-id="{{ $ratingModalId }}">
        <a href="{{ route('web.app.jobs') }}"
            class="inline-flex items-center gap-2 text-[10px] font-black uppercase opacity-40 hover:opacity-100 hover:text-[var(--brand)] transition-all mb-8">
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
                                class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase border border-emerald-100">
                                {{ Str::replace('_', ' ', $job->status) }}
                            </span>
                            <span class="text-[10px] font-bold opacity-30 uppercase tracking-tighter italic">Ref:
                                #AH-{{ $job->id }}</span>
                        </div>

                        <h1 class="text-4xl md:text-5xl font-black tracking-tighter text-[var(--ink)] leading-[1.1] mb-6">
                            {{ $job->title }}
                        </h1>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 py-8 border-y border-slate-50">
                            <div>
                                <p class="text-[10px] font-black uppercase opacity-30 mb-1">
                                    {{ $agreedAmount !== null ? 'Listed Budget' : 'Budget' }}
                                </p>
                                <p class="text-xl font-black text-emerald-600">&#8358;{{ number_format($job->budget) }}</p>
                            </div>
                            @if ($agreedAmount !== null)
                                <div>
                                    <p class="text-[10px] font-black uppercase opacity-30 mb-1">Agreed Fee
                                    </p>
                                    <p class="text-xl font-black text-[var(--ink)]">
                                        &#8358;{{ number_format($agreedAmount) }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="text-[10px] font-black uppercase opacity-30 mb-1">Category</p>
                                <p class="text-xl font-black text-[var(--ink)]">{{ $job->skill->name }}</p>
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <p class="text-[10px] font-black uppercase opacity-30 mb-1">Location</p>
                                <p class="text-sm font-bold text-[var(--ink)] truncate">{{ $job->location }}</p>
                            </div>
                        </div>

                        <div class="mt-8">
                            <h2 class="text-xs font-black uppercase mb-4 opacity-40">Description</h2>
                            <p class="text-slate-600 font-medium leading-relaxed italic text-lg">
                                "{{ $job->description }}"
                            </p>
                        </div>
                    </div>
                </section>

                @include('web.job-detail.partials.lifecycle')

                @if ($showWorkerToClientMap)
                    <x-route-map title="Distance To Client" :source-latitude="$workerRouteSourceLatitude" :source-longitude="$workerRouteSourceLongitude"
                        :destination-latitude="$clientLatitude" :destination-longitude="$clientLongitude" source-label="You"
                        destination-label="Client" />
                @endif
            </div>

            <aside class="lg:col-span-4 space-y-6">
                @if ($isOwner && $job->worker && $canSeeAssignedWorker)
                    <x-job-entity :isOwner='true' :user="$job->worker" :canChat="$canChatOnJob" :existingConversation="$existingConversation" :partnerLabel="$chatPartnerLabel"
                        modal="assignedWorkerModal" />
                @elseif(!$isOwner)
                    <x-job-entity :isOwner="false" :user="$job->client" :canChat="$canChatOnJob" :existingConversation="$existingConversation" :partnerLabel="$chatPartnerLabel"
                        modal="jobOwnerModal" />
                @endif

                @if ($isWorker && !$isOwner && $job->status === 'open' && $job->assigned_to === null && $workerNeedsPayoutDetails)
                    <section class="rounded-[2rem] bg-white border border-amber-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase text-amber-500 mb-3">Account Details
                            Required
                        </p>
                        <div class="rounded-2xl bg-amber-50 border border-amber-100 px-4 py-4">
                            <p class="text-xs font-black uppercase text-amber-700">Update payment profile
                            </p>
                            <p class="mt-2 text-sm font-medium text-amber-900">
                                Before you apply for this job, add your bank name, account name, and account number so the
                                client can pay you by transfer if hired.
                            </p>
                            <a href="{{ route('web.app.me') }}"
                                class="mt-4 inline-flex items-center gap-2 rounded-2xl bg-amber-500 px-4 py-3 text-[10px] font-black uppercase text-white transition hover:bg-amber-600">
                                <i data-lucide="badge-info" class="h-4 w-4"></i>
                                Update Profile
                            </a>
                        </div>
                    </section>
                @elseif($isWorker && !$isOwner && $job->status === 'open' && $job->assigned_to === null && $workerNeedsVerification)
                    <section class="rounded-[2rem] bg-white border border-amber-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase text-amber-500 mb-3">Verification
                            Required
                        </p>
                        <div class="rounded-2xl bg-amber-50 border border-amber-100 px-4 py-4">
                            <p class="text-xs font-black uppercase text-amber-700">ID verification pending
                            </p>
                            <p class="mt-2 text-sm font-medium text-amber-900">
                                Complete ID verification from your profile before applying for jobs.
                            </p>
                            <a href="{{ route('web.app.me') }}"
                                class="mt-4 inline-flex items-center gap-2 rounded-2xl bg-amber-500 px-4 py-3 text-[10px] font-black uppercase text-white transition hover:bg-amber-600">
                                <i data-lucide="shield-check" class="h-4 w-4"></i>
                                Open Profile
                            </a>
                        </div>
                    </section>
                @elseif($canApply)
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-4">Application</p>
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
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-3">Application Status
                        </p>
                        <div class="rounded-2xl bg-blue-50 border border-blue-100 px-4 py-4">
                            <p class="text-xs font-black uppercase text-blue-700">You were selected</p>
                            <p class="mt-2 text-sm font-medium text-blue-800">
                                You are the assigned worker on this job. Use the job progress section to move it forward.
                            </p>
                        </div>
                    </section>
                @elseif($isWorker && !$isOwner && $hasApplied && !$job->assigned_to)
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-3">Application Status
                        </p>
                        <div class="rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-4">
                            <p class="text-xs font-black uppercase text-emerald-700">Applied</p>
                            <p class="mt-2 text-sm font-medium text-emerald-800">
                                Your application has been sent. You can now continue the conversation with the client.
                            </p>
                        </div>
                    </section>
                @elseif($isWorker && !$isOwner && $hasApplied && $job->assigned_to && !$isAssignedWorker)
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-3">Application Status
                        </p>
                        <div class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-4">
                            <p class="text-xs font-black uppercase text-slate-700">Not Selected</p>
                            <p class="mt-2 text-sm font-medium text-slate-600">
                                This job has already been assigned to another worker.
                            </p>
                        </div>
                    </section>
                @elseif($isWorker && !$isOwner && $job->status !== 'open')
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-3">Job Status</p>
                        <div class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-4">
                            <p class="text-xs font-black uppercase text-slate-700">
                                {{ $isAssignedWorker ? 'Assigned to You' : 'No Longer Open' }}
                            </p>
                            <p class="mt-2 text-sm font-medium text-slate-600">
                                {{ $isAssignedWorker ? 'This job has been assigned to you already.' : 'This job has already been assigned and is no longer accepting applications.' }}
                            </p>
                        </div>
                    </section>
                @endif

                @include('web.job-detail.partials.negotiation')
                @include('web.job-detail.partials.rating')
            </aside>
        </div>
    </div>
    @include('web.job-detail.partials.modals')
@endsection
