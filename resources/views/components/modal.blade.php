@props(['id', 'title' => '', 'size' => 'max-w-md'])

<div id="{{ $id }}"
    class="js-modal fixed inset-0 z-100 hidden overflow-y-auto px-4 py-10 transition-all duration-300">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0 js-modal-backdrop"></div>

    <!-- Modal Content -->
    <div
        class="relative mx-auto w-full {{ $size }} transform rounded-[2.5rem] bg-white p-8 shadow-2xl transition-all scale-95 opacity-0 js-modal-content">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-black tracking-tight text-slate-900 js-modal-title">{{ $title }}</h3>
            <button onclick="closeModal('{{ $id }}')"
                class="rounded-xl cursor-pointer p-2 bg-red-500 text-white hover:bg-red-400 hover:text-white/85 transition-colors">
                <i data-lucide="x" class="h-6 w-6"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="js-modal-body text-slate-600 font-medium">
            {{ $slot }}
        </div>
    </div>
</div>
