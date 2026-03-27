@extends('admin.layout')

@section('title', 'Activity Logs')
@section('admin-page-title', 'Activity Logs')

@section('content')
    <section class="rounded-[2.2rem] border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-xl">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-[var(--brand)]">Audit Trail</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950 sm:text-3xl">Inspect account and system activity</h2>
                <p class="mt-3 max-w-2xl text-sm font-medium leading-7 text-slate-500">
                    Search by user or IP, then narrow by action label to investigate moderation, security, or support issues.
                </p>
            </div>

            <div class="rounded-[1.6rem] border border-slate-100 bg-slate-50 px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-slate-400">Results</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ number_format($logs->total()) }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-500">Log entries matching the current filters</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.activity.index') }}" class="mt-6 grid gap-3 lg:grid-cols-[2fr_1fr_auto]">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search user or IP"
                class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-[var(--brand)]">
            <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter action"
                class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-[var(--brand)]">
            <div class="flex gap-3">
                <button class="h-12 rounded-2xl bg-slate-950 px-5 text-xs font-black uppercase tracking-[0.24em] text-white">
                    Apply
                </button>
                <a href="{{ route('admin.activity.index') }}"
                    class="inline-flex h-12 items-center justify-center rounded-2xl border border-slate-200 px-5 text-xs font-black uppercase tracking-[0.24em] text-slate-500 transition hover:border-slate-300 hover:text-slate-900">
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section class="mt-6 rounded-[2.2rem] border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-xl">
        <div class="hidden overflow-x-auto lg:block">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Date</th>
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">User</th>
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Action</th>
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">IP</th>
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Metadata</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($logs as $log)
                        <tr class="align-top">
                            <td class="px-3 py-4 text-sm font-semibold text-slate-600">{{ $log->created_at }}</td>
                            <td class="px-3 py-4 text-sm font-semibold text-slate-600">{{ $log->user->name ?? 'System' }}</td>
                            <td class="px-3 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-600">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-sm font-semibold text-slate-600">{{ $log->ip_address ?? 'n/a' }}</td>
                            <td class="px-3 py-4 text-xs font-medium leading-6 text-slate-500">
                                {{ $log->metadata ? json_encode($log->metadata) : 'n/a' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid gap-4 lg:hidden">
            @foreach ($logs as $log)
                <article class="rounded-[1.75rem] border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-black text-slate-900">{{ $log->user->name ?? 'System' }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $log->created_at }}</p>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-600">
                            {{ $log->action }}
                        </span>
                    </div>
                    <p class="mt-4 text-xs font-semibold text-slate-500">IP: {{ $log->ip_address ?? 'n/a' }}</p>
                    <p class="mt-3 text-sm font-medium leading-6 text-slate-500">
                        {{ $log->metadata ? json_encode($log->metadata) : 'n/a' }}
                    </p>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $logs->withQueryString()->links() }}
        </div>
    </section>
@endsection
