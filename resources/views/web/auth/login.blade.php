@extends('layouts.app', ['title' => 'Login | Asaba Hustle'])

@section('content')
    <section class="mx-auto max-w-md">
        <div
            class="rounded-[2.5rem] border border-[var(--brand)]/5 bg-white p-8 shadow-[0_32px_64px_-16px_rgba(255,122,0,0.1)]">

            <!-- Compact Header -->
            <div class="mb-8 text-center">
                <div onclick="location.href='{{ route('web.home') }}'"
                    class="inline-flex cursor-pointer h-10 w-10 items-center justify-center rounded-xl mb-4 shadow-md shadow-orange-500/10 text-white"
                    style="background: var(--brand)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900">Welcome Back</h1>
                <p class="mt-1 text-xs font-bold text-slate-400">Manage your hustles & bookings.</p>
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
                    default => null,
                };
            @endphp

            @if ($vMsg)
                <div
                    class="mb-6 rounded-xl border px-4 py-3 text-[11px] font-black uppercase tracking-wider flex items-center gap-3 animate-in fade-in slide-in-from-top-1 {{ $vMsg[0] }}">
                    {{ $vMsg[1] }}
                </div>
            @endif

            @if (session('loggedOutStatus'))
                <div data-message="{{ session('loggedOutStatus') }}" id="loggedOutBox" class="hidden"></div>
            @endif
            <form id="login-form" action="{{ route('web.login.submit') }}" method="POST" class="space-y-4">
                @csrf
                <x-input name="phone" type="tel" label="Phone Number" placeholder="08012345678" icon="phone"
                    required />

                <x-input name="password" type="password" label="Password" placeholder="••••••••" icon="lock" required />
                <a href="{{ route('web.password.request') }}"
                    class="text-[10px] font-black text-orange-500 uppercase tracking-widest hover:opacity-70 transition">Forgot
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
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">New here?</p>
                <a href="{{ route('web.register') }}"
                    class="mt-1 inline-block text-xs font-black text-orange-600 hover:text-orange-700 transition-colors uppercase tracking-widest">
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
