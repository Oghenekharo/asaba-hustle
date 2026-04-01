@props([
    'user' => null,
    'id' => null,
    'title' => null,
    'sourceLatitude' => null,
    'sourceLongitude' => null,
    'destinationLatitude' => null,
    'destinationLongitude' => null,
    'sourceLabel' => 'Point A',
    'destinationLabel' => 'Point B',
    'mapTitle' => 'Route Map',
])
<x-modal id="{{ $id }}" title="{{ $title }}" size="max-w-2xl">
    <div class="space-y-6">
        <div class="flex flex-col gap-5 rounded-[2rem] bg-slate-50/80 p-6 sm:flex-row sm:items-start">
            <div class="shrink-0">
                <x-avatar :user="$user" name="{{ $user->name ?? $title }}" size="h-20 w-20" rounded="rounded-[1.75rem]"
                    text="text-2xl" />
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h3 class="text-2xl font-black tracking-tight text-slate-900">
                        {{ $user->name ?? $title }}
                    </h3>
                    @if ($user->is_verified)
                        <span
                            class="inline-flex items-center gap-1 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-700">
                            <i data-lucide="badge-check" class="h-3.5 w-3.5"></i>
                            Verified
                        </span>
                    @endif
                </div>
                <p class="mt-2 text-sm font-medium leading-relaxed text-slate-500">
                    {{ $user->bio ?: 'This worker has not added a bio yet.' }}
                </p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Rating</p>
                <p class="mt-3 text-2xl font-black text-slate-900">
                    {{ number_format($user->average_rating, 1) }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Primary Skill</p>
                <p class="mt-3 text-lg font-black text-slate-900">{{ $user->skill->name ?? 'Not set' }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Availability</p>
                <p class="mt-3 text-lg font-black text-slate-900">
                    {{ ucfirst($user->availability_status ?? 'unknown') }}</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Email</p>
                <p class="mt-3 text-sm font-bold text-slate-900">{{ $user->email ?? 'Not set' }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-slate-100 bg-white px-4 py-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Phone Number</p>
                <p class="mt-3 text-sm font-bold text-slate-900">{{ $user->phone ?? 'Not set' }}</p>
            </div>
        </div>

        <x-route-map :title="$mapTitle" :source-latitude="$sourceLatitude" :source-longitude="$sourceLongitude" :destination-latitude="$destinationLatitude"
            :destination-longitude="$destinationLongitude" :source-label="$sourceLabel" :destination-label="$destinationLabel" height-class="h-56" />

        <div class="rounded-[1.5rem] border border-slate-100 bg-white px-5 py-5">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Additional Skills</p>
            <div class="mt-4 flex flex-wrap gap-2">
                @forelse ($user?->skills?->reject(fn($skill) => $skill->id === $user->primary_skill_id) ?? collect() as $skill)
                    <span
                        class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-slate-700">
                        {{ $skill->name }}
                    </span>
                @empty
                    <p class="text-sm font-medium text-slate-500">No additional skills listed.</p>
                @endforelse
            </div>
        </div>

    </div>
</x-modal>
