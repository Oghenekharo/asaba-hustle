@extends('layouts.app', ['title' => 'Dashboard | Asaba Hustle'])

@section('content')
    <div class="mx-auto max-w-7xl pt-20">
        <section id="dashboard-permission-prompts"
            data-location-url="{{ route('web.location.update') }}"
            data-has-location="{{ auth()->user()->latitude !== null && auth()->user()->longitude !== null ? 'true' : 'false' }}"
            class="mb-8 flex flex-col gap-5 rounded-3xl border border-[var(--line)] bg-[var(--surface-raised)] p-5 shadow-sm md:flex-row md:items-center md:justify-between md:p-6">
            <div>
                <p class="text-sm font-bold text-[var(--ink)]">Stay up to date and discover nearby work</p>
                <p class="mt-1 text-xs text-slate-500">Enable push alerts and share your location to improve nearby job matches.</p>
                <p id="dashboard-permission-status" class="mt-2 hidden text-xs font-semibold text-slate-500" role="status" aria-live="polite"></p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" id="dashboard-enable-push" class="rounded-xl bg-[var(--brand)] px-4 py-3 text-xs font-bold text-white transition hover:opacity-90">
                    Enable push notifications
                </button>
                <button type="button" id="dashboard-enable-location" class="rounded-xl border border-[var(--line)] bg-[var(--surface-sunken)] px-4 py-3 text-xs font-bold text-[var(--ink)] transition hover:border-[var(--brand)]">
                    Share my location
                </button>
            </div>
        </section>
    </div>

    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 lg:grid-cols-12">
        <!-- Left Column: Job Feed -->
        <div class="lg:col-span-8 space-y-8">
            <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black  text-slate-900">
                        {{ auth()->user()->hasRole('worker') ? 'Available Jobs' : 'Post a Job' }}</h1>
                    <p class="text-xs font-bold text-slate-400 uppercase  mt-1">
                        {{ auth()->user()->hasRole('worker') ? 'Jobs matching your skill' : 'Select a category to post' }}
                    </p>
                </div>

                <!-- Modern Search Pill -->
                <form id="job-filter-form" action="{{ route('web.app') }}" method="GET" class="relative group">
                    <x-input type="text" name="search" value="{{ $searchTerm }}" icon="search"
                        placeholder="{{ auth()->user()->hasRole('worker') ? 'Search jobs in your skills...' : 'Search skills (e.g. Painting)...' }}"
                        class="w-full sm:w-72" />
                </form>
            </header>

            @if (auth()->user()->hasRole('worker'))
                @forelse ($jobs as $job)
                    <!-- JOB FEED ITEM: MOBILE-FIRST & GLASS-STYLE -->
                    <x-job-card :job="$job" variant="feed" />

                @empty

                    <x-empty-state title="No Jobs Available" actionUrl="/dashboard" actionText="Refresh Jobs" icon="search-x"
                        subtitle="{{ $searchTerm !== '' ? 'No open jobs matched your search within your skill set.' : 'There are currently no jobs matching your skill set.' }}" />
                @endforelse
            @elseif(auth()->user()->hasRole('client'))
                <div class="border-2 border-dashed border-(--brand)/30 px-5 py-7 rounded-4xl">
                    <div class="cols-span-2 md:cols-span-3 lg:cols-span-4 mb-4">
                        <h4 class="text-xl md:text-2xl font-black uppercase">Top Skills in Demand</h4>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

                        @forelse ($skills as $skill)
                            <button type="button"
                                class="js-open-job-modal group cursor-pointer relative flex w-full items-center gap-4 p-4 rounded-[1.5rem] bg-white backdrop-blur-xl border border-gray-100 shadow-sm transition-all duration-300 hover:scale-[1.02] hover:bg-white hover:border-[var(--brand)]/20 hover:shadow-lg overflow-hidden text-left"
                                data-skill-id="{{ $skill->id }}" data-skill-name="{{ $skill->name }}">

                                <!-- Slim ID Badge -->
                                <span
                                    class="absolute top-2 right-4 text-[9px] font-black text-slate-400 group-hover:text-[var(--brand)] transition-all">
                                    #{{ $skill->id }}
                                </span>

                                <!-- Smaller, Sleeker Icon Container -->
                                <div class="dashboard-skill-icon flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition-all duration-500 group-hover:rotate-6 group-hover:shadow-lg group-hover:shadow-orange-500/20">
                                    <i data-lucide="{{ $skill->icon ?? 'palette' }}" class="h-5 w-5"></i>
                                </div>

                                <!-- Text Content: Left Aligned -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-xs font-black uppercase  transition-colors group-hover:text-[var(--brand)] truncate"
                                        style="color: var(--ink)">
                                        {{ $skill->name }}
                                    </h3>

                                    <p class="text-[10px] font-medium text-slate-500 leading-tight line-clamp-1 italic mt-0.5">
                                        {{ $skill->description }}
                                    </p>
                                </div>

                                <!-- Accent Indicator (Subtle vertical bar instead of full bottom) -->
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-0 w-1 rounded-full transition-all duration-500 group-hover:h-3/5"
                                    style="background: var(--brand)">
                                </div>
                            </button>
                        @empty
                            <div class="col-span-full">
                                <x-empty-state title="No Skills Found"
                                    subtitle="{{ $searchTerm !== '' ? 'No skill matched your search term.' : 'No skills are available right now.' }}"
                                    variant="small" />
                            </div>
                        @endforelse
                    </div>
                </div>

                <section class="rounded-[2rem] border border-slate-100 bg-white p-4 shadow-sm md:p-8">
                    <!-- Header: More compact on mobile -->
                    <div class="mb-6 flex items-start justify-between gap-4">
                        <div class="max-w-md">
                            <span
                                class="dashboard-recommended inline-block text-[9px] font-black uppercase px-2 py-0.5 rounded-md mb-2">
                                Recommended
                            </span>
                            <h2 class="text-xl font-black uppercase  text-slate-900 md:text-2xl">Top Rated
                                Workers</h2>
                            <p
                                class="mt-1 text-xs font-bold text-slate-400 uppercase  md:text-sm md:normal-case">
                                Skilled professionals verified by the community.
                            </p>
                        </div>
                    </div>

                    <!-- Grid: 1 column on mobile, 2 on tablet+ -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:gap-6">
                        @forelse ($topRatedWorkers as $worker)
                            <article
                                class="group relative flex flex-col rounded-3xl border border-slate-50 bg-slate-50 p-5 transition-all hover:border-orange-100 hover:bg-white hover:shadow-xl hover:shadow-orange-500/5">

                                <div class="flex items-start gap-4">
                                    <!-- Avatar with Rating Badge -->
                                    <div class="relative shrink-0">
                                        <x-avatar :user="$worker" size="h-16 w-16" rounded="rounded-2xl"
                                            class="shadow-md" />
                                        <div
                                            class="absolute -bottom-2 -right-1 flex items-center gap-1 rounded-full bg-slate-900 px-2 py-1 text-[9px] font-black text-white shadow-lg">
                                            <i data-lucide="star" class="h-3 w-3 fill-amber-400 text-amber-400"></i>
                                            {{ number_format((float) ($worker->ratings_received_avg_rating ?? $worker->average_rating), 1) }}
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <h3 class="truncate text-sm font-black uppercase  text-slate-900">
                                            {{ $worker->name }}
                                        </h3>
                                        <p
                                            class="dashboard-skill-category mt-0.5 truncate text-[10px] font-bold uppercase">
                                            {{ $worker->skill->name ?? 'General worker' }}
                                        </p>

                                        <div class="mt-2 flex items-center gap-2">
                                            <span class="text-[9px] font-bold uppercase  text-slate-400">
                                                {{ $worker->ratings_received_count }} Ratings
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bio: Hidden on very small screens or clamped -->
                                <p
                                    class="mt-4 line-clamp-2 text-xs font-medium leading-relaxed text-slate-500 md:text-[13px]">
                                    {{ $worker->bio ?: 'Verified worker with strong client feedback and completed jobs.' }}
                                </p>

                                <!-- Action: Full width on mobile for easier tapping -->
                                <div class="mt-5">
                                    <button type="button"
                                        class="js-open-job-modal cursor-pointer flex w-full items-center justify-center gap-2 rounded-xl bg-white border border-slate-200 py-3 text-[10px] font-black uppercase  text-slate-700 transition-all hover:bg-slate-900 hover:text-white hover:border-slate-900 active:scale-95"
                                        data-skill-id="{{ $worker->primary_skill_id ?? $worker->skill?->id }}"
                                        data-skill-name="{{ $worker->skill->name ?? 'Worker' }}">
                                        <i data-lucide="zap" class="h-3.5 w-3.5"></i>
                                        Hire Now
                                    </button>
                                </div>
                            </article>
                        @empty
                            <div class="sm:col-span-2">
                                <x-empty-state title="No Rated Workers Yet" variant="small" />
                            </div>
                        @endforelse
                    </div>
                </section>
            @endif



            <div class="mt-10 mb-6">

                <h2 class="text-lg font-black text-slate-900 mb-2">

                    {{ auth()->user()->hasRole('worker') ? 'Your Assigned Jobs' : 'Recently Posted Jobs' }}

                </h2>

                <p class="text-xs text-slate-500 font-medium">

                    {{ auth()->user()->hasRole('worker') ? 'Jobs currently assigned to you' : 'Jobs created' }}

                </p>

            </div>


            @forelse ($myJobs as $job)
                <x-job-card :job="$job" variant="assigned" />

            @empty

                <x-empty-state />
            @endforelse

        </div>

        <!-- Right Column: Profile & Activity -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Profile Summary Card (Glassmorphic) -->
            <section
                class="rounded-[2.5rem] bg-white backdrop-blur-xl border-2 border-dashed border-(--brand)/30 p-5 shadow-[0_20px_50px_rgba(0,0,0,0.04)] relative overflow-hidden group">
                <!-- Brand Accent Blur -->

                <div class="flex items-center justify-between mb-6 relative">
                    <h2 class="text-xs font-black uppercase  opacity-50" style="color: var(--ink)">Recent
                        Chats</h2>
                    <button id="refresh-chats" class="p-2 rounded-xl transition-all hover:scale-110"
                        style="background: var(--surface-soft); color: var(--brand)">
                        <i data-lucide="message-square-plus" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Chat List Container -->
                <div id="chats-panel" class="space-y-4 relative">
                    @forelse ($recentChats as $chat)
                        @php
                            $otherUser = auth()->id() === $chat->client_id ? $chat->worker : $chat->client;
                            $lastMessage = $chat->messages->first();
                            $displayName = $otherUser?->name ?? 'Deleted user';
                        @endphp

                        <a href="{{ route('web.app.conversations', ['conversation' => $chat->uuid]) }}"
                            class="flex items-center gap-4 p-4 border border-slate-300 rounded-3xl transition-all hover:bg-white hover:shadow-xl hover:shadow-orange-500/5 hover:border-[var(--brand)]/10 group/item">
                            <div class="relative">
                                <div class="h-12 w-12 rounded-2xl flex items-center justify-center font-black text-lg shadow-sm group-hover/item:rotate-3 transition-transform text-white"
                                    style="background: var(--brand)">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($displayName, 0, 1)) }}
                                </div>

                                @if (($chat->unread_messages_count ?? 0) > 0)
                                    <span
                                        class="absolute -bottom-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full border-2 border-white bg-[var(--brand)] px-1 text-[9px] font-black text-white shadow-sm">
                                        {{ $chat->unread_messages_count > 9 ? '9+' : $chat->unread_messages_count }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start gap-3">
                                    <p class="text-sm font-black truncate" style="color: var(--ink)">{{ $displayName }}
                                    </p>
                                    <span class="shrink-0 text-[10px] font-bold opacity-30 uppercase ">
                                        {{ $lastMessage?->created_at?->diffForHumans() ?? $chat->created_at?->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="text-xs font-medium opacity-50 truncate">
                                    {{ $lastMessage?->message ?? 'No messages yet.' }}
                                </p>
                            </div>

                            @if (($chat->unread_messages_count ?? 0) > 0)
                                <div class="h-2 w-2 rounded-full" style="background: var(--brand)"></div>
                            @endif
                        </a>
                    @empty
                        <div class="py-4 text-center">
                            <p class="text-[10px] font-black uppercase  opacity-30">No active messages</p>
                        </div>
                    @endforelse
                </div>
            </section>


            {{-- My Active Jobs (Compact List) -->
            <section class="rounded-[2.5rem] bg-white p-8 border border-slate-100 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                    <h2 class="text-sm font-black uppercase  text-slate-900">Live Work</h2>
                </div>
                <div id="my-jobs-list" class="space-y-4">
                    @if (auth()->user()->hasRole('worker'))
                        @forelse ($jobs as $job)
                            <x-job-card :job="$job" variant="feed" />
                        @empty
                            <x-empty-state title="No active job" subtitle="You do not have an active job" variant="small" />
                        @endforelse
                    @elseif(auth()->user()->hasRole('client'))
                        @forelse ($myJobs as $job)
                            <x-job-card :job="$job" variant="activity" />
                        @empty
                            <x-empty-state title="No recent job" subtitle="You have not posted a recent job"
                                variant="small" />
                        @endforelse
                    @endif
                </div>
            </section> --}}

            <!-- Notifications Slide-over/List -->
            <section class="dashboard-notifications rounded-[2.5rem] border p-8 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <i data-lucide="bell" class="w-4 h-4 text-[var(--brand)]"></i>
                        <h2 class="text-sm font-black uppercase ">Notifications</h2>
                    </div>
                    <a href="{{ route('web.app.notifications') }}"
                        class="dashboard-notifications__link text-[10px] font-black uppercase hover:opacity-75 transition-colors">
                        View all
                    </a>
                </div>
                <div id="notifications-list" class="max-h-64 overflow-y-auto space-y-4 custom-scrollbar">
                    @forelse ($recentNotifications as $notification)
                        <article
                            class="dashboard-notification rounded-[1.5rem] border px-4 py-4 {{ $notification->is_read ? '' : 'dashboard-notification--unread' }}">
                            <div class="flex items-start gap-3">
                                <div class="dashboard-notification__icon mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl">
                                    <i data-lucide="bell" class="h-4 w-4"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="dashboard-notification__title text-xs font-black truncate">{{ $notification->title }}</p>
                                        @if (!$notification->is_read)
                                            <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-[var(--brand)]"></span>
                                        @endif
                                    </div>
                                    <p class="dashboard-notification__message mt-1 text-[11px] font-medium leading-relaxed line-clamp-2">
                                        {{ $notification->message }}
                                    </p>
                                    <p class="dashboard-notification__time mt-3 text-[9px] font-black uppercase">
                                        {{ $notification->created_at?->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p class="text-xs font-medium text-slate-500 text-center py-4">All caught up!</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    @if (auth()->user()->hasRole('client'))
        <button type="button" id="floating-create-job-button"
            class="js-open-job-modal cursor-pointer fixed bottom-24 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[var(--brand)] text-white shadow-[0_18px_45px_rgba(255,122,0,0.35)] transition-all hover:scale-105 hover:shadow-[0_22px_55px_rgba(255,122,0,0.42)] md:bottom-8 md:right-6"
            aria-label="Create new job">
            <i data-lucide="plus" class="h-5 w-5"></i>
        </button>

        <x-modal id="createJobModal" title="Post a New Hustle" size="max-w-2xl">
            <div class="space-y-6">
                <div>
                    <p class="text-xs font-black uppercase  text-[var(--brand)]">Client Workspace</p>
                    <p class="mt-2 text-sm text-slate-500">
                        Choose a skill, add the job details, and publish straight from your dashboard.
                    </p>
                </div>

                <form id="job-create-form" action="{{ route('web.app.jobs.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid gap-4 md:grid-cols-2">
                        <x-select name="skill_id" id="job_skill_id" label="Skill" icon="briefcase-business"
                            placeholder="Choose skill" :options="$skills->pluck('name', 'id')->all()" />

                        <x-select name="payment_method" id="job_payment_method" label="Payment Method" icon="wallet"
                            placeholder="Choose payment" :options="[
                                'cash' => 'Cash',
                                'transfer' => 'Transfer',
                            ]" />
                    </div>

                    <x-input name="title" id="job_title" label="Job Title" icon="pen-square"
                        placeholder="e.g. Deep cleaning for 3-bedroom flat" />

                    <x-input type="textarea" name="description" id="job_description" rows="4" label="Description"
                        icon="file-text" placeholder="Describe the work, timing, and expectations..." />

                    <div class="grid gap-4 md:grid-cols-2">
                        <x-input type="number" step="0.01" min="0" name="budget" id="job_budget"
                            label="Budget" icon="piggy-bank" placeholder="e.g. 25000" />

                        <x-input name="location" id="job_location" label="Location" icon="map-pin"
                            placeholder="e.g. DBS Road, Asaba" />
                    </div>

                    <div
                        class="rounded-2xl border border-dashed border-[var(--brand)]/25 bg-[var(--surface-soft)] px-4 py-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase  text-[var(--brand)]">Job
                                    Location</p>
                                <p class="mt-1 text-xs font-medium text-slate-500">
                                    Existing account map are used automatically. Refresh from your browser if
                                    needed.
                                </p>
                            </div>
                            <x-button type="button" id="job-location-refresh" color="black" variant="outline"
                                class="js-location-refresh shrink-0" data-lat-target="#job_latitude"
                                data-long-target="#job_longitude" data-status-target="#job-location-status">
                                <x-slot:icon>
                                    <i data-lucide="locate-fixed" class="h-4 w-4"></i>
                                </x-slot:icon>
                                Use Map Location
                            </x-button>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <input type="hidden" step="0.000001" name="latitude" id="job_latitude"
                                value="{{ auth()->user()->latitude }}" />

                            <input type="hidden" step="0.000001" name="longitude" id="job_longitude"
                                value="{{ auth()->user()->longitude }}" />
                        </div>

                        <p id="job-location-status"
                            class="mt-3 text-[10px] font-black uppercase  text-slate-400">
                            {{ auth()->user()->latitude !== null && auth()->user()->longitude !== null ? 'Saved map location loaded' : 'Waiting for browser location' }}
                        </p>
                    </div>

                    <x-error />

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <x-button type="button" color="black" variant="outline"
                            onclick="closeModal('createJobModal')">
                            <x-slot:icon>
                                <i data-lucide="x" class="h-4 w-4"></i>
                            </x-slot:icon>
                            Cancel
                        </x-button>

                        <x-button id="job-create-submit" type="submit" color="orange">
                            <x-slot:icon>
                                <i data-lucide="plus" class="h-4 w-4"></i>
                            </x-slot:icon>
                            Publish Job
                        </x-button>
                    </div>
                </form>
            </div>
        </x-modal>
    @endif
@endsection
