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
    $canViewTransferDetails =
        $isOwner && $job->worker && $job->status === 'payment_pending' && $job->payment_method === 'transfer';
    $showCashPaymentNote =
        $isOwner && $job->worker && $job->status === 'payment_pending' && $job->payment_method === 'cash';
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

                @if ($showNegotiationSection)

                    <div class="mt-8 rounded-2xl border border-slate-100 bg-white p-6">

                        <h3 class="text-sm font-black uppercase text-slate-500 mb-4">
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
                                                    class="shrink-0 rounded-lg border px-2 py-0.5 text-[8px] font-black uppercase
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
                                                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-[9px] font-black uppercase text-slate-600 transition hover:border-[var(--brand)] hover:text-[var(--brand)]"
                                                        onclick="openModal('negotiationWorkerModal{{ $negotiation->id }}')">
                                                        <i data-lucide="eye" class="w-3 h-3"></i>
                                                        Worker Profile
                                                    </button>
                                                </div>
                                            @endif

                                            @if ($isOwner && $negotiation->status === 'pending')
                                                <div class="mt-4 grid grid-cols-3 gap-2">
                                                    <button type="button"
                                                        class="reject-offer flex h-9 items-center justify-center gap-1.5 rounded-xl border border-rose-100 bg-rose-50 text-[9px] font-black uppercase text-rose-600 transition-all hover:bg-rose-600 hover:text-white"
                                                        onclick="openNegotiationDecisionModal({
                                                            action: 'reject',
                                                            url: @js(route('web.app.negotiate.reject', $negotiation)),
                                                            workerName: @js($negotiation->worker->name)
                                                        })">
                                                        <i data-lucide="x" class="w-3 h-3"></i>
                                                        Reject
                                                    </button>

                                                    <button type="button"
                                                        class="flex h-9 items-center justify-center gap-1.5 rounded-xl border border-amber-100 bg-amber-50 text-[9px] font-black uppercase text-amber-700 transition-all hover:bg-amber-500 hover:text-white"
                                                        onclick="openNegotiationDecisionModal({
                                                            action: 'counter',
                                                            url: @js(route('web.app.negotiate.counter', $negotiation)),
                                                            amount: @js($negotiation->amount),
                                                            workerName: @js($negotiation->worker->name)
                                                        })">
                                                        <i data-lucide="arrow-left-right" class="w-3 h-3"></i>
                                                        Counter
                                                    </button>

                                                    <button type="button"
                                                        class="accept-offer flex h-9 items-center justify-center gap-1.5 rounded-xl bg-emerald-600 text-[9px] font-black uppercase text-white shadow-md shadow-emerald-500/10 transition-all hover:bg-emerald-700"
                                                        onclick="openNegotiationDecisionModal({
                                                            action: 'accept',
                                                            url: @js(route('web.app.negotiate.accept', $negotiation))
                                                        })">
                                                        <i data-lucide="check" class="w-3 h-3"></i>
                                                        Accept
                                                    </button>
                                                </div>
                                            @elseif(
                                                !$isOwner &&
                                                    $isWorker &&
                                                    $negotiation->status === 'pending' &&
                                                    (int) $negotiation->worker_id === (int) $viewer->id &&
                                                    $negotiation->created_by === 'client')
                                                <div class="mt-4 grid grid-cols-2 gap-2">
                                                    <button type="button"
                                                        class="flex h-9 items-center justify-center gap-1.5 rounded-xl border border-amber-100 bg-amber-50 text-[9px] font-black uppercase text-amber-700 transition-all hover:bg-amber-500 hover:text-white"
                                                        onclick="openNegotiationDecisionModal({
                                                            action: 'counter',
                                                            url: @js(route('web.app.negotiate.counter', $negotiation)),
                                                            amount: @js($negotiation->amount)
                                                        })">
                                                        <i data-lucide="arrow-left-right" class="w-3 h-3"></i>
                                                        Counter
                                                    </button>

                                                    <button type="button"
                                                        class="accept-offer flex h-9 items-center justify-center gap-1.5 rounded-xl bg-emerald-600 text-[9px] font-black uppercase text-white shadow-md shadow-emerald-500/10 transition-all hover:bg-emerald-700"
                                                        onclick="openNegotiationDecisionModal({
                                                            action: 'accept',
                                                            url: '{{ route('web.app.negotiate.accept', $negotiation->id) }}',
                                                            modalToClose: 'negotiationWorkerModal{{ $negotiation->id }}',
                                                        })">
                                                        <i data-lucide="check" class="w-3 h-3"></i>
                                                        Accept
                                                    </button>
                                                </div>
                                            @endif
                                        </article>
                                    @empty
                                        <p class="py-4 text-center text-[10px] font-black uppercase text-slate-300 italic">
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

                @if ($canRateWorker || $canRateClient)
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-4">
                            {{ $canRateWorker ? 'Rate Worker' : 'Rate Client' }}
                        </p>
                        <form id="job-rate-form" action="{{ route('web.app.jobs.rate', $job) }}" method="POST"
                            class="space-y-4">
                            @csrf
                            <x-select :options="[
                                '{{ (int) 1 }}' => '1 Star',
                                '{{ (int) 2 }}' => '2 Stars',
                                '{{ (int) 3 }}' => '3 Stars',
                                '{{ (int) 4 }}' => '4 Stars',
                                '{{ (int) 5 }}' => '5 Stars',
                            ]" name="rating" icon="star" id="job_worker_rating"
                                label="Star Rating" placeholder="Give a score from 1 to 5" required />

                            <x-input type="textarea" name="review" id="job_worker_review" rows="4"
                                icon="message-square-quote" label="Review"
                                placeholder="{{ $canRateWorker ? 'Share how the worker handled this job...' : 'Share how the client handled this job...' }}" />
                            <x-error />
                            <x-button id="job-rate-submit" type="submit" class="w-full">
                                <x-slot:icon>
                                    <i data-lucide="star" class="h-4 w-4"></i>
                                </x-slot:icon>
                                Submit Rating
                            </x-button>
                        </form>
                    </section>
                @elseif($isOwner && $clientRating)
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-4">Worker Rating</p>
                        <div class="rounded-2xl border border-violet-100 bg-violet-50 px-4 py-4">
                            <div class="flex items-center gap-2 text-violet-700">
                                <i data-lucide="star" class="h-4 w-4 fill-current"></i>
                                <p class="text-sm font-black">{{ number_format($clientRating->rating, 1) }}/5</p>
                            </div>
                            <p class="mt-3 text-sm font-medium text-violet-900">
                                {{ $clientRating->review ?: 'You rated this worker after the job was closed.' }}
                            </p>
                        </div>
                    </section>
                @elseif($isAssignedWorker && $workerRating)
                    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-4">Client Rating</p>
                        <div class="rounded-2xl border border-violet-100 bg-violet-50 px-4 py-4">
                            <div class="flex items-center gap-2 text-violet-700">
                                <i data-lucide="star" class="h-4 w-4 fill-current"></i>
                                <p class="text-sm font-black">{{ number_format($workerRating->rating, 1) }}/5</p>
                            </div>
                            <p class="mt-3 text-sm font-medium text-violet-900">
                                {{ $workerRating->review ?: 'You rated this client after the job was closed.' }}
                            </p>
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
    @include('web.job-detail.partials.modals')
@endsection
