@props([
    'label' => null,
    'name' => '',
    'id' => null,
    'icon' => null,
    'options' => [], // Expects associative array [value => label]
    'selected' => null,
    'placeholder' => 'Select an option',
])

@php
    $id = $id ?? $name;
    $selectedValues = collect(old($name, $selected))->flatten()->map(fn ($value) => (string) $value)->all();
    $isMultiple = $attributes->has('multiple');

    $hasIcon = $icon !== null;

    if ($hasIcon) {
        $pl = 'pl-10 ';
    } else {
        $pl = 'pl-4 ';
    }
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $id }}"
            class="ml-1 text-[10px] block font-black uppercase tracking-widest text-slate-400">
            {{ $label }}
        </label>
    @endif

    <div class="relative group">
        @if ($icon !== null)
            <i data-lucide="{{ $icon }}"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
        @endif
        <select name="{{ $name }}" id="{{ $id }}"
            {{ $attributes->whereDoesntStartWith('class')->merge([
                'class' =>
                    'block w-full ' .
                    $pl .
                    ' rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold outline-none transition-all focus:border-orange-500 focus:bg-white cursor-pointer appearance-none',
            ]) }}>

            @if ($placeholder && !$isMultiple)
                <option value="" disabled {{ is_null(old($name, $selected)) ? 'selected' : '' }}>
                    {{ $placeholder }}
                </option>
            @endif

            @foreach ($options as $value => $display)
                <option value="{{ $value }}" {{ in_array((string) $value, $selectedValues, true) ? 'selected' : '' }}>
                    {{ $display }}
                </option>
            @endforeach
        </select>

        <!-- Custom Chevron Icon -->
        @unless ($isMultiple)
            <div
                class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-slate-600 transition-colors">
                <i data-lucide="chevron-down" class="w-5 h-5"></i>
            </div>
        @endunless
    </div>

    <p id="error_{{ $name }}" class="text-xs font-medium text-red-500 mt-1 hidden"></p>
</div>
