@extends('layouts.app', ['title' => 'App | Asaba Hustle'])

@section('content')
    <!-- PROFILE PAGE: MODERN GLASS BENTO -->
    <div class="max-w-4xl mx-auto pt-20 space-y-6">

        <header
            class="relative overflow-hidden rounded-[3rem] bg-white/70 backdrop-blur-xl border border-white p-8 shadow-sm">
            <!-- Background Glow -->
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-orange-500/10 blur-[100px] rounded-full"></div>

            <div class="relative flex flex-col md:flex-row items-center gap-8 text-center md:text-left">
                <!-- Avatar / Initial -->
                <div class="relative group cursor-pointer" onclick="openModal('uploadPhotoModal')">
                    <x-avatar :user="$user" size="h-20 w-20" rounded="rounded-2xl" text="text-3xl"
                        class="shadow-xl transition-all group-hover:scale-105" />
                    <div
                        class="absolute inset-0 bg-black/40 rounded-2xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                        <i data-lucide="camera" class="w-6 h-6 text-white"></i>
                    </div>
                    @if ($user->is_verified)
                        <div
                            class="absolute -bottom-1 -right-1 h-6 w-6 bg-green-500 border-2 border-white rounded-full flex items-center justify-center text-white">
                            <i data-lucide="check" class="w-3 h-3"></i>
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-2">
                        <h1 class="text-3xl font-black tracking-tighter text-slate-900">{{ $user->name }}</h1>
                        <span
                            class="px-3 py-1 rounded-full bg-slate-100 text-[10px] font-black uppercase  text-slate-500 border border-slate-200">
                            {{ strtoupper($user->getRoleNames()[0] ?? 'User') }}
                        </span>
                    </div>
                    <p class="text-slate-500 font-medium leading-relaxed max-w-md italic">
                        "{{ $user->bio }}"
                    </p>
                </div>

                <!-- Profile Action -->
                <button onclick="openModal('editProfileModal')"
                    class="px-6 py-3 cursor-pointer rounded-2xl bg-white border border-slate-200 text-slate-900 font-black text-xs uppercase  hover:bg-slate-50 transition-all active:scale-95 shadow-sm">
                    Edit Profile
                </button>
            </div>
        </header>

        <section class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-[2rem] border border-slate-100 bg-white px-5 py-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Profile</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $profileMetrics['profile_completion'] ?? 0 }}%</p>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-300">Completion</p>
            </div>

            <div class="rounded-[2rem] border border-slate-100 bg-white px-5 py-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Unread</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $profileMetrics['unread_messages'] ?? 0 }}</p>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-300">Messages</p>
            </div>

            <div class="rounded-[2rem] border border-slate-100 bg-white px-5 py-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Unread</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $profileMetrics['unread_notifications'] ?? 0 }}</p>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-300">Notifications</p>
            </div>

            <div class="rounded-[2rem] border border-slate-100 bg-white px-5 py-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Rating</p>
                <p class="mt-3 text-3xl font-black text-slate-900">
                    {{ number_format($profileMetrics['average_rating'] ?? 0, 1) }}</p>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-300">Average</p>
            </div>
        </section>

        <!-- Content Grid: Stats & Info -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

            <!-- Left: Primary Skill & Status (Bento Box) -->
            <div class="md:col-span-7 space-y-6">
                @if (auth()->user()->hasRole('worker'))
                    <section class="p-8 rounded-[2.5rem] bg-slate-900 text-white shadow-2xl shadow-slate-900/20">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-xs font-black uppercase tracking-[0.2em] text-orange-400">Primary Skill</h2>
                            <span
                                class="flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-[10px] font-black uppercase">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                {{ $user->availability_status }}
                            </span>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="h-16 w-16 rounded-3xl bg-white/10 flex items-center justify-center">
                                <i data-lucide="{{ $user->skill->icon ?? 'sparkles' }}" class="w-8 h-8 text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black italic">{{ $user->skill->name ?? 'Not added' }}</h3>
                                <p class="text-sm text-white/50 font-medium">{{ $user->skill->description ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="mt-8 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl bg-white/5 px-4 py-4">
                                <p class="text-[9px] font-black uppercase tracking-widest text-white/40">Assigned</p>
                                <p class="mt-2 text-2xl font-black">{{ $profileMetrics['assigned_jobs'] ?? 0 }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/5 px-4 py-4">
                                <p class="text-[9px] font-black uppercase tracking-widest text-white/40">Active</p>
                                <p class="mt-2 text-2xl font-black">{{ $profileMetrics['active_jobs'] ?? 0 }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/5 px-4 py-4">
                                <p class="text-[9px] font-black uppercase tracking-widest text-white/40">Skills</p>
                                <p class="mt-2 text-2xl font-black">{{ $profileMetrics['skills_count'] ?? 1 }}</p>
                            </div>
                        </div>
                    </section>
                @endif

                <section class="p-8 rounded-[2.5rem] bg-white border border-slate-100 shadow-sm space-y-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Workspace
                                Details</p>
                            <h2 class="mt-2 text-xl font-black text-slate-900">Skill, location and verification</h2>
                        </div>
                        <button onclick="openModal('editProfileModal')"
                            class="rounded-2xl border border-slate-200 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:border-[var(--brand)] hover:text-[var(--brand)]">
                            Manage
                        </button>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Primary Skill</p>
                            <p class="mt-2 text-sm font-bold text-slate-900">{{ $user->skill->name ?? 'Not set' }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Availability</p>
                            <p class="mt-2 text-sm font-bold text-slate-900">
                                {{ ucfirst($user->availability_status ?? 'Not set') }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Latitude</p>
                            <p class="mt-2 text-sm font-bold text-slate-900">
                                {{ $user->latitude !== null ? number_format((float) $user->latitude, 6) : 'Not set' }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Longitude</p>
                            <p class="mt-2 text-sm font-bold text-slate-900">
                                {{ $user->longitude !== null ? number_format((float) $user->longitude, 6) : 'Not set' }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-4 sm:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">ID Document</p>
                            <p class="mt-2 text-sm font-bold text-slate-900">
                                {{ $user->id_document ? 'Uploaded and ready for review' : 'No document uploaded yet' }}
                            </p>
                        </div>
                        @if (auth()->user()->hasRole('worker'))
                            <div class="rounded-2xl bg-slate-50 px-4 py-4 sm:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Additional Skills
                                </p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @forelse ($skills->whereIn('id', $selectedSkillIds ?? [])->reject(fn ($skill) => $skill->id === $user->primary_skill_id) as $skill)
                                        <span
                                            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-2 text-[10px] font-black uppercase tracking-widest text-slate-700">
                                            {{ $skill->name }}
                                        </span>
                                    @empty
                                        <p class="text-sm font-bold text-slate-500">No extra skills selected yet.</p>
                                    @endforelse
                                </div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-4 sm:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Account Details
                                </p>
                                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-300">Bank</p>
                                        <p class="mt-1 text-sm font-bold text-slate-900">
                                            {{ $user->bank_name ?: 'Not set' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-300">Account
                                            Name</p>
                                        <p class="mt-1 text-sm font-bold text-slate-900">
                                            {{ $user->account_name ?: 'Not set' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-300">Account
                                            Number</p>
                                        <p class="mt-1 text-sm font-bold text-slate-900">
                                            {{ $user->account_number ?: 'Not set' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>


                <section class="p-8 rounded-[2.5rem] bg-white border border-slate-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <i data-lucide="phone"
                            class="w-5 h-5 text-{{ $user->phone_verified_at ? 'green' : 'slate' }}-500"></i>
                        <div>
                            <p class="text-[10px] font-black uppercase text-slate-400">SMS Verification</p>
                            <p class="text-xs font-bold">{{ $user->phone_verified_at ? 'Verified' : 'Pending' }}</p>
                        </div>
                    </div>
                </section>

                <!-- PASSWORD RESET FORM (NEW) -->
                <section class="p-6 rounded-[2rem] bg-white border border-slate-100 shadow-sm">
                    <h2 class="text-xl font-black uppercase text-slate-400 mb-6">Change Password</h2>
                    <form id="password-change-form" action="{{ route('web.app.password.change') }}" method="POST"
                        class="space-y-4">
                        @csrf
                        <x-input type="password" label="Current Password" name="current_password"
                            placeholder="Current Password" icon="lock" />
                        <x-input type="password" label="New Password" name="password" placeholder="New Password"
                            icon="lock" />
                        <x-input type="password" label="Confirm Password" name="password_confirmation"
                            placeholder="Confirm" icon="lock" />

                        <x-error />
                        <x-button id="password-change-submit" class="w-full" type="submit">
                            <x-slot:icon>
                                <i data-lucide="lock" class="h-4 w-4"></i>
                            </x-slot:icon>
                            Change Password
                        </x-button>
                    </form>
                </section>
            </div>

            <!-- Right: Ratings & Meta -->
            <div class="md:col-span-5 space-y-6">
                <section class="p-8 rounded-[2.5rem] bg-white border border-slate-100 shadow-sm">
                    <p class="text-[10px] font-black uppercase text-slate-400 mb-4">Performance Snapshot</p>
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <div class="text-6xl font-black text-slate-900 mb-2">
                                {{ number_format($profileMetrics['average_rating'] ?? 0, 1) }}
                            </div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Average Rating</p>
                        </div>
                        <div class="rounded-2xl bg-[var(--surface-soft)] px-4 py-3 text-right">
                            <p class="text-[9px] font-black uppercase tracking-widest text-[var(--brand)]">Member Since</p>
                            <p class="mt-2 text-sm font-black text-slate-900">
                                {{ \Carbon\Carbon::parse($user->created_at)->format('M Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-center gap-1 text-orange-400">
                        @for ($star = 1; $star <= 5; $star++)
                            <i data-lucide="star"
                                class="w-4 h-4 {{ ($profileMetrics['average_rating'] ?? 0) >= $star ? 'fill-current' : 'text-slate-200' }}"></i>
                        @endfor
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                {{ auth()->user()->hasRole('client') ? 'Jobs Created' : 'Applications Sent' }}
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ auth()->user()->hasRole('client') ? $profileMetrics['jobs_created'] ?? 0 : $profileMetrics['applications_sent'] ?? 0 }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Completed</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ $profileMetrics['completed_jobs'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Active</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">{{ $profileMetrics['active_jobs'] ?? 0 }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                {{ auth()->user()->hasRole('client') ? 'Applications' : 'Unread Alerts' }}
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ auth()->user()->hasRole('client') ? $profileMetrics['applications_received'] ?? 0 : ($profileMetrics['unread_notifications'] ?? 0) + ($profileMetrics['unread_messages'] ?? 0) }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Contact Card -->
                <section class="p-8 rounded-[2.5rem] bg-orange-600 text-white shadow-xl shadow-orange-600/20">
                    <h2 class="text-[10px] font-black uppercase  mb-6 opacity-70">Contact Info</h2>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <i data-lucide="smartphone" class="w-4 h-4"></i>
                            <span class="text-sm font-bold">{{ $user->phone }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i data-lucide="at-sign" class="w-4 h-4"></i>
                            <span class="text-sm font-bold truncate">{{ $user->email }}</span>
                        </div>
                    </div>
                </section>

                <section class="p-8 rounded-[2.5rem] bg-white border border-slate-100 shadow-sm">
                    <p class="text-[10px] font-black uppercase text-slate-400 mb-5">Account Health</p>
                    <div class="space-y-4">
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Profile
                                    Completion</p>
                                <span
                                    class="text-sm font-black text-slate-900">{{ $profileMetrics['profile_completion'] ?? 0 }}%</span>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-slate-200">
                                <div class="h-2 rounded-full bg-[var(--brand)]"
                                    style="width: {{ min(100, $profileMetrics['profile_completion'] ?? 0) }}%"></div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">SMS
                                Verification</p>
                            <p class="mt-2 text-sm font-black text-slate-900">
                                {{ $user->phone_verified_at ? 'Verified' : 'Pending' }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="text-center pt-4">
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-tighter">
                Member since {{ \Carbon\Carbon::parse($user->created_at)->format('F Y') }}
            </p>
        </div>
    </div>
    <!-- Modals (Hidden by Default) -->
    <x-modal id="uploadPhotoModal" title="Update Photo">
        <div class="">
            <p class="text-xs opacity-50 mb-6">Choose a clear profile picture.</p>
            <form id="profile-image-form" method="POST" enctype="multipart/form-data"
                action="{{ route('web.app.photo.upload') }}" class="space-y-4">
                @csrf
                <input type="file" name="profile_photo"
                    class="text-xs border border-(--brand) p-2 rounded-2xl font-bold w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-[var(--surface-soft)] file:text-[var(--brand)]">
                <x-error />
                <div class="flex gap-3 pt-1">
                    <!-- Submit Button -->
                    <x-button id="profile-image-submit" class="w-full" type="submit">
                        <x-slot:icon>
                            <i data-lucide="save" class="h-4 w-4"></i>
                        </x-slot:icon>
                        Upload
                    </x-button>

                    <x-button onclick="closeModal('uploadPhotoModal')" color="black" class="w-full" type="button">
                        <x-slot:icon>
                            <i data-lucide="circle-x" class="h-4 w-4"></i>
                        </x-slot:icon>
                        Cancel
                    </x-button>
                </div>
            </form>
        </div>
    </x-modal>
    <x-modal id="editProfileModal" size="max-w-2xl" title="Edit Profile">
        <div class="relative ">
            <form id="profile-update-form" action="{{ route('web.app.profile.update') }}" method="POST"
                enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name Field -->
                <x-input name="name" type="text" icon="user" label="Full name" placeholder="e.g. John Doe"
                    value="{{ $user->name }}" />

                <!-- Email Field (Disabled/Read-only Style) -->
                <x-input name="email" type="email" icon="mail" label="Email address"
                    placeholder="e.g. email@email.com" readonly value="{{ $user->email }}" />

                <!-- Phone Field -->
                <x-input name="phone" type="tel" icon="phone" label="Phone number" placeholder="e.g. 0810..."
                    value="{{ $user->phone }}" />

                <!-- Bio Field -->
                <x-input type="textarea" resizable="false" name="bio" rows="4" icon="align-left"
                    label="Short Bio" placeholder="Tell us about yourself..." value="{{ $user->bio }}" />

                <div class="grid gap-4 md:grid-cols-2">
                    <x-select name="primary_skill_id" id="profile_primary_skill_id" label="Primary Skill"
                        icon="briefcase-business" placeholder="Choose your main skill" :options="$skills->pluck('name', 'id')->all()"
                        :selected="$user->primary_skill_id" />

                    <x-select name="availability_status" id="profile_availability_status" label="Availability Status"
                        icon="activity" placeholder="Set availability" :options="[
                            'available' => 'Available',
                            'busy' => 'Busy',
                            'offline' => 'Offline',
                        ]" :selected="$user->availability_status" />
                </div>

                @if (auth()->user()->hasRole('worker'))
                    <div class="space-y-4">
                        <!-- Header & Instructions -->
                        <div class="flex items-end justify-between">
                            <div>
                                <label
                                    class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--ink)] opacity-40">
                                    Additional Expertise
                                </label>
                                <p class="text-[10px] font-medium text-slate-400 italic">Select all other services you can
                                    provide</p>
                            </div>
                            <span
                                class="text-[9px] font-black px-2 py-1 rounded-md bg-[var(--surface-soft)] text-[var(--brand)] uppercase">
                                Multi-Select
                            </span>
                        </div>

                        <!-- Interactive Skill Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach ($skills as $skill)
                                @php
                                    $isSelected = in_array($skill->id, $selectedSkillIds ?? []);
                                @endphp

                                <label class="relative group cursor-pointer">
                                    {{-- Hidden Native Checkbox --}}
                                    <input type="checkbox" name="skill_ids[]" value="{{ $skill->id }}"
                                        class="peer hidden" {{ $isSelected ? 'checked' : '' }}>

                                    {{-- Visual Tile --}}
                                    <div
                                        class="flex items-center gap-3 p-3 rounded-2xl border-2 border-slate-50 bg-slate-50/50 transition-all duration-300
                        peer-checked:border-[var(--brand)] peer-checked:bg-white peer-checked:shadow-lg peer-checked:shadow-orange-500/10
                        group-hover:border-[var(--brand)]/20">

                                        <div class="h-8 w-8 rounded-xl flex items-center justify-center text-sm transition-all
                            peer-checked:bg-[var(--brand)] peer-checked:text-white bg-white text-slate-400 shadow-sm"
                                            style="background-color: {{ $isSelected ? 'var(--brand)' : '' }}; color: {{ $isSelected ? 'white' : '' }}">
                                            <i data-lucide="{{ $skill->icon ?? 'layers-3' }}" class="w-4 h-4"></i>
                                        </div>

                                        <span
                                            class="text-[11px] font-black uppercase tracking-tight text-slate-500 peer-checked:text-[var(--ink)]">
                                            {{ $skill->name }}
                                        </span>

                                        {{-- Active Indicator --}}
                                        <div
                                            class="absolute top-2 right-2 h-1.5 w-1.5 rounded-full bg-[var(--brand)] scale-0 peer-checked:scale-100 transition-transform">
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <x-input name="bank_name" type="text" icon="building-2" label="Bank Name"
                            placeholder="e.g. Access Bank" value="{{ $user->bank_name }}" />
                        <x-input name="account_name" type="text" icon="badge-info" label="Account Name"
                            placeholder="e.g. John Doe" value="{{ $user->account_name ?? $user->name }}" />
                        <x-input name="account_number" type="text" icon="credit-card" label="Account Number"
                            placeholder="e.g. 0123456789" value="{{ $user->account_number }}" />
                    </div>
                @endif


                <x-input type="file" name="id_document" id="profile_id_document" icon="file-badge"
                    label="ID Document" />

                <div class="rounded-2xl border border-dashed border-[var(--brand)]/25 bg-[var(--surface-soft)] px-4 py-4">
                    <div class="flex flex-col gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[var(--brand)]">Location</p>
                            <p class="mt-1 text-xs font-medium text-slate-500">
                                Saved coordinates are prefilled automatically. Use your browser location to refresh them.
                            </p>
                        </div>
                        <x-button type="button" id="profile-location-refresh" color="black"
                            class="js-location-refresh shrink-0" data-lat-target="#profile_latitude"
                            data-long-target="#profile_longitude" data-status-target="#profile-location-status">
                            <x-slot:icon>
                                <i data-lucide="locate-fixed" class="h-4 w-4"></i>
                            </x-slot:icon>
                            Use Current Location
                        </x-button>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <x-input type="number" step="0.000001" name="latitude" id="profile_latitude" icon="map-pinned"
                            label="Latitude" placeholder="Fetched from browser location"
                            value="{{ $user->latitude }}" />

                        <x-input type="number" step="0.000001" name="longitude" id="profile_longitude"
                            icon="navigation" label="Longitude" placeholder="Fetched from browser location"
                            value="{{ $user->longitude }}" />
                    </div>

                    <p id="profile-location-status"
                        class="mt-3 text-[10px] font-black uppercase tracking-widest text-slate-400">
                        {{ $user->latitude !== null && $user->longitude !== null ? 'Saved coordinates loaded' : 'Waiting for browser location' }}
                    </p>
                </div>

                <x-error />
                <!-- Submit Button -->
                <x-button id="profile-update-submit" class="w-full mt-2" type="submit">
                    <x-slot:icon>
                        <i data-lucide="save" class="h-4 w-4"></i>
                    </x-slot:icon>
                    Save Changes
                </x-button>
            </form>

            <!-- Subtle Background Glow -->
            <div
                class="absolute -bottom-20 -left-20 w-64 h-64 bg-orange-500/5 blur-[100px] rounded-full pointer-events-none">
            </div>
        </div>
    </x-modal>
@endsection
