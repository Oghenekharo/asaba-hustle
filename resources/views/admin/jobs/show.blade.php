@extends('admin.layout')

@section('title', 'Job Review')
@section('admin-page-title', 'Job Review')

@section('content')
    @php
        $stageReached = fn($job, array $states) => in_array($job->status, $states, true);
        $canCancelJob = in_array($job->status, \App\Models\ServiceJob::adminCancellableStatuses(), true);
        $rollbackTargets = \App\Models\ServiceJob::adminRollbackTargets($job->status);
        $rollbackLabels = [
            \App\Models\ServiceJob::STATUS_WORKER_ACCEPTED => 'Worker Accepted',
            \App\Models\ServiceJob::STATUS_IN_PROGRESS => 'In Progress',
            \App\Models\ServiceJob::STATUS_PAYMENT_PENDING => 'Payment Pending',
            \App\Models\ServiceJob::STATUS_COMPLETED => 'Completed',
        ];

        $timelineStages = [
            [
                'title' => 'Job posted',
                'description' => 'The client created the job and opened it to interested workers.',
                'complete' => true,
                'active' => $job->status === \App\Models\ServiceJob::STATUS_OPEN,
            ],
            [
                'title' => 'Worker assigned',
                'description' => 'An applicant was selected for the job.',
                'complete' => filled($job->assigned_to),
                'active' => $job->status === \App\Models\ServiceJob::STATUS_ASSIGNED,
            ],
            [
                'title' => 'Worker accepted',
                'description' => 'The assigned worker confirmed they are taking the job.',
                'complete' => $stageReached($job, [
                    \App\Models\ServiceJob::STATUS_WORKER_ACCEPTED,
                    \App\Models\ServiceJob::STATUS_IN_PROGRESS,
                    \App\Models\ServiceJob::STATUS_PAYMENT_PENDING,
                    \App\Models\ServiceJob::STATUS_COMPLETED,
                    \App\Models\ServiceJob::STATUS_RATED,
                ]),
                'active' => $job->status === \App\Models\ServiceJob::STATUS_WORKER_ACCEPTED,
            ],
            [
                'title' => 'Work in progress',
                'description' => 'The worker started the job.',
                'complete' => $stageReached($job, [
                    \App\Models\ServiceJob::STATUS_IN_PROGRESS,
                    \App\Models\ServiceJob::STATUS_PAYMENT_PENDING,
                    \App\Models\ServiceJob::STATUS_COMPLETED,
                    \App\Models\ServiceJob::STATUS_RATED,
                ]),
                'active' => $job->status === \App\Models\ServiceJob::STATUS_IN_PROGRESS,
            ],
            [
                'title' => 'Awaiting payment confirmation',
                'description' => 'The worker marked the work as complete and is waiting for payment confirmation.',
                'complete' => $stageReached($job, [
                    \App\Models\ServiceJob::STATUS_PAYMENT_PENDING,
                    \App\Models\ServiceJob::STATUS_COMPLETED,
                    \App\Models\ServiceJob::STATUS_RATED,
                ]),
                'active' => $job->status === \App\Models\ServiceJob::STATUS_PAYMENT_PENDING,
            ],
            [
                'title' => 'Job closed',
                'description' => 'Payment was confirmed and the job was successfully closed.',
                'complete' => $stageReached($job, [
                    \App\Models\ServiceJob::STATUS_COMPLETED,
                    \App\Models\ServiceJob::STATUS_RATED,
                ]),
                'active' => $job->status === \App\Models\ServiceJob::STATUS_COMPLETED,
            ],
            [
                'title' => 'Rated',
                'description' => 'The client submitted a rating for the worker.',
                'complete' => $job->status === \App\Models\ServiceJob::STATUS_RATED,
                'active' => $job->status === \App\Models\ServiceJob::STATUS_RATED,
            ],
        ];

        $statusConfig = [
            'open' => [
                'color' => 'bg-emerald-500',
                'bg' => 'bg-emerald-50',
                'text' => 'text-emerald-700',
                'border' => 'border-emerald-100',
            ],
            'assigned' => [
                'color' => 'bg-blue-500',
                'bg' => 'bg-blue-50',
                'text' => 'text-blue-700',
                'border' => 'border-blue-100',
            ],
            'worker_accepted' => [
                'color' => 'bg-indigo-500',
                'bg' => 'bg-indigo-50',
                'text' => 'text-indigo-700',
                'border' => 'border-indigo-100',
            ],
            'in_progress' => [
                'color' => 'bg-amber-500',
                'bg' => 'bg-amber-50',
                'text' => 'text-amber-700',
                'border' => 'border-amber-100',
            ],
            'payment_pending' => [
                'color' => 'bg-orange-600',
                'bg' => 'bg-orange-50',
                'text' => 'text-orange-700',
                'border' => 'border-orange-100',
            ],
            'completed' => [
                'color' => 'bg-slate-600',
                'bg' => 'bg-slate-50',
                'text' => 'text-slate-700',
                'border' => 'border-slate-200',
            ],
            'rated' => [
                'color' => 'bg-violet-600',
                'bg' => 'bg-violet-50',
                'text' => 'text-violet-700',
                'border' => 'border-violet-200',
            ],
            'cancelled' => [
                'color' => 'bg-rose-600',
                'bg' => 'bg-rose-50',
                'text' => 'text-rose-700',
                'border' => 'border-rose-200',
            ],
        ];
        $config = $statusConfig[$job->status] ?? $statusConfig['open'];
    @endphp

    <section class="rounded-[2.3rem] border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-xl sm:p-8">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
            <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between p-2">
                <div class="flex items-start gap-5">
                    <!-- Visual Category Icon -->
                    <div
                        class="h-6 md:h-16 w-6 md:w-16 shrink-0 rounded-[2rem] bg-[var(--ink)] text-white flex items-center justify-center shadow-2xl shadow-slate-950/20 transition-transform hover:rotate-3">
                        <i data-lucide="{{ $job->skill->icon ?? 'briefcase' }}"
                            class="h-3 w-3 md:h-8 md:w-8 text-orange-400"></i>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[var(--brand)]">Operations Audit
                            </p>
                            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">ID:
                                #{{ $job->id }}</p>
                        </div>

                        <h2 class="text-3xl font-black tracking-tighter text-[var(--ink)] sm:text-4xl leading-tight">
                            {{ $job->title }}
                        </h2>

                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <div
                                class="inline-flex items-center gap-2 rounded-xl border {{ $config['border'] }} {{ $config['bg'] }} px-3 py-1.5 shadow-sm">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $config['color'] }} opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 {{ $config['color'] }}"></span>
                                </span>
                                <span class="text-[10px] font-black uppercase tracking-widest {{ $config['text'] }}">
                                    {{ str_replace('_', ' ', $job->status) }}
                                </span>
                            </div>

                            @if ($job->location)
                                <div
                                    class="flex items-center gap-1.5 rounded-xl border border-slate-100 bg-white px-3 py-1.5 shadow-sm text-slate-500">
                                    <i data-lucide="map-pin" class="h-3 w-3"></i>
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider italic">{{ $job->location }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>


            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.jobs.index') }}"
                    class="inline-flex items-center gap-2 rounded-[1.35rem] border border-slate-200 bg-white px-4 py-3 text-xs font-black uppercase text-slate-500 transition hover:border-slate-300 hover:text-slate-900">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    Go back
                </a>
                @foreach ($rollbackTargets as $targetStatus)
                    <form method="POST" action="{{ route('admin.jobs.rollback', $job) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="target_status" value="{{ $targetStatus }}">
                        <button
                            class="rounded-[1.35rem] border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-black uppercase text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                            Roll Back To {{ $rollbackLabels[$targetStatus] ?? str_replace('_', ' ', $targetStatus) }}
                        </button>
                    </form>
                @endforeach
                @if ($canCancelJob)
                    <form method="POST" action="{{ route('admin.jobs.cancel', $job) }}">
                        @csrf
                        @method('PATCH')
                        <button class="rounded-[1.35rem] bg-rose-600 px-4 py-3 text-xs font-black uppercase text-white">
                            Cancel
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            @include('admin.components.card', [
                'title' => 'Budget',
                'value' => 'N' . number_format((float) $job->budget, 2),
                'icon' => 'banknote',
                'tone' => 'orange',
            ])
            @include('admin.components.card', [
                'title' => 'Skill',
                'value' => $job->skill->name ?? 'n/a',
                'icon' => 'sparkles',
                'tone' => 'blue',
            ])
            @include('admin.components.card', [
                'title' => 'Applications',
                'value' => number_format($job->applications->count()),
                'icon' => 'files',
                'tone' => 'emerald',
            ])
            @include('admin.components.card', [
                'title' => 'Assigned Worker',
                'value' => $job->worker->name ?? 'Not assigned',
                'icon' => 'hard-hat',
                'tone' => 'violet',
            ])
        </div>

        @if (!empty($rollbackTargets))
            <div class="mt-6 rounded-[2rem] border border-amber-100 bg-amber-50/80 p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-amber-600">Admin Recovery</p>
                <p class="mt-2 text-sm font-semibold text-amber-900">
                    If a worker moved this job forward by mistake, you can roll it back to an earlier valid stage.
                </p>
                <p class="mt-2 text-xs font-medium text-amber-800">
                    Available rollback targets for this job:
                    {{ collect($rollbackTargets)->map(fn ($status) => $rollbackLabels[$status] ?? str_replace('_', ' ', $status))->implode(', ') }}.
                </p>
            </div>
        @endif
    </section>

    {{-- <div class="space-y-6">
        <div class="rounded-[2rem] border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-xl">
            <h3 class="text-lg font-black text-slate-950">Job Timeline</h3>
            <div class="mt-5 space-y-4">
                @foreach ($timelineStages as $stage)
                    <div
                        class="flex gap-3 rounded-[1.5rem] border px-4 py-4 {{ $stage['complete'] ? 'border-emerald-100 bg-emerald-50/70' : ($stage['active'] ? 'border-orange-100 bg-orange-50/70' : 'border-slate-100 bg-slate-50') }}">
                        <span
                            class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl {{ $stage['complete'] ? 'bg-emerald-600 text-white' : ($stage['active'] ? 'bg-orange-500 text-white' : 'bg-white text-slate-400') }}">
                            <i data-lucide="{{ $stage['complete'] ? 'check' : ($stage['active'] ? 'clock-3' : 'circle') }}"
                                class="h-4 w-4"></i>
                        </span>
                        <div>
                            <p class="text-sm font-black text-slate-900">{{ $stage['title'] }}</p>
                            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">{{ $stage['description'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-xl">
            <h3 class="text-lg font-black text-slate-950">Payment</h3>
            @if ($job->payment)
                <div class="mt-5 space-y-3">
                    <div class="rounded-[1.4rem] border border-slate-100 bg-slate-50 px-4 py-4">
                        <p class="text-[10px] font-black uppercase text-slate-400">Reference</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ $job->payment->reference }}</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-[1.4rem] border border-slate-100 bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Status</p>
                            <p class="mt-1 text-sm font-semibold capitalize text-slate-700">{{ $job->payment->status }}
                            </p>
                        </div>
                        <div class="rounded-[1.4rem] border border-slate-100 bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Method</p>
                            <p class="mt-1 text-sm font-semibold capitalize text-slate-700">
                                {{ $job->payment->payment_method }}</p>
                        </div>
                    </div>
                    @if ($job->paid_at)
                        <div class="rounded-[1.4rem] border border-slate-100 bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase text-slate-400">Marked Paid</p>
                            <p class="mt-1 text-sm font-semibold text-slate-700">
                                {{ $job->paid_at->format('d M Y, h:i A') }}</p>
                        </div>
                    @endif
                </div>
            @else
                <p class="mt-4 text-sm font-semibold text-slate-500">No payment record.</p>
            @endif
        </div>
    </div> --}}

    <section
        class="relative overflow-hidden mt-3 rounded-[2.5rem] border border-white bg-white/70 p-8 shadow-sm backdrop-blur-xl">
        <!-- Section Header -->
        <div class="flex items-center gap-4 mb-10">
            <div
                class="h-12 w-12 rounded-2xl bg-[var(--surface-soft)] flex items-center justify-center text-[var(--brand)] shadow-inner">
                <i data-lucide="git-commit-vertical" class="h-6 w-6"></i>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase text-slate-400">Lifecycle</p>
                <h3 class="text-xl font-black text-[var(--ink)]">Hustle Timeline</h3>
            </div>
        </div>

        <!-- Timeline Container -->
        <div class="relative space-y-2">
            <!-- The Vertical Track Line -->
            <div class="absolute left-6 top-2 bottom-2 w-0.5 bg-slate-100"></div>

            @foreach ($timelineStages as $stage)
                @php
                    $isComplete = $stage['complete'];
                    $isActive = $stage['active'];
                    $isPending = !$isComplete && !$isActive;
                @endphp

                <div class="group relative flex gap-6 pl-2 pr-4 py-4 rounded-[2rem] transition-all hover:bg-white/50">
                    <!-- Status Node (The Circle) -->
                    <div
                        class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl shadow-sm transition-all duration-500
                    {{ $isComplete
                        ? 'bg-emerald-500 text-white shadow-emerald-500/20'
                        : ($isActive
                            ? 'bg-[var(--brand)] text-white shadow-orange-500/20 scale-110'
                            : 'bg-white text-slate-300 border border-slate-100') }}">

                        <i data-lucide="{{ $isComplete ? 'check' : ($isActive ? 'play' : 'circle') }}"
                            class="{{ $isActive ? 'fill-current' : '' }} h-4 w-4"></i>
                    </div>

                    <!-- Stage Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3">
                            <h4 class="text-sm font-black {{ $isPending ? 'text-slate-400' : 'text-[var(--ink)]' }}">
                                {{ $stage['title'] }}
                            </h4>
                            @if ($isActive)
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-orange-100 px-2 py-0.5 text-[8px] font-black uppercase text-orange-600 animate-pulse">
                                    Current Phase
                                </span>
                            @endif
                        </div>

                        <p
                            class="mt-1 text-xs font-medium leading-relaxed {{ $isPending ? 'text-slate-300' : 'text-slate-500' }}">
                            {{ $stage['description'] }}
                        </p>
                    </div>

                    <!-- Date/Time Stamp (If available in your data) -->
                    @if (isset($stage['updated_at']))
                        <div class="hidden sm:block text-right shrink-0">
                            <p class="text-[9px] font-black uppercase text-slate-300">
                                {{ \Carbon\Carbon::parse($stage['updated_at'])->format('H:i A') }}
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>


    <section class="mt-6 grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="space-y-8">
            <div
                class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white/70 p-8 shadow-sm backdrop-blur-xl">
                <div class="absolute left-0 top-0 h-full w-1 rounded-r-full bg-blue-400/70"></div>

                <div class="flex items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-4">
                        <div
                            class="h-11 w-11 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-inner">
                            <i data-lucide="user-round" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-slate-400">Client Desk</p>
                            <h3 class="text-xl font-black text-[var(--ink)]">Client Details</h3>
                        </div>
                    </div>

                    <a href="{{ route('admin.users.show', $job->client) }}"
                        class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700">
                        <i data-lucide="eye" class="h-4 w-4"></i>
                        View User
                    </a>
                </div>

                <div class="flex flex-col gap-5 rounded-[2rem] bg-slate-50/70 p-5 sm:flex-row sm:items-start">
                    <x-avatar :user="$job->client" size="h-16 w-16" text="text-lg" rounded="rounded-[1.5rem]"
                        class="shadow-sm border border-white" />

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <h4 class="text-2xl font-black tracking-tight text-[var(--ink)]">
                                {{ $job->client->name }}
                            </h4>
                            <span
                                class="inline-flex rounded-xl border px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em] {{ $job->client->account_status === 'active'
                                    ? 'border-emerald-100 bg-emerald-50 text-emerald-700'
                                    : ($job->client->account_status === 'suspended'
                                        ? 'border-amber-100 bg-amber-50 text-amber-700'
                                        : 'border-rose-100 bg-rose-50 text-rose-700') }}">
                                {{ $job->client->account_status }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm font-medium text-slate-500">
                            {{ $job->client->email ?: 'No email on file.' }}
                        </p>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Phone</p>
                        <p class="mt-3 text-sm font-black text-slate-900">
                            {{ $job->client->phone ?: 'Not provided' }}
                        </p>
                    </div>
                    <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Verification</p>
                        <p class="mt-3 text-sm font-black text-slate-900">
                            {{ $job->client->phone_verified_at ? 'Phone verified' : 'Phone pending' }}
                        </p>
                    </div>
                    <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Joined</p>
                        <p class="mt-3 text-sm font-black text-slate-900">
                            {{ optional($job->client->created_at)->format('d M Y') ?: 'Unknown' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- HUSTLE DESCRIPTION -->
            <div
                class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white/70 p-8 shadow-sm backdrop-blur-xl">
                <!-- Brand Accent -->
                <div class="absolute left-0 top-0 h-full w-1 rounded-r-full bg-[var(--brand)] opacity-40"></div>

                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="h-11 w-11 rounded-2xl bg-[var(--surface-soft)] flex items-center justify-center text-[var(--brand)] shadow-inner">
                        <i data-lucide="align-left" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400">Project Brief</p>
                        <h3 class="text-xl font-black text-[var(--ink)]">Hustle Description</h3>
                    </div>
                </div>

                <div class="rounded-3xl bg-slate-50/50 p-6 border border-slate-100/50">
                    <p
                        class="text-sm font-medium leading-relaxed text-slate-600 italic transition-all group-hover:text-[var(--ink)]">
                        "{{ $job->description }}"
                    </p>
                </div>
            </div>
        </div>

        <!-- HUSTLE PERFORMANCE / RATING -->
        <div
            class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white/70 p-8 shadow-sm backdrop-blur-xl">
            <div class="flex flex-col md:flex-row md:items-center gap-3 justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div
                        class="h-11 w-11 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center shadow-inner">
                        <i data-lucide="star" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400">Quality Control</p>
                        <h3 class="text-xl font-black text-[var(--ink)]">Client Feedback</h3>
                    </div>
                </div>
                @if ($job->rating)
                    <span
                        class="rounded-xl bg-violet-600 px-4 py-1.5 text-[10px] font-black uppercase flex justify-center text-white shadow-lg shadow-violet-500/20">
                        Verified Rating
                    </span>
                @endif
            </div>

            @if ($job->rating)
                <div
                    class="relative rounded-[2rem] border border-violet-100 bg-violet-50/30 p-8 transition-all hover:bg-white hover:shadow-xl hover:shadow-violet-500/5">
                    <div class="flex items-center gap-6">
                        <!-- Large Score Display -->
                        <div class="text-center">
                            <p class="text-5xl font-black text-violet-600">{{ $job->rating->rating }}
                            </p>
                            <p class="text-[9px] font-black uppercase text-violet-400 mt-1">Out of 5</p>
                        </div>

                        <!-- Review Content -->
                        <div class="flex-1 border-l border-violet-100 pl-6">
                            <div class="flex gap-0.5 mb-3">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i data-lucide="star"
                                        class="w-4 h-4 {{ $i <= $job->rating->rating ? 'text-orange-400 fill-current' : 'text-slate-200' }}"></i>
                                @endfor
                            </div>
                            <p class="text-sm font-bold leading-relaxed text-slate-700 italic">
                                "{{ $job->rating->review ?: 'The client completed this hustle without a written review.' }}"
                            </p>
                            <p class="mt-3 text-[10px] font-black uppercase text-slate-400">
                                Submitted {{ $job->rating->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    <!-- Abstract Star Decoration -->
                    <i data-lucide="quote" class="absolute right-6 top-6 h-12 w-12 text-violet-200/30 rotate-12"></i>
                </div>
            @else
                <div
                    class="flex flex-col items-center justify-center py-10 rounded-[2rem] border-2 border-dashed border-slate-100 bg-slate-50/50">
                    <i data-lucide="star-off" class="h-8 w-8 text-slate-200 mb-3"></i>
                    <p class="text-sm font-black text-slate-400 uppercase">Pending Review</p>
                    <p class="text-[10px] font-bold text-slate-300 italic mt-1">Feedback will appear once the client
                        rates the worker.</p>
                </div>
            @endif
        </div>
    </section>
    <section class="mt-3">
        <div
            class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white/70 px-4 py-5 md:p-8 shadow-sm backdrop-blur-xl">
            <!-- Header: Dynamic Counter -->
            <div class="flex flex-col md:flex-row gap-3 md:items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div
                        class="h-12 w-12 rounded-2xl bg-[var(--surface-soft)] flex items-center justify-center text-[var(--brand)] shadow-inner">
                        <i data-lucide="users-round" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400">Marketplace Interest</p>
                        <h3 class="text-xl font-black text-[var(--ink)]">Hustle Applications</h3>
                    </div>
                </div>
                <span
                    class="rounded-xl flex justify-center bg-[var(--ink)] px-4 py-1.5 text-xs font-black text-white shadow-lg shadow-slate-900/20">
                    {{ $job->applications->count() }} Total
                </span>
            </div>

            <div class="space-y-4">
                @forelse ($job->applications as $application)
                    @php
                        $applicant = $application->user;
                        $isSelected = (int) $job->assigned_to === (int) $application->user_id;
                    @endphp

                    <article
                        class="group relative overflow-hidden rounded-[2.2rem] border border-slate-100 bg-white/50 p-6 transition-all hover:bg-white hover:shadow-xl hover:shadow-black/5 {{ $isSelected ? 'border-emerald-200 ring-1 ring-emerald-100' : '' }}">

                        <!-- Selection Indicator -->
                        @if ($isSelected)
                            <div class="absolute left-0 top-0 h-full w-1.5 bg-emerald-500"></div>
                        @endif

                        <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex items-start gap-4">
                                <!-- Avatar with Status Pulse -->
                                <div class="relative">
                                    <x-avatar :user="$applicant" size="h-14 w-14" text="text-xs" rounded="rounded-2xl"
                                        class="shadow-md border-2 border-white" />
                                    @if ($isSelected)
                                        <div
                                            class="absolute -bottom-1 -right-1 h-5 w-5 bg-emerald-500 border-2 border-white rounded-full flex items-center justify-center text-white shadow-lg">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('admin.users.show', $applicant) }}"
                                            class="text-base font-black text-[var(--ink)] hover:text-[var(--brand)] transition-colors">
                                            {{ $applicant->name ?? 'Unknown Applicant' }}
                                        </a>

                                        @if ($isSelected)
                                            <span
                                                class="rounded-lg bg-emerald-500 px-2.5 py-1 text-[9px] font-black uppercase text-white shadow-sm">
                                                Assigned Worker
                                            </span>
                                        @else
                                            <span
                                                class="rounded-lg bg-slate-100 px-2.5 py-1 text-[9px] font-black uppercase text-slate-500 border border-slate-200/50">
                                                {{ $application->status ?? 'Candidate' }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-2 flex items-center gap-3">
                                        <p class="text-[10px] font-black uppercase text-slate-400">
                                            {{ $applicant->skill->name ?? 'General Labor' }}
                                        </p>
                                        @if ($applicant?->average_rating)
                                            <span class="h-1 w-1 rounded-full bg-slate-200"></span>
                                            <div class="flex items-center gap-1">
                                                <i data-lucide="star" class="w-3 h-3 text-orange-400 fill-current"></i>
                                                <span
                                                    class="text-[10px] font-black text-slate-500">{{ number_format((float) $applicant->average_rating, 1) }}
                                                    Score</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Bento Pill -->
                            <div
                                class="rounded-2xl bg-[var(--surface-soft)]/80 p-3 pr-5 border border-white shadow-sm flex items-center gap-3 self-start">
                                <div
                                    class="h-8 w-8 rounded-xl bg-white flex items-center justify-center text-[var(--brand)] shadow-sm">
                                    <i data-lucide="phone" class="h-4 w-4"></i>
                                </div>
                                <div>
                                    <p class="text-[8px] font-black uppercase text-slate-400">Contact Desk
                                    </p>
                                    <p class="text-xs font-black text-[var(--ink)] font-mono">
                                        {{ $applicant->phone ?? 'No Phone' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Application Note -->
                        <div class="mt-5 relative">
                            <div class="absolute left-0 top-0 bottom-0 w-0.5 bg-slate-100"></div>
                            <div class="pl-5">
                                <p class="text-xs font-medium leading-relaxed text-slate-500 italic">
                                    "{{ $application->message ?: 'Applicant did not provide a custom cover note.' }}"
                                </p>
                                <p class="mt-3 text-[9px] font-black uppercase text-slate-300">
                                    Sent {{ optional($application->created_at)->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div
                        class="flex flex-col items-center justify-center py-12 rounded-[2.5rem] border-2 border-dashed border-slate-100 bg-slate-50/50">
                        <i data-lucide="inbox" class="h-10 w-10 text-slate-200 mb-4"></i>
                        <p class="text-sm font-black text-slate-400 uppercase">Quiet Registry</p>
                        <p class="text-[10px] font-bold text-slate-300 italic mt-1">No workers have expressed interest in
                            this hustle yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
