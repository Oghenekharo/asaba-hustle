    {{-- <!-- HERO: BENTO STYLE -->
    <header class="relative pt-10 pb-20 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-12 gap-8 items-center">

            <div class="lg:col-span-7">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-50 border border-orange-100 mb-8">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                    </span>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-orange-600">Verified
                        Marketplace</span>
                </div>

                <h1 class="text-4xl md:text-6xl font-black leading-[1.1] tracking-tight text-slate-900 mb-6">
                    Find the best <br>
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-500 italic">Hustlers</span>
                    in town.
                </h1>

                <p class="text-base text-slate-500 mb-10 max-w-lg leading-relaxed font-medium">
                    From professional plumbing to home cooking, we connect you with verified local talent instantly.
                    API-driven, speed-optimized.
                </p>

                <div class="flex flex-col sm:flex-row gap-4">
                    <button
                        class="px-8 py-4 rounded-[2rem] bg-orange-600 text-white font-bold text-sm uppercase shadow-xl shadow-orange-600/20 hover:-translate-y-1 transition-all active:scale-95">
                        Explore Providers
                    </button>
                    <button
                        class="px-8 py-4 rounded-[2rem] bg-white border border-slate-200 text-slate-900 font-bold text-sm uppercase hover:bg-slate-50 transition-all">
                        Learn More
                    </button>
                </div>
            </div>

            <!-- MASONRY IMAGE PREVIEW -->
            <div class="lg:col-span-5 relative">
                <div
                    class="relative z-10 rounded-[3rem] overflow-hidden shadow-2xl rotate-2 transition-transform hover:rotate-0 duration-700">
                    <img src="https://images.unsplash.com/photo-1770131091438-c5c4b89ea264?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                        class="w-full h-full object-cover grayscale-[0.2] hover:grayscale-0 transition-all">
                </div>
                <!-- Floating Card -->
                <div
                    class="absolute -bottom-8 -left-8 z-20 bg-white/80 backdrop-blur-xl p-6 rounded-[2rem] border border-white shadow-2xl max-w-[240px]">
                    <div class="flex items-center gap-4">
                        <div
                            class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold">
                            ✓</div>
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider">Skill Verified</p>
                            <p class="text-[10px] text-slate-500">100% Trusted Providers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header> --}}

    <!-- COMPACT HERO: MODERN BENTO -->
    <header class="relative pt-28 pb-16 md:pt-36 md:pb-24 overflow-hidden">
        <!-- Background Glow -->
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 -z-10 w-full max-w-4xl h-64 bg-orange-100/40 blur-[100px] rounded-full">
        </div>

        <div class="max-w-6xl mx-auto px-6 grid lg:grid-cols-2 gap-10 items-center">

            <!-- Left: Concise Content -->
            <div class="text-center lg:text-left">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 mb-6 transition-all hover:bg-white">
                    <span class="flex h-1.5 w-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Verified
                        Marketplace</span>
                </div>

                <h1 class="text-4xl md:text-5xl font-black leading-[1.1] tracking-tighter text-slate-900 mb-4">
                    Find the best <br>
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-500 italic">Hustlers</span>
                    in town.
                </h1>

                <p class="text-sm md:text-base text-slate-500 mb-8 max-w-md mx-auto lg:mx-0 leading-relaxed">
                    Skip the search. Connect with top-rated pros in your city through our high-speed marketplace.
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3">
                    <a href="{{ route('web.register') }}"
                        class="px-6 py-3.5 rounded-2xl bg-slate-900 text-white font-bold text-xs uppercase tracking-widest shadow-lg shadow-slate-900/10 active:scale-95 transition-all">
                        Get Started
                    </a>
                    <a href="{{ route('login') }}"
                        class="px-6 py-3.5 rounded-2xl bg-white border border-slate-200 text-slate-600 font-bold text-xs uppercase tracking-widest hover:bg-slate-50 transition-all">
                        Login
                    </a>
                </div>
            </div>

            <!-- Right: Compact Bento Visual -->
            <div class="relative ">
                <div
                    class="relative z-10 rounded-[3rem] overflow-hidden shadow-2xl rotate-2 transition-transform hover:rotate-0 duration-700">
                    <img src="https://images.unsplash.com/photo-1770131091438-c5c4b89ea264?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                        class="w-full h-full object-cover grayscale-[0.2] hover:grayscale-0 transition-all">
                </div>
                <!-- Floating Card -->
                <div
                    class="absolute -bottom-8 -left-8 z-20 bg-white/80 backdrop-blur-xl p-6 rounded-[2rem] border border-white shadow-2xl max-w-[240px]">
                    <div class="flex items-center gap-4">
                        <div
                            class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold">
                            ✓</div>
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider">Skill Verified</p>
                            <p class="text-[10px] text-slate-500">100% Trusted Providers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
