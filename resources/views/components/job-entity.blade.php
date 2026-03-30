@props([
    'user' => null,
    'modal' => 'assignedWorkerModal',
    'canChat' => false,
    'isOwner' => false,
    'existingConversation' => null,
    'partnerLabel' => null,
])
<section class="p-6 rounded-[2.5rem] bg-white border border-slate-100 shadow-sm text-center">
    <p class="text-[10px] font-black uppercase opacity-30 mb-6">{{ $isOwner ? 'Assigned Worker' : 'Posted By' }}</p>
    <div class="inline-flex relative mb-4">
        <x-avatar :user="$user" size="h-20 w-20" rounded="rounded-3xl" text="text-3xl" class="shadow-xl" />
        @if ($user->is_verified)
            <div
                class="absolute -bottom-1 -right-1 h-7 w-7 bg-green-500 border-4 border-white rounded-full flex items-center justify-center text-white">
                <i data-lucide="check" class="w-3 h-3"></i>
            </div>
        @endif
    </div>
    <h3 class="text-xl font-black tracking-tight text-[var(--ink)]">{{ $user->name }}</h3>
    <div class="flex items-center justify-center gap-1 text-orange-400">
        <i data-lucide="star" class="w-3 h-3 fill-current"></i>
        <span class="text-[10px] font-bold">{{ $user->rating }}</span>
    </div>
    <div class="flex items-center justify-center gap-4 py-4 border-t border-slate-50">
        <div>
            <p class="text-[10px] font-black opacity-30 uppercase">Rating</p>
            <p class="font-black flex items-center gap-1 text-sm">
                <i data-lucide="star" class="w-3 h-3 fill-current"></i>
                {{ $user->average_rating > 0 ? $user->average_rating : 'New' }}
            </p>
        </div>
        <div class="w-px h-6 bg-slate-100"></div>
        <div>
            <p class="text-[10px] font-black opacity-30 uppercase">Joined</p>
            <p class="font-black text-sm">
                {{ \Carbon\Carbon::parse($user->created_at)->format('M Y') }}</p>
        </div>
    </div>
    <div class="mt-4 grid gap-1">
        <button type="button" onclick="openModal('{{ $modal }}')"
            class="flex items-center justify-center gap-2 py-4 rounded-2xl cursor-pointer bg-black text-white/90 font-black text-[10px] uppercase hover:bg-[var(--brand)] hover:text-white transition-all">
            <i data-lucide="user-round-search" class="w-4 h-4"></i>
            view Profile
        </button>
        @if ($canChat)
            @if ($existingConversation)
                <a href="{{ route('web.app.conversations', ['conversation' => $existingConversation->uuid]) }}"
                    class="w-full cursor-pointer mt-4 flex items-center justify-center gap-3 py-4 rounded-2xl bg-[var(--surface-soft)] text-[var(--brand)] font-black text-[10px] uppercase hover:bg-[var(--brand)] hover:text-white transition-all">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    Open Chat with {{ $partnerLabel }}
                </a>
            @else
                <button type="button" onclick="openModal('jobChatStarterModal')"
                    class="w-full cursor-pointer mt-4 flex items-center justify-center gap-3 py-4 rounded-2xl bg-[var(--surface-soft)] text-[var(--brand)] font-black text-[10px] uppercase hover:bg-[var(--brand)] hover:text-white transition-all">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    Start Chat with {{ $partnerLabel }}
                </button>
            @endif
        @endif
    </div>
</section>
