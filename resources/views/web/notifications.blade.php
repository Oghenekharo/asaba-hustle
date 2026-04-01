@extends('layouts.app', ['title' => 'Notifications | Asaba Hustle'])

@section('content')
    <div class="mx-auto max-w-5xl pt-20" id="notifications-page" data-read-url="{{ route('web.app.notifications.read') }}"
        data-read-all-url="{{ route('web.app.notifications.read-all') }}">
        <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Activity Center</p>
                <h1 class="mt-2 text-4xl font-black tracking-tight text-slate-900">All Notifications</h1>
                <p class="mt-3 max-w-2xl text-sm font-medium leading-relaxed text-slate-500">
                    Review every message, hiring update, and account activity in one place.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3">
                    <p class="text-[10px] font-black uppercase tracking-widest text-orange-500">Unread</p>
                    <p id="notifications-page-unread-count" class="mt-2 text-2xl font-black text-slate-900">
                        {{ $unreadNotificationsCount }}
                    </p>
                </div>

                <button type="button" id="notifications-page-mark-all"
                    class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-4 text-[10px] font-black uppercase tracking-widest text-white transition hover:opacity-90">
                    <i data-lucide="check-check" class="h-4 w-4"></i>
                    Mark All Read
                </button>
            </div>
        </div>

        <div id="notifications-page-feedback" class="mt-6 hidden rounded-2xl border px-4 py-3 text-sm"></div>

        <section class="mt-8 rounded-[2rem] border border-slate-100 bg-white p-4 shadow-sm md:p-6">
            <div class="space-y-3">
                @forelse ($notifications as $notification)
                    @php
                        $meta = \App\Support\NotificationType::meta($notification->type);
                        $readStateClass = match ($meta['color']) {
                            'blue' => 'border-slate-100 bg-blue-50',
                            'purple' => 'border-slate-100 bg-purple-50',
                            'green' => 'border-slate-100 bg-green-50',
                            'yellow' => 'border-slate-100 bg-yellow-50',
                            default => 'border-slate-100 bg-gray-50',
                        };
                    @endphp
                    <article
                        class="js-notification-page-item rounded-[1.75rem] border px-5 py-5 transition {{ $notification->is_read ? $readStateClass : 'border-orange-100 bg-orange-50/50' }}"
                        data-notification-id="{{ $notification->id }}">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="text-lg">{{ $meta['icon'] }}</span>
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $notification->is_read ? 'bg-slate-100 text-slate-500' : 'bg-orange-100 text-orange-600' }}">
                                        {{ $notification->is_read ? 'Read' : 'Unread' }}
                                    </span>

                                    @if ($notification->type)
                                        <span
                                            class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-500">
                                            {{ str_replace('_', ' ', $notification->type) }}
                                        </span>
                                    @endif
                                </div>

                                <h2 class="mt-4 text-lg font-black tracking-tight text-slate-900">
                                    {{ $notification->title }}
                                </h2>
                                <p class="mt-2 text-sm font-medium leading-relaxed text-slate-600">
                                    {{ $notification->message }}
                                </p>
                            </div>

                            <div class="flex shrink-0 flex-col items-start gap-3 md:items-end">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-300">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </p>

                                @if ($notification->action_url)
                                    <a href="{{ $notification->action_url }}"
                                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-white transition hover:opacity-90">
                                        <i data-lucide="external-link" class="h-4 w-4"></i>
                                        {{ $notification->action_label ?: 'Open' }}
                                    </a>
                                @endif

                                @if (!$notification->is_read)
                                    <button type="button"
                                        class="js-notification-page-read inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:border-[var(--brand)] hover:text-[var(--brand)]"
                                        data-notification-id="{{ $notification->id }}">
                                        <i data-lucide="check" class="h-4 w-4"></i>
                                        Mark Read
                                    </button>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <x-empty-state title="No notifications yet"
                        subtitle="Your messages, hiring updates, and account activity will appear here." icon="bell-off" />
                @endforelse
            </div>

            @if ($notifications->hasPages())
                <div
                    class="mt-8 flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ $notifications->previousPageUrl() ?: '#' }}"
                        class="{{ $notifications->previousPageUrl() ? '' : 'pointer-events-none opacity-40' }} inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:border-[var(--brand)] hover:text-[var(--brand)]">
                        <i data-lucide="arrow-left" class="h-4 w-4"></i>
                        Newer
                    </a>

                    <p class="text-center text-[10px] font-black uppercase tracking-widest text-slate-300">
                        Cursor Pagination
                    </p>

                    <a href="{{ $notifications->nextPageUrl() ?: '#' }}"
                        class="{{ $notifications->nextPageUrl() ? '' : 'pointer-events-none opacity-40' }} inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-[10px] font-black uppercase tracking-widest text-slate-700 transition hover:border-[var(--brand)] hover:text-[var(--brand)]">
                        Older
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                </div>
            @endif
        </section>
    </div>
@endsection
