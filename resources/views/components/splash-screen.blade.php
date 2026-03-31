<div id="app-splash"
    {{ $attributes->merge(['class' => 'fixed inset-0 z-[2147483647] bg-white flex flex-col items-center justify-center pointer-events-auto']) }}>
    <style>
        @keyframes soft-pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.08);
                opacity: 0.8;
            }
        }

        #app-splash {
            isolation: isolate;
        }

        .animate-soft-pulse {
            animation: soft-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Sleek loading line */
        .loading-bar {
            width: 100px;
            height: 2px;
            background: #f3f4f6;
            position: relative;
            overflow: hidden;
            border-radius: 10px;
        }

        .loading-bar::after {
            content: "";
            position: absolute;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, #ff7a00, transparent);
            animation: loading-slide 1.5s infinite;
        }

        @keyframes loading-slide {
            100% {
                left: 100%;
            }
        }
    </style>

    <div class="flex flex-col items-center gap-6">
        <!-- Logo with soft pulse -->
        <div class="relative">
            <div class="absolute inset-0 bg-orange-100 rounded-full blur-2xl opacity-40 animate-soft-pulse"></div>
            <img src="{{ asset('images/icons/asaba-hustle.svg') }}" class="w-20 h-20 relative z-10 animate-soft-pulse"
                alt="Logo" />
        </div>

        <div class="flex flex-col items-center gap-3">
            <p class="uppercase tracking-[0.2em] text-[10px] font-bold text-orange-600 opacity-80">
                Asaba Hustle
            </p>
            <!-- Modern loading indicator -->
            <div class="loading-bar"></div>
        </div>
    </div>
</div>

<script>
    document.documentElement.style.overflow = 'hidden';

    window.addEventListener('load', () => {
        const splash = document.getElementById('app-splash');

        if (!splash) return;

        setTimeout(() => {
            splash.style.transition = "opacity 0.4s ease";
            splash.style.opacity = "0";

            setTimeout(() => {
                splash.remove();
                document.documentElement.style.overflow = '';
            }, 400);

        }, 600); // shorter delay = snappier UX
    });
</script>
