@props([
    'user' => null,
    'name' => null,
    'photo' => null,
    'size' => 'h-10 w-10',
    'rounded' => 'rounded-2xl',
    'text' => 'text-sm',
    'class' => '',
])

@php
    $displayName = trim((string) ($name ?? $user?->name ?? 'User'));
    $profilePhoto = $photo ?? $user?->profile_photo ?? null;
    $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($displayName, 0, 1));
@endphp

<div
    {{ $attributes->merge(['class' => "{$size} {$rounded} {$text} {$class} overflow-hidden bg-slate-900 text-white flex items-center justify-center font-black uppercase shrink-0"]) }}>
    @if ($profilePhoto)
        <img src="{{ asset('storage/' . $profilePhoto) }}" alt="{{ $displayName }}"
            class="h-full w-full object-cover">
    @else
        <span>{{ $initial }}</span>
    @endif
</div>
