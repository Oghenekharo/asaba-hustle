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
                $applicant?->skills?->reject(fn($skill) => $skill->id === $applicant->primary_skill_id) ?? collect();
            $workerRating = $applicant?->average_rating ? round((float) $applicant->average_rating, 1) : null;
            $completedJobs = (int) ($applicant?->ratings_received_count ?? 0);
        @endphp
        <x-modal id="negotiationWorkerModal{{ $negotiation->id }}"
            title="{{ ($applicant?->name ?? 'Worker') . ' Profile' }}" size="max-w-2xl">
            <div class="space-y-6">
                <div class="flex flex-col gap-5 rounded-[2rem] bg-slate-50/80 p-6 sm:flex-row sm:items-start">
                    <div class="shrink-0">
                        <x-avatar :user="$applicant" name="{{ $applicant->name ?? 'Unknown worker' }}" size="h-20 w-20"
                            rounded="rounded-[1.75rem]" text="text-2xl" />
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
                                                            url: '{{ route('web.app.negotiate.accept', $negotiation->id) }}',
                                                            modalToClose: 'negotiationWorkerModal{{ $negotiation->id }}',
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
    <x-user-profile :user="$job->worker" id="assignedWorkerModal" title="Assigned Worker" />
@elseif(!$isOwner)
    <x-user-profile :user="$job->client" id="jobOwnerModal" title="Job Owner" />
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
            <div class="flex gap-3 items-center">
                <h4 class="text-lg font-semibold">Agreed Amount:</h4>
                <p class="text-lg font-black text-slate-900">₦{{ number_format($agreedAmount, 2) }}</p>
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
                            <button type="button"
                                onclick="copyTransferDetail(@js($job->worker->bank_name), 'Bank name')"
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

@if ($canChatOnJob && !$existingConversation)
    <x-modal id="jobChatStarterModal" title="Start Chat with {{ $chatPartnerLabel }}" size="max-w-xl">
        <form id="job-chat-starter-form" action="{{ route('web.app.messages.send') }}" method="POST"
            class="space-y-5">
            @csrf
            <input type="hidden" name="job_id" value="{{ $job->id }}">

            <div>
                <p class="text-xs font-medium text-slate-500">
                    Send the first message to open the conversation with {{ $chatPartnerName }} for this job.
                </p>
            </div>

            <x-input type="textarea" name="message" id="job_chat_message" rows="5" icon="message-square-text"
                label="Message" placeholder="Send a quick message to coordinate the job details..." />

            <x-error />

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button type="button" color="black" variant="outline"
                    onclick="closeModal('jobChatStarterModal')">
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
