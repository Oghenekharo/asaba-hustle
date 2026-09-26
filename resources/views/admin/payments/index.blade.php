@extends('admin.layout')

@section('title', 'Payments')
@section('admin-page-title', 'Payments')

@section('content')
    <section class="rounded-[2.2rem] border border-[var(--line)] bg-white p-6 shadow-sm backdrop-blur-xl">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase  text-[var(--brand)]">Payments Review</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950 sm:text-3xl">Payment records</h2>
            </div>

            <div class="rounded-[1.6rem] border border-[var(--line)] bg-slate-50 px-5 py-4">
                <p class="text-[10px] font-black uppercase  text-slate-400">Results</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ number_format($summary['total']) }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-500">Matching records</p>
            </div>
        </div>

        <div class="mt-6 grid gap-3 md:grid-cols-3 xl:grid-cols-4">
            <div class="rounded-[1.6rem] border border-[var(--line)] bg-emerald-50 px-5 py-4">
                <p class="text-[10px] font-black uppercase  text-emerald-600">Settled Amount</p>
                <p class="mt-2 text-2xl font-black text-slate-950">N{{ number_format($summary['settled_amount'], 2) }}</p>
            </div>
            <div class="rounded-[1.6rem] border border-[var(--line)] bg-slate-50 px-5 py-4">
                <p class="text-[10px] font-black uppercase  text-slate-500">Manual Methods</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ number_format($summary['manual_count']) }}</p>
            </div>
            <div class="rounded-[1.6rem] border border-[var(--line)] bg-slate-50 px-5 py-4">
                <p class="text-[10px] font-black uppercase  text-slate-500">Gateway Methods</p>
                <p class="mt-2 text-2xl font-black text-slate-950">{{ number_format($summary['gateway_count']) }}</p>
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
                <button class="h-12 rounded-2xl bg-slate-950 px-5 text-xs font-black uppercase  text-white">
                    Apply
                </button>
                <a href="{{ route('admin.payments.index') }}"
                    class="inline-flex h-12 items-center justify-center rounded-2xl border border-slate-200 px-5 text-xs font-black uppercase  text-slate-500 transition hover:border-slate-300 hover:text-slate-900">
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section class="mt-6 rounded-[2.2rem] border border-[var(--line)] bg-white p-6 shadow-sm backdrop-blur-xl">
        <div class="mt-8 hidden overflow-hidden lg:block">
            <table class="min-w-full border-collapse text-sm">
                <thead class="bg-slate-50">
                    <tr class="border-b border-slate-100">
                        <th class="rounded-tl-2xl px-4 py-5 text-left text-[10px] font-black uppercase text-slate-400">Payment</th>
                        <th class="px-2 py-5 text-left text-[10px] font-black uppercase text-slate-400">User</th>
                        <th class="px-2 py-5 text-left text-[10px] font-black uppercase text-slate-400">Job</th>
                        <th class="px-2 py-5 text-left text-[10px] font-black uppercase text-slate-400">Amount</th>
                        <th class="px-2 py-5 text-left text-[10px] font-black uppercase text-slate-400">Status</th>
                        <th class="px-2 py-5 text-left text-[10px] font-black uppercase text-slate-400">Method</th>
                        <th class="rounded-tr-2xl px-4 py-5 text-right text-[10px] font-black uppercase text-slate-400">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($payments as $payment)
                        @php
                            $paymentStatusStyle = match ($payment->status) {
                                'successful' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'awaiting_confirmation' => 'bg-blue-50 text-blue-700 border-blue-100',
                                'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                                default => 'bg-rose-50 text-rose-700 border-rose-100',
                            };
                        @endphp
                        <tr class="group transition-colors hover:bg-[var(--surface-soft)]">
                            <td class="px-4 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--brand)] text-white shadow-sm">
                                        <span class="text-xs font-black">{{ $payment->id }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-black text-[var(--ink)]">{{ $payment->reference }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-5">
                                <div class="flex items-center gap-3">
                                    <x-avatar :user="$payment->user" :name="$payment->user->name ?? 'N/A'" size="h-10 w-10" text="text-[10px]" rounded="rounded-xl" class="shadow-sm border border-[var(--line)]" />
                                    <span class="text-xs font-black text-slate-700">{{ $payment->user->name ?? 'n/a' }}</span>
                                </div>
                            </td>
                            <td class="max-w-xs px-2 py-5">
                                @if ($payment->job)
                                    <a href="{{ route('admin.jobs.show', $payment->job) }}"
                                        class="block truncate text-xs font-bold text-slate-700 transition hover:text-[var(--brand)]">
                                        {{ $payment->job->title }}
                                    </a>
                                @else
                                    <p class="text-xs font-bold text-slate-400">n/a</p>
                                @endif
                            </td>
                            <td class="px-2 py-5 text-sm font-black text-[var(--ink)]">N{{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="px-2 py-5">
                                <span class="inline-flex rounded-xl border px-2.5 py-1 text-[9px] font-black uppercase {{ $paymentStatusStyle }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td class="px-2 py-5 text-xs font-black capitalize text-slate-600">{{ $payment->payment_method }}</td>
                            <td class="px-2 py-5 text-right">
                                @if ($receiptUrl = data_get($payment->provider_payload, 'receipt_url'))
                                    <a href="{{ $receiptUrl }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 rounded-xl bg-[var(--surface-soft)] px-3 py-2 text-[10px] font-black uppercase text-[var(--brand)] transition hover:bg-[var(--brand)] hover:text-white">
                                        <i data-lucide="file-text" class="h-3.5 w-3.5"></i> View
                                    </a>
                                @else
                                    <span class="text-[10px] font-bold uppercase text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid gap-4 lg:hidden">
            @foreach ($payments as $payment)
                @php
                    $paymentStatusStyle = match ($payment->status) {
                        'successful' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                        'awaiting_confirmation' => 'bg-blue-50 text-blue-700 border-blue-100',
                        'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                        default => 'bg-rose-50 text-rose-700 border-rose-100',
                    };
                @endphp
                <article class="relative overflow-hidden rounded-[2.5rem] border border-[var(--line)] bg-white p-6 shadow-sm transition-all active:scale-[0.98]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-4">
                            <x-avatar :user="$payment->user" :name="$payment->user->name ?? 'N/A'" size="h-14 w-14" text="text-xs" rounded="rounded-2xl" class="shadow-md border border-[var(--line)]" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-[var(--ink)]">{{ $payment->reference }}</p>
                                <p class="mt-1 truncate text-[10px] font-bold uppercase text-slate-400">{{ $payment->user->name ?? 'n/a' }}</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-xl border px-2.5 py-1 text-[9px] font-black uppercase {{ $paymentStatusStyle }}">
                            {{ $payment->status }}
                        </span>
                    </div>
                    <div class="mt-6 grid grid-cols-2 gap-4 rounded-3xl bg-[var(--surface-soft)] p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-white text-[var(--brand)]">
                                <i data-lucide="banknote" class="h-4 w-4"></i>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase opacity-50">Amount</p>
                                <p class="mt-1 text-[11px] font-black text-[var(--ink)]">N{{ number_format((float) $payment->amount, 2) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 border-l border-[var(--line)] pl-4">
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-white text-[var(--brand)]">
                                <i data-lucide="credit-card" class="h-4 w-4"></i>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase opacity-50">Method</p>
                                <p class="mt-1 text-[11px] font-black capitalize text-[var(--ink)]">{{ $payment->payment_method }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 px-1 text-xs font-semibold text-slate-500">
                        <i data-lucide="briefcase-business" class="h-4 w-4 shrink-0 text-[var(--brand)]"></i>
                        @if ($payment->job)
                            <a href="{{ route('admin.jobs.show', $payment->job) }}"
                                class="truncate transition hover:text-[var(--brand)]">{{ $payment->job->title }}</a>
                        @else
                            <span class="truncate">No job linked</span>
                        @endif
                    </div>
                    @if ($receiptUrl = data_get($payment->provider_payload, 'receipt_url'))
                        <a href="{{ $receiptUrl }}" target="_blank" rel="noopener noreferrer"
                            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[var(--surface-soft)] px-4 py-2.5 text-[10px] font-black uppercase text-[var(--brand)] transition hover:bg-[var(--brand)] hover:text-white">
                            <i data-lucide="file-text" class="h-3.5 w-3.5"></i>
                            View receipt
                        </a>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $payments->withQueryString()->links() }}
        </div>
    </section>
@endsection
