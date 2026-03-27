@props([
    'label' => null,
    'type' => 'text',
    'name' => '',
    'icon' => null,
    'id' => null,
    'placeholder' => '',
    'value' => '',
])

@php
    $id = $id ?? $name;
    $isPassword = $type === 'password';
    $hasIcon = $icon !== null;

    if ($hasIcon) {
        $pl = 'pl-10 ';
    } else {
        $pl = 'pl-4 ';
    }
@endphp

<!-- Phone Input -->

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
                class="absolute left-4 {{ $type === 'textarea' ? 'top-6' : 'top-1/2' }} -translate-y-1/2 w-4 h-4 text-slate-400"></i>
        @endif
        @if ($type === 'textarea')
            <textarea type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ old($name, $value) }}"
                placeholder="{{ $placeholder }}"
                {{ $attributes->whereDoesntStartWith('class')->merge([
                    'class' =>
                        'w-full ' .
                        $pl .
                        ' rounded-xl resize-none border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold outline-none transition-all focus:border-[var(--brand)] focus:bg-white focus:ring-4 focus:ring-orange-500/5',
                ]) }}>{{ old($name, $value) }}</textarea>
        @else
            <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}"
                value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}"
                {{ $attributes->whereDoesntStartWith('class')->merge([
                    'class' =>
                        'w-full ' .
                        $pl .
                        ' rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold outline-none transition-all focus:border-[var(--brand)] focus:bg-white focus:ring-4 focus:ring-orange-500/5' .
                        ($isPassword ? ' pr-11' : ''),
                ]) }} />

            @if ($isPassword)
                <button type="button"
                    class="js-password-toggle cursor-pointer absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none"
                    data-target="{{ $id }}">
                    <i data-lucide="eye" class="w-5 h-5 js-eye-icon"></i>
                    <i data-lucide="eye-off" class="w-5 h-5 js-eye-off-icon hidden"></i>
                </button>
            @endif
        @endif
    </div>
    <p id="error_{{ $name }}" class="text-xs font-medium text-red-500 mt-1 hidden"></p>
</div>
