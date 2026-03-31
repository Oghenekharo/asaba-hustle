@extends('layouts.app', ['title' => 'Reset Password | Asaba Hustle'])

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

                <h1 class="text-2xl font-black tracking-tight text-slate-900 leading-tight">Password Reset</h1>
                <p class="mt-1 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Fill the fields below to
                    reset your password.</p>
            </div>


            <form id="reset-password-form" method="POST" action="{{ route('web.password.update') }}"
                class="mt-8 space-y-5">
                @csrf
                <input type="hidden" name="channel" value="phone">

                <div class="space-y-2">
                    <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Recovery
                        method</label>
                    <div class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-orange-600">SMS token</p>
                        <p class="mt-1 text-xs font-bold text-orange-900/80">
                            Reset this password with the token sent to your phone number.
                        </p>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <x-input name="phone" type="tel" value="{{ $phone }}" label="Phone Number" icon="phone"
                        placeholder="0810..." />
                </div>
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
