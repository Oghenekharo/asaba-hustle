<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <link rel="manifest" href="/manifest.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="icon" href="/images/icons/icon-192.png">
    <meta name="theme-color" content="#ff7a00">
    <title>Asaba Hustle | Local Marketplace</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
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

<body class="antialiased bg-[#fafafa] text-[#1a1a1a]">
    <x-splash-screen />

    @include('partials.nav')

    @include('partials.hero')

    @include('partials.services')

    <section id="how-it-works" class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-black tracking-tight text-slate-900">Simple as 1-2-3</h2>
                <p class="text-sm text-slate-400 mt-2 font-bold uppercase tracking-widest">Your journey to a better
                    hustle</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 relative">
                <!-- Connection Line (Desktop) -->
                <div class="hidden md:block absolute top-1/2 left-0 w-full h-px bg-slate-200 -z-10"></div>

                @php
                    $steps = [
                        [
                            'id' => '01',
                            'title' => 'Discover',
                            'desc' => 'Search for verified pros near you.',
                            'icon' => 'search',
                        ],
                        [
                            'id' => '02',
                            'title' => 'Connect',
                            'desc' => 'Chat and agree on the perfect terms.',
                            'icon' => 'message-square',
                        ],
                        [
                            'id' => '03',
                            'title' => 'Finish',
                            'desc' => 'Get it done and pay securely.',
                            'icon' => 'check-circle',
                        ],
                    ];
                @endphp

                @foreach ($steps as $step)
                    <div
                        class="relative p-8 rounded-[2.5rem] bg-white/60 backdrop-blur-xl border border-white text-center group hover:bg-white transition-all">
                        <div
                            class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-slate-900 text-white text-[10px] font-black tracking-widest">
                            STEP {{ $step['id'] }}
                        </div>
                        <div
                            class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center mx-auto mb-6 group-hover:rotate-6 transition-transform">
                            <i data-lucide="{{ $step['icon'] }}" class="w-6 h-6"></i>
                        </div>
                        <h3 class="font-bold text-lg text-slate-900 mb-2">{{ $step['title'] }}</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-slate-900 mb-6">Built on trust, <br><span
                        class="text-orange-600">backed by results.</span></h2>
                <div class="space-y-4">
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div
                            class="h-10 w-10 rounded-xl bg-green-500/10 text-green-600 flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-700">100% Identity Verified Professionals</p>
                    </div>
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="h-10 w-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center">
                            <i data-lucide="lock" class="w-5 h-5"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-700">Secure Escrow Payment Protection</p>
                    </div>
                </div>
            </div>

            <!-- Stats Bento -->
            <div class="grid grid-cols-2 gap-4">
                <div class="p-8 rounded-[2rem] bg-orange-600 text-white shadow-xl shadow-orange-600/20">
                    <p class="text-3xl font-black mb-1">4.9</p>
                    <p class="text-[10px] uppercase font-bold tracking-widest opacity-80">Average Rating</p>
                </div>
                <div class="p-8 rounded-[2rem] bg-slate-900 text-white shadow-xl shadow-slate-900/20">
                    <p class="text-3xl font-black mb-1">12k+</p>
                    <p class="text-[10px] uppercase font-bold tracking-widest opacity-80">Jobs Done</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FINAL CTA -->
    <section class="py-20 bg-slate-50">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <div class="p-12 rounded-[3rem] bg-slate-900 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-3xl font-black mb-6">Ready to find your pro?</h2>
                    <button
                        class="px-10 py-4 rounded-2xl bg-orange-600 text-white font-bold text-xs uppercase tracking-widest hover:bg-orange-500 transition-all active:scale-95 shadow-xl shadow-orange-600/30">
                        Join the Marketplace
                    </button>
                </div>
                <!-- Decorative Glow -->
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-orange-500/20 blur-[100px] -translate-y-1/2 translate-x-1/2">
                </div>
            </div>
        </div>
    </section>

    <!-- SLIM FOOTER -->
    <footer class="pb-32 md:pb-12 pt-12 bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="flex items-center gap-2">
                <img src="/images/icons/asaba-hustle.svg" class="w-7 h-7" />
                <span class="text-sm font-black tracking-tighter text-slate-900">AsabaHustle</span>
            </div>

            <div class="flex items-center gap-8 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                <a href="#" class="hover:text-orange-600">Privacy</a>
                <a href="#" class="hover:text-orange-600">Terms</a>
                <a href="#" class="hover:text-orange-600">Help</a>
            </div>

            <div class="text-[10px] font-medium text-slate-400">
                © {{ date('Y') }} AsabaHustle. Built for the grind.
            </div>
        </div>
    </footer>

    <div id="installBanner" class="hidden fixed bottom-6 left-4 right-4 md:left-auto md:right-8 md:w-96 z-50">
        <div
            class="bg-gradient-to-r from-orange-600 to-orange-500 p-4 rounded-2xl shadow-2xl border border-orange-400/30 backdrop-blur-sm">
            <div class="flex items-center gap-3"> <!-- Icon -->
                <div
                    class="flex-shrink-0 w-11 h-11 sm:w-12 sm:h-12 bg-white/20 rounded-xl flex items-center justify-center text-white shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-7 sm:w-7" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div> <!-- Text -->
                <div class="flex-1 min-w-0">
                    <h4 class="text-white font-semibold text-sm truncate"> Install Asaba Hustle </h4>
                    <p class="text-white/80 text-xs mt-0.5 leading-tight"> Faster access & offline updates </p>
                </div> <!-- Close (top-right for mobile) -->
                <button onclick="document.getElementById('installBanner').classList.add('hidden')"
                    class="text-white/70 hover:text-white text-lg leading-none px-1"> × </button>
            </div> <!-- Actions -->
            <div class="mt-3 flex gap-2"> <button id="installBtn"
                    class="flex-1 bg-white text-orange-600 px-4 py-2.5 rounded-xl font-semibold text-sm active:scale-95 transition shadow-md">
                    Install </button> <button onclick="document.getElementById('installBanner').classList.add('hidden')"
                    class="flex-1 text-white border border-white px-4 py-2.5 rounded-xl text-sm font-medium active:scale-95">
                    Later </button> </div>
        </div>
    </div>
    <x-modal id="installGuideModal" title="Install App">
        <div class="space-y-5">
            <!-- Icon + Title -->
            <div class="flex items-center gap-3">
                <img src="/images/icons/asaba-hustle.png" class="w-10 h-10 rounded-xl" />
                <div>
                    <h3 class="font-semibold text-gray-900">Install Asaba Hustle</h3>
                    <p class="text-xs text-gray-500">Get faster access & offline support</p>
                </div>
            </div>

            <!-- Android / Chrome -->
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-gray-800">📱 Android (Chrome)</h4>
                <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                    <li>Tap the menu (⋮) at the top right</li>
                    <li>Select <strong>Install App</strong></li>
                    <li>Tap <strong>Install</strong></li>
                </ol>
            </div>

            <!-- Firefox -->
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-gray-800">🦊 Firefox</h4>
                <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                    <li>Tap the menu (⋮)</li>
                    <li>Select <strong>Add to Home screen</strong></li>
                    <li>Confirm installation</li>
                </ol>
            </div>

            <!-- iOS Safari -->
            <div class="space-y-2">
                <h4 class="text-sm font-semibold text-gray-800">🍎 iPhone (Safari)</h4>
                <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                    <li>Tap the share icon <span class="font-bold">⬆️</span></li>
                    <li>Select <strong>Add to Home Screen</strong></li>
                    <li>Tap <strong>Add</strong></li>
                </ol>
            </div>

            <!-- CTA -->
            <div class="pt-2">
                <button onclick="closeModal('installGuideModal')"
                    class="w-full bg-orange-500 text-white py-2.5 rounded-xl font-semibold active:scale-95 transition">
                    Got it
                </button>
            </div>

        </div>
    </x-modal>
</body>

</html>
