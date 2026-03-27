{{-- @props(['type' => 'error'])

<div id="js-error-container" data-type="{{ $type }}"
    class="hidden mt-3 rounded-xl border animate-in fade-in slide-in-from-top-1
     {{ $type === 'success' ? 'border-emerald-100 bg-emerald-50/50 text-emerald-600' : '' }}
     {{ $type === 'warning' ? 'border-amber-100 bg-amber-50/50 text-amber-600' : '' }}
     {{ $type === 'info' ? 'border-blue-100 bg-blue-50/50 text-blue-600' : '' }}
     {{ $type === 'error' ? 'border-red-100 bg-red-50/50 text-red-600' : '' }} p-4 text-sm">
    <div class="flex items-center gap-2">
        <i data-lucide="alert-circle" id="error-icon" class="h-4 w-4"></i>
        <span id="error-message"></span>
    </div>
</div> --}}

@props(['type' => 'error'])

@php
    $typeClasses = match ($type) {
        'success' => 'border-emerald-100 bg-emerald-50/50 text-emerald-600',
        'warning' => 'border-amber-100 bg-amber-50/50 text-amber-600',
        'info' => 'border-blue-100 bg-blue-50/50 text-blue-600',
        default => 'border-red-100 bg-red-50/50 text-red-600',
    };
@endphp

<div id="js-error-container" data-type="{{ $type }}"
    {{ $attributes->merge([
        'class' => "space-y-1.5 hidden mt-3 rounded-xl border animate-in fade-in slide-in-from-top-1 p-4 text-sm $typeClasses",
    ]) }}>

    <div class="flex items-start justify-between gap-3">

        <div class="flex items-center gap-2">
            <i data-lucide="alert-circle" class="h-4 w-4"></i>
            <span id="error-message"></span>
        </div>
        <button type="button" onclick="closeAlert(this)"
            class="flex items-center cursor-pointer justify-center opacity-70 hover:opacity-100">
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
    </div>
</div>
