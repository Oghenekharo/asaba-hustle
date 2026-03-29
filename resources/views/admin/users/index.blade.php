@extends('admin.layout')

@section('title', 'Users')
@section('admin-page-title', 'Users')

@section('content')
    <section
        class="relative overflow-hidden rounded-[3rem] border border-white bg-white/70 p-8 shadow-sm backdrop-blur-xl sm:p-10">
        <!-- Visual Accents -->
        <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[var(--brand)]/5 blur-3xl opacity-50"></div>
        <div class="absolute left-0 top-0 h-full w-1.5 rounded-r-full bg-[var(--brand)]"></div>

        <div class="relative flex flex-col gap-8 xl:flex-row xl:items-center xl:justify-between">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--brand)]/10 mb-4">
                    <i data-lucide="users" class="w-3 h-3 text-[var(--brand)]"></i>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[var(--brand)]">Identity Management</p>
                </div>
                <h2 class="text-3xl font-black tracking-tighter text-[var(--ink)] sm:text-4xl">
                    All <span class="italic text-[var(--brand)]">Users</span>
                </h2>
                <p class="mt-4 text-sm font-medium leading-relaxed text-slate-500 max-w-lg">
                    Verify identities, manage worker availability, and audit account standings. Use filters to isolate
                    high-priority verification requests.
                </p>
            </div>

            <!-- Dynamic Results Bento -->
            <div class="rounded-[2.2rem] border border-slate-100 bg-[var(--surface-soft)]/50 p-6 min-w-[240px]">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Total Matched</p>
                    <i data-lucide="filter" class="w-3.5 h-3.5 text-[var(--brand)]"></i>
                </div>
                <p class="text-4xl font-black tracking-tighter text-[var(--ink)]">{{ number_format($users->total()) }}</p>
                <p class="mt-2 text-[10px] font-bold text-slate-400 italic">Unique accounts in current view</p>
            </div>
        </div>

        <!-- High-Velocity Filter Bar -->
        <form method="GET" action="{{ route('admin.users.index') }}"
            class="mt-10 grid gap-4 lg:grid-cols-[2.5fr_1.2fr_1.2fr_auto] items-end bg-white/50 p-4 rounded-[2rem] border border-white/50 shadow-inner">


            <x-input type="text" icon="search" name="q" value="{{ request('q') }}" label="Search Keywords"
                placeholder="Name, phone, or email..." />


            <x-select name="status" icon="activity" :options="[
                'active' => 'Active',
                'suspended' => 'Suspended',
                'banned' => 'Banned',
            ]" :selected="request('status')" label="Account status" />

            <x-select name="role" icon="users" :options="[
                'client' => 'Clients',
                'worker' => 'Workers',
                'admin' => 'Administrators',
            ]" :selected="request('role')" label="Account roles" />


            <div class="flex gap-2">
                <button
                    class="h-13.5 rounded-2xl bg-[var(--ink)] px-8 text-[10px] font-black uppercase tracking-[0.2em] text-white shadow-xl shadow-slate-900/20 hover:bg-[var(--brand)] transition-all active:scale-95">
                    Apply
                </button>
                <a href="{{ route('admin.users.index') }}"
                    class="flex h-13.5 w-13.5 items-center justify-center rounded-2xl border-2 border-slate-100 bg-white text-slate-400 transition hover:bg-slate-50 hover:text-[var(--ink)]"
                    title="Reset Filters">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </section>


    <section class="mt-6 rounded-[2.2rem] border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-xl">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-slate-400">Bulk Actions</p>
                <h3 class="mt-1 text-lg font-black text-slate-950">Apply a status update to selected users</h3>
            </div>

            <form id="bulk-user-form" method="POST" action="{{ route('admin.users.bulk') }}"
                class="flex flex-col gap-3 sm:flex-row sm:items-center">
                @csrf

                <x-select name="action" icon="activity" :options="[
                    'active' => 'Activate',
                    'suspended' => 'Suspended',
                    'banned' => 'Banned',
                ]" :selected="request('action')" placeholder="" />

                <button
                    class="h-11 rounded-2xl bg-[var(--brand)] px-5 text-xs font-black uppercase tracking-[0.24em] text-white">
                    Apply to Selected
                </button>
            </form>
        </div>

        <div class="mt-8 hidden overflow-hidden lg:block">
            <table class="min-w-full border-collapse text-sm">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-4 py-5 text-left">
                            <input type="checkbox" id="select-all-users"
                                class="h-5 w-5 rounded-lg border-slate-200 text-[var(--brand)] focus:ring-[var(--brand)] transition-all">
                        </th>
                        <th class="px-2 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            User</th>
                        <th class="px-2 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            Role</th>
                        <th class="px-2 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            Contact</th>
                        <th class="px-2 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            Account Status</th>
                        <th class="px-2 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">KYC
                            Status</th>
                        <th
                            class="px-2 py-5 text-right text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 pr-8">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($users as $user)
                        <tr class="group transition-colors hover:bg-[var(--surface-soft)]/30">
                            <td class="px-4 py-5">
                                <input type="checkbox" name="users[]" value="{{ $user->id }}" form="bulk-user-form"
                                    class="h-5 w-5 rounded-lg border-slate-200 text-[var(--brand)] focus:ring-[var(--brand)]">
                            </td>
                            <td class="px-2 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <x-avatar :user="$user" size="h-12 w-12" text="text-xs" rounded="rounded-2xl"
                                            class="shadow-sm border-2 border-white" />
                                        @if ($user->account_status === 'active')
                                            <span class="absolute -bottom-1 -right-1 flex h-3 w-3">
                                                <span
                                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span
                                                    class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500 border-2 border-white"></span>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.users.show', $user) }}"
                                            class="block text-sm font-black tracking-tight text-[var(--ink)] transition hover:text-[var(--brand)] truncate">
                                            {{ $user->name }}
                                        </a>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter truncate">
                                            {{ $user->email ?: 'ID: #' . $user->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-5">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="h-2 w-2 rounded-full {{ $user->hasRole('worker') ? 'bg-orange-400' : 'bg-blue-400' }}">
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-600">
                                        {{ $user->roles->pluck('name')->first() ?? 'Client' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-2 py-5">
                                <p class="text-xs font-black text-slate-700 font-mono tracking-tighter">
                                    {{ $user->phone }}</p>
                            </td>
                            <td class="px-2 py-5">
                                @php
                                    $statusMap = [
                                        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'suspended' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        'banned' => 'bg-rose-50 text-rose-700 border-rose-100',
                                    ];
                                    $statusStyle = $statusMap[$user->account_status] ?? 'bg-slate-100 text-slate-600';
                                @endphp
                                <span
                                    class="inline-flex rounded-xl border px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] {{ $statusStyle }}">
                                    {{ $user->account_status }}
                                </span>
                            </td>
                            <td class="px-2 py-5">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="{{ $user->is_verified ? 'shield-check' : 'shield-alert' }}"
                                        class="h-4 w-4 {{ $user->is_verified ? 'text-blue-500' : 'text-slate-300' }}"></i>
                                    <span
                                        class="text-[10px] font-bold text-slate-500">{{ $user->is_verified ? 'Verified' : 'Pending' }}</span>
                                </div>
                            </td>
                            <td class="px-2 py-5 pr-5">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @if ($user->account_status !== 'active')
                                        <form method="POST" action="{{ route('admin.users.status', $user) }}"
                                            title="Activate Account">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="active">
                                            <button
                                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm shadow-emerald-500/10">
                                                <i data-lucide="check-circle" class="h-4 w-4"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if ($user->account_status !== 'suspended')
                                        <form method="POST" action="{{ route('admin.users.status', $user) }}"
                                            title="Suspend User">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="suspended">
                                            <button
                                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white transition-all shadow-sm shadow-amber-500/10">
                                                <i data-lucide="pause-circle" class="h-4 w-4"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.users.status', $user) }}"
                                        title="Permablock">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="banned">
                                        <button
                                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all shadow-sm shadow-rose-500/10">
                                            <i data-lucide="slash" class="h-4 w-4"></i>
                                        </button>
                                    </form>

                                    <a href="{{ route('admin.users.show', $user->id) }}"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm shadow-indigo-500/10">
                                        <i data-lucide="eye" class="h-4 w-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>


        <div class="mt-6 grid gap-6 lg:hidden">
            @foreach ($users as $user)
                <article
                    class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white p-6 shadow-sm transition-all active:scale-[0.98]">
                    <!-- Subtle Brand Accent for Workers -->
                    @if ($user->hasRole('worker'))
                        <div class="absolute right-0 top-0 h-16 w-16 bg-[var(--brand)]/5 blur-2xl rounded-full"></div>
                    @endif


                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <x-avatar :user="$user" size="h-14 w-14" text="text-xs" rounded="rounded-2xl"
                                    class="shadow-md border-2 border-white" />
                                <div class="absolute -bottom-1 -right-1">
                                    <input type="checkbox" name="users[]" value="{{ $user->id }}"
                                        form="bulk-user-form"
                                        class="h-5 w-5 rounded-lg border-slate-200 text-[var(--brand)] shadow-sm focus:ring-[var(--brand)]">
                                </div>
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('admin.users.show', $user) }}"
                                    class="block text-base font-black tracking-tight text-[var(--ink)] truncate">
                                    {{ $user->name }}
                                </a>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                    {{ $user->phone }}</p>
                            </div>
                        </div>

                        <!-- Status Pill -->
                        <span
                            class="rounded-xl border px-2.5 py-1 text-[9px] font-black uppercase tracking-widest
                    {{ $user->account_status === 'active'
                        ? 'bg-emerald-50 text-emerald-600 border-emerald-100'
                        : ($user->account_status === 'suspended'
                            ? 'bg-amber-50 text-amber-600 border-amber-100'
                            : 'bg-rose-50 text-rose-600 border-rose-100') }}">
                            {{ $user->account_status }}
                        </span>
                    </div>

                    <!-- Middle Row: Metadata Grid -->
                    <div class="mt-6 grid grid-cols-2 gap-4 rounded-3xl bg-[var(--surface-soft)]/50 p-4">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-xl bg-white flex items-center justify-center text-slate-400">
                                <i data-lucide="{{ $user->hasRole('worker') ? 'hard-hat' : 'user' }}"
                                    class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase tracking-[0.2em] opacity-30">Role</p>
                                <p class="text-[11px] font-black text-[var(--ink)] capitalize">
                                    {{ $user->roles->pluck('name')->first() ?? 'Client' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 border-l border-white pl-4">
                            <div
                                class="h-8 w-8 rounded-xl bg-white flex items-center justify-center {{ $user->is_verified ? 'text-blue-500' : 'text-slate-300' }}">
                                <i data-lucide="{{ $user->is_verified ? 'shield-check' : 'shield-alert' }}"
                                    class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase tracking-[0.2em] opacity-30">KYC Status</p>
                                <p class="text-[11px] font-black text-[var(--ink)] uppercase tracking-tighter">
                                    {{ $user->is_verified ? 'Verified' : 'Pending' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Row: Quick Action Bar -->
                    <div class="mt-6 grid grid-cols-2 gap-2">
                        @if ($user->account_status !== 'active')
                            <form method="POST" action="{{ route('admin.users.status', $user) }}" class="flex-1">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="active">
                                <button
                                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-emerald-500/20 active:scale-95 transition-all">
                                    <i data-lucide="check" class="w-3 h-3"></i> Activate
                                </button>
                            </form>
                        @endif

                        @if ($user->account_status !== 'suspended')
                            <form method="POST" action="{{ route('admin.users.status', $user) }}" class="flex-1">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="suspended">
                                <button
                                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-amber-500 py-3 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-amber-500/20 active:scale-95 transition-all">
                                    <i data-lucide="pause" class="w-3 h-3"></i> Suspend
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.users.status', $user) }}" class="flex-1">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="banned">
                            <button
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[var(--ink)] py-3 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-slate-900/20 active:scale-95 transition-all">
                                <i data-lucide="slash" class="w-3 h-3 text-rose-500"></i> Ban
                            </button>
                        </form>
                        <a href="{{ route('admin.users.show', $user) }}"
                            class="w-full col-span-2 flex items-center justify-center gap-2 rounded-xl bg-indigo-600 py-3 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-amber-500/20 active:scale-95 transition-all">
                            <i data-lucide="eye" class="w-3 h-3 text-white"></i> View
                        </a>
                    </div>
                </article>
            @endforeach
        </div>


        <div class="mt-6">
            {{ $users->withQueryString()->links('partials.pagination') }}
        </div>
    </section>
@endsection
