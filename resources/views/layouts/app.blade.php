{{-- <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Asaba Hustle') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[var(--surface)] text-[var(--ink)] antialiased">
    <div class="relative isolate min-h-screen overflow-x-hidden">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-80 bg-[radial-gradient(circle_at_top,_rgba(255,122,0,0.20),_transparent_55%)]"></div>

        <header class="border-b border-black/5 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('web.home') }}" class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-2xl bg-[var(--brand)] font-black text-white">AH</span>
                    <span>
                        <span class="block text-sm font-semibold uppercase tracking-[0.28em] text-[var(--brand)]">Asaba Hustle</span>
                        <span class="block text-xs text-black/55">API-first local services marketplace</span>
                    </span>
                </a>

                <nav class="flex items-center gap-3 text-sm font-medium">
                    @auth
                        @if (auth()->user()->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}" class="rounded-full px-4 py-2 text-black/70 transition hover:bg-black/5 hover:text-black">Admin</a>
                        @else
                            <a href="{{ route('web.app') }}" class="rounded-full px-4 py-2 text-black/70 transition hover:bg-black/5 hover:text-black">App</a>
                        @endif
                        <button type="button" id="logout-button" class="rounded-full bg-[var(--ink)] px-4 py-2 text-white transition hover:bg-black/85">Logout</button>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full px-4 py-2 text-black/70 transition hover:bg-black/5 hover:text-black">Login</a>
                        <a href="{{ route('web.register') }}" class="rounded-full bg-[var(--brand)] px-4 py-2 text-white transition hover:bg-[var(--brand-strong)]">Create account</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            @yield('content')
        </main>
    </div>

    @auth
        <script>
            window.asabaLogoutUrl = @json(route('web.logout'));
            window.asabaLoginUrl = @json(route('login'));
        </script>
    @endauth

    @stack('scripts')
</body>
</html> --}}
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Asaba Hustle') }} | Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="web-ui min-h-screen bg-white font-sans antialiased text-[var(--ink)]
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
    [&_.rounded-\[2rem\]]:!rounded-[1.5rem]
    [&_.p-12]:!p-6 md:[&_.p-12]:!p-8
    [&_.p-10]:!p-6 md:[&_.p-10]:!p-8
    [&_.p-8]:!p-5 md:[&_.p-8]:!p-6
    [&_.p-6]:!p-4 md:[&_.p-6]:!p-5
    [&_.px-8]:!px-5 md:[&_.px-8]:!px-6
    [&_.px-6]:!px-4 md:[&_.px-6]:!px-5
    [&_.py-10]:!py-6 md:[&_.py-10]:!py-8
    [&_.py-8]:!py-5 md:[&_.py-8]:!py-6
    [&_.gap-10]:!gap-6 md:[&_.gap-10]:!gap-8
    [&_.gap-8]:!gap-5 md:[&_.gap-8]:!gap-6
    [&_.h-20]:!h-16 [&_.w-20]:!w-16 md:[&_.h-20]:!h-20 md:[&_.w-20]:!w-20
    [&_.h-14]:!h-12 [&_.w-14]:!w-12
    [&_.h-12]:!h-11">

    @if (
        !request()->is('login') &&
            !request()->is('register') &&
            !request()->is('forgot-password') &&
            !request()->is('reset-password') &&
            !request()->is('verify-phone') &&
            !request()->is('auth*'))
        @include('partials.nav')
    @endif
    <!-- Main Content Area -->
    <main class="mx-auto max-w-7xl px-4 py-6 md:px-6 md:py-8">
        {{-- <!-- Optional Breadcrumb or Page Header can go here via @yield --> --}}
        <div class="animate-in fade-in slide-in-from-bottom-4 duration-700">
            @yield('content')
        </div>
    </main>

    <!-- App Configuration -->
    <script>
        window.asabaAppConfig = {
            meUrl: "{{ route('web.app.me') }}",
            jobsUrl: "{{ route('web.app.jobs') }}",
            myJobsUrl: "{{ route('web.app.my-jobs') }}",
            notificationsUrl: "{{ route('web.app.notifications') }}",
            notificationReadUrl: "{{ route('web.app.notifications.read') }}",
            notificationReadAllUrl: "{{ route('web.app.notifications.read-all') }}",
            currentUserId: {{ auth()->id() ?? 'null' }},
            jobShowBase: "/app/jobs"
        };
        window.asabaLogoutUrl = "{{ route('web.logout') }}";

        // // Modern Logout Handling with jQuery
        // $('#logout-button').on('click', function(e) {
        //     e.preventDefault();
        //     if (confirm('Are you sure you want to logout?')) {
        //         window.location.href = window.asabaLogoutUrl;
        //     }
        // });
    </script>
</body>

</html>
