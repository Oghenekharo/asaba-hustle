@extends('layouts.app', ['title' => 'Verify Phone | Asaba Hustle'])

@section('content')
    <section class="mx-auto max-w-md">
        <div
            class="rounded-[3rem] border border-[var(--brand)]/5 bg-[var(--surface)] p-8 md:p-10 shadow-[0_40px_100px_-20px_rgba(255,122,0,0.12)]">

            <!-- Compact Header -->
            <div class="relative mb-8 text-center">
                <!-- Logout Button -->
                <div class="absolute -top-4 -right-4">
                    <x-logout />
                </div>

                <div class="inline-flex items-center justify-center mb-4">
                    <img src="/images/icons/asaba-hustle.svg" class="w-12 h-12 drop-shadow-sm" alt="Asaba Hustle" />
                </div>

                <h1 class="text-2xl font-black tracking-tight text-slate-900 leading-tight">Verify Phone</h1>
                <p class="mt-1 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Fill the fields below to
                    verify your phone number.</p>
            </div>

            <form id="verify-phone-form" method="POST" action="{{ route('web.verify.phone.submit') }}" class="space-y-6">
                @csrf
                <x-input name="phone" readonly type="tel" value="{{ $phone }}" label="Verifying Number"
                    icon="phone" placeholder="0810..." />

                <!-- Token Entry -->
                <div class="space-y-1.5">
                    <x-input name="token" type="number" label="Verification Token" numeric icon="key"
                        placeholder="000000" autofocus minlength="1" maxlength="6" />
                </div>


                <x-error />
                <!-- Submit Button -->
                <x-button class="w-full mt-2" id="verify-phone-submit" type="submit">
                    <x-slot:icon>
                        <i data-lucide="key" class="h-4 w-4"></i>
                    </x-slot:icon>
                    Confirm Identity
                </x-button>
            </form>
            <div class="mt-8 text-center">
                <p class="text-sm font-bold opacity-40">Didn't receive a code?</p>
                <!-- Submit Button -->
                <x-button id="resend-btn" data-url="{{ route('web.app.verification.send') }}" class="w-full mt-2"
                    color="black">
                    <x-slot:icon>
                        <i data-lucide="message-square-more" class="h-4 w-4"></i>
                    </x-slot:icon>
                    Resend SMS Token
                </x-button>
            </div>
        </div>
    </section>
@endsection
