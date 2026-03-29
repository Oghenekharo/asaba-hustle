@extends('layouts.app', ['title' => 'Explore Hustles | Asaba Hustle'])

@php
    $statusOptions = [
        '' => 'All open jobs',
        'open' => 'Open now',
    ];

    $hasFilters = $selectedSkillId > 0 || $statusFilter !== '' || $searchTerm !== '';
    $selectedSkill = $selectedSkillId > 0 ? $skills->firstWhere('id', $selectedSkillId) : null;
@endphp

@section('content')
    <section class="mx-auto max-w-7xl pt-20">
        <div
            class="relative overflow-hidden rounded-[2rem] md:rounded-[3rem] border border-slate-100 bg-white px-5 py-8 shadow-sm sm:px-8 lg:px-10">
            <!-- Brand Glow -->
            <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[var(--brand)]/10 blur-3xl opacity-50"></div>
            <div class="absolute left-0 top-0 h-full w-1.5 rounded-r-full bg-[var(--brand)]"></div>

            <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-5xl">
                    <div class="inline-flex items-center gap-2 mb-3">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[var(--brand)] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-[var(--brand)]"></span>
                        </span>
                        <p class="text-[10px] font-black uppercase tracking-[0.28em] text-[var(--brand)]">Marketplace</p>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 md:text-5xl">
                        Explore <span class="italic text-[var(--brand)]">Available Hustles</span>
                    </h1>
                    <p class="mt-3 text-xs md:text-sm font-medium leading-relaxed text-slate-500 max-w-md">
                        Search local opportunities, narrow by skill, and jump into the details when a hustle fits.
                    </p>
                </div>
            </div>
        </div>


        <div class="mt-8 grid gap-8 lg:grid-cols-[18rem_minmax(0,1fr)]">
            <aside class="lg:sticky lg:top-28 lg:self-start">
                <div class="rounded-[2.5rem] border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="mb-6 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Refine Feed</p>
                            <h2 class="mt-2 text-lg font-black text-slate-900">Search and Filters</h2>
                        </div>
                        @if ($hasFilters)
                            <a href="{{ route('web.app.jobs') }}"
                                class="text-[10px] font-black uppercase tracking-widest text-[var(--brand)] transition hover:opacity-70">
                                Reset
                            </a>
                        @endif
                    </div>

                    <form method="GET" action="{{ route('web.app.jobs') }}" class="space-y-5">
                        <div>
                            <label for="jobs_search"
                                class="ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">
                                Search
                            </label>
                            <div class="relative mt-1">
                                <i data-lucide="search"
                                    class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                                <input id="jobs_search" type="text" name="q" value="{{ $searchTerm }}"
                                    placeholder="Title, skill, location, client..."
                                    class="block w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 pl-11 text-sm font-semibold text-slate-700 outline-none transition focus:border-orange-500 focus:bg-white" />
                            </div>
                        </div>

                        <x-select name="skill_id" id="jobs_skill_id" label="Skill" icon="briefcase-business"
                            placeholder="Any skill" :options="$skills->pluck('name', 'id')->all()" :selected="$selectedSkillId > 0 ? $selectedSkillId : null" />

                        <x-select name="status" id="jobs_status" label="Status" icon="activity" placeholder=""
                            :options="$statusOptions" :selected="$statusFilter" />

                        <div class="flex gap-3">
                            <x-button type="submit" class="w-full">
                                <x-slot:icon>
                                    <i data-lucide="filter" class="h-4 w-4"></i>
                                </x-slot:icon>
                                Apply Filters
                            </x-button>
                        </div>
                    </form>

                    <div class="mt-8 border-t border-slate-100 pt-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Popular Skills</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($skills->take(8) as $skill)
                                <a href="{{ route('web.app.jobs', ['skill_id' => $skill->id]) }}"
                                    class="inline-flex items-center rounded-full border px-3 py-2 text-[10px] font-black uppercase tracking-widest transition {{ $selectedSkillId === $skill->id ? 'border-[var(--brand)] bg-[var(--brand)] text-white' : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-[var(--brand)] hover:text-[var(--brand)]' }}">
                                    {{ $skill->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </aside>

            <main class="min-w-0">
                <div class="mb-6 rounded-2xl border border-slate-100 bg-white px-5 py-5 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400">
                                {{ $jobs->total() }} {{ \Illuminate\Support\Str::plural('result', $jobs->total()) }}
                            </p>
                            <p class="mt-2 text-sm font-medium text-slate-500">
                                {{ $hasFilters ? 'Showing jobs that match your current filters.' : 'Showing current open jobs in the marketplace.' }}
                            </p>
                        </div>

                        @if ($hasFilters)
                            <div class="flex flex-wrap gap-2">
                                @if ($searchTerm !== '')
                                    <span
                                        class="inline-flex items-center rounded-full bg-slate-100 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-slate-700">
                                        Search: {{ $searchTerm }}
                                    </span>
                                @endif
                                @if ($selectedSkill)
                                    <span
                                        class="inline-flex items-center rounded-full bg-orange-50 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-orange-700">
                                        Skill: {{ $selectedSkill->name }}
                                    </span>
                                @endif
                                @if ($statusFilter !== '')
                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-50 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-blue-700">
                                        Status: {{ str_replace('_', ' ', $statusFilter) }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-5">
                    @forelse ($jobs as $job)
                        <x-job-card :job="$job"
                            variant="{{ auth()->user()?->hasRole('client') ? 'activity' : 'feed' }}" />
                    @empty
                        <div
                            class="rounded-[2.5rem] border border-dashed border-slate-200 bg-white px-3 md:px-8 py-14 shadow-sm">
                            <x-empty-state variant="large" title="No jobs matched your filters"
                                subtitle="Try broadening your search, switching the skill, or clearing all filters."
                                icon="search-x" />
                        </div>
                    @endforelse
                </div>

                @if ($jobs->hasPages())
                    <div class="mt-10">
                        {{ $jobs->links('partials.pagination') }}
                    </div>
                @endif
            </main>
        </div>
    </section>
@endsection
