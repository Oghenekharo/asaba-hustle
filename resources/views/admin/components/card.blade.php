@props(['title', 'value', 'icon' => 'circle', 'tone' => 'orange', 'meta' => null])

@php
    $tones = [
        'orange' => 'text-orange-600 bg-orange-50 shadow-orange-500/10 border-orange-100',
        'emerald' => 'text-emerald-600 bg-emerald-50 shadow-emerald-500/10 border-emerald-100',
        'blue' => 'text-blue-600 bg-blue-50 shadow-blue-500/10 border-blue-100',
        'violet' => 'text-violet-600 bg-violet-50 shadow-violet-500/10 border-violet-100',
        'rose' => 'text-rose-600 bg-rose-50 shadow-rose-500/10 border-rose-100',
        'slate' => 'text-slate-600 bg-slate-50 shadow-slate-500/10 border-slate-100',
    ];
    $toneClass = $tones[$tone] ?? $tones['orange'];
@endphp

<div
    {{ $attributes->merge(['class' => 'group relative overflow-hidden rounded-[2rem] border border-white bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-black/5']) }}>

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
            class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 group-hover:text-[var(--brand)] transition-colors">
            {{ $title }}
        </p>

        <h3 class="mt-1 text-xl font-black tracking-tighter text-[var(--ink)] md:text-2xl">
            {{ $value }}
        </h3>

        @if ($meta)
            <div class="mt-3 flex items-center gap-1.5">
                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                <p class="text-[10px] font-bold italic text-slate-400">
                    {{ $meta }}
                </p>
            </div>
        @endif
    </div>

    <!-- Bottom Accent (Invisible until hover) -->
    <div class="absolute bottom-0 left-0 h-1 w-0 bg-[var(--brand)] transition-all duration-500 group-hover:w-full">
    </div>
</div>
