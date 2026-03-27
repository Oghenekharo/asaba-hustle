@props([
    'type' => 'button',
    'variant' => 'solid', // solid | outline
    'color' => 'orange', // orange | blue | green | black
    'size' => 'md', // sm | md | lg
    'disabled' => false,
])

@php
    $solidColors = [
        'orange' => 'bg-orange-500 text-white hover:shadow-orange-500/20',
        'blue' => 'bg-blue-600 text-white hover:shadow-blue-600/20',
        'green' => 'bg-green-600 text-white hover:shadow-green-600/20',
        'black' => 'bg-slate-900 text-white hover:shadow-slate-900/20',
    ];

    $outlineColors = [
        'orange' => 'border border-orange-500 text-orange-500 hover:bg-orange-50',
        'blue' => 'border border-blue-600 text-blue-600 hover:bg-blue-50',
        'green' => 'border border-green-600 text-green-600 hover:bg-green-50',
        'black' => 'border border-slate-900 text-slate-900 hover:bg-slate-100',
    ];

    $sizes = [
        'sm' => 'px-4 py-2 text-xs',
        'md' => 'px-5 py-3 text-xs',
        'lg' => 'px-6 py-4 text-sm',
    ];

    $colorClass =
        $variant === 'outline'
            ? $outlineColors[$color] ?? $outlineColors['orange']
            : $solidColors[$color] ?? $solidColors['orange'];

    $sizeClass = $sizes[$size] ?? $sizes['md'];

    $disabledClass = $disabled
        ? 'opacity-60 cursor-not-allowed pointer-events-none'
        : 'cursor-pointer active:scale-95 hover:shadow-lg';
@endphp


<button type="{{ $type }}" data-loading="false"
    {{ $attributes->merge([
        'class' => "ajax-button relative flex items-center justify-center gap-2 overflow-hidden rounded-xl font-black uppercase tracking-widest transition-all $sizeClass $colorClass $disabledClass",
    ]) }}
    @disabled($disabled)>

    {{-- Spinner --}}
    <i data-lucide="loader-2" class="spinner h-5 w-5 animate-spin hidden"></i>

    {{-- Icon --}}
    @isset($icon)
        <span class="button-icon flex items-center">
            {{ $icon }}
        </span>
    @endisset

    {{-- Text --}}
    <span class="button-text">
        {{ $slot }}
    </span>

</button>
