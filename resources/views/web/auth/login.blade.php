@extends('layouts.app', ['title' => 'Login | Asaba Hustle'])

@section('content')
    <section class="mx-auto max-w-md">
        <div
                    class="rounded-[2.5rem] border border-[var(--brand)]/5 bg-white p-8 shadow-[0_32px_64px_-16px_color-mix(in_srgb,var(--brand)_10%,transparent)]">

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

                <h1 class="text-2xl font-black  text-slate-900 leading-tight">Welcome Back</h1>
                <p class="mt-1 text-[10px] font-black uppercase  text-slate-400">Manage your hustles</p>
            </div>


            @php
                $vState = request()->query('verified');
                $vMsg = match ($vState) {
                    'phone-success' => [
                        'bg-emerald-50 text-emerald-700 border-emerald-100',
                        '✓ Verified! Log in below.',
                    ],
                    'phone-invalid', 'phone-failed' => [
                        'bg-rose-50 text-rose-700 border-rose-100',
                        'Token expired or invalid.',
                    ],
                    'email-success' => [
                        'bg-emerald-50 text-emerald-700 border-emerald-100',
                        'Email verified. You can sign in now.',
                    ],
                    'email-pending' => [
                        'bg-blue-50 text-blue-700 border-blue-100',
                        'Check your email for a verification link before signing in.',
                    ],
                    'email-invalid', 'email-failed' => [
                        'bg-rose-50 text-rose-700 border-rose-100',
                        'That email verification link is invalid or expired. Register again or contact support.',
                    ],
                    default => null,
                };
            @endphp

            @if ($vMsg)
                <div
                    class="mb-6 rounded-xl border px-4 py-3 text-[11px] font-black uppercase  flex items-center gap-3 animate-in fade-in slide-in-from-top-1 {{ $vMsg[0] }}">
                    {{ $vMsg[1] }}
                </div>
            @endif

            @if (session('loggedOutStatus'))
                <div data-message="{{ session('loggedOutStatus') }}" id="loggedOutBox" class="hidden"></div>
            @endif
            <form id="login-form" action="{{ route('web.login.submit') }}" method="POST" class="space-y-4">
                @csrf
                <fieldset>
                    <legend class="mb-2 text-[10px] font-medium uppercase  text-slate-500">Sign in with</legend>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input class="peer sr-only" type="radio" name="channel" value="phone">
                            <span class="auth-method-option flex min-h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-600 transition peer-checked:border-[var(--brand)] peer-checked:bg-[var(--surface-soft)] peer-checked:text-[var(--brand)]">
                                <i data-lucide="phone" class="h-4 w-4"></i> Phone
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input class="peer sr-only" type="radio" name="channel" value="email" checked>
                            <span class="auth-method-option flex min-h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-600 transition peer-checked:border-[var(--brand)] peer-checked:bg-[var(--surface-soft)] peer-checked:text-[var(--brand)]">
                                <i data-lucide="mail" class="h-4 w-4"></i> Email
                            </span>
                        </label>
                    </div>
                </fieldset>

                <div id="phone-field">
                    <x-input name="phone" type="tel" label="Phone Number" placeholder="08012345678" icon="phone" />
                </div>
                <div id="email-field" class="hidden">
                    <x-input name="email" type="email" label="Email address" placeholder="you@example.com" icon="mail" />
                </div>

                <x-input name="password" type="password" label="Password" placeholder="••••••••" icon="lock" required />
                <a href="{{ route('web.password.request') }}"
                    class="text-[10px] font-black text-[var(--brand)] uppercase hover:opacity-70 transition">Forgot
                    password?</a>

                <x-error />
                <x-button class="w-full mt-2" id="login-submit" type="submit">
                    <x-slot:icon>
                        <i data-lucide="log-in" class="h-4 w-4"></i>
                    </x-slot:icon>
                    Login
                </x-button>
            </form>

            <div class="mt-4 border-t border-slate-50 pt-6 text-center">
                <p class="text-[11px] font-bold text-slate-400 uppercase ">New here?</p>
                <a href="{{ route('web.register') }}"
                    class="mt-1 inline-block text-xs font-black text-[var(--brand)] hover:opacity-75 transition-colors uppercase">
                    Join the Hustle
                </a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.asabaAuthConfig = {
            action: @json(route('web.login.submit')),
            redirectFallback: @json(route('web.app')),
            formId: '#login-form',
            feedbackId: '#auth-feedback'
        };
    </script>
@endpush
