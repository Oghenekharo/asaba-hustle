{{-- @props(['type' => 'error'])

<div id="js-error-container" data-type="{{ $type }}"
    class="hidden mt-3 rounded-xl border animate-in fade-in slide-in-from-top-1
     {{ $type === 'success' ? 'border-emerald-300 bg-emerald-100 text-emerald-800' : '' }}
     {{ $type === 'warning' ? 'border-amber-300 bg-amber-100 text-amber-800' : '' }}
     {{ $type === 'info' ? 'border-blue-300 bg-blue-100 text-blue-800' : '' }}
     {{ $type === 'error' ? 'border-red-300 bg-red-100 text-red-800' : '' }} p-4 text-sm">
    <div class="flex items-center gap-2">
        <i data-lucide="alert-circle" id="error-icon" class="h-4 w-4"></i>
        <span id="error-message"></span>
    </div>
</div> --}}

@props(['type' => 'error'])

@php
    $typeClasses = match ($type) {
        'success' => 'border-emerald-300 bg-emerald-100 text-emerald-800 dark:border-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
        'warning' => 'border-amber-300 bg-amber-100 text-amber-800',
        'info' => 'border-blue-300 bg-blue-100 text-blue-800',
        default => 'border-red-300 bg-red-100 text-red-800',
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
