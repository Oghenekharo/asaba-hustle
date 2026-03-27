@props(['job', 'variant' => 'feed'])

@php
    $user = auth()->user();
    $isOwner = $job->user_id === $user->id;
    $isFeed = $variant === 'feed';
    $isAssignedWorker = (int) $job->assigned_to === (int) $user->id;
    $statusLabel = str_replace('_', ' ', $job->status);

    // Logic for the status badge
    $badgeLabel = $isFeed ? 'Available Hustle' : ($isOwner ? 'Your Posting' : 'Active Assignment');
    $badgeBg = $isFeed
        ? 'bg-orange-50 text-orange-600 border-orange-100'
        : ($isOwner
            ? 'bg-indigo-50 text-indigo-600 border-indigo-100'
            : 'bg-blue-50 text-blue-600 border-blue-100');

    // Logic for contact display
    $contactName = $isOwner ? $job->worker->name ?? 'Searching...' : $job->client->name ?? 'Client';
    $contactPhone = $isOwner
        ? $job->worker->phone ?? null
        : ($isAssignedWorker ? $job->client->phone ?? null : null);
@endphp

<div
    class="group relative overflow-hidden rounded-[2rem] border border-white bg-white/70 p-5 shadow-sm backdrop-blur-xl transition-all hover:shadow-xl hover:-translate-y-0.5 border-slate-100/50 mb-4">

    <!-- Top Meta Row -->
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-widest {{ $badgeBg }}">
                    <i data-lucide="{{ $isFeed ? 'zap' : ($isOwner ? 'user' : 'clock') }}" class="h-3 w-3"></i>
                    {{ $badgeLabel }}
                </span>
                <span
                    class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-slate-600">
                    {{ $statusLabel }}
                </span>
                @if (!$isFeed && $job->status === 'assigned')
                    <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse"></span>
                @endif
        </div>
        <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter bg-slate-50 px-2 py-1 rounded-md">
            ID #{{ $job->id }}
        </span>
    </div>

    <!-- Main Content Body -->
    <div class="flex flex-col gap-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-4">
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-lg transition-transform group-hover:scale-105">
                    <i data-lucide="{{ $job->skill->icon ?? 'briefcase' }}" class="h-5 w-5 text-orange-400"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-900 leading-tight line-clamp-1 italic">
                        {{ $job->title }}
                    </h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                        {{ $job->skill->name }}
                    </p>
                </div>
            </div>

            <div class="text-right">
                <p class="text-[9px] font-black text-slate-300 uppercase">Budget</p>
                <p class="text-sm font-black text-slate-900 italic">₦{{ number_format($job->budget) }}</p>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-2 gap-4 py-4 border-y border-slate-50">
            <div>
                <p class="text-[9px] font-black uppercase text-slate-400 mb-1">
                    {{ $isFeed ? 'Location' : ($isOwner ? 'Assigned Worker' : 'Client Name') }}
                </p>
                <div class="flex items-center gap-1.5">
                    <i data-lucide="{{ $isFeed ? 'map-pin' : 'user-check' }}" class="h-3 w-3 text-slate-400"></i>
                    <p class="text-[11px] font-bold text-slate-700 truncate">
                        {{ $isFeed ? Str::limit($job->location, 22) : $contactName }}
                    </p>
                </div>
            </div>

            <div class="text-right">
                <p class="text-[9px] font-black uppercase text-slate-400 mb-1">
                    {{ $isFeed ? 'Payment' : 'Contact' }}
                </p>
                @if ($isFeed)
                    <p class="text-[10px] font-bold text-slate-600 uppercase">{{ $job->payment_method ? ucfirst($job->payment_method) : 'Cash' }}</p>
                @elseif($contactPhone)
                    <a href="tel:{{ $contactPhone }}"
                        class="inline-flex items-center gap-1 text-[10px] font-black text-orange-600 uppercase hover:underline">
                        <i data-lucide="phone" class="h-3 w-3"></i>
                        {{ $isOwner ? 'Call Worker' : 'Call Client' }}
                    </a>
                @elseif(!$isOwner && !$isAssignedWorker)
                    <p class="text-[9px] font-bold text-slate-300 uppercase italic">Phone Hidden</p>
                @else
                    <p class="text-[9px] font-bold text-slate-300 uppercase italic">Awaiting Pro</p>
                @endif
            </div>
        </div>

        <!-- Footer Action -->
        <div class="flex items-center justify-between gap-3">
            <div class="flex flex-col">
                <p class="text-[8px] font-black uppercase text-slate-300">Last Update</p>
                <p class="text-[10px] font-bold text-slate-400">{{ $job->updated_at->diffForHumans() }}</p>
            </div>

            <a href="/app/jobs/{{ $job->id }}"
                class="flex-1 sm:flex-none cursor-pointer text-center px-6 py-3 rounded-xl {{ $isFeed ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/20' : 'bg-white border border-slate-200 text-slate-900 hover:bg-slate-50' }} font-black text-[10px] uppercase tracking-widest transition-all active:scale-95">
                {{ $isFeed ? 'Grab Hustle' : 'View Job' }}
            </a>
        </div>
    </div>
</div>
