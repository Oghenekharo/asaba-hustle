<!-- SLIM SERVICES SECTION: BLENDED GLASS STYLE -->
<section id="services" class="relative py-20 bg-slate-50 overflow-hidden">

    <!-- Subtle Background Radial Glow -->
    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-orange-100/30 blur-[120px] rounded-full pointer-events-none">
    </div>

    <div class="relative max-w-7xl mx-auto px-6">

        <!-- Header -->
        <div class="flex items-center justify-between mb-12">
            <div class="flex items-center gap-4">
                <div class="h-10 w-1 bg-gradient-to-b from-orange-500 to-rose-500 rounded-full"></div>
                <div>
                    <h2 class="text-xl font-black tracking-tight text-slate-900 leading-none">Popular Services</h2>
                    <p class="text-[10px] text-slate-400 mt-1.5 uppercase tracking-[0.2em] font-bold">Verified
                        Professionals</p>
                </div>
            </div>
            <a href="#"
                class="group flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-orange-600 transition-all">
                View All <i data-lucide="arrow-right"
                    class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1"></i>
            </a>
        </div>

        <!-- Grid: Glass Cards -->
        <div class="flex overflow-x-auto pb-8 md:pb-0 md:grid md:grid-cols-4 gap-5 no-scrollbar">
            @php
                $services = [
                    ['name' => 'Cleaning', 'icon' => 'sparkles', 'color' => 'text-blue-500', 'bg' => 'bg-blue-500/10'],
                    [
                        'name' => 'Plumbing',
                        'icon' => 'wrench',
                        'color' => 'text-orange-500',
                        'bg' => 'bg-orange-500/10',
                    ],
                    ['name' => 'Electrical', 'icon' => 'zap', 'color' => 'text-amber-500', 'bg' => 'bg-amber-500/10'],
                    ['name' => 'Cooking', 'icon' => 'utensils', 'color' => 'text-rose-500', 'bg' => 'bg-rose-500/10'],
                    [
                        'name' => 'Gardening',
                        'icon' => 'leaf',
                        'color' => 'text-emerald-500',
                        'bg' => 'bg-emerald-500/10',
                    ],
                    ['name' => 'Moving', 'icon' => 'package', 'color' => 'text-indigo-500', 'bg' => 'bg-indigo-500/10'],
                    ['name' => 'Laundry', 'icon' => 'droplets', 'color' => 'text-sky-500', 'bg' => 'bg-sky-500/10'],
                    [
                        'name' => 'AC Repair',
                        'icon' => 'snowflake',
                        'color' => 'text-cyan-500',
                        'bg' => 'bg-cyan-500/10',
                    ],
                ];
            @endphp

            @foreach ($services as $service)
                <!-- Card with Glass Effect -->
                <div
                    class="min-w-[170px] cursor-pointer md:min-w-0 group relative p-6 rounded-[2rem] bg-white/60 backdrop-blur-xl border border-slate-200 transition-all duration-500 hover:bg-white hover:border-orange-200 hover:shadow-[0_30px_60px_-15px_rgba(0,0,0,0.08)] hover:-translate-y-1">

                    <div class="flex items-center justify-between mb-10">
                        <div
                            class="p-3 rounded-2xl {{ $service['bg'] }} {{ $service['color'] }} transition-all group-hover:scale-110">
                            <i data-lucide="{{ $service['icon'] }}" class="w-5 h-5"></i>
                        </div>
                        <div
                            class="opacity-0 group-hover:opacity-100 transition-all -translate-y-2 group-hover:translate-y-0">
                            <div class="p-1 rounded-full bg-slate-900 text-white">
                                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <h3 class="font-bold text-sm text-slate-900 mb-1.5">{{ $service['name'] }}</h3>
                        <div class="flex items-center gap-1.5">
                            <div class="h-1.5 w-1.5 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]">
                            </div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Available Now</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
