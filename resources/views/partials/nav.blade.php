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
@endphp
<nav class="fixed top-6 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2rem)] max-w-4xl">
    <!-- Floating Glass Container -->
    <div
        class="relative flex items-center justify-between p-2 pl-6 bg-white/80 backdrop-blur-2xl border border-white/20 shadow-[0_8px_32px_0_rgba(0,0,0,0.1)] rounded-3xl">

        <!-- Brand -->
        <a href="{{ auth()->check() ? '/app' : '/' }}" class="flex items-center gap-3">
            <img src="/images/icons/asaba-hustle.svg" class="w-9 h-9" />
            <div class="text-lg font-black tracking-tight text-slate-900 leading-none">
                Asaba<span class="block text-[10px] uppercase tracking-[0.2em] text-orange-500">Hustle</span>
            </div>
        </a>

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

                            <div class="border-t border-slate-50 p-3">
                                <a href="{{ route('web.app.notifications') }}"
                                    class="flex items-center justify-center gap-2 rounded-2xl bg-slate-100 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-orange-50 hover:text-orange-600">
                                    <i data-lucide="external-link" class="h-4 w-4"></i>
                                    View All Notifications
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- User Dropdown Container -->
                    <div id="userDrawerOverlay"
                        class="fixed inset-0 z-40 hidden bg-slate-900/35 backdrop-blur-[2px] md:hidden"></div>
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
                        <div id="userDropdownMenu"
                            class="hidden fixed inset-y-0 right-0 z-50 h-screen w-72 max-w-[85vw] translate-x-[105%] bg-white backdrop-blur-2xl border-l border-slate-100 shadow-[0_20px_50px_rgba(0,0,0,0.1)] overflow-hidden p-2 transition-transform duration-300 md:absolute md:inset-auto md:right-0 md:mt-3 md:h-auto md:w-48 md:max-w-none md:translate-x-0 md:rounded-2xl md:border md:border-slate-100">

                            <div class="mb-1 flex items-center justify-end md:hidden">
                                <button type="button" id="userDrawerClose"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                                    <i data-lucide="x" class="h-4 w-4"></i>
                                </button>
                            </div>

                            <div class="px-4 py-3 border-b border-slate-50 mb-1">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Account</p>
                                <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
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
                @endif
            </div>
        </div>
    </nav>
