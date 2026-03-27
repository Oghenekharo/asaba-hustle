@extends('layouts.app', ['title' => 'Messages | Asaba Hustle'])

@section('content')
    <div class="max-w-6xl pt-20 mx-auto lg:h-[calc(100vh-180px)] relative">
        <div class="mb-4 flex items-center justify-between lg:hidden">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Messaging</p>
                <h1 class="text-2xl font-black tracking-tight text-[var(--ink)]">Inbox</h1>
            </div>
            <button type="button" id="mobile-conversations-toggle"
                class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--ink)] shadow-sm border border-slate-100">
                <i data-lucide="panel-left-open" class="h-4 w-4 text-[var(--brand)]"></i>
                Conversations
            </button>
        </div>

        <div id="mobile-conversations-overlay"
            class="fixed inset-0 z-30 hidden bg-slate-900/35 backdrop-blur-[2px] lg:hidden"></div>

        <div class="flex gap-6 h-full">

            <!-- Left Sidebar: Conversations List -->
            <aside id="mobile-conversations-panel"
                class="fixed inset-y-0 left-0 z-40 flex w-[88vw] max-w-sm -translate-x-[105%] flex-col bg-white/95 backdrop-blur-xl border-r border-white shadow-2xl transition-transform duration-300 lg:static lg:z-auto lg:w-80 lg:max-w-none lg:translate-x-0 lg:rounded-[2.5rem] lg:border lg:border-white lg:shadow-sm overflow-hidden">
                <div class="flex items-center justify-between pt-24 md:py-6 px-6 border-b border-slate-50">
                    <div>
                        <h1 class="text-xl font-black tracking-tighter text-[var(--ink)]">Inbox</h1>
                        <p class="text-[10px] font-black uppercase tracking-widest opacity-30 mt-1">Active Hustles</p>
                    </div>
                    <button type="button" id="mobile-conversations-close"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 lg:hidden">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-2 custom-scrollbar">
                    @forelse($conversations as $convo)
                        @php
                            $otherUser = auth()->id() === $convo->client_id ? $convo->worker : $convo->client;
                            $lastMsg = $convo->messages->first();
                        @endphp
                        <button type="button"
                            class="js-conversation-trigger w-full flex items-center gap-4 p-4 rounded-3xl transition-all hover:bg-white hover:shadow-lg border border-gray-200 cursor-pointer hover:border-(--brand)/10 group"
                            data-conversation-id="{{ $convo->uuid }}" data-job-id="{{ $convo->job_id }}"
                            data-job-url="{{ route('web.app.jobs.show', ['job' => $convo->job]) }}"
                            data-other-user-name="{{ $otherUser->name ?? 'Deleted User' }}"
                            data-other-user-avatar="{{ substr($otherUser->name ?? 'U', 0, 1) }}"
                            data-other-user-avatar-url="{{ $otherUser?->profile_photo ? asset('storage/' . $otherUser->profile_photo) : '' }}"
                            data-job-title="{{ $convo->job->title ?? 'Untitled Hustle' }}"
                            data-messages-url="{{ route('web.app.conversations.messages', ['conversation' => $convo]) }}"
                            data-read-url="{{ route('web.app.conversations.read', ['conversation' => $convo]) }}">

                            <x-avatar :user="$otherUser" size="h-12 w-12" rounded="rounded-2xl" text="text-sm"
                                class="shadow-sm transition-transform group-hover:rotate-3" />

                            <div class="flex-1 min-w-0 text-left">
                                <div class="flex justify-between items-center mb-0.5">
                                    <h3 class="text-xs font-black text-[var(--ink)] truncate js-conversation-name">
                                        {{ $otherUser->name ?? 'Deleted User' }}</h3>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span
                                            class="js-unread-message-badge {{ ($convo->unread_messages_count ?? 0) > 0 ? '' : 'hidden' }} min-w-5 h-5 px-1 rounded-full bg-[var(--brand)] text-white text-[9px] font-black flex items-center justify-center">
                                            {{ ($convo->unread_messages_count ?? 0) > 9 ? '9+' : $convo->unread_messages_count ?? 0 }}
                                        </span>
                                        <span class="js-conversation-time text-[9px] font-bold opacity-30 uppercase">
                                            {{ $lastMsg ? $lastMsg->created_at->diffForHumans(null, true) : '' }}
                                        </span>
                                    </div>
                                </div>
                                <p class="js-conversation-preview text-[10px] font-medium opacity-50 truncate">
                                    {{ $lastMsg->message ?? 'No messages yet...' }}
                                </p>
                            </div>
                        </button>
                    @empty
                        <x-empty-state variant="small" title="No messages" icon="message-square-off" />
                    @endforelse
                </div>
            </aside>

            <!-- Right: Active Chat Window -->
            <main
                class="flex min-h-[70vh] flex-1 flex-col bg-white border border-white rounded-[2rem] lg:rounded-[2.5rem] shadow-sm overflow-hidden relative">
                <div id="chat-blank-state" class="absolute inset-0 z-10 bg-white flex items-center justify-center">
                    <x-empty-state variant="large" title="Select a conversation"
                        subtitle="Pick a hustle from the left to start chatting." icon="message-circle" />
                </div>

                <!-- Chat Header -->
                <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-4 min-w-0">
                        <button type="button" id="chat-mobile-conversations-toggle"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 lg:hidden">
                            <i data-lucide="menu" class="h-4 w-4"></i>
                        </button>
                        <div id="active-user-avatar"
                            class="h-10 w-10 shrink-0 overflow-hidden rounded-2xl bg-slate-900 text-white flex items-center justify-center font-black text-xs uppercase">
                            A</div>
                        <div class="min-w-0">
                            <h2 id="active-user-name" class="text-sm font-black text-[var(--ink)] truncate">Loading...</h2>
                            <a id="active-job-title" href="#"
                                class="inline-flex max-w-full text-[9px] font-black uppercase tracking-widest text-[var(--brand)] truncate hover:underline">
                                Loading Hustle...
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div id="messages-container"
                    class="flex-1 overflow-y-auto p-8 space-y-6 custom-scrollbar bg-[var(--surface-soft)]/30"
                    data-current-user-id="{{ auth()->id() }}" data-current-user-name="{{ auth()->user()?->name }}"
                    data-current-user-avatar-url="{{ auth()->user()?->profile_photo ? asset('storage/' . auth()->user()->profile_photo) : '' }}">
                    <!-- Messages injected via AJAX -->
                </div>

                <!-- Message Input -->
                <div class="p-6 bg-white border-t border-slate-50">
                    <form id="send-message-form" class="relative flex items-end gap-3"
                        data-send-url="{{ route('web.app.messages.send') }}">
                        <input type="hidden" id="active-conversation-id" name="conversation_uuid">
                        <input type="hidden" id="active-job-id" name="job_id">
                        <x-input type="text" id="message-input" name="message" class="w-full flex-1" icon="mail"
                            placeholder="Type your message..." />
                        <button type="submit" id="send-message-submit"
                            class="h-12 w-12 shrink-0 cursor-pointer rounded-xl bg-[var(--brand)] text-white flex items-center justify-center shadow-lg shadow-orange-500/20 hover:scale-105 active:scale-95 transition-all">
                            <i data-lucide="send" class="w-4 h-4"></i>
                        </button>
                    </form>
                    <x-error class="mt-4" />
                </div>
            </main>
        </div>
    </div>
@endsection
