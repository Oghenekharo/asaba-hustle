@extends('admin.layout')

@section('title', 'Appearance')

@section('content')
    <div class="mx-auto max-w-5xl space-y-8">
        <header>
            <p class="text-xs font-semibold uppercase text-[var(--brand)]">Site settings</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Primary color</h1>
            <p class="mt-2 text-sm text-slate-500">Choose a solid color or gradient for the site’s primary accents.</p>
        </header>

        <form method="POST" action="{{ route('admin.appearance.update') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PATCH')

            @foreach (['solid' => 'Solid colors', 'gradient' => 'Gradient colors'] as $type => $heading)
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
                    <h2 class="mb-5 text-lg font-semibold text-slate-800">{{ $heading }}</h2>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                        @foreach ($colors as $key => $color)
                            @continue($color['type'] !== $type)
                            <label class="cursor-pointer">
                                <input class="peer sr-only" type="radio" name="primary_color" value="{{ $key }}"
                                    @checked(old('primary_color', $selectedColor) === $key)>
                                <span class="block rounded-xl border-2 border-transparent p-2 transition peer-checked:border-slate-800 peer-focus-visible:ring-2 peer-focus-visible:ring-[var(--brand)]">
                                    <span class="mb-2 block h-14 rounded-lg" style="background: {{ $color['type'] === 'gradient' ? "linear-gradient(135deg, {$color['start']}, {$color['end']})" : $color['start'] }}"></span>
                                    <span class="block text-center text-xs font-medium text-slate-700">{{ $color['label'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
                <h2 class="mb-2 text-lg font-semibold text-slate-800">Site icon</h2>
                <p class="mb-5 text-sm text-slate-500">Used in the site header, browser favicon, and splash screen. Upload PNG, JPG, SVG, WebP, or ICO (max 2 MB).</p>
                <div class="flex flex-wrap items-center gap-5">
                    <img src="{{ $siteIcon ? asset('storage/' . $siteIcon) : asset('images/icons/asaba-hustle.svg') }}" alt="Current site icon" class="h-16 w-16 rounded-xl border border-slate-200 object-contain p-2">
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Choose a new icon
                        <input type="file" name="site_icon" accept="image/png,image/jpeg,image/svg+xml,image/webp,image/x-icon" class="text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[var(--surface-soft)] file:px-4 file:py-2 file:font-medium file:text-[var(--brand)]">
                    </label>
                </div>
                @error('site_icon') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            </section>

            @error('primary_color')
                <p class="text-sm text-rose-600">{{ $message }}</p>
            @enderror

            <button type="submit" class="rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-sm" style="background: var(--brand-gradient)">Save appearance</button>
        </form>
    </div>
@endsection
