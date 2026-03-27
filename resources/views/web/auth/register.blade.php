@extends('layouts.app', ['title' => 'Register | Asaba Hustle'])

@section('content')
    <section class="mx-auto max-w-2xl">
        <div
            class="rounded-[2.5rem] border border-slate-100 bg-white p-8 md:p-10 shadow-[0_32px_64px_-16px_rgba(255,122,0,0.1)]">

            <!-- Header: Tighter & Focused -->
            <div class="mb-10">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-orange-50 border border-orange-100 mb-4">
                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-orange-600">Join Asaba Hustle</span>
                </div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">Start your journey.</h1>
                <p class="mt-2 text-sm font-bold text-slate-400">Join the marketplace to hire or provide services.</p>
            </div>

            <div id="register-feedback"
                class="mb-8 hidden rounded-xl border px-5 py-3 text-[11px] font-black uppercase tracking-wider animate-in fade-in slide-in-from-top-2">
            </div>

            <form id="register-form" action="{{ route('web.register.submit') }}" class="grid gap-x-5 gap-y-4 md:grid-cols-2">
                @csrf
                <input type="hidden" name="verification_method" value="phone">

                <x-input label="Full Name" name="name" type="text" icon="user" placeholder="Olajide Eze Adamu" />
                <x-input label="Phone Number" name="phone" type="tel" icon="phone" placeholder="0801..." />
                <x-input label="Email address (optional)" name="email" type="email" icon="mail"
                    placeholder="oea@email.com" />
                <x-select :options="[
                    'client' => 'Hire a service',
                    'worker' => 'Provide a service',
                ]" name="role" icon="user-plus" placeholder="Select an option"
                    label="I want to ..." />
                <x-input name="password" type="password" label="Password" placeholder="••••••••" icon="lock" required />
                <x-input name="password_confirmation" type="password" label="Confirm Password" placeholder="••••••••"
                    icon="lock" required />

                <div class="md:col-span-2 space-y-1.5">
                    <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Account
                        verification</label>
                    <div class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-orange-600">SMS only</p>
                        <p class="mt-1 text-xs font-bold text-orange-900/80">
                            Your verification code will be sent to the phone number above.
                        </p>
                    </div>
                </div>

                <x-error class="md:col-span-2" />

                <x-button size="md" type="submit" id="register-submit" class="w-full mt-2 md:col-span-2">
                    <x-slot:icon>
                        <i data-lucide="log-in" class="h-4 w-4"></i>
                    </x-slot:icon>
                    Create account
                </x-button>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-50 text-center">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Already have an account?</p>
                <a href="{{ route('login') }}"
                    class="mt-1 inline-block text-xs font-black text-orange-600 hover:text-orange-700 transition-colors uppercase tracking-widest">
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
