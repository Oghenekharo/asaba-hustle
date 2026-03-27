@extends('layouts.app', ['title' => 'My Jobs | Asaba Hustle'])

@php
    $pageTitle = $isClient ? 'Jobs You Posted' : 'Jobs Assigned to You';
    $pageSubtitle = $isClient
        ? 'Track every hustle you created, who got hired, and what is still open.'
        : 'See the hustles currently assigned to you and follow their current status.';

    $statusOptions = [
        '' => 'All statuses',
        'open' => 'Open',
        'assigned' => 'Assigned',
        'worker_accepted' => 'Accepted',
        'in_progress' => 'In Progress',
        'payment_pending' => 'Payment Pending',
        'completed' => 'Completed',
        'rated' => 'Rated',
    ];
@endphp

@section('content')
    <section class="mx-auto max-w-7xl pt-20">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[var(--brand)]">
                    {{ $isClient ? 'Client Workspace' : 'Worker Workspace' }}
                </p>
                <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 md:text-4xl">{{ $pageTitle }}</h1>
                <p class="mt-3 max-w-2xl text-sm font-medium leading-relaxed text-slate-500">
                    {{ $pageSubtitle }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('web.app') }}"
                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:border-[var(--brand)] hover:text-[var(--brand)]">
                    <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                    Dashboard
                </a>
                <a href="{{ route('web.app.jobs') }}"
                    class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-slate-900/15 transition hover:bg-slate-800">
                    <i data-lucide="arrow-up-right" class="h-4 w-4 text-orange-400"></i>
                    {{ $isClient ? 'Explore Marketplace' : 'Browse More Jobs' }}
                </a>
            </div>
        </div>

        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <div class="rounded-[2rem] border border-slate-100 bg-white px-5 py-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $jobCounts['total'] }}</p>
            </div>
            <div class="rounded-[2rem] border border-slate-100 bg-white px-5 py-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Open</p>
                <p class="mt-3 text-3xl font-black text-orange-600">{{ $jobCounts['open'] }}</p>
            </div>
            <div class="rounded-[2rem] border border-slate-100 bg-white px-5 py-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Assigned</p>
                <p class="mt-3 text-3xl font-black text-blue-600">{{ $jobCounts['assigned'] }}</p>
            </div>
            <div class="rounded-[2rem] border border-slate-100 bg-white px-5 py-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">In Progress</p>
                <p class="mt-3 text-3xl font-black text-indigo-600">{{ $jobCounts['in_progress'] }}</p>
            </div>
            <div class="rounded-[2rem] border border-slate-100 bg-white px-5 py-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Payment Pending</p>
                <p class="mt-3 text-3xl font-black text-amber-600">{{ $jobCounts['payment_pending'] }}</p>
            </div>
            <div class="rounded-[2rem] border border-slate-100 bg-white px-5 py-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Completed</p>
                <p class="mt-3 text-3xl font-black text-emerald-600">{{ $jobCounts['completed'] }}</p>
            </div>
        </div>

        <section class="rounded-[2.5rem] border border-slate-100 bg-white p-6 shadow-sm">
            <form method="GET" action="{{ route('web.app.my-jobs') }}"
                class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_13rem_auto] lg:items-end">
                <div>
                    <x-input label="Search" icon="search" id="my_jobs_search" type="text" name="q" value="{{ $searchTerm }}"
                            placeholder="{{ $isClient ? 'Search your posted jobs...' : 'Search assigned jobs...' }}" />

                </div>

                <x-select name="status" id="my_jobs_status" label="Status" icon="sliders-horizontal"
                    placeholder="" :options="$statusOptions" :selected="$statusFilter" />

                <div class="flex gap-3">
                    <x-button type="submit" class="w-full lg:w-auto">
                        <x-slot:icon>
                            <i data-lucide="filter" class="h-4 w-4"></i>
                        </x-slot:icon>
                        Filter
                    </x-button>

                    @if ($statusFilter !== '' || $searchTerm !== '')
                        <a href="{{ route('web.app.my-jobs') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:border-[var(--brand)] hover:text-[var(--brand)]">
                            <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <section class="mt-8">
            @forelse ($jobs as $job)
                <x-job-card :job="$job" variant="activity" />
            @empty
                <div class="rounded-[2.5rem] border border-dashed border-slate-200 bg-white px-8 py-14 shadow-sm">
                    <x-empty-state :title="$isClient ? 'No posted jobs found' : 'No assigned jobs found'"
                        :subtitle="$searchTerm !== '' || $statusFilter !== ''
                            ? 'No jobs matched your current filters.'
                            : ($isClient
                                ? 'You have not posted any jobs yet.'
                                : 'You do not have any jobs assigned to you yet.')"
                        icon="briefcase-business" />
                </div>
            @endforelse
        </section>

        @if ($jobs->hasPages())
            <div class="mt-8">
                {{ $jobs->links() }}
            </div>
        @endif
    </section>
@endsection
