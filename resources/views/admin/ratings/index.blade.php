@extends('admin.layout')

@section('title', 'Ratings')
@section('admin-page-title', 'Ratings')

@section('content')
    <section class="rounded-[2.2rem] border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-xl">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-[var(--brand)]">Quality Signals</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950 sm:text-3xl">Review client feedback and delete abuse</h2>
                <p class="mt-3 max-w-2xl text-sm font-medium leading-7 text-slate-500">
                    Filter by score or search worker, client, job, and review text to moderate public trust signals.
                </p>
            </div>

            <div class="rounded-[1.6rem] border border-slate-100 bg-slate-50 px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-slate-400">Results</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ number_format($ratings->total()) }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-500">Ratings matching the current filters</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.ratings.index') }}" class="mt-6 grid gap-3 lg:grid-cols-[2fr_1fr_auto]">
            <x-input type="text" icon="search" name="q" value="{{ request('q') }}"
                placeholder="Search client, review job, client, worker" />

            <x-select name="rating" :options="[
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
            ]" :selected="request('rating')" icon="chart-bar" />

            <div class="flex flex-col md:flex-row gap-3">
                <button
                    class="h-12 rounded-2xl bg-slate-950 px-5 text-xs font-black uppercase tracking-[0.24em] text-white">
                    Apply
                </button>
                <a href="{{ route('admin.ratings.index') }}"
                    class="inline-flex h-12 items-center justify-center rounded-2xl border border-slate-200 px-5 text-xs font-black uppercase tracking-[0.24em] text-slate-500 transition hover:border-slate-300 hover:text-slate-900">
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section class="mt-6 rounded-[2.2rem] border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-xl">
        <div
            class="mt-8 hidden overflow-hidden rounded-[2.5rem] border border-white bg-white/50 shadow-sm backdrop-blur-xl lg:block transition-all hover:shadow-xl hover:shadow-black/5">
            <table class="min-w-full border-collapse text-sm">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            Hustle Parties</th>
                        <th class="px-4 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            Score & Feedback</th>
                        <th class="px-4 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            Hustle Context</th>
                        <th
                            class="px-4 py-5 text-right text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 pr-8">
                            Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($ratings as $rating)
                        <tr class="group transition-all hover:bg-[var(--surface-soft)]/30">

                            <td class="px-6 py-5">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="h-6 w-6 rounded-lg bg-orange-50 text-orange-500 flex items-center justify-center shadow-sm border border-orange-100">
                                            <i data-lucide="hard-hat" class="h-3 w-3"></i>
                                        </div>
                                        <span
                                            class="text-xs font-black text-[var(--ink)] truncate">{{ $rating->worker->name ?? 'Unknown Worker' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="h-6 w-6 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center shadow-sm border border-blue-100">
                                            <i data-lucide="user" class="h-3 w-3"></i>
                                        </div>
                                        <span
                                            class="text-xs font-bold text-slate-500 truncate">{{ $rating->client->name ?? 'Unknown Client' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Score & Feedback -->
                            <td class="px-4 py-5 max-w-md">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex gap-0.5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i data-lucide="star"
                                                class="w-3 h-3 {{ $i <= $rating->rating ? 'text-orange-400 fill-current' : 'text-slate-200' }}"></i>
                                        @endfor
                                    </div>
                                    <span
                                        class="text-[10px] font-black text-orange-600 bg-orange-50 px-2 py-0.5 rounded-lg border border-orange-100">
                                        {{ $rating->rating }}.0
                                    </span>
                                </div>
                                <p class="text-xs font-medium leading-relaxed text-slate-500 italic line-clamp-2">
                                    "{{ $rating->review ?: 'The client provided a star rating without a written testimonial.' }}"
                                </p>
                            </td>

                            <!-- Job Context -->
                            <td class="px-4 py-5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-9 w-9 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400">
                                        <i data-lucide="briefcase" class="h-4 w-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-black text-[var(--ink)] truncate uppercase tracking-tighter">
                                            {{ $rating->job->title ?? 'N/A' }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 italic">Audit Log
                                            #{{ $rating->id }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Operations -->
                            <td class="px-4 py-5 pr-8 text-right">
                                <div class="flex justify-end opacity-0 group-hover:opacity-100 transition-all">
                                    <button type="button"
                                        class="flex h-10 items-center gap-2 rounded-xl bg-rose-50 px-4 text-[10px] font-black uppercase tracking-widest text-rose-600 border border-rose-100 hover:bg-rose-600 hover:text-white transition-all active:scale-95 shadow-sm shadow-rose-500/10"
                                        onclick="openDeleteRatingModal({
                                    action: @js(route('admin.ratings.destroy', $rating)),
                                    worker: @js($rating->worker->name ?? 'Unknown worker'),
                                    client: @js($rating->client->name ?? 'Unknown client'),
                                    score: @js($rating->rating . '/5'),
                                    job: @js($rating->job->title ?? 'No job title')
                                })">
                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                        Delete Rating
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>


        <div class="mt-6 grid gap-6 lg:hidden">
            @foreach ($ratings as $rating)
                <article
                    class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white p-6 shadow-sm transition-all active:scale-[0.98]">
                    <!-- Dynamic Brand Accent -->
                    <div class="absolute right-0 top-0 h-16 w-16 bg-orange-500/5 blur-2xl rounded-full"></div>

                    <!-- Top Row: Worker & Score -->
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-12 w-12 shrink-0 rounded-2xl bg-[var(--ink)] text-white flex items-center justify-center shadow-lg">
                                <i data-lucide="star" class="h-5 w-5 text-orange-400 fill-current"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-orange-500">Worker Rated</p>
                                <h4 class="text-sm font-black tracking-tight text-[var(--ink)] truncate">
                                    {{ $rating->worker->name ?? 'Unknown Worker' }}
                                </h4>
                            </div>
                        </div>

                        <!-- Score Badge -->
                        <div class="flex flex-col items-end">
                            <span
                                class="rounded-xl border border-orange-100 bg-orange-50 px-2.5 py-1 text-[11px] font-black text-orange-600 shadow-sm">
                                {{ $rating->rating }}.0
                            </span>
                            <div class="flex gap-0.5 mt-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i data-lucide="star"
                                        class="w-2.5 h-2.5 {{ $i <= $rating->rating ? 'text-orange-400 fill-current' : 'text-slate-200' }}"></i>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <!-- Middle Bento: Client & Job Context -->
                    <div
                        class="grid grid-cols-2 gap-4 rounded-3xl bg-[var(--surface-soft)]/50 p-4 border border-slate-100/50">
                        <div class="flex flex-col gap-1">
                            <p class="text-[8px] font-black uppercase tracking-[0.2em] text-blue-400">By Client</p>
                            <p class="text-[11px] font-black text-[var(--ink)] truncate">
                                {{ $rating->client->name ?? 'N/A' }}</p>
                        </div>
                        <div class="flex flex-col gap-1 border-l border-white pl-4 text-right">
                            <p class="text-[8px] font-black uppercase tracking-[0.2em] text-slate-400 text-right">Hustle
                                Title</p>
                            <p class="text-[11px] font-black text-[var(--ink)] truncate uppercase tracking-tighter">
                                {{ $rating->job->title ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Review Content -->
                    <div class="mt-5 px-1">
                        <p class="text-xs font-medium leading-relaxed text-slate-600 italic">
                            "{{ $rating->review ?: 'The client provided a star rating without a written testimonial.' }}"
                        </p>
                    </div>

                    <!-- Action Bar -->
                    <div class="mt-6 pt-4 border-t border-slate-50 flex items-center justify-between">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-300">
                            ID: #{{ $rating->id }}
                        </p>

                        <button type="button"
                            class="flex items-center gap-2 rounded-xl bg-rose-50 px-5 py-2.5 text-[10px] font-black uppercase tracking-widest text-rose-600 border border-rose-100 active:scale-95 transition-all shadow-sm shadow-rose-500/5"
                            onclick="openDeleteRatingModal({
                        action: @js(route('admin.ratings.destroy', $rating)),
                        worker: @js($rating->worker->name ?? 'Unknown worker'),
                        client: @js($rating->client->name ?? 'Unknown client'),
                        score: @js($rating->rating . '/5'),
                        job: @js($rating->job->title ?? 'No job title')
                    })">
                            <i data-lucide="trash-2" class="h-3 w-3"></i>
                            Delete
                        </button>
                    </div>
                </article>
            @endforeach
        </div>


        <div class="mt-6">
            {{ $ratings->withQueryString()->links('partials.pagination') }}
        </div>
    </section>

    <x-modal id="deleteRatingModal" title="Delete Rating" size="max-w-lg">
        <div class="space-y-5">
            <div class="rounded-[1.5rem] border border-rose-100 bg-rose-50 px-4 py-4">
                <p class="text-sm font-semibold leading-6 text-rose-700">
                    This will permanently remove the rating from the worker profile and admin records.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-[1.3rem] border border-slate-100 bg-slate-50 px-4 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Worker</p>
                    <p id="delete-rating-worker" class="mt-1 text-sm font-black text-slate-900">-</p>
                </div>
                <div class="rounded-[1.3rem] border border-slate-100 bg-slate-50 px-4 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Client</p>
                    <p id="delete-rating-client" class="mt-1 text-sm font-black text-slate-900">-</p>
                </div>
                <div class="rounded-[1.3rem] border border-slate-100 bg-slate-50 px-4 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Score</p>
                    <p id="delete-rating-score" class="mt-1 text-sm font-black text-slate-900">-</p>
                </div>
                <div class="rounded-[1.3rem] border border-slate-100 bg-slate-50 px-4 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Job</p>
                    <p id="delete-rating-job" class="mt-1 text-sm font-black text-slate-900">-</p>
                </div>
            </div>

            <form id="delete-rating-form" method="POST">
                @csrf
                @method('DELETE')

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button"
                        class="rounded-2xl border border-slate-200 px-5 py-3 text-xs font-black uppercase tracking-[0.24em] text-slate-500 transition hover:border-slate-300 hover:text-slate-900"
                        onclick="closeModal('deleteRatingModal')">
                        Cancel
                    </button>
                    <button type="submit"
                        class="rounded-2xl bg-rose-600 px-5 py-3 text-xs font-black uppercase tracking-[0.24em] text-white">
                        Yes, Delete Rating
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <script>
        window.openDeleteRatingModal = function(details) {
            document.getElementById('delete-rating-form').action = details.action;
            document.getElementById('delete-rating-worker').textContent = details.worker;
            document.getElementById('delete-rating-client').textContent = details.client;
            document.getElementById('delete-rating-score').textContent = details.score;
            document.getElementById('delete-rating-job').textContent = details.job;
            openModal('deleteRatingModal');
        };
    </script>
@endsection
