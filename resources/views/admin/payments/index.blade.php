@extends('admin.layout')

@section('title', 'Payments')
@section('admin-page-title', 'Payments')

@section('content')
    <section class="rounded-[2.2rem] border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-xl">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-[var(--brand)]">Payments Review</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950 sm:text-3xl">Audit money movement across the marketplace</h2>
                <p class="mt-3 max-w-2xl text-sm font-medium leading-7 text-slate-500">
                    Filter by reference, user, job, payment status, and method to spot issues quickly.
                </p>
            </div>

            <div class="rounded-[1.6rem] border border-slate-100 bg-slate-50 px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-slate-400">Results</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ number_format($summary['total']) }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-500">Payments matching the current filters</p>
            </div>
        </div>

        <div class="mt-6 grid gap-3 md:grid-cols-3 xl:grid-cols-4">
            <div class="rounded-[1.6rem] border border-emerald-100 bg-emerald-50 px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-emerald-600">Settled Amount</p>
                <p class="mt-2 text-2xl font-black text-slate-950">N{{ number_format($summary['settled_amount'], 2) }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-500">Successful payment records</p>
            </div>
            <div class="rounded-[1.6rem] border border-slate-100 bg-slate-50 px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-500">Manual Methods</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ number_format($summary['manual_count']) }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-500">Cash and transfer records</p>
            </div>
            <div class="rounded-[1.6rem] border border-slate-100 bg-slate-50 px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-500">Gateway Methods</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ number_format($summary['gateway_count']) }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-500">Legacy Paystack and Flutterwave records</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.payments.index') }}" class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-[2fr_1fr_1fr_auto]">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search reference, user, job"
                class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-[var(--brand)]">

            <select name="status"
                class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-[var(--brand)]">
                <option value="">All statuses</option>
                <option value="awaiting_confirmation" @selected(request('status') === 'awaiting_confirmation')>Awaiting Confirmation</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="successful" @selected(request('status') === 'successful')>Successful</option>
                <option value="failed" @selected(request('status') === 'failed')>Failed</option>
            </select>

            <select name="payment_method"
                class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-[var(--brand)]">
                <option value="">All methods</option>
                <option value="cash" @selected(request('payment_method') === 'cash')>Cash</option>
                <option value="transfer" @selected(request('payment_method') === 'transfer')>Transfer</option>
                <option value="paystack" @selected(request('payment_method') === 'paystack')>Paystack (Legacy)</option>
                <option value="flutterwave" @selected(request('payment_method') === 'flutterwave')>Flutterwave (Legacy)</option>
            </select>

            <div class="flex gap-3">
                <button class="h-12 rounded-2xl bg-slate-950 px-5 text-xs font-black uppercase tracking-[0.24em] text-white">
                    Apply
                </button>
                <a href="{{ route('admin.payments.index') }}"
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
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Reference</th>
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">User</th>
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Job</th>
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Amount</th>
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Status</th>
                        <th class="px-3 py-4 text-left text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Method</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($payments as $payment)
                        <tr>
                            <td class="px-3 py-4 text-sm font-black text-slate-900">{{ $payment->reference }}</td>
                            <td class="px-3 py-4 text-sm font-semibold text-slate-600">{{ $payment->user->name ?? 'n/a' }}</td>
                            <td class="px-3 py-4 text-sm font-semibold text-slate-600">{{ $payment->job->title ?? 'n/a' }}</td>
                            <td class="px-3 py-4 text-sm font-black text-slate-900">N{{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="px-3 py-4">
                                <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $payment->status === 'successful' ? 'bg-emerald-50 text-emerald-700' : ($payment->status === 'awaiting_confirmation' ? 'bg-blue-50 text-blue-700' : ($payment->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700')) }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-sm font-semibold capitalize text-slate-600">{{ $payment->payment_method }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid gap-4 lg:hidden">
            @foreach ($payments as $payment)
                <article class="rounded-[1.75rem] border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-black text-slate-900">{{ $payment->reference }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $payment->user->name ?? 'n/a' }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $payment->status === 'successful' ? 'bg-emerald-50 text-emerald-700' : ($payment->status === 'awaiting_confirmation' ? 'bg-blue-50 text-blue-700' : ($payment->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700')) }}">
                            {{ $payment->status }}
                        </span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs font-semibold text-slate-500">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Amount</p>
                            <p class="mt-1 text-slate-700">N{{ number_format((float) $payment->amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Method</p>
                            <p class="mt-1 capitalize text-slate-700">{{ $payment->payment_method }}</p>
                        </div>
                    </div>
                    <p class="mt-4 text-xs font-semibold text-slate-500">{{ $payment->job->title ?? 'n/a' }}</p>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $payments->withQueryString()->links() }}
        </div>
    </section>
@endsection
