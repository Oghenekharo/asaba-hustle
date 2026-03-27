<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminActivityLogIndexRequest;
use App\Models\ActivityLog;

class AdminActivityLogController extends Controller
{
    public function index(AdminActivityLogIndexRequest $request)
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->adminFilter($request->filters())
            ->latest()
            ->paginate(25);

        return view('admin.activity.index', compact('logs'));
    }
}
