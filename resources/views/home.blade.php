@php
    $departments = [
        ['name' => 'Cleaning', 'icon' => '✨', 'image' => 'https://unsplash.com', 'size' => 'col-span-1'],
        ['name' => 'Plumbing', 'icon' => '🚰', 'image' => 'https://unsplash.com', 'size' => 'col-span-1'],
        ['name' => 'Electrical', 'icon' => '⚡', 'image' => 'https://unsplash.com', 'size' => 'md:col-span-2'],
        ['name' => 'Cooking', 'icon' => '🍳', 'image' => 'https://unsplash.com', 'size' => 'md:col-span-2'],
        ['name' => 'Gardening', 'icon' => '🌱', 'image' => 'https://unsplash.com', 'size' => 'col-span-1'],
        ['name' => 'Moving', 'icon' => '📦', 'image' => 'https://unsplash.com', 'size' => 'col-span-1'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asaba Hustle | Local Marketplace</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://jquery.com"></script>
</head>

<body class="antialiased selection:bg-[var(--brand)] selection:text-white">

    <!-- Glassmorphism Nav -->
    <nav class="sticky top-0 z-50 border-b border-[var(--brand)]/5 bg-[var(--surface)]/70 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="text-2xl font-black tracking-tighter italic" style="color: var(--brand)">
                ASABA<span style="color: var(--ink)">HUSTLE</span>
            </div>
            <div class="hidden md:flex items-center space-x-10 text-sm font-bold uppercase tracking-widest opacity-70">
                <a href="#services" class="hover:text-[var(--brand)] transition">Services</a>
                <a href="#" class="hover:text-[var(--brand)] transition">Providers</a>
                <a href="#" class="hover:text-[var(--brand)] transition">API Docs</a>
            </div>
            <div class="flex items-center space-x-6">
                <a href="/login" class="font-bold text-sm uppercase tracking-wider">Login</a>
                <a href="/register"
                    class="px-8 py-3 rounded-full font-bold text-sm uppercase tracking-tighter transition-all hover:scale-105 active:scale-95 shadow-2xl shadow-orange-500/30 text-white"
                    style="background: var(--brand)">
                    Get Started
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative pt-20 pb-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-12 gap-16 items-center">
            <div class="lg:col-span-7">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full border border-[var(--brand)]/20 mb-8"
                    style="background: var(--surface-soft)">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                            style="background: var(--brand)"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2" style="background: var(--brand)"></span>
                    </span>
                    <span class="text-xs font-black uppercase tracking-widest" style="color: var(--brand)">Live in Delta
                        State</span>
                </div>
                <h1 class="text-6xl md:text-8xl font-black leading-[0.9] mb-8 tracking-tighter">
                    Hire talent <br /> <span style="color: var(--brand)">Faster than ever.</span>
                </h1>
                <p class="text-xl mb-12 max-w-xl leading-relaxed opacity-70 font-medium">
                    The modern API-first marketplace connecting Asaba's finest service providers with clients who value
                    quality and speed.
                </p>
                <div class="flex flex-wrap gap-4">
                    <button
                        class="px-10 py-5 rounded-2xl font-black text-white transition-all hover:shadow-orange-500/40 shadow-xl"
                        style="background: var(--brand-strong)">
                        Book a Service
                    </button>
                    <button
                        class="group px-10 py-5 rounded-2xl font-black border-2 transition-all flex items-center space-x-3"
                        style="border-color: var(--ink)">
                        <span>View Categories</span>
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </button>
                </div>
            </div>

            <!-- Hero Image Visual -->
            <div class="lg:col-span-5 relative">
                <div
                    class="relative z-10 rounded-[3rem] overflow-hidden shadow-[0_50px_100px_-20px_rgba(0,0,0,0.2)] border-8 border-white rotate-3 hover:rotate-0 transition-all duration-700">
                    <img src="https://images.unsplash.com/photo-1770131091438-c5c4b89ea264?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D
                    "
                        class="w-full aspect-[4/5] object-cover" alt="Hustle">
                </div>
                <div class="absolute -bottom-10 -right-10 w-64 h-64 rounded-full blur-[120px] -z-10 opacity-40"
                    style="background: var(--brand)"></div>
            </div>
        </div>
    </header>

    <!-- Bento Grid Services -->
    <section id="services" class="py-32 bg-[#fafafa]">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                <div>
                    <h2 class="text-4xl md:text-5xl font-black tracking-tighter">Top Departments</h2>
                    <p class="text-lg opacity-50 font-medium mt-2">Hand-picked professionals for your home.</p>
                </div>
                <a href="#" class="font-bold border-b-2 border-[var(--brand)]" style="color: var(--brand)">Explore
                    all 24+ Categories</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @foreach ($departments as $dept)
                    <div
                        class="{{ $dept['size'] }} group relative h-[350px] rounded-[2.5rem] overflow-hidden cursor-pointer shadow-sm hover:shadow-2xl transition-all duration-500">
                        <!-- Background Image -->
                        <img src="{{ $dept['image'] }}"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                            alt="{{ $dept['name'] }}">

                        <!-- Overlay Gradient -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                        <!-- Content -->
                        <div class="absolute inset-0 p-8 flex flex-col justify-end text-white">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="text-2xl">{{ $dept['icon'] }}</span>
                                <span class="text-xs font-black uppercase tracking-[0.2em] opacity-80">Verified</span>
                            </div>
                            <h3 class="text-3xl font-black tracking-tighter">{{ $dept['name'] }}</h3>

                            <!-- Hidden Button shown on hover -->
                            <div class="max-h-0 group-hover:max-h-20 transition-all duration-500 overflow-hidden">
                                <p class="text-sm opacity-70 mt-2 mb-4">Starting from ₦5,000</p>
                                <span class="inline-block px-6 py-2 rounded-full text-xs font-bold text-black"
                                    style="background: var(--surface)">
                                    Book Now
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Footer Stats -->
    <footer class="py-20 border-t border-[var(--brand)]/10" style="background: var(--ink)">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-12 items-center">
            <div class="text-white">
                <div class="text-2xl font-black italic mb-2" style="color: var(--brand)">ASABA<span
                        class="text-white">HUSTLE</span></div>
                <p class="opacity-50 text-sm">Empowering local artisans through technology.</p>
            </div>
            <div class="flex justify-around md:col-span-2">
                <div class="text-center text-white">
                    <div class="text-4xl font-black" style="color: var(--brand)">1.2k+</div>
                    <div class="text-xs uppercase tracking-widest opacity-40 mt-2 font-bold">Providers</div>
                </div>
                <div class="text-center text-white">
                    <div class="text-4xl font-black" style="color: var(--brand)">98%</div>
                    <div class="text-xs uppercase tracking-widest opacity-40 mt-2 font-bold">Rating</div>
                </div>
                <div class="text-center text-white">
                    <div class="text-4xl font-black" style="color: var(--brand)">24/7</div>
                    <div class="text-xs uppercase tracking-widest opacity-40 mt-2 font-bold">Support</div>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>
