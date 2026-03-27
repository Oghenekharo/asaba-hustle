<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPaymentIndexRequest;
use App\Models\Payment;

class AdminPaymentController extends Controller
{
    public function index(AdminPaymentIndexRequest $request)
    {
        $baseQuery = Payment::query()->adminFilter($request->filters());

        $payments = (clone $baseQuery)
            ->with(['user', 'job'])
            ->latest()
            ->paginate(25);

        $summary = [
            'total' => (clone $baseQuery)->count(),
            'settled_amount' => (clone $baseQuery)
                ->where('status', Payment::STATUS_SUCCESSFUL)
                ->sum('amount'),
            'manual_count' => (clone $baseQuery)
                ->whereIn('payment_method', ['cash', 'transfer'])
                ->count(),
            'gateway_count' => (clone $baseQuery)
                ->whereIn('payment_method', ['paystack', 'flutterwave'])
                ->count(),
        ];

        return view('admin.payments.index', compact('payments', 'summary'));
    }
}
