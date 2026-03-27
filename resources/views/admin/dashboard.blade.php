@extends('admin.layout')

@section('title', 'Admin Dashboard')
@section('admin-page-title', 'Operations Dashboard')

@section('content')
    @php
        $conversion = $totalUsers > 0 ? round(($activeWorkers / $totalUsers) * 100) : 0;
    @endphp

    <section
        class="relative overflow-hidden rounded-[3rem] border border-white bg-white/70 p-8 shadow-sm backdrop-blur-xl sm:p-10">
        <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[var(--brand)]/10 blur-3xl opacity-50"></div>
        <div class="absolute left-0 top-0 h-full w-1.5 rounded-r-full bg-[var(--brand)]"></div>

        <div class="relative flex flex-col gap-10 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full bg-[var(--brand)]/10 px-3 py-1 mb-4">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[var(--brand)] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[var(--brand)]"></span>
                    </span>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[var(--brand)]">Marketplace Health</p>
                </div>
                <h2 class="text-4xl font-black leading-[1.1] tracking-tighter text-[var(--ink)] sm:text-5xl">
                    Keep the marketplace <span class="italic text-[var(--brand)]">accountable.</span>
                </h2>
                <p class="mt-6 max-w-lg text-sm font-medium leading-relaxed text-slate-500">
                    Your control surface for users, jobs, and payments. Modernised for faster moderation and real-time
                    oversight.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:min-w-[24rem]">
                <div
                    class="rounded-[2.5rem] border border-orange-100 bg-orange-50/50 p-6 transition-all hover:shadow-lg hover:shadow-orange-500/5">
                    <p class="text-[9px] font-black uppercase tracking-[0.2em] text-orange-500">Worker Share</p>
                    <p class="mt-2 text-3xl font-black tracking-tighter text-[var(--ink)]">{{ $conversion }}%</p>
                    <div class="mt-3 h-1 w-full overflow-hidden rounded-full bg-orange-200/50">
                        <div class="h-full rounded-full bg-orange-500" style="width: {{ $conversion }}%"></div>
                    </div>
                </div>
                <div class="rounded-[2.5rem] bg-[var(--ink)] p-6 text-white shadow-2xl shadow-slate-900/20">
                    <p class="text-[9px] font-black uppercase tracking-[0.2em] text-white/40">Gross Volume</p>
                    <p class="mt-2 text-3xl font-black tracking-tighter text-[var(--brand)]">N{{ number_format($revenue) }}</p>
                    <p class="mt-2 text-[10px] font-bold italic text-white/30">{{ number_format($manualSettlements) }} manual settlements confirmed</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @include('admin.components.card', [
            'title' => 'Total Users',
            'value' => number_format($totalUsers),
            'icon' => 'users',
            'tone' => 'blue',
            'meta' => 'All registered accounts',
        ])

        @include('admin.components.card', [
            'title' => 'Active Workers',
            'value' => number_format($activeWorkers),
            'icon' => 'hard-hat',
            'tone' => 'orange',
            'meta' => 'Workers marked available',
        ])

        @include('admin.components.card', [
            'title' => 'Jobs Completed',
            'value' => number_format($jobsCompleted),
            'icon' => 'briefcase-business',
            'tone' => 'emerald',
            'meta' => 'Completed and rated jobs',
        ])

        @include('admin.components.card', [
            'title' => 'Settled Volume',
            'value' => 'N' . number_format($revenue),
            'icon' => 'banknote',
            'tone' => 'violet',
            'meta' => 'Successful cash, transfer, and gateway settlements',
        ])
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-[3rem] border border-white bg-white/80 p-8 shadow-sm backdrop-blur-xl">
            <div class="mb-8 flex items-center gap-4">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-[1.5rem] bg-[var(--surface-soft)] text-[var(--brand)] shadow-inner">
                    <i data-lucide="radar" class="h-6 w-6"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Strategy</p>
                    <h3 class="text-xl font-black tracking-tight text-[var(--ink)]">Operational Priorities</h3>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach ([['t' => 'Trust', 'd' => 'ID review & verified status control.', 'i' => 'shield-check'], ['t' => 'Oversight', 'd' => 'Inspect jobs & chat logs manually.', 'i' => 'eye'], ['t' => 'Quality', 'd' => 'Moderate ratings & audit payments.', 'i' => 'star']] as $prio)
                    <div
                        class="group rounded-[2rem] border border-slate-50 bg-[var(--surface-soft)]/50 p-5 transition-all hover:bg-white hover:shadow-md">
                        <i data-lucide="{{ $prio['i'] }}"
                            class="mb-3 h-5 w-5 text-[var(--brand)] opacity-50 transition-opacity group-hover:opacity-100"></i>
                        <p class="mb-2 text-xs font-black uppercase tracking-widest text-[var(--ink)]">{{ $prio['t'] }}</p>
                        <p class="text-xs font-medium italic leading-relaxed text-slate-500">{{ $prio['d'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[3rem] bg-[var(--ink)] p-8 text-white shadow-2xl shadow-slate-900/30">
            <div class="absolute -right-10 -bottom-10 h-40 w-40 rounded-full bg-[var(--brand)]/10 blur-3xl"></div>

            <p class="mb-6 text-[10px] font-black uppercase tracking-[0.3em] text-[var(--brand)]">Quick Navigation</p>
            <div class="relative z-10 space-y-3">
                @foreach ([['route' => 'admin.users.index', 'label' => 'Users & Verifications', 'icon' => 'users-round'], ['route' => 'admin.jobs.index', 'label' => 'Live Job Inspection', 'icon' => 'activity'], ['route' => 'admin.payments.index', 'label' => 'Payment Audit Trail', 'icon' => 'credit-card'], ['route' => 'admin.activity.index', 'label' => 'System Activity Logs', 'icon' => 'history']] as $link)
                    <a href="{{ route($link['route']) }}"
                        class="group flex items-center justify-between rounded-2xl border border-white/5 bg-white/5 px-5 py-4 text-xs font-black uppercase tracking-wider text-white/70 transition-all hover:bg-[var(--brand)] hover:text-white">
                        <div class="flex items-center gap-3">
                            <i data-lucide="{{ $link['icon'] }}" class="h-4 w-4 opacity-50 group-hover:opacity-100"></i>
                            <span>{{ $link['label'] }}</span>
                        </div>
                        <i data-lucide="arrow-right"
                            class="h-4 w-4 -translate-x-2 opacity-0 transition-all group-hover:translate-x-0 group-hover:opacity-100"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
