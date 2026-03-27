<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUserIndexRequest;
use App\Http\Requests\Admin\BulkUpdateUsersRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\User;

class AdminUserController extends Controller
{
    public function index(AdminUserIndexRequest $request)
    {
        $users = User::query()
            ->with(['roles', 'skill'])
            ->withCount('ratingsReceived')
            ->adminFilter($request->filters())
            ->latest()
            ->paginate(25);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load([
            'roles',
            'ratingsReceived',
            'skill',
            'postedJobs.skill',
            'assignedJobs.skill',
        ]);

        return view('admin.users.show', compact('user'));
    }

    public function updateStatus(User $user, UpdateUserStatusRequest $request)
    {
        $data = $request->validated();

        $updates = [
            'account_status' => $data['status'],
        ];

        if (array_key_exists('is_verified', $data)) {
            if ((bool) $data['is_verified'] && blank($user->id_document)) {
                return redirect()
                    ->back()
                    ->with('status', 'Upload and review an ID document before verifying this user.');
            }

            $updates['is_verified'] = (bool) $data['is_verified'];
        }

        $user->update($updates);

        return redirect()
            ->back()
            ->with('status', 'User status updated successfully.');
    }

    public function bulk(BulkUpdateUsersRequest $request)
    {
        $data = $request->validated();

        User::whereIn('id', $data['users'])->update([
            'account_status' => $data['action'],
        ]);

        return redirect()
            ->back()
            ->with('status', 'Bulk status update completed.');
    }
}
