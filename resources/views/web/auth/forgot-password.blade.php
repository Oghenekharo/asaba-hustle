@extends('layouts.app', ['title' => 'Forgot Password | Asaba Hustle'])

@section('content')
    <section class="mx-auto max-w-md">
        <div
            class="rounded-[2.5rem] border border-slate-100 bg-white p-8 md:p-10 shadow-[0_32px_64px_-16px_rgba(255,122,0,0.1)]">

            <!-- Header -->
            <div class="mb-8 text-center">
                <div
                    class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-50 text-orange-600 mb-4 shadow-sm">
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

                <!-- Channel Switcher (Sleeker than a standard select) -->
                <div class="space-y-2">
                    <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Reset via</label>
                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-50 rounded-xl border border-slate-100">
                        <label class="cursor-pointer">
                            <input type="radio" name="channel" value="phone" class="peer hidden"
                                {{ old('channel', 'phone') === 'phone' ? 'checked' : '' }}
                                onchange="toggleChannel(this.value)">
                            <div
                                class="text-center py-2 text-[10px] font-black uppercase tracking-widest rounded-lg peer-checked:bg-white peer-checked:text-orange-600 peer-checked:shadow-sm transition-all text-slate-400">
                                Phone</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="channel" value="email" class="peer hidden"
                                {{ old('channel', 'email') === 'email' ? 'checked' : '' }}
                                onchange="toggleChannel(this.value)">
                            <div
                                class="text-center py-2 text-[10px] font-black uppercase tracking-widest rounded-lg peer-checked:bg-white peer-checked:text-orange-600 peer-checked:shadow-sm transition-all text-slate-400">
                                Email</div>
                        </label>
                    </div>
                </div>

                <!-- Dynamic Input Fields -->
                <div id="phone-field" class="space-y-1.5 transition-all">
                    <x-input name="phone" type="tel" label="Phone Number" icon="phone" placeholder="0810..." />
                </div>
                <div id="email-field" class="hidden space-y-1.5 transition-all">
                    <x-input name="email" type="email" label="Email Address" icon="mail"
                        placeholder="aoe@email.com" />
                </div>
                <!-- Alert Container (Reused from your showAlert component) -->
                <x-error />
                <!-- Submit Button -->
                <x-button class="w-full mt-2" id="forgot-password-submit" type="submit">
                    <x-slot:icon>
                        <i data-lucide="mail" class="h-4 w-4"></i>
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
