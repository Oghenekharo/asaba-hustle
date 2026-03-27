<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ServiceJob;
use App\Models\User;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $metrics = Cache::remember(CacheKeys::ADMIN_DASHBOARD_METRICS, now()->addMinutes(5), function () {
            return [
                'totalUsers' => User::count(),
                'activeWorkers' => User::role('worker')
                    ->where('availability_status', 'available')
                    ->count(),
                'jobsCompleted' => ServiceJob::whereIn('status', ['completed', 'rated'])->count(),
                'revenue' => Payment::where('status', Payment::STATUS_SUCCESSFUL)->sum('amount'),
                'manualSettlements' => Payment::where('status', Payment::STATUS_SUCCESSFUL)
                    ->whereIn('payment_method', ['cash', 'transfer'])
                    ->count(),
            ];
        });

        return view('admin.dashboard', $metrics);
    }
}
