@extends('layouts.app', ['title' => 'Register | Asaba Hustle'])

@section('content')
    <section class="mx-auto max-w-2xl">
        <div
            class="rounded-[2.5rem] border border-slate-100 bg-white p-8 md:p-10 shadow-[0_32px_64px_-16px_color-mix(in_srgb,var(--brand)_10%,transparent)]">

            <!-- Header: Branded & Focused -->
            <div class="relative mb-10 group">
                <!-- Back Home Action -->
                <a href="{{ route('web.home') }}"
                    class="absolute right-0 top-0 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-all hover:bg-[var(--surface-soft)] hover:text-[var(--brand)] active:scale-90"
                    title="Exit to Home">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </a>

                <!-- Logo + Badge Row -->
                <div class="flex items-center gap-3 mb-6">
                    <img src="{{ $siteTheme['icon'] ? asset('storage/' . $siteTheme['icon']) : asset('images/icons/asaba-hustle.svg') }}"
                        class="w-10 h-10 drop-shadow-sm transition-transform group-hover:rotate-12" alt="Asaba Hustle" />
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-[var(--surface-soft)] border border-[var(--brand)]/15">
                        <span class="text-[9px] font-black uppercase text-[var(--brand)]">Join the
                            Hustle</span>
                    </div>
                </div>

                <h1 class="text-3xl font-black  text-slate-900 leading-tight">Start your <br />journey.</h1>
                <p class="mt-3 text-[11px] font-bold text-slate-400 uppercase  leading-relaxed">
                    Join the marketplace to <span class="text-[var(--brand)]">hire</span> or <span
                        class="text-[var(--brand)]">provide</span> services.
                </p>
            </div>


            <form id="register-form" action="{{ route('web.register.submit') }}"
                class="grid gap-x-5 gap-y-4 md:grid-cols-2">
                @csrf
                <div class="md:col-span-2">
                    <x-input label="Full Name" name="name" type="text" icon="user" placeholder="Olajide Eze Adamu" />
                </div>
                <x-input label="Phone Number" name="phone" type="tel" icon="phone" placeholder="0801..." required />
                <x-input label="Email address" name="email" type="email" icon="mail"
                    placeholder="oea@email.com" />
                <x-select :options="[
                    'client' => 'Hire a service',
                    'worker' => 'Provide a service',
                ]" name="role" icon="user-plus" placeholder="Select an option"
                    label="I want to ..." />
                <x-input name="password" type="password" label="Password" placeholder="••••••••" icon="lock" required />
                <x-input name="password_confirmation" type="password" label="Confirm Password" placeholder="••••••••"
                    icon="lock" required />

                @if ($phoneAuthEnabled)
                <div class="md:col-span-2 space-y-1.5">
                    <fieldset>
                        <legend class="ml-1 mb-2 text-[10px] font-black uppercase  text-slate-400">
                            Choose your verification method</legend>
                        <div class="grid {{ $phoneAuthEnabled ? 'grid-cols-2' : 'grid-cols-1' }} gap-3">
                            @if ($phoneAuthEnabled)
                            <label class="cursor-pointer">
                                <input class="peer sr-only" type="radio" name="verification_method" value="phone">
                                <span class="auth-method-option flex min-h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-600 transition peer-checked:border-[var(--brand)] peer-checked:bg-[var(--surface-soft)] peer-checked:text-[var(--brand)]">
                                    <i data-lucide="phone" class="h-4 w-4"></i> Phone (SMS)
                                </span>
                            </label>
                            @endif
                            <label class="cursor-pointer">
                                <input class="peer sr-only" type="radio" name="verification_method" value="email" checked>
                                <span class="auth-method-option flex min-h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-600 transition peer-checked:border-[var(--brand)] peer-checked:bg-[var(--surface-soft)] peer-checked:text-[var(--brand)]">
                                    <i data-lucide="mail" class="h-4 w-4"></i> Email
                                </span>
                            </label>
                        </div>
                        <p id="verification-method-help" class="mt-2 px-1 text-xs text-slate-500">
                            We’ll send a verification link to your email address.
                        </p>
                    </fieldset>
                </div>
                @else
                    <input type="hidden" name="verification_method" value="email">
                @endif

                <x-error class="md:col-span-2" />

                <x-button size="md" type="submit" id="register-submit" class="w-full mt-2 md:col-span-2">
                    <x-slot:icon>
                        <i data-lucide="log-in" class="h-4 w-4"></i>
                    </x-slot:icon>
                    Create account
                </x-button>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-50 text-center">
                <p class="text-[11px] font-bold text-slate-400 uppercase ">Already have an account?</p>
                <a href="{{ route('login') }}"
                    class="mt-1 inline-block text-xs font-black text-[var(--brand)] hover:opacity-75 transition-colors uppercase">
                    Sign In
                </a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.asabaAuthConfig = {
            action: @json(route('web.register.submit')),
            redirectFallback: @json(route('login')),
            formId: '#register-form',
            feedbackId: '#register-feedback'
        };
    </script>
@endpush
