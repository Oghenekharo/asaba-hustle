@extends('admin.layout')

@section('title', 'Activity Logs')
@section('admin-page-title', 'Activity Logs')

@section('content')
    <section class="rounded-[2.2rem] border border-[var(--line)] bg-white p-6 shadow-sm backdrop-blur-xl">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase  text-[var(--brand)]">Audit Trail</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950 sm:text-3xl">Inspect account and system activity</h2>
                <p class="mt-3 max-w-2xl text-sm font-medium leading-7 text-slate-500">
                    Search by user or IP, then narrow by action label to investigate moderation, security, or support
                    issues.
                </p>
            </div>

            <div class="rounded-[1.6rem] border border-slate-100 bg-slate-50 px-5 py-4">
                <p class="text-[10px] font-black uppercase  text-slate-400">Results</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ number_format($logs->total()) }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-500">Log entries matching the current filters</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.activity.index') }}"
            class="mt-6 grid gap-3 lg:grid-cols-[2fr_1fr_auto]">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search user or IP"
                class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-[var(--brand)]">
            <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter action"
                class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-[var(--brand)]">
            <div class="flex gap-3">
                <button
                    class="h-12 rounded-2xl bg-slate-950 px-5 text-xs font-black uppercase  text-white">
                    Apply
                </button>
                <a href="{{ route('admin.activity.index') }}"
                    class="inline-flex h-12 items-center justify-center rounded-2xl border border-slate-200 px-5 text-xs font-black uppercase  text-slate-500 transition hover:border-slate-300 hover:text-slate-900">
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section class="mt-6 rounded-[2.2rem] border border-[var(--line)] bg-white p-6 shadow-sm backdrop-blur-xl">
        <div class="mt-2 hidden overflow-hidden lg:block">
            <table class="min-w-full border-collapse text-sm">
                <thead class="bg-slate-50">
                    <tr class="border-b border-slate-100">
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase  text-slate-400">
                            Date</th>
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase  text-slate-400">
                            User</th>
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase  text-slate-400">
                            Action</th>
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase  text-slate-400">IP
                        </th>
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase  text-slate-400">
                            Metadata</th>
                    </tr>
                </thead>
                {{-- <tbody class="divide-y divide-slate-100">
                    @foreach ($logs as $log)
                        <tr class="align-top">
                            <td class="px-3 py-4 text-sm font-semibold text-slate-600">{{ $log->created_at }}</td>
                            <td class="px-3 py-4 text-sm font-semibold text-slate-600">{{ $log->user->name ?? 'System' }}</td>
                            <td class="px-3 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase  text-slate-600">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-sm font-semibold text-slate-600">{{ $log->ip_address ?? 'n/a' }}</td>
                            <td class="px-3 py-4 text-xs font-medium leading-6 text-slate-500">
                                {{ $log->metadata ? json_encode($log->metadata) : 'n/a' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody> --}}
                <tbody class="divide-y divide-slate-50">
                    @foreach ($logs as $log)
                        @php
                            $logActionStyle = str_contains($log->action, 'payment') ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                : (str_contains($log->action, 'rated') ? 'bg-amber-50 text-amber-700 border-amber-100'
                                : (str_contains($log->action, 'job') ? 'bg-blue-50 text-blue-700 border-blue-100'
                                : (str_contains($log->action, 'login') ? 'bg-violet-50 text-violet-700 border-violet-100'
                                : (str_contains($log->action, 'negotiation') ? 'bg-cyan-50 text-cyan-700 border-cyan-100'
                                : (str_contains($log->action, 'sent') ? 'bg-teal-50 text-teal-700 border-teal-100' : 'bg-slate-100 text-slate-600 border-slate-200')))));
                        @endphp
                        <tr class="group cursor-pointer align-top transition-colors hover:bg-[var(--surface-soft)]"
                            onclick="openLogModal(@js($log))">
                            <td class="px-4 py-5 text-xs font-semibold text-slate-500">
                                {{ $log->created_at?->format('M j, Y') }}
                                <p class="mt-1 text-[10px] font-bold text-slate-400">{{ $log->created_at?->format('g:i A') }}</p>
                            </td>

                            <td class="px-2 py-5">
                                <div class="flex items-center gap-3">
                                    <x-avatar :user="$log->user" :name="$log->user->name ?? 'System'" size="h-10 w-10" text="text-[10px]" rounded="rounded-xl" class="shadow-sm border border-[var(--line)]" />
                                    <span class="text-xs font-black text-slate-700">{{ $log->user->name ?? 'System' }}</span>
                                </div>
                            </td>

                            <td class="px-2 py-5">
                                <span class="inline-flex rounded-xl border px-2.5 py-1 text-[9px] font-black uppercase {{ $logActionStyle }}">
                                    {{ $log->action }}
                                </span>
                            </td>

                            <td class="px-2 py-5 text-xs font-semibold text-slate-500">
                                {{ $log->ip_address ?? 'n/a' }}
                            </td>

                            <td class="px-2 py-5 pr-5 text-[10px] font-bold uppercase text-[var(--brand)]">
                                <span class="inline-flex items-center gap-2">View details <i data-lucide="arrow-up-right" class="h-3.5 w-3.5"></i></span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- <div class="grid gap-4 lg:hidden">
            @foreach ($logs as $log)
                <article class="rounded-[1.75rem] border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-black text-slate-900">{{ $log->user->name ?? 'System' }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $log->created_at }}</p>
                        </div>
                        <span
                            class="rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase  text-slate-600">
                            {{ $log->action }}
                        </span>
                    </div>
                    <p class="mt-4 text-xs font-semibold text-slate-500">IP: {{ $log->ip_address ?? 'n/a' }}</p>
                    <p class="mt-3 text-sm font-medium leading-6 text-slate-500">
                        {{ $log->metadata ? json_encode($log->metadata) : 'n/a' }}
                    </p>
                </article>
            @endforeach
        </div> --}}
        <div class="mt-6 grid gap-6 lg:hidden">
            @foreach ($logs as $log)
                @php
                    $logActionStyle = str_contains($log->action, 'payment') ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                        : (str_contains($log->action, 'rated') ? 'bg-amber-50 text-amber-700 border-amber-100'
                        : (str_contains($log->action, 'job') ? 'bg-blue-50 text-blue-700 border-blue-100'
                        : (str_contains($log->action, 'login') ? 'bg-violet-50 text-violet-700 border-violet-100'
                        : (str_contains($log->action, 'negotiation') ? 'bg-cyan-50 text-cyan-700 border-cyan-100'
                        : (str_contains($log->action, 'sent') ? 'bg-teal-50 text-teal-700 border-teal-100' : 'bg-slate-100 text-slate-600 border-slate-200')))));
                @endphp
                <article onclick="openLogModal(@js($log))"
                    class="relative cursor-pointer overflow-hidden rounded-[2.5rem] border border-[var(--line)] bg-white p-6 shadow-sm transition-all active:scale-[0.98]">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-4">
                            <x-avatar :user="$log->user" :name="$log->user->name ?? 'System'" size="h-14 w-14" text="text-xs" rounded="rounded-2xl" class="shadow-md border border-[var(--line)]" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-[var(--ink)]">{{ $log->user->name ?? 'System' }}</p>
                                <p class="mt-1 text-[10px] font-bold uppercase text-slate-400">{{ $log->created_at?->format('M j, Y · g:i A') }}</p>
                            </div>
                        </div>

                        <span class="shrink-0 rounded-xl border px-2.5 py-1 text-[9px] font-black uppercase {{ $logActionStyle }}">
                            {{ $log->action }}
                        </span>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-4 rounded-3xl bg-[var(--surface-soft)] p-4">
                        <div>
                            <p class="text-[8px] font-black uppercase opacity-50">IP Address</p>
                            <p class="mt-1 truncate text-[11px] font-black text-[var(--ink)]">{{ $log->ip_address ?? 'n/a' }}</p>
                        </div>
                        <div class="border-l border-[var(--line)] pl-4">
                            <p class="text-[8px] font-black uppercase opacity-50">Record</p>
                            <p class="mt-1 text-[11px] font-black text-[var(--ink)]">#{{ $log->id }}</p>
                        </div>
                    </div>
                    <p class="mt-4 flex items-center gap-2 px-1 text-[10px] font-black uppercase text-[var(--brand)]">Tap to view details <i data-lucide="arrow-right" class="h-3 w-3"></i></p>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $logs->withQueryString()->links('partials.pagination') }}
        </div>
    </section>

    <x-modal id="activityModal" title="Activity" size="max-w-xl">
        <div class="space-y-4 text-sm">
            <div>
                <p class="text-xs text-slate-400">User</p>
                <p id="user-name" class="font-semibold text-slate-900"></p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Action</p>
                <p id="user-action" class="font-semibold text-slate-900"></p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Date</p>
                <p id="date-logged" class="font-semibold text-slate-900"></p>
            </div>

            <div>
                <p class="text-xs text-slate-400">IP Address</p>
                <p id="ip-address" class="font-semibold text-slate-900"></p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Metadata</p>
                <pre class="mt-2 rounded-xl bg-slate-900 p-3 text-xs text-green-400 overflow-x-auto">
                    <pre id="metadata-content"></pre>
                </pre>
            </div>
        </div>
    </x-modal>
@endsection
