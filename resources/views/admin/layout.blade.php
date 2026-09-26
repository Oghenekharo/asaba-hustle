<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $siteTheme['primary'] }}">
    <link rel="icon" href="{{ $siteTheme['icon'] ? asset('storage/' . $siteTheme['icon']) : asset('images/icons/icon-192.png') }}">
    <script>
        (() => {
            const saved = localStorage.getItem('asaba-theme');
            document.documentElement.classList.toggle('dark', saved ? saved === 'dark' : matchMedia('(prefers-color-scheme: dark)').matches);
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <title>{{ $title ?? trim($__env->yieldContent('title')) ?: 'Admin Dashboard' }} |
        {{ config('app.name', 'Asaba Hustle') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="site-typography min-h-screen text-[var(--ink)] antialiased" style="--brand: {{ $siteTheme['primary'] }}; --brand-strong: {{ $siteTheme['strong'] }}; --brand-gradient: {{ $siteTheme['gradient'] }}; --surface-soft: {{ $siteTheme['soft'] }}; background-color: var(--surface); color: var(--ink)">
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
            ['route' => 'admin.appearance.edit', 'label' => 'Appearance', 'icon' => 'palette'],
        ];
    @endphp

    <div
        class="admin-ui relative isolate min-h-screen font-sans selection:bg-[var(--brand)] selection:text-white
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
            class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[40rem] bg-[radial-gradient(circle_at_top_left,color-mix(in_srgb,var(--brand)_8%,transparent),_transparent_70%)]">
        </div>

        <!-- Mobile Sidebar Overlay (Glass) -->
        <div id="admin-sidebar-overlay"
            class="fixed inset-0 z-40 hidden bg-[var(--ink)]/20 backdrop-blur-md lg:hidden transition-all duration-500">
        </div>

        <!-- Sidebar: Floating Bento Navigation -->
        <aside id="admin-sidebar"
            class="fixed inset-y-4 left-4 z-50 flex w-72 -translate-x-[calc(100%+2rem)] flex-col rounded-[2.5rem] border border-[#263449] bg-[#1f2b3d] text-white shadow-[0_32px_64px_-16px_rgba(0,0,0,0.3)] transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] lg:translate-x-0">


            <div class="p-8">
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-3">
                        <div
                            class="grid h-11 w-11 place-items-center rounded-2xl bg-[var(--brand)] text-sm font-black italic text-white shadow-[0_8px_16px_color-mix(in_srgb,var(--brand)_30%,transparent)] group-hover:scale-110 transition-transform duration-300">
                            AH</div>
                        <div>
                            <span
                                class="block text-[10px] font-black uppercase text-slate-300">Control</span>
                            <span class="block text-sm font-black  text-white italic">Asaba Hustle</span>
                        </div>
                    </a>
                    <button type="button" id="admin-sidebar-close"
                        class="lg:hidden p-2 rounded-xl hover:bg-[#253a59] text-white">
                        <i data-lucide="x-circle" class="h-5 w-5"></i>
                    </button>
                </div>
            </div>

            <!-- Admin Profile Pill -->
            <div class="px-5 mb-6">
                <div
                    class="flex items-center gap-3 rounded-[2rem] bg-[#1b2a40] p-2 pr-4 border border-[#3d5678]">
                    <div
                        class="h-10 w-10 rounded-[1.25rem] bg-[#293f5f] flex items-center justify-center font-black text-xs border border-[#6784aa] text-white">
                        {{ substr($adminUser?->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-[11px] font-black text-white uppercase ">
                            {{ $adminUser?->name }}</p>
                        <div
                            class="flex items-center gap-1.5 text-[9px] font-bold uppercase text-emerald-300">
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
                        class="group flex items-center gap-3 rounded-2xl border-b px-4 py-3.5 text-[10px] font-black uppercase transition-all duration-300 {{ $active ? 'border-b-transparent bg-[var(--brand)] text-white shadow-lg' : 'border-b-[#35445a] bg-[#29384d] text-white hover:border-b-[#35445a] hover:bg-[#34465f] hover:text-white' }}">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-xl transition-colors {{ $active ? 'bg-white text-[var(--brand)]' : 'bg-[#1b2a40] text-white group-hover:bg-[#293f5f]' }}">
                            <i data-lucide="{{ $item['icon'] }}"
                                class="h-4 w-4"></i>
                        </div>
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if ($active)
                            <div class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></div>
                        @endif
                    </a>
                @endforeach
            </nav>

            <!-- Sidebar Footer Actions -->
            <div class="mt-auto p-4">
                <div class="rounded-2xl border border-[#293e5e] bg-[#14223a] p-3">
                    <div class="grid grid-cols-1 gap-2">
                        <a href="{{ route('web.app') }}"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#263b5c] px-3 py-2 text-[9px] font-black uppercase text-white transition-all hover:bg-[#334f77]">
                            <i data-lucide="external-link" class="h-3.5 w-3.5 text-[var(--brand)]"></i>
                            <span>User Site</span>
                        </a>
                        <form method="POST" action="{{ route('web.logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-rose-600 px-3 py-2 text-[9px] font-black uppercase text-white transition-all hover:bg-rose-500">
                                <i data-lucide="log-out" class="h-3.5 w-3.5"></i>
                                <span>Logout</span>
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
                    class="flex items-center justify-between rounded-[2rem] border border-[var(--line)] bg-[var(--surface-raised)] px-6 py-4 shadow-sm">
                    <div class="flex items-center gap-4">
                        <button type="button" id="admin-sidebar-open"
                            class="lg:hidden h-11 w-11 flex items-center justify-center rounded-2xl bg-[var(--ink)] text-white shadow-lg shadow-slate-900/20 active:scale-90 transition-transform">
                            <i data-lucide="menu" class="h-5 w-5"></i>
                        </button>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-[9px] font-black uppercase  text-[var(--brand)]">
                                    Operations</p>
                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                <p class="text-[9px] font-black uppercase  text-slate-400">
                                    {{ now()->format('H:i') }}</p>
                            </div>
                            <h1 class="text-lg font-black  text-[var(--ink)] md:text-xl">
                                @yield('admin-page-title', 'Dashboard')</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" data-theme-toggle class="theme-toggle inline-flex h-10 w-10 items-center justify-center rounded-xl transition" aria-label="Toggle dark mode" title="Toggle dark mode">
                            <i data-lucide="moon" data-theme-icon-dark class="h-4 w-4"></i>
                            <i data-lucide="sun" data-theme-icon-light class="hidden h-4 w-4"></i>
                        </button>
                        <div class="hidden items-center gap-4 md:flex">
                        <div
                            class="flex items-center gap-3 rounded-2xl bg-[var(--surface-soft)] px-4 py-2.5 border border-[var(--brand)]/10 shadow-sm">
                            <i data-lucide="calendar" class="h-4 w-4 text-[var(--brand)]"></i>
                            <span
                                class="text-[10px] font-black uppercase  text-[var(--ink)] opacity-70">{{ now()->format('D, d M Y') }}</span>
                        </div>
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
                            <p class="text-[9px] font-black uppercase  text-emerald-500">System Success
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
