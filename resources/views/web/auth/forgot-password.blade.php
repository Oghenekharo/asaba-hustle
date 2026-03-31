@extends('layouts.app', ['title' => 'Forgot Password | Asaba Hustle'])

@section('content')
    <section class="mx-auto max-w-md">
        <div
            class="rounded-[2.5rem] border border-slate-100 bg-white p-8 md:p-10 shadow-[0_32px_64px_-16px_rgba(255,122,0,0.1)]">

            <!-- Compact Header -->
            <div class="relative mb-8 text-center">
                <!-- Back Home Button -->
                <a href="{{ route('web.home') }}"
                    class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400 transition-all hover:bg-orange-50 hover:text-orange-500 active:scale-95"
                    title="Go Home">
                    <i data-lucide="chevron-left" class="h-5 w-5"></i>
                </a>

                <div class="inline-flex items-center justify-center mb-4">
                    <img src="/images/icons/asaba-hustle.svg" class="w-12 h-12 drop-shadow-sm" alt="Asaba Hustle" />
                </div>

                <h1 class="text-2xl font-black tracking-tight text-slate-900 leading-tight">Forgot Password?</h1>
                <p class="mt-1 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Enter your phone number
                    below to reset your password.</p>
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
