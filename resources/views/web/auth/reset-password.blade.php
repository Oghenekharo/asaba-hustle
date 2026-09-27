@extends('layouts.app', ['title' => 'Reset Password | Asaba Hustle'])

@section('content')
    <section class="mx-auto max-w-md">
        <div
            class="rounded-[2.5rem] border border-slate-100 bg-white p-8 md:p-10 shadow-[0_32px_64px_-16px_color-mix(in_srgb,var(--brand)_10%,transparent)]">

            <!-- Compact Header -->
            <div class="relative mb-8 text-center">
                <!-- Back Home Button -->
                <a href="{{ route('web.home') }}"
                    class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400 transition-all hover:bg-[var(--surface-soft)] hover:text-[var(--brand)] active:scale-95"
                    title="Go Home">
                    <i data-lucide="chevron-left" class="h-5 w-5"></i>
                </a>

                <div class="inline-flex items-center justify-center mb-4">
                    <img src="{{ $siteTheme['icon'] ? asset('storage/' . $siteTheme['icon']) : asset('images/icons/asaba-hustle.svg') }}" class="w-12 h-12 drop-shadow-sm object-contain" alt="Asaba Hustle" />
                </div>

                <h1 class="text-2xl font-black  text-slate-900 leading-tight">Password Reset</h1>
                <p class="mt-1 text-[10px] font-black uppercase  text-slate-400">Fill the fields below to
                    reset your password.</p>
            </div>


            <form id="reset-password-form" method="POST" action="{{ route('web.password.update') }}"
                class="mt-8 space-y-5">
                @csrf
                @php($resetChannel = !$phoneAuthEnabled || $email !== '' ? 'email' : 'phone')
                @if ($phoneAuthEnabled)
                <fieldset>
                    <legend class="mb-2 text-[10px] font-medium uppercase  text-slate-500">Recovery method</legend>
                    <div class="grid {{ $phoneAuthEnabled ? 'grid-cols-2' : 'grid-cols-1' }} gap-3">
                        @if ($phoneAuthEnabled)
                        <label class="cursor-pointer">
                            <input class="peer sr-only" type="radio" name="channel" value="phone" @checked($resetChannel === 'phone')>
                            <span class="auth-method-option flex min-h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-600 transition peer-checked:border-[var(--brand)] peer-checked:bg-[var(--surface-soft)] peer-checked:text-[var(--brand)]">
                                <i data-lucide="phone" class="h-4 w-4"></i> Phone (SMS)
                            </span>
                        </label>
                        @endif
                        <label class="cursor-pointer">
                            <input class="peer sr-only" type="radio" name="channel" value="email" @checked($resetChannel === 'email')>
                            <span class="auth-method-option flex min-h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-600 transition peer-checked:border-[var(--brand)] peer-checked:bg-[var(--surface-soft)] peer-checked:text-[var(--brand)]">
                                <i data-lucide="mail" class="h-4 w-4"></i> Email
                            </span>
                        </label>
                    </div>
                </fieldset>
                @endif

                @if ($phoneAuthEnabled)
                <div id="phone-field" @class(['space-y-1.5', 'hidden' => $resetChannel !== 'phone'])>
                    <x-input name="phone" type="tel" value="{{ $phone }}" label="Phone Number" icon="phone"
                        placeholder="0810..." />
                </div>
                <div id="email-field" @class(['space-y-1.5', 'hidden' => $resetChannel !== 'email'])>
                    <x-input name="email" type="email" value="{{ $email }}" label="Email address" icon="mail"
                        placeholder="you@example.com" />
                </div>
                @else
                    <input type="hidden" name="channel" value="email">
                    <x-input name="email" type="email" value="{{ $email }}" label="Email address" icon="mail"
                        placeholder="you@example.com" />
                @endif
                <div class="space-y-1.5">
                    <x-input name="token" type="number" label="Verification Token" numeric icon="key"
                        placeholder="000000" minlength="1" maxlength="6" />
                </div>

                <div class="space-y-3">
                    <div class="space-y-1.5">
                        <x-input name="password" type="password" label="Password" placeholder="••••••••" icon="lock"
                            required />
                    </div>
                    <div class="space-y-1.5">
                        <x-input name="password_confirmation" type="password" label="Confirm Password"
                            placeholder="••••••••" icon="lock" required />
                    </div>
                </div>
                <x-error />
                <!-- Submit Button -->
                <x-button class="w-full mt-2" id="reset-password-submit" type="submit">
                    <x-slot:icon>
                        <i data-lucide="key" class="h-4 w-4"></i>
                    </x-slot:icon>
                    Update Password
                </x-button>
            </form>
        </div>
    </section>
@endsection
