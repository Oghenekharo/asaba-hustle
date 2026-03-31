<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <link rel="manifest" href="/manifest.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#ff7a00">
    <link rel="icon" href="/images/icons/icon-192.png">
    <title>{{ $title ?? config('app.name', 'Asaba Hustle') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="web-ui min-h-screen bg-white font-sans antialiased text-[var(--ink)]
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

    <x-splash-screen />

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
</body>

</html>
