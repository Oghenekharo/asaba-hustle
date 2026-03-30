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
                                        <h4 class="text-sm font-black tracking-tight text-[var(--ink)] truncate">
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
                                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-[9px] font-black uppercase text-slate-600 transition hover:border-[var(--brand)] hover:text-[var(--brand)]"
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
            <form id="negotiation-form" method="POST" action="{{ route('web.app.negotiate.submit', $job) }}"
                class="space-y-3">
                @csrf

                <x-input type="number" name="amount" placeholder="Enter your offer" icon="dollar-sign"
                    value="{{ $latestMine?->status === 'rejected' ? $latestMine->amount : $job->budget }}" required />

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
