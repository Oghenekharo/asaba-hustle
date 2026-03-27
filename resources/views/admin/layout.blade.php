<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? trim($__env->yieldContent('title')) ?: 'Admin Dashboard' }} |
        {{ config('app.name', 'Asaba Hustle') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[var(--surface)] text-[var(--ink)] antialiased">
    @php
        $adminUser = auth()->user();
        $navItems = [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
            ['route' => 'admin.users.index', 'label' => 'Users', 'icon' => 'users'],
            ['route' => 'admin.skills.index', 'label' => 'Skills', 'icon' => 'sparkles'],
            ['route' => 'admin.jobs.index', 'label' => 'Jobs', 'icon' => 'briefcase-business'],
            ['route' => 'admin.payments.index', 'label' => 'Payments', 'icon' => 'wallet'],
            ['route' => 'admin.ratings.index', 'label' => 'Ratings', 'icon' => 'star'],
            ['route' => 'admin.activity.index', 'label' => 'Activity', 'icon' => 'history'],
        ];
    @endphp

    <div
        class="admin-ui relative isolate min-h-screen bg-[#F8FAFC] font-sans selection:bg-[var(--brand)] selection:text-white
        [&_.text-5xl]:!text-3xl md:[&_.text-5xl]:!text-4xl
        [&_.text-4xl]:!text-2xl md:[&_.text-4xl]:!text-3xl
        [&_.text-3xl]:!text-xl md:[&_.text-3xl]:!text-2xl
        [&_.text-2xl]:!text-lg md:[&_.text-2xl]:!text-xl
        [&_.text-xl]:!text-base md:[&_.text-xl]:!text-lg
        [&_.text-lg]:!text-sm md:[&_.text-lg]:!text-base
        [&_.text-base]:!text-sm
        [&_.text-sm]:!text-xs md:[&_.text-sm]:!text-sm
        [&_.rounded-\[3rem\]]:!rounded-[2rem]
        [&_.rounded-\[2\.5rem\]]:!rounded-[1.75rem]
        [&_.rounded-\[2\.3rem\]]:!rounded-[1.5rem]
        [&_.rounded-\[2\.2rem\]]:!rounded-[1.5rem]
        [&_.p-8]:!p-5 md:[&_.p-8]:!p-6
        [&_.p-7]:!p-5
        [&_.p-6]:!p-4 md:[&_.p-6]:!p-5
        [&_.px-6]:!px-4 md:[&_.px-6]:!px-5
        [&_.py-4]:!py-3
        [&_.h-12]:!h-11
        [&_.h-14]:!h-12">
        <!-- Brand Gradient Background -->
        <div
            class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[40rem] bg-[radial-gradient(circle_at_top_left,_rgba(255,122,0,0.08),_transparent_70%)]">
        </div>

        <!-- Mobile Sidebar Overlay (Glass) -->
        <div id="admin-sidebar-overlay"
            class="fixed inset-0 z-40 hidden bg-[var(--ink)]/20 backdrop-blur-md lg:hidden transition-all duration-500">
        </div>

        <!-- Sidebar: Floating Bento Navigation -->
        <aside id="admin-sidebar"
            class="fixed inset-y-4 left-4 z-50 flex w-72 -translate-x-[calc(100%+2rem)] flex-col rounded-[2.5rem] border border-white/20 bg-[var(--ink)] text-white shadow-[0_32px_64px_-16px_rgba(0,0,0,0.3)] transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] lg:translate-x-0">


            <div class="p-8">
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-3">
                        <div
                            class="grid h-11 w-11 place-items-center rounded-2xl bg-[var(--brand)] text-sm font-black italic tracking-tighter text-white shadow-[0_8px_16px_rgba(255,122,0,0.3)] group-hover:scale-110 transition-transform duration-300">
                            AH</div>
                        <div>
                            <span
                                class="block text-[10px] font-black uppercase tracking-[0.3em] text-orange-400/60">Control</span>
                            <span class="block text-sm font-black tracking-tight text-white italic">Asaba Hustle</span>
                        </div>
                    </a>
                    <button type="button" id="admin-sidebar-close"
                        class="lg:hidden p-2 rounded-xl hover:bg-white/10 text-white/40">
                        <i data-lucide="x-circle" class="h-5 w-5"></i>
                    </button>
                </div>
            </div>

            <!-- Admin Profile Pill -->
            <div class="px-5 mb-6">
                <div
                    class="flex items-center gap-3 rounded-[2rem] bg-white/5 p-2 pr-4 border border-white/5 backdrop-blur-sm">
                    <div
                        class="h-10 w-10 rounded-[1.25rem] bg-gradient-to-br from-white/10 to-white/5 flex items-center justify-center font-black text-xs border border-white/10 shadow-inner">
                        {{ substr($adminUser?->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-[11px] font-black text-white uppercase tracking-wider">
                            {{ $adminUser?->name }}</p>
                        <div
                            class="flex items-center gap-1.5 text-[9px] font-bold uppercase tracking-widest text-emerald-400/80">
                            <span class="h-1 w-1 rounded-full bg-emerald-400 animate-pulse"></span>
                            Verified Admin
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scrollable Navigation -->
            <nav class="flex-1 space-y-1.5 overflow-y-auto px-4 custom-scrollbar">
                @foreach ($navItems as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                        class="group flex items-center gap-3 rounded-2xl px-4 py-3.5 text-[10px] font-black uppercase tracking-[0.15em] transition-all duration-300 {{ $active ? 'bg-[var(--brand)] text-white shadow-[0_12px_24px_-8px_rgba(255,122,0,0.5)]' : 'text-white/40 hover:bg-white/5 hover:text-white' }}">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-xl transition-colors {{ $active ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }}">
                            <i data-lucide="{{ $item['icon'] }}"
                                class="h-4 w-4 {{ $active ? 'text-white' : 'group-hover:text-orange-400' }}"></i>
                        </div>
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if ($active)
                            <div class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></div>
                        @endif
                    </a>
                @endforeach
            </nav>

            <!-- Sidebar Footer Actions -->
            <div class="p-5 mt-auto">
                <div class="rounded-[2rem] bg-white/5 p-4 border border-white/5 backdrop-blur-md">
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('web.app') }}"
                            class="flex flex-col items-center gap-2 rounded-2xl bg-white/5 py-3 text-[9px] font-black uppercase tracking-widest text-white/50 hover:bg-white/10 hover:text-white transition-all">
                            <i data-lucide="external-link" class="h-4 w-4 text-orange-400"></i>
                            <span>Live Site</span>
                        </a>
                        <form method="POST" action="{{ route('web.logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex flex-col items-center gap-2 rounded-2xl bg-rose-500/10 py-3 text-[9px] font-black uppercase tracking-widest text-rose-400 hover:bg-rose-500 hover:text-white transition-all">
                                <i data-lucide="log-out" class="h-4 w-4"></i>
                                <span>Exit</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Viewport -->
        <div class="lg:pl-80 transition-all duration-500">
            <!-- Floating Glass Header -->
            <header class="sticky top-0 z-30 px-4 py-4 md:px-8">
                <div
                    class="flex items-center justify-between rounded-[2rem] border border-white bg-white/70 px-6 py-4 shadow-[0_8px_32px_rgba(0,0,0,0.04)] backdrop-blur-xl">
                    <div class="flex items-center gap-4">
                        <button type="button" id="admin-sidebar-open"
                            class="lg:hidden h-11 w-11 flex items-center justify-center rounded-2xl bg-[var(--ink)] text-white shadow-lg shadow-slate-900/20 active:scale-90 transition-transform">
                            <i data-lucide="menu" class="h-5 w-5"></i>
                        </button>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-[9px] font-black uppercase tracking-[0.3em] text-[var(--brand)]">
                                    Operations</p>
                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                <p class="text-[9px] font-black uppercase tracking-[0.3em] text-slate-400">
                                    {{ now()->format('H:i') }}</p>
                            </div>
                            <h1 class="text-lg font-black tracking-tighter text-[var(--ink)] md:text-xl">
                                @yield('admin-page-title', 'Dashboard')</h1>
                        </div>
                    </div>

                    <div class="hidden items-center gap-4 md:flex">
                        <div
                            class="flex items-center gap-3 rounded-2xl bg-[var(--surface-soft)] px-4 py-2.5 border border-[var(--brand)]/10 shadow-sm">
                            <i data-lucide="calendar" class="h-4 w-4 text-[var(--brand)]"></i>
                            <span
                                class="text-[10px] font-black uppercase tracking-widest text-[var(--ink)] opacity-70">{{ now()->format('D, d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content Area -->
            <main class="px-4 py-6 md:px-5">
                @if (session('status'))
                    <div
                        class="mb-8 flex items-center gap-4 rounded-[2rem] border border-emerald-100 bg-white p-4 text-emerald-900 shadow-sm animate-in fade-in slide-in-from-top-4 duration-500">
                        <div
                            class="h-10 w-10 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/20">
                            <i data-lucide="check" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-emerald-500">System Success
                            </p>
                            <p class="text-xs font-bold">{{ session('status') }}</p>
                        </div>
                    </div>
                @endif

                <div class="animate-in fade-in slide-in-from-bottom-6 duration-1000">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        (() => {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('admin-sidebar-overlay');
            const openButton = document.getElementById('admin-sidebar-open');
            const closeButton = document.getElementById('admin-sidebar-close');

            if (!sidebar || !overlay || !openButton || !closeButton) return;

            const toggleSidebar = (show) => {
                if (show) {
                    // Remove the negative translate and set it to 0
                    sidebar.classList.remove('-translate-x-[calc(100%+2rem)]');
                    sidebar.classList.add('translate-x-0');
                    overlay.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                } else {
                    sidebar.classList.add('-translate-x-[calc(100%+2rem)]');
                    sidebar.classList.remove('translate-x-0');
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            };

            openButton.addEventListener('click', () => toggleSidebar(true));
            closeButton.addEventListener('click', () => toggleSidebar(false));
            overlay.addEventListener('click', () => toggleSidebar(false));

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                    // Ensure sidebar isn't stuck in "closed" state when resizing up
                    sidebar.classList.remove('-translate-x-[calc(100%+2rem)]');
                    sidebar.classList.add('translate-x-0');
                } else {
                    // Re-hide on small screen resize if previously expanded
                    sidebar.classList.add('-translate-x-[calc(100%+2rem)]');
                }
            });
        })();
    </script>

</body>

</html>
