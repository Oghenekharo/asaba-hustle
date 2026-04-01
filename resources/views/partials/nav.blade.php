@php
    $navUnreadNotifications = auth()->check() ? auth()->user()->notifications()->where('is_read', false)->count() : 0;
    $navUnreadMessages = auth()->check()
        ? \App\Models\ChatMessage::query()
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false)
            ->whereHas('conversation', function ($query) {
                $query->where(function ($conversationQuery) {
                    $conversationQuery->where('client_id', auth()->id())->orWhere('worker_id', auth()->id());
                });
            })
            ->count()
        : 0;
    $myJobsNavLabel = auth()->check() && auth()->user()->hasRole('worker') ? 'My Hustles' : 'My Jobs';
    $showMobileBackButton = auth()->check() && request()->routeIs('web.app*') && !request()->routeIs('web.app');
    $mobileBackUrl = url()->previous() !== url()->current() ? url()->previous() : route('web.app');
    $mobileUserRoleLabel = auth()->check()
        ? (auth()->user()->hasRole('worker')
            ? 'Worker'
            : (auth()->user()->hasRole('client')
                ? 'Client'
                : 'Account'))
        : null;
@endphp
<nav class="fixed top-6 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2rem)] max-w-4xl">
    <!-- Floating Glass Container -->
    <div
        class="relative flex items-center justify-between p-2 pl-6 bg-white/80 backdrop-blur-2xl border border-white/20 shadow-[0_8px_32px_0_rgba(0,0,0,0.1)] rounded-3xl">

        <!-- Brand -->
        <a href="{{ auth()->check() ? '/app' : '/' }}"
            class="flex items-center gap-3 {{ $showMobileBackButton ? 'ml-7 md:ml-0' : '' }}">
            <img src="/images/icons/asaba-hustle.svg" class="w-9 h-9" />
            <div class="text-lg font-black tracking-tight text-slate-900 leading-none">
                Asaba<span class="block text-[10px] uppercase tracking-[0.2em] text-orange-500">Hustle</span>
            </div>
        </a>

        @if ($showMobileBackButton)
            <a href="{{ $mobileBackUrl }}"
                class="absolute left-4 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center text-slate-500 transition hover:text-orange-600 md:hidden"
                aria-label="Go back">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
            </a>
            <div class="w-7 md:hidden" aria-hidden="true"></div>
        @endif

        <!-- Desktop Navigation (Logged In) -->
        <div class="hidden md:flex items-center bg-slate-100/50 p-1 rounded-2xl border border-white/50">
            @guest
                <a href="#services"
                    class="px-5 py-2 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl {{ request()->is('app/jobs*') ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                    Our <span class="hidden lg:inline">Services</span>
                </a>
                <a href="#About"
                    class="px-5 py-2 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl {{ request()->is('app/jobs*') ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                    About <span class="hidden lg:inline">us</span>
                </a>
            @else
                <a href="{{ route('web.app.jobs') }}"
                    class="px-5 py-2 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl {{ request()->is('app/jobs*') ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                    Explore <span class="hidden lg:inline">Jobs</span>
                </a>

                <a href="{{ route('web.app.my-jobs') }}"
                    class="px-5 py-2 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl {{ request()->is('app/my-jobs*') ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                    {{ $myJobsNavLabel }}
                </a>

                <a href="{{ route('web.app.conversations') }}"
                    class="relative px-5 py-2 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl {{ request()->is('app/conversations*') ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                    Messages
                    @if ($navUnreadMessages > 0)
                        <span
                            class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-[var(--brand)] text-white text-[9px] font-black flex items-center justify-center">
                            {{ $navUnreadMessages > 9 ? '9+' : $navUnreadMessages }}
                        </span>
                    @endif
                </a>
                @endif
            </div>


            <!-- Action Area -->
            <div class="flex items-center gap-2">
                @if (!Auth::check())
                    <a href="/register"
                        class="hidden sm:block px-4 text-xs font-bold text-slate-600 hover:text-slate-900">Join</a>
                    <a href="/login"
                        class="flex items-center gap-2 px-5 py-3 rounded-2xl bg-slate-900 text-white transition-transform active:scale-95 shadow-xl shadow-slate-900/10 hover:shadow-orange-500/20">
                        <span class="text-xs font-bold uppercase tracking-wider">Login</span>
                        <svg xmlns="http://w3.org" class="h-4 w-4 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                @else
                    <div id="notificationDrawerOverlay"
                        class="fixed inset-0 z-40 hidden bg-slate-900/35 backdrop-blur-[2px] md:hidden"></div>
                    <div class="relative" id="notificationDropdownWrapper">
                        <button id="notificationDropdownTrigger"
                            class="relative flex items-center bg-slate-100 hover:bg-slate-200 transition-all shadow-sm rounded-xl p-2 justify-center cursor-pointer">
                            <span data-lucide="bell" class="h-5 w-5"></span>
                            <span id="navNotificationBadge"
                                class="{{ $navUnreadNotifications > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-[var(--brand)] text-white text-[9px] font-black flex items-center justify-center">
                                {{ $navUnreadNotifications > 9 ? '9+' : $navUnreadNotifications }}
                            </span>
                        </button>

                        <div id="notificationDropdownMenu"
                            class="hidden fixed inset-y-0 right-0 z-50 flex h-screen w-[22rem] max-w-[85vw] translate-x-[105%] flex-col bg-white backdrop-blur-2xl border-l border-slate-100 shadow-[0_20px_50px_rgba(0,0,0,0.1)] overflow-hidden transition-transform duration-300 md:absolute md:inset-auto md:right-0 md:mt-3 md:h-auto md:w-[22rem] md:max-w-[85vw] md:translate-x-0 md:rounded-3xl md:border md:border-slate-100">
                            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                        Notifications</p>
                                    <p class="text-xs font-bold text-slate-900">Latest activity in your account</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" id="markAllNotificationsReadButton"
                                        class="text-[10px] font-black uppercase tracking-widest text-[var(--brand)] hover:opacity-70 transition">
                                        Mark all
                                    </button>
                                    <button type="button" id="notificationDrawerClose"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 md:hidden">
                                        <i data-lucide="x" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </div>

                            <div id="navNotificationsList" class="max-h-96 overflow-y-auto p-3 space-y-2 custom-scrollbar">
                                <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-300">No
                                        notifications yet</p>
                                </div>
                            </div>

                            <div class="border-t border-slate-50 p-3 space-y-2">
                                <a href="{{ route('web.app.notifications') }}"
                                    class="flex items-center justify-center gap-2 rounded-2xl bg-slate-100 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-orange-50 hover:text-orange-600">
                                    <i data-lucide="external-link" class="h-4 w-4"></i>
                                    View All Notifications
                                </a>
                                <button id="enableNotifications"
                                    class="px-6 w-full py-3 bg-orange-500 text-white rounded-xl font-bold transition-all active:scale-95">
                                    Enable Notifications
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- User Dropdown Container -->
                    <div id="userDrawerOverlay"
                        class="fixed -inset-8.25 z-40 h-screen hidden bg-slate-900/45 backdrop-blur-[2px] md:hidden"></div>
                    <div class="relative" id="userDropdownWrapper">
                        <button id="userDropdownTrigger"
                            class="flex cursor-pointer items-center gap-2 p-1 pr-4 rounded-2xl bg-slate-100 hover:bg-slate-200 transition-all active:scale-95 border border-white/50">
                            <!-- User Avatar/Initial -->
                            <x-avatar :user="auth()->user()" size="h-8 w-8" rounded="rounded-xl" text="text-xs"
                                class="shadow-sm" />
                            <i data-lucide="chevron-down"
                                class="h-3.5 w-3.5 text-slate-500 transition-transform duration-200" id="dropdownArrow"></i>
                        </button>

                        <!-- Dropdown Menu (Hidden by Default) -->
                        {{-- <div id="userDropdownMenu"
                            class="hidden fixed inset-y-0 left-0 z-50 h-screen w-[21rem] max-w-[88vw] -translate-x-[105%] opacity-0 overflow-hidden border-r border-slate-900/10 bg-[linear-gradient(180deg,#fffaf5_0%,#fff_30%,#f8fafc_100%)] shadow-[0_30px_80px_rgba(15,23,42,0.22)] transition-[transform,opacity] duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] md:absolute md:inset-auto md:left-auto md:right-0 md:mt-3 md:h-auto md:w-48 md:max-w-none md:translate-x-0 md:opacity-100 md:rounded-2xl md:border md:border-slate-100 md:bg-white md:shadow-[0_20px_50px_rgba(0,0,0,0.1)]">

                            <div class="flex h-full flex-col md:block">
                                <div
                                    class="relative overflow-hidden bg-[radial-gradient(circle_at_top_right,rgba(255,186,73,0.35),transparent_35%),linear-gradient(155deg,#0f172a_0%,#111827_45%,#ea580c_140%)] px-5 pb-6 pt-5 text-white md:hidden">
                                    <div class="absolute -right-8 -top-10 h-28 w-28 rounded-full bg-orange-300/30 blur-2xl">
                                    </div>
                                    <div class="absolute -left-6 bottom-0 h-20 w-20 rounded-full bg-white/10 blur-2xl">
                                    </div>
                                    <div
                                        class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent">
                                    </div>

                                    <div class="relative flex items-center justify-between">
                                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">
                                            Account
                                        </p>
                                        <button type="button" id="userDrawerClose"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/80">
                                            <i data-lucide="x" class="h-4 w-4"></i>
                                        </button>
                                    </div>

                                    <div class="relative mt-6 flex items-center gap-4">
                                        <div class="relative">
                                            <div class="absolute inset-0 rounded-[1.4rem] bg-orange-400/30 blur-md"></div>
                                            <x-avatar :user="auth()->user()" size="h-14 w-14" rounded="rounded-2xl"
                                                text="text-lg" class="relative ring-2 ring-white/10 shadow-lg" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-base font-black">{{ auth()->user()->name }}</p>
                                            <p
                                                class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-white/45">
                                                {{ $mobileUserRoleLabel }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="relative mt-5 flex items-center gap-3">
                                        <a href="{{ route('web.app.me') }}"
                                            class="inline-flex items-center gap-2 rounded-2xl bg-white/12 px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-white/90 backdrop-blur">
                                            <i data-lucide="user-round" class="h-4 w-4"></i>
                                            View profile
                                        </a>
                                        <span
                                            class="inline-flex items-center rounded-full border border-white/10 bg-white/8 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.22em] text-white/55">
                                            Asaba Hustle
                                        </span>
                                    </div>
                                </div>

                                <div class="flex-1 overflow-y-auto px-3 pb-4 pt-3 md:hidden">
                                    <div
                                        class="rounded-[1.75rem] border border-orange-100 bg-white/90 p-2 shadow-[0_16px_35px_rgba(255,122,0,0.08)]">
                                        <a href="{{ route('web.app') }}"
                                            class="group flex items-center gap-3 rounded-[1.25rem] px-4 py-3 text-xs font-black uppercase tracking-[0.18em] transition-all {{ request()->routeIs('web.app') ? 'bg-[linear-gradient(135deg,rgba(255,122,0,0.16),rgba(255,255,255,0.95))] text-[var(--brand)] shadow-[0_12px_24px_rgba(255,122,0,0.12)]' : 'text-slate-600 hover:bg-orange-50/70' }}">
                                            <span
                                                class="flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('web.app') ? 'bg-white text-[var(--brand)]' : 'bg-slate-100 text-slate-500 group-hover:bg-white group-hover:text-[var(--brand)]' }}">
                                                <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                                            </span>
                                            <span class="flex-1">Dashboard</span>
                                        </a>

                                        <a href="{{ route('web.app.jobs') }}"
                                            class="group mt-2 flex items-center gap-3 rounded-[1.25rem] px-4 py-3 text-xs font-black uppercase tracking-[0.18em] transition-all {{ request()->routeIs('web.app.jobs*') ? 'bg-[linear-gradient(135deg,rgba(255,122,0,0.16),rgba(255,255,255,0.95))] text-[var(--brand)] shadow-[0_12px_24px_rgba(255,122,0,0.12)]' : 'text-slate-600 hover:bg-orange-50/70' }}">
                                            <span
                                                class="flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('web.app.jobs*') ? 'bg-white text-[var(--brand)]' : 'bg-slate-100 text-slate-500 group-hover:bg-white group-hover:text-[var(--brand)]' }}">
                                                <i data-lucide="briefcase-business" class="h-4 w-4"></i>
                                            </span>
                                            <span class="flex-1">Explore Jobs</span>
                                        </a>

                                        <a href="{{ route('web.app.my-jobs') }}"
                                            class="group mt-2 flex items-center gap-3 rounded-[1.25rem] px-4 py-3 text-xs font-black uppercase tracking-[0.18em] transition-all {{ request()->routeIs('web.app.my-jobs*') ? 'bg-[linear-gradient(135deg,rgba(255,122,0,0.16),rgba(255,255,255,0.95))] text-[var(--brand)] shadow-[0_12px_24px_rgba(255,122,0,0.12)]' : 'text-slate-600 hover:bg-orange-50/70' }}">
                                            <span
                                                class="flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('web.app.my-jobs*') ? 'bg-white text-[var(--brand)]' : 'bg-slate-100 text-slate-500 group-hover:bg-white group-hover:text-[var(--brand)]' }}">
                                                <i data-lucide="folders" class="h-4 w-4"></i>
                                            </span>
                                            <span class="flex-1">{{ $myJobsNavLabel }}</span>
                                        </a>

                                        <a href="{{ route('web.app.conversations') }}"
                                            class="group mt-2 flex items-center justify-between gap-3 rounded-[1.25rem] px-4 py-3 text-xs font-black uppercase tracking-[0.18em] transition-all {{ request()->routeIs('web.app.conversations*') ? 'bg-[linear-gradient(135deg,rgba(255,122,0,0.16),rgba(255,255,255,0.95))] text-[var(--brand)] shadow-[0_12px_24px_rgba(255,122,0,0.12)]' : 'text-slate-600 hover:bg-orange-50/70' }}">
                                            <span class="flex min-w-0 flex-1 items-center gap-3">
                                                <span
                                                    class="flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('web.app.conversations*') ? 'bg-white text-[var(--brand)]' : 'bg-slate-100 text-slate-500 group-hover:bg-white group-hover:text-[var(--brand)]' }}">
                                                    <i data-lucide="messages-square" class="h-4 w-4"></i>
                                                </span>
                                                <span>Messages</span>
                                            </span>
                                            @if ($navUnreadMessages > 0)
                                                <span
                                                    class="flex h-5 min-w-5 items-center justify-center rounded-full bg-[var(--brand)] px-1 text-[9px] font-black text-white shadow-sm">
                                                    {{ $navUnreadMessages > 9 ? '9+' : $navUnreadMessages }}
                                                </span>
                                            @endif
                                        </a>

                                        <a href="{{ route('web.app.notifications') }}"
                                            class="group mt-2 flex items-center justify-between gap-3 rounded-[1.25rem] px-4 py-3 text-xs font-black uppercase tracking-[0.18em] transition-all {{ request()->routeIs('web.app.notifications*') ? 'bg-[linear-gradient(135deg,rgba(255,122,0,0.16),rgba(255,255,255,0.95))] text-[var(--brand)] shadow-[0_12px_24px_rgba(255,122,0,0.12)]' : 'text-slate-600 hover:bg-orange-50/70' }}">
                                            <span class="flex min-w-0 flex-1 items-center gap-3">
                                                <span
                                                    class="flex h-10 w-10 items-center justify-center rounded-2xl {{ request()->routeIs('web.app.notifications*') ? 'bg-white text-[var(--brand)]' : 'bg-slate-100 text-slate-500 group-hover:bg-white group-hover:text-[var(--brand)]' }}">
                                                    <i data-lucide="bell" class="h-4 w-4"></i>
                                                </span>
                                                <span>Notifications</span>
                                            </span>
                                            @if ($navUnreadNotifications > 0)
                                                <span
                                                    class="flex h-5 min-w-5 items-center justify-center rounded-full bg-slate-900 px-1 text-[9px] font-black text-white shadow-sm">
                                                    {{ $navUnreadNotifications > 9 ? '9+' : $navUnreadNotifications }}
                                                </span>
                                            @endif
                                        </a>
                                    </div>
                                </div>

                                <div class="hidden px-2 pb-2 md:block">
                                    <div class="px-4 py-3 border-b border-slate-50 mb-1">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                            Account</p>
                                        <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}
                                        </p>
                                    </div>

                                    <a href="{{ route('web.app.me') }}"
                                        class="flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-600 hover:bg-orange-50 hover:text-orange-600 rounded-2xl transition-colors">
                                        <i data-lucide="user" class="h-4 w-4"></i> Profile
                                    </a>

                                    <a href="{{ route('web.app') }}"
                                        class="flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-600 hover:bg-orange-50 hover:text-orange-600 rounded-2xl transition-colors">
                                        <i data-lucide="layout-dashboard" class="h-4 w-4"></i> Dashboard
                                    </a>

                                    <a href="{{ route('web.app.jobs') }}"
                                        class="flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-600 hover:bg-orange-50 hover:text-orange-600 rounded-2xl transition-colors">
                                        <i data-lucide="briefcase-business" class="h-4 w-4"></i> Explore Jobs
                                    </a>

                                    <a href="{{ route('web.app.my-jobs') }}"
                                        class="flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-600 hover:bg-orange-50 hover:text-orange-600 rounded-2xl transition-colors">
                                        <i data-lucide="folders" class="h-4 w-4"></i> {{ $myJobsNavLabel }}
                                    </a>

                                    <a href="{{ route('web.app.conversations') }}"
                                        class="flex items-center justify-between gap-3 px-4 py-3 text-xs font-bold text-slate-600 hover:bg-orange-50 hover:text-orange-600 rounded-2xl transition-colors">
                                        <span class="flex items-center gap-3">
                                            <i data-lucide="messages-square" class="h-4 w-4"></i> Messages
                                        </span>
                                        @if ($navUnreadMessages > 0)
                                            <span
                                                class="min-w-5 h-5 px-1 rounded-full bg-[var(--brand)] text-white text-[9px] font-black flex items-center justify-center">
                                                {{ $navUnreadMessages > 9 ? '9+' : $navUnreadMessages }}
                                            </span>
                                        @endif
                                    </a>

                                    <a href="{{ route('web.app.notifications') }}"
                                        class="flex items-center justify-between gap-3 px-4 py-3 text-xs font-bold text-slate-600 hover:bg-orange-50 hover:text-orange-600 rounded-2xl transition-colors">
                                        <span class="flex items-center gap-3">
                                            <i data-lucide="bell" class="h-4 w-4"></i> Notifications
                                        </span>
                                        @if ($navUnreadNotifications > 0)
                                            <span
                                                class="min-w-5 h-5 px-1 rounded-full bg-[var(--brand)] text-white text-[9px] font-black flex items-center justify-center">
                                                {{ $navUnreadNotifications > 9 ? '9+' : $navUnreadNotifications }}
                                            </span>
                                        @endif
                                    </a>

                                    <div class="h-px bg-slate-50 my-1"></div>

                                    <!-- Logout via Form -->
                                    <x-logout class="float-right" />
                                </div>
                            </div>
                        </div> --}}
                        <!-- The Menu -->
                        <div id="userDropdownMenu"
                            class="hidden fixed -inset-y-6.25 -left-4.25 z-50 h-screen w-72 max-w-[86vw] border-r border-slate-200 bg-white shadow-2xl transition-transform duration-300 -translate-x-full md:absolute md:inset-auto md:right-0 md:top-full md:mt-2 md:h-auto md:w-56 md:max-w-none md:translate-x-0 md:rounded-xl md:border md:border-slate-100 md:shadow-lg">

                            <div class="flex h-full flex-col">
                                <!-- Mobile Header (Hidden on Desktop) -->
                                <div class="border-b border-slate-100 bg-slate-50 p-6 md:hidden">
                                    <div class="flex items-center justify-between mb-4">
                                        <span
                                            class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Account</span>
                                        <button id="userDrawerClose" class="text-slate-400"><i data-lucide="x"
                                                class="h-5 w-5"></i></button>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <x-avatar :user="auth()->user()" size="h-12 w-12" />
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                            <p class="text-[10px] text-orange-600 font-bold uppercase">
                                                {{ $mobileUserRoleLabel }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Menu Links -->
                                <div class="p-3 bg-white space-y-1">
                                    <a href="{{ route('web.app.me') }}"
                                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold {{ request()->routeIs('web.app.me') ? 'bg-orange-50 text-orange-600' : 'text-slate-600 hover:bg-slate-50' }}">
                                        <i data-lucide="user" class="h-4 w-4"></i>
                                        Profile
                                    </a>

                                    <a href="{{ route('web.app') }}"
                                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold {{ request()->routeIs('web.app') ? 'bg-orange-50 text-orange-600' : 'text-slate-600 hover:bg-slate-50' }}">
                                        <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                                        Dashboard
                                    </a>

                                    <a href="{{ route('web.app.jobs') }}"
                                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold {{ request()->routeIs('web.app.jobs*') ? 'bg-orange-50 text-orange-600' : 'text-slate-600 hover:bg-slate-50' }}">
                                        <i data-lucide="briefcase-business" class="h-4 w-4"></i>
                                        Explore Jobs
                                    </a>

                                    <a href="{{ route('web.app.my-jobs') }}"
                                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold {{ request()->routeIs('web.app.my-jobs*') ? 'bg-orange-50 text-orange-600' : 'text-slate-600 hover:bg-slate-50' }}">
                                        <i data-lucide="folders" class="h-4 w-4"></i>
                                        {{ $myJobsNavLabel }}
                                    </a>

                                    <a href="{{ route('web.app.conversations') }}"
                                        class="flex items-center justify-between px-4 py-3 rounded-lg text-sm font-bold {{ request()->routeIs('web.app.conversations*') ? 'bg-orange-50 text-orange-600' : 'text-slate-600 hover:bg-slate-50' }}">
                                        <span class="flex items-center gap-3">
                                            <i data-lucide="messages-square" class="h-4 w-4"></i>
                                            Messages
                                        </span>
                                        @if ($navUnreadMessages > 0)
                                            <span
                                                class="bg-orange-500 text-white text-[10px] px-2 py-0.5 rounded-full">{{ $navUnreadMessages }}</span>
                                        @endif
                                    </a>

                                    <a href="{{ route('web.app.notifications') }}"
                                        class="flex items-center justify-between px-4 py-3 rounded-lg text-sm font-bold {{ request()->routeIs('web.app.notifications*') ? 'bg-orange-50 text-orange-600' : 'text-slate-600 hover:bg-slate-50' }}">
                                        <span class="flex items-center gap-3">
                                            <i data-lucide="bell" class="h-4 w-4"></i>
                                            Notifications
                                        </span>
                                        @if ($navUnreadNotifications > 0)
                                            <span
                                                class="bg-slate-900 text-white text-[10px] px-2 py-0.5 rounded-full">{{ $navUnreadNotifications > 9 ? '9+' : $navUnreadNotifications }}</span>
                                        @endif
                                    </a>

                                    <hr class="my-2 border-slate-100">

                                    <x-logout
                                        class="w-full md:w-min text-left px-4 py-3 text-sm font-bold text-slate-600 hover:bg-rose-50 hover:text-rose-600 rounded-lg" />
                                </div>
                            </div>
                        </div>

                    </div>
                @endif
            </div>
        </div>
    </nav>

    @auth
        @if (request()->routeIs('web.app*'))
            <nav class="fixed w-full bottom-0 z-50 md:hidden">
                <div
                    class="mx-auto max-w-lg overflow-hidden rounded-t-2xl border border-white/20 bg-slate-900/90 px-2 py-2 shadow-[0_20px_50px_rgba(0,0,0,0.3)] backdrop-blur-xl">
                    <div class="grid grid-cols-5 items-center">

                        <!-- Home -->
                        <a href="{{ route('web.app') }}"
                            class="group flex flex-col items-center justify-center gap-1 py-2 transition-all {{ request()->routeIs('web.app') ? 'text-orange-500' : 'text-slate-400' }}">
                            <div class="relative flex items-center justify-center">
                                <i data-lucide="layout-dashboard"
                                    class="h-5 w-5 transition-transform group-active:scale-75"></i>
                                @if (request()->routeIs('web.app'))
                                    <span class="absolute -bottom-1 h-1 w-1 rounded-full bg-orange-500"></span>
                                @endif
                            </div>
                            <span class="text-[9px] font-bold uppercase tracking-widest">Home</span>
                        </a>

                        <!-- Jobs -->
                        <a href="{{ route('web.app.jobs') }}"
                            class="group flex flex-col items-center justify-center gap-1 py-2 transition-all {{ request()->routeIs('web.app.jobs*') ? 'text-orange-500' : 'text-slate-400' }}">
                            <div class="relative flex items-center justify-center">
                                <i data-lucide="briefcase-business"
                                    class="h-5 w-5 transition-transform group-active:scale-75"></i>
                                @if (request()->routeIs('web.app.jobs*'))
                                    <span class="absolute -bottom-1 h-1 w-1 rounded-full bg-orange-500"></span>
                                @endif
                            </div>
                            <span class="text-[9px] font-bold uppercase tracking-widest">Jobs</span>
                        </a>

                        <!-- Center Highlight: My Jobs/Hustles -->
                        <a href="{{ route('web.app.my-jobs') }}" class="flex flex-col items-center justify-center gap-1">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl {{ request()->routeIs('web.app.my-jobs*') ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/40' : 'bg-slate-800 text-slate-300' }} transition-all active:scale-90">
                                <i data-lucide="folders" class="h-6 w-6"></i>
                            </div>
                            <span class="text-[8px] font-black uppercase tracking-tighter text-slate-500">Hustles</span>
                        </a>

                        <!-- Chats -->
                        <a href="{{ route('web.app.conversations') }}"
                            class="group relative flex flex-col items-center justify-center gap-1 py-2 transition-all {{ request()->routeIs('web.app.conversations*') ? 'text-orange-500' : 'text-slate-400' }}">
                            <div class="relative flex items-center justify-center">
                                <i data-lucide="messages-square"
                                    class="h-5 w-5 transition-transform group-active:scale-75"></i>
                                @if ($navUnreadMessages > 0)
                                    <span
                                        class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-orange-500 text-[8px] font-black text-white ring-2 ring-slate-900">
                                        {{ $navUnreadMessages > 9 ? '9+' : $navUnreadMessages }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-[9px] font-bold uppercase tracking-widest">Chats</span>
                        </a>

                        <!-- Alerts -->
                        <a href="{{ route('web.app.notifications') }}"
                            class="group relative flex flex-col items-center justify-center gap-1 py-2 transition-all {{ request()->routeIs('web.app.notifications*') ? 'text-orange-500' : 'text-slate-400' }}">
                            <div class="relative flex items-center justify-center">
                                <i data-lucide="bell" class="h-5 w-5 transition-transform group-active:scale-75"></i>
                                @if ($navUnreadNotifications > 0)
                                    <span
                                        class="absolute -right-1 -top-1 flex h-2 w-2 rounded-full bg-emerald-400 ring-2 ring-slate-900"></span>
                                @endif
                            </div>
                            <span class="text-[9px] font-bold uppercase tracking-widest">Alerts</span>
                        </a>

                    </div>
                </div>
            </nav>

        @endif
    @endauth
