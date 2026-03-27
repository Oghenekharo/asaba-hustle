@props([
    'title' => 'No jobs yet',
    'subtitle' => 'Check back later or try adjusting your filters.',
    'icon' => null,
    'actionText' => null,
    'actionUrl' => '#',
    'variant' => 'large', //{{-- small or large --}}
])

@php
    $isSmall = $variant === 'small';

    $containerClasses = $isSmall ? 'p-6 rounded-2xl border-1' : 'p-12 rounded-[2.5rem] border-2';

    $iconClasses = $isSmall ? 'h-12 w-12 rounded-xl mb-3 text-xl' : 'h-20 w-20 rounded-3xl mb-6 text-4xl';

    $titleClasses = $isSmall ? 'text-lg' : 'text-2xl';
@endphp

<div {{ $attributes->merge(['class' => "relative overflow-hidden border-dashed border-[var(--brand)]/30 text-center transition-all $containerClasses"]) }}
    style="background: var(--surface-soft)">

    <!-- Abstract Background Element (Hidden on small to save space) -->
    @if (!$isSmall)
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full blur-3xl opacity-10"
            style="background: var(--brand)"></div>
    @endif

    <div class="relative z-10 flex flex-col items-center">
        <!-- Icon Container -->
        <div
            class="flex items-center justify-center bg-white shadow-xl shadow-orange-500/5 animate-bounce-slow {{ $iconClasses }}">
            @if ($icon)
                <span data-lucide="{{ $icon }}" style="color: var(--brand)"
                    class="{{ $isSmall ? 'h-5 w-5' : 'h-10 w-10' }}"></span>
            @else
                <span data-lucide="briefcase-business" style="color: var(--brand)"
                    class="{{ $isSmall ? 'h-5 w-5' : 'h-9 w-9' }}"></span>
            @endif
        </div>
        {{--
        <div
            class="mb-6 flex h-16 w-16 md:h-20 md:w-20 items-center justify-center rounded-2xl md:rounded-3xl bg-white text-4xl shadow-xl shadow-orange-500/5 animate-bounce-slow">
            @if ($icon)
                <span data-lucide="{{ $icon }}" class="h-6 w-6 md:h-10 md:w-10"></span>
            @else
                <span data-lucide="briefcase-business" class="h-6 w-6 md:h-9 md:w-9"></span>
            @endif
        </div> --}}

        <!-- Typography -->
        <h3 class="font-black tracking-tighter text-[var(--ink)] {{ $titleClasses }}">
            {{ $title }}
        </h3>

        <p class="mt-1 max-w-[240px] text-xs font-medium leading-relaxed opacity-50">
            {{ $subtitle }}
        </p>

        <!-- Optional Action Button -->
        @if ($actionText)
            <a href="{{ $actionUrl }}"
                class="rounded-xl font-black uppercase tracking-widest text-white transition-all hover:scale-105 active:scale-95 shadow-lg shadow-orange-500/20 {{ $isSmall ? 'mt-4 px-4 py-2 text-[10px]' : 'mt-8 px-8 py-3 text-xs' }}"
                style="background: var(--brand)">
                {{ $actionText }}
            </a>
        @endif
    </div>
</div>

<style>
    @keyframes bounce-slow {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .animate-bounce-slow {
        animation: bounce-slow 4s ease-in-out infinite;
    }
</style>
