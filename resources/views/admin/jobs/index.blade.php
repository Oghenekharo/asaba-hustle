@extends('admin.layout')

@section('title', 'Jobs')
@section('admin-page-title', 'Jobs')

@section('content')
    <section class="rounded-[2.2rem] border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-xl">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-[var(--brand)]">Marketplace Oversight</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950 sm:text-3xl">Track jobs across the full lifecycle</h2>
                <p class="mt-3 max-w-2xl text-sm font-medium leading-7 text-slate-500">
                    Search by title, participant, location, skill ID, and status, then open any job for its payment,
                    rating, and conversation trail.
                </p>
            </div>

            <div class="rounded-[1.6rem] border border-slate-100 bg-slate-50 px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-slate-400">Results</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ number_format($jobs->total()) }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-500">Jobs matching the current filters</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.jobs.index') }}"
            class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-[2fr_1fr_1fr_auto]">
            <x-input type="text" icon="search" name="q" value="{{ request('q') }}"
                placeholder="Search title, location, client, worker" />
            <x-input type="text" icon="briefcase" name="skill" value="{{ request('skill') }}"
                placeholder="Search skill name, ID" />
            <x-select icon="activity" :options="[
                'open' => 'Open',
                'assigned' => 'Assigned',
                'worker_accepted' => 'Worker Accepted',
                'in_progress' => 'In Progress',
                'payment_pending' => 'Payment Pending',
                'completed' => 'Completed',
                'rated' => 'Rated',
                'cancelled' => 'Cancelled',
            ]" :selected="request('status')" name="status" />
            <div class="flex flex-col md:flex-row gap-3">
                <button
                    class="h-12 rounded-2xl bg-slate-950 px-5 text-xs font-black uppercase tracking-[0.24em] text-white">
                    Apply
                </button>
                <a href="{{ route('admin.jobs.index') }}"
                    class="inline-flex h-12 items-center justify-center rounded-2xl bg-rose-500 border border-rose-600 px-5 text-xs font-black uppercase tracking-[0.24em] text-slate-100 transition hover:border-rose-400">
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section class="mt-6 rounded-[2.2rem] border border-slate-200/80 bg-white/85 p-4 md:p-6 shadow-sm backdrop-blur-xl">
        <div class="mt-3 hidden overflow-hidden lg:block transition-all hover:shadow-xl hover:shadow-black/5">
            <table class="min-w-full border-collapse text-sm">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-4 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            Hustle Details</th>
                        <th class="px-2 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            Status</th>
                        <th class="px-2 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            Category</th>
                        <th class="px-2 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            Parties</th>
                        <th
                            class="px-2 py-5 text-right text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 pr-8">
                            Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($jobs as $job)
                        <tr class="group transition-all hover:bg-[var(--surface-soft)]/30">
                            <!-- Hustle Details -->
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="h-10 w-10 shrink-0 rounded-xl bg-[var(--ink)] text-white flex items-center justify-center shadow-lg group-hover:rotate-6 transition-transform">
                                        <i data-lucide="{{ $job->skill->icon ?? 'briefcase' }}"
                                            class="h-5 w-5 text-orange-400"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.jobs.show', $job) }}"
                                            class="block text-sm font-black tracking-tight text-[var(--ink)] transition hover:text-[var(--brand)] truncate">
                                            {{ $job->title }}
                                        </a>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter truncate">
                                            #{{ $job->id }} • {{ $job->location ?? 'Asaba Area' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-2 py-5">
                                @php
                                    $statusColors = [
                                        'open' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'assigned' => 'bg-blue-50 text-blue-600 border-blue-100',
                                        'in_progress' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        'completed' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    ];
                                    $style =
                                        $statusColors[$job->status] ?? 'bg-slate-50 text-slate-400 border-slate-100';
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-xl border px-2.5 py-1 text-[9px] font-black uppercase tracking-widest {{ $style }}">
                                    <span class="h-1 w-1 rounded-full bg-current"></span>
                                    {{ str_replace('_', ' ', $job->status) }}
                                </span>
                            </td>

                            <!-- Category -->
                            <td class="px-2 py-5">
                                <span class="text-xs font-black text-slate-600 tracking-tight">
                                    {{ $job->skill->name ?? 'Uncategorized' }}
                                </span>
                            </td>

                            <!-- Parties (Client & Worker) -->
                            <td class="px-2 py-5">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[8px] font-black uppercase text-blue-400">Client:</span>
                                        <span
                                            class="text-[11px] font-bold text-slate-700 truncate">{{ $job->client->name ?? 'N/A' }}</span>
                                    </div>
                                    @if ($job->worker)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[8px] font-black uppercase text-orange-400">Worker:</span>
                                            <span
                                                class="text-[11px] font-bold text-slate-700 truncate">{{ $job->worker->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-[9px] font-bold italic text-slate-300">No worker assigned</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-2 py-5 pr-8 text-right">
                                <a href="{{ route('admin.jobs.show', $job->id) }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white border border-slate-100 text-[var(--brand)] shadow-sm hover:bg-[var(--brand)] hover:text-white transition-all active:scale-95 group/btn">
                                    <i data-lucide="eye" class="h-4 w-4 group-hover/btn:scale-110 transition-transform"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>


        <div class="mt-6 grid gap-6 lg:hidden">
            @foreach ($jobs as $job)
                <article
                    class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white p-6 shadow-sm transition-all active:scale-[0.98]">
                    <!-- Dynamic Brand Accent -->
                    <div class="absolute right-0 top-0 h-16 w-16 bg-[var(--brand)]/5 blur-2xl rounded-full"></div>

                    <!-- Header: Title & Status -->
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div class="flex items-center gap-4">
                            <div
                                class="h-12 w-12 shrink-0 rounded-2xl bg-[var(--ink)] text-white flex items-center justify-center shadow-lg shadow-slate-900/10">
                                <i data-lucide="{{ $job->skill->icon ?? 'briefcase' }}"
                                    class="h-5 w-5 text-orange-400"></i>
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('admin.jobs.show', $job) }}"
                                    class="block text-sm font-black tracking-tight text-[var(--ink)] truncate">
                                    {{ $job->title }}
                                </a>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">ID:
                                    #{{ $job->id }} • {{ $job->skill->name ?? 'General' }}</p>
                            </div>
                        </div>

                        <!-- Status Pill -->
                        <span
                            class="rounded-xl border px-2.5 py-1 text-[9px] font-black uppercase tracking-widest
                    {{ $job->status === 'open'
                        ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                        : ($job->status === 'in_progress'
                            ? 'bg-amber-50 text-amber-600 border-amber-100'
                            : 'bg-slate-100 text-slate-500 border-slate-200') }}">
                            {{ str_replace('_', ' ', $job->status) }}
                        </span>
                    </div>

                    <!-- Parties Bento: Client & Worker -->
                    <div
                        class="grid grid-cols-2 gap-4 rounded-3xl bg-[var(--surface-soft)]/50 p-4 border border-[var(--brand)]/5">
                        <div class="flex flex-col gap-1">
                            <p class="text-[8px] font-black uppercase tracking-[0.2em] text-blue-400">Client Info</p>
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-6 w-6 rounded-lg bg-white flex items-center justify-center text-blue-400 shadow-sm">
                                    <i data-lucide="user" class="w-3 h-3"></i>
                                </div>
                                <p class="text-[11px] font-black text-[var(--ink)] truncate">
                                    {{ $job->client->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1 border-l border-white pl-4">
                            <p class="text-[8px] font-black uppercase tracking-[0.2em] text-orange-400">Worker</p>
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-6 w-6 rounded-lg bg-white flex items-center justify-center text-orange-400 shadow-sm">
                                    <i data-lucide="hard-hat" class="w-3 h-3"></i>
                                </div>
                                <p class="text-[11px] font-black text-[var(--ink)] truncate">
                                    {{ $job->worker->name ?? 'Unassigned' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer: Quick Meta -->
                    <div class="mt-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3 h-3 text-slate-300"></i>
                                <span class="text-[10px] font-bold text-slate-400">{{ $job->location ?? 'Asaba' }}</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.jobs.show', $job) }}"
                            class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-[var(--brand)] hover:opacity-70 transition-all">
                            View Details
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>


        <div class="mt-6">
            {{ $jobs->withQueryString()->links('partials.pagination') }}
        </div>
    </section>
@endsection
