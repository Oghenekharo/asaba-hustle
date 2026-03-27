@extends('admin.layout')

@section('title', 'User Review')
@section('admin-page-title', 'User Review')

@section('content')
    <section class="rounded-[2.3rem] border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-xl sm:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-start gap-4">
                <x-avatar :user="$user" size="h-16 w-16 sm:h-20 sm:w-20" text="text-xl" rounded="rounded-[1.6rem]" />
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[var(--brand)]">User Review</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950 sm:text-3xl">{{ $user->name }}</h2>
                    <p class="mt-2 text-sm font-semibold text-slate-500">
                        {{ $user->phone }} @if ($user->email)
                            <span class="mx-2 text-slate-300">•</span>{{ $user->email }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.users.index') }}"
                    class="inline-flex items-center gap-2 rounded-[1.35rem] border border-slate-200 bg-white px-4 py-3 text-xs font-black uppercase tracking-[0.24em] text-slate-500 transition hover:border-slate-300 hover:text-slate-900">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    Back to users
                </a>
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @include('admin.components.card', [
                'title' => 'Account Status',
                'value' => ucfirst($user->account_status ?? 'n/a'),
                'icon' => 'shield-check',
                'tone' =>
                    $user->account_status === 'active'
                        ? 'emerald'
                        : ($user->account_status === 'suspended'
                            ? 'orange'
                            : 'rose'),
            ])
            @include('admin.components.card', [
                'title' => 'Primary Skill',
                'value' => $user->skill->name ?? 'Not set',
                'icon' => 'sparkles',
                'tone' => 'blue',
            ])
            @include('admin.components.card', [
                'title' => 'Average Rating',
                'value' => number_format((float) ($user->average_rating ?? 0), 1),
                'icon' => 'star',
                'tone' => 'violet',
                'meta' => $user->ratingsReceived->count() . ' ratings received',
            ])
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <div class="space-y-6">
            <div
                class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white/70 p-8 shadow-sm backdrop-blur-xl">
                <!-- Section Header -->
                <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--brand)]/10 mb-4">
                            <i data-lucide="shield-check" class="w-3 h-3 text-[var(--brand)]"></i>
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[var(--brand)]">Security Audit
                            </p>
                        </div>
                        <h3 class="text-xl font-black tracking-tighter text-[var(--ink)]">KYC Approval Controls</h3>
                        <p class="mt-2 max-w-md text-xs font-medium leading-relaxed text-slate-500">
                            Official verification is only granted after a manual audit of the government-issued
                            identification provided by the hustler.
                        </p>
                    </div>

                    <!-- Status Pills -->
                    <div class="flex flex-wrap gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-[9px] font-black uppercase tracking-widest
                {{ $user->is_verified ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100' }}">
                            <span
                                class="h-1 w-1 rounded-full {{ $user->is_verified ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            {{ $user->is_verified ? 'Verified Account' : 'Awaiting Audit' }}
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-[9px] font-black uppercase tracking-widest
                {{ $user->id_document ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-slate-50 text-slate-400 border-slate-100' }}">
                            <i data-lucide="{{ $user->id_document ? 'file-check-2' : 'file-warning' }}" class="w-3 h-3"></i>
                            {{ $user->id_document ? 'ID Uploaded' : 'Missing Docs' }}
                        </span>
                    </div>
                </div>

                <!-- Document Preview Slot -->
                <div class="mt-8 relative group">
                    <div
                        class="rounded-3xl border-2 border-dashed border-slate-100 bg-slate-50/50 p-6 transition-all group-hover:border-[var(--brand)]/20 group-hover:bg-white group-hover:shadow-lg group-hover:shadow-orange-500/5">
                        @if ($user->id_document)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="h-12 w-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-[var(--brand)]">
                                        <i data-lucide="file-text" class="h-6 w-6"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">File Type
                                        </p>
                                        <p class="text-sm font-black text-[var(--ink)]">Official Identity Document</p>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $user->id_document) }}" target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex h-11 items-center gap-2 rounded-xl bg-[var(--ink)] px-5 text-[10px] font-black uppercase tracking-widest text-white transition-all hover:bg-[var(--brand)] active:scale-95">
                                    <i data-lucide="external-link" class="h-4 w-4"></i>
                                    View Full ID
                                </a>
                            </div>
                        @else
                            <div class="flex flex-col items-center py-4 text-center">
                                <i data-lucide="alert-circle" class="h-8 w-8 text-slate-300 mb-3"></i>
                                <p class="text-xs font-bold text-slate-400 italic">No identification document has been
                                    uploaded yet.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Audit Actions -->
                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <form method="POST" action="{{ route('admin.users.status', $user) }}" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $user->account_status ?? 'active' }}">
                        <input type="hidden" name="is_verified" value="0">
                        <button
                            class="w-full flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-100 bg-white px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 transition-all hover:border-rose-200 hover:text-rose-500 hover:bg-rose-50 shadow-sm active:scale-95">
                            <i data-lucide="shield-x" class="w-4 h-4"></i>
                            Revoke Verification
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.users.status', $user) }}" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $user->account_status ?? 'active' }}">
                        <input type="hidden" name="is_verified" value="1">
                        <button
                            class="w-full flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-white shadow-xl shadow-emerald-500/20 transition-all hover:bg-emerald-700 active:scale-95 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:shadow-none disabled:text-slate-400"
                            {{ $user->id_document ? '' : 'disabled' }}>
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            Approve Identity
                        </button>
                    </form>
                </div>
            </div>


            <div
                class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white/70 p-8 shadow-sm backdrop-blur-xl transition-all hover:shadow-xl hover:shadow-black/5">
                <!-- Brand Accent Blur -->
                <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-[var(--brand)]/5 blur-3xl"></div>

                <div class="relative flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[var(--brand)]">Hustle Metrics</p>
                        <h3 class="mt-1 text-lg font-black tracking-tighter text-[var(--ink)]">Account Activity</h3>
                    </div>
                    <div
                        class="h-10 w-10 rounded-xl bg-[var(--surface-soft)] flex items-center justify-center text-[var(--brand)] shadow-inner">
                        <i data-lucide="bar-chart-3" class="h-5 w-5"></i>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <!-- Client Metric -->
                    <div
                        class="group relative rounded-[2rem] border border-slate-50 bg-slate-50/50 p-6 transition-all hover:bg-white hover:shadow-lg hover:shadow-blue-500/5">
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="h-9 w-9 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center transition-transform group-hover:scale-110">
                                <i data-lucide="megaphone" class="h-4 w-4"></i>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-300">Client Side</span>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jobs Posted</p>
                        <p class="mt-1 text-3xl font-black tracking-tighter text-[var(--ink)]">
                            {{ number_format($user->postedJobs->count()) }}</p>
                        {{-- Invisible Brand Bar --}}
                        <div
                            class="absolute bottom-0 left-6 right-6 h-0.5 bg-blue-400 scale-x-0 transition-transform group-hover:scale-x-100">
                        </div>
                    </div>

                    <!-- Worker Metric -->
                    <div
                        class="group relative rounded-[2rem] border border-slate-50 bg-slate-50/50 p-6 transition-all hover:bg-white hover:shadow-lg hover:shadow-orange-500/5">
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="h-9 w-9 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center transition-transform group-hover:scale-110">
                                <i data-lucide="briefcase" class="h-4 w-4"></i>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-300">Worker Side</span>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Hustles Assigned</p>
                        <p class="mt-1 text-3xl font-black tracking-tighter text-[var(--ink)]">
                            {{ number_format($user->assignedJobs->count()) }}</p>
                        {{-- Invisible Brand Bar --}}
                        <div
                            class="absolute bottom-0 left-6 right-6 h-0.5 bg-[var(--brand)] scale-x-0 transition-transform group-hover:scale-x-100">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="space-y-8">
            <!-- POSTED JOBS (CLIENT SIDE) -->
            <div
                class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white/70 p-8 shadow-sm backdrop-blur-xl">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div
                            class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-inner">
                            <i data-lucide="megaphone" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black tracking-tighter text-[var(--ink)]">Posted Hustles</h3>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Client History</p>
                        </div>
                    </div>
                    <span
                        class="rounded-xl bg-[var(--ink)] px-4 py-1.5 text-xs font-black text-white shadow-lg shadow-slate-900/20">
                        {{ $user->postedJobs->count() }}
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse ($user->postedJobs as $job)
                        <a href="{{ route('admin.jobs.show', $job) }}"
                            class="group block rounded-3xl border border-slate-50 bg-slate-50/50 p-5 transition-all hover:bg-white hover:shadow-xl hover:shadow-blue-500/5 hover:translate-x-1">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="h-10 w-10 rounded-xl bg-white flex items-center justify-center text-blue-400 shadow-sm">
                                        <i data-lucide="{{ $job->skill->icon ?? 'briefcase' }}" class="h-5 w-5"></i>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-black text-[var(--ink)] group-hover:text-blue-600 transition-colors">
                                            {{ $job->title }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                            {{ $job->skill->name ?? 'General' }}</p>
                                    </div>
                                </div>
                                <span
                                    class="rounded-lg border px-2.5 py-1 text-[9px] font-black uppercase tracking-widest bg-white shadow-sm
                            {{ $job->status === 'open' ? 'text-emerald-500 border-emerald-100' : 'text-slate-400 border-slate-100' }}">
                                    {{ str_replace('_', ' ', $job->status) }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <x-empty-state variant="small" title="No posted jobs" icon="package-open" />
                    @endforelse
                </div>
            </div>

            <!-- ASSIGNED JOBS (WORKER SIDE) -->
            <div
                class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white/70 p-8 shadow-sm backdrop-blur-xl">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div
                            class="h-12 w-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center shadow-inner">
                            <i data-lucide="wrench" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black tracking-tighter text-[var(--ink)]">Assigned Tasks</h3>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Worker History</p>
                        </div>
                    </div>
                    <span
                        class="rounded-xl bg-[var(--brand)] px-4 py-1.5 text-xs font-black text-white shadow-lg shadow-orange-500/20">
                        {{ $user->assignedJobs->count() }}
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse ($user->assignedJobs as $job)
                        <a href="{{ route('admin.jobs.show', $job) }}"
                            class="group block rounded-3xl border border-slate-50 bg-slate-50/50 p-5 transition-all hover:bg-white hover:shadow-xl hover:shadow-orange-500/5 hover:translate-x-1">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="h-10 w-10 rounded-xl bg-white flex items-center justify-center text-orange-400 shadow-sm">
                                        <i data-lucide="{{ $job->skill->icon ?? 'tool' }}" class="h-5 w-5"></i>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-black text-[var(--ink)] group-hover:text-[var(--brand)] transition-colors">
                                            {{ $job->title }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                            {{ $job->skill->name ?? 'General' }}</p>
                                    </div>
                                </div>
                                <span
                                    class="rounded-lg border px-2.5 py-1 text-[9px] font-black uppercase tracking-widest bg-white shadow-sm text-[var(--brand)] border-orange-100">
                                    {{ str_replace('_', ' ', $job->status) }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <x-empty-state variant="small" title="No assigned jobs" icon="clipboard-list" />
                    @endforelse
                </div>
            </div>

            <!-- RATINGS FEED -->
            <div
                class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white/70 p-8 shadow-sm backdrop-blur-xl">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div
                            class="h-12 w-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center shadow-inner">
                            <i data-lucide="star" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black tracking-tighter text-[var(--ink)]">Peer Reviews</h3>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Reputation Log</p>
                        </div>
                    </div>
                    <span
                        class="rounded-xl bg-violet-600 px-4 py-1.5 text-xs font-black text-white shadow-lg shadow-violet-500/20">
                        {{ $user->ratingsReceived->count() }}
                    </span>
                </div>

                <div class="space-y-4">
                    @forelse ($user->ratingsReceived as $rating)
                        <div class="relative rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex gap-0.5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i data-lucide="star"
                                            class="w-3.5 h-3.5 {{ $i <= $rating->rating ? 'text-orange-400 fill-current' : 'text-slate-200' }}"></i>
                                    @endfor
                                </div>
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-300 italic">
                                    {{ optional($rating->created_at)->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-xs font-medium leading-relaxed text-slate-600 italic">
                                "{{ $rating->review ?: 'Professional service provided.' }}"</p>
                            <div
                                class="absolute -left-1.5 top-1/2 -translate-y-1/2 h-8 w-1 rounded-full bg-violet-400 opacity-40">
                            </div>
                        </div>
                    @empty
                        <x-empty-state variant="small" title="No ratings yet" icon="star-off" />
                    @endforelse
                </div>
            </div>
        </div>

    </section>
@endsection
