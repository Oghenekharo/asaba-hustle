@props(['title', 'value', 'icon' => 'circle', 'tone' => 'orange', 'meta' => null])

@php
    $tones = [
        'orange' => 'text-white bg-[var(--brand)] border-[var(--brand-strong)]',
        'emerald' => 'text-white bg-emerald-600 border-emerald-700',
        'blue' => 'text-white bg-blue-600 border-blue-700',
        'violet' => 'text-white bg-violet-600 border-violet-700',
        'rose' => 'text-white bg-rose-600 border-rose-700',
        'slate' => 'text-white bg-slate-600 border-slate-700',
    ];
    $toneClass = $tones[$tone] ?? $tones['orange'];
@endphp

<div
    {{ $attributes->merge(['class' => 'group relative overflow-hidden rounded-[2rem] border border-[var(--line)] bg-[var(--surface-raised)] p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-black/5']) }}>

    <!-- Top Row: Icon & Trend Decoration -->
    <div class="mb-4 flex items-center justify-between">
        <div
            class="flex h-10 w-10 items-center justify-center rounded-xl border transition-all duration-500 group-hover:scale-110 group-hover:rotate-3 {{ $toneClass }}">
            <i data-lucide="{{ $icon }}" class="h-5 w-5"></i>
        </div>

        <!-- Abstract Trend Graphic (Purely Aesthetic) -->
        <div class="opacity-10 group-hover:opacity-30 transition-opacity">
            <i data-lucide="trending-up" class="h-5 w-5"></i>
        </div>
    </div>

    <!-- Content Row -->
    <div class="relative z-10">
        <p
            class="text-[10px] font-black uppercase text-[var(--muted)] group-hover:text-[var(--brand)] transition-colors">
            {{ $title }}
        </p>

        <h3 class="mt-1 text-xl font-black  text-[var(--ink)] md:text-2xl">
            {{ $value }}
        </h3>

        @if ($meta)
            <div class="mt-3 flex items-center gap-1.5">
                    <span class="h-1 w-1 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                <p class="text-[10px] font-bold italic text-[var(--muted)]">
                    {{ $meta }}
                </p>
            </div>
        @endif
    </div>

    <!-- Bottom Accent (Invisible until hover) -->
    <div class="absolute bottom-0 left-0 h-1 w-0 bg-[var(--brand)] transition-all duration-500 group-hover:w-full">
    </div>
</div>
