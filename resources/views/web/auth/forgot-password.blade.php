@extends('layouts.app', ['title' => 'Forgot Password | Asaba Hustle'])

@section('content')
    <section class="mx-auto max-w-md">
        <div
            class="rounded-[2.5rem] border border-slate-100 bg-white p-8 md:p-10 shadow-[0_32px_64px_-16px_rgba(255,122,0,0.1)]">

            <!-- Header -->
            <div class="mb-8 text-center">
                <div onclick="location.href='{{ route('web.home') }}'"
                    class="inline-flex cursor-pointer h-12 w-12 items-center justify-center rounded-2xl bg-orange-50 text-orange-600 mb-4 shadow-sm">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <p class="text-[10px] font-black uppercase tracking-[0.4em] text-orange-500">Security</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Forgot Password?</h1>
                <p class="mt-1 text-xs font-bold text-slate-400">Choose a channel to receive your reset token.</p>
            </div>

            <form id="forgot-password-form" method="POST" action="{{ route('web.password.email') }}"
                class="mt-6 space-y-5">
                @csrf
                <input type="hidden" name="channel" value="phone">

                <div class="space-y-2">
                    <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Reset
                        channel</label>
                    <div class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-orange-600">SMS token</p>
                        <p class="mt-1 text-xs font-bold text-orange-900/80">
                            Enter the phone number on your account to receive a reset code by text.
                        </p>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <x-input name="phone" type="tel" label="Phone Number" icon="phone" placeholder="0810..." />
                </div>
                <x-error />
                <x-button class="w-full mt-2" id="forgot-password-submit" type="submit">
                    <x-slot:icon>
                        <i data-lucide="message-square-more" class="h-4 w-4"></i>
                    </x-slot:icon>
                    Send Reset Token
                </x-button>
            </form>

            <div class="mt-8 text-center">
                <a href="{{ route('login') }}"
                    class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-orange-500 transition-colors">
                    ← Back to Login
                </a>
            </div>
        </div>
    </section>
@endsection
