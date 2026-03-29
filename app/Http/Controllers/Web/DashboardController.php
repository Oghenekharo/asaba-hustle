<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ServiceJob;
use App\Models\Skill;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $searchTerm = trim((string) $request->query('search', ''));

        $skills = Skill::query()
            ->when($searchTerm !== '' && $user->hasRole('client'), function ($query) use ($searchTerm) {
                $query->where(function ($builder) use ($searchTerm) {
                    $builder
                        ->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('description', 'like', '%' . $searchTerm . '%');
                });
            })
            ->orderBy('name')
            ->get();

        $jobs = collect();
        $myJobs = collect();
        $recentNotifications = UserNotification::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->take(4)
            ->get();
        $recentChats = Conversation::query()
            ->where(function ($query) use ($user) {
                $query
                    ->where('client_id', $user->id)
                    ->orWhere('worker_id', $user->id);
            })
            ->with(['client', 'worker', 'job', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->withCount(['messages as unread_messages_count' => function ($query) use ($user) {
                $query
                    ->where('sender_id', '!=', $user->id)
                    ->where('is_read', false);
            }])
            ->withMax('messages', 'created_at')
            ->orderByDesc('messages_max_created_at')
            ->take(2)
            ->get();

        if ($user->hasRole('client')) {
            $myJobs = ServiceJob::query()
                ->where('user_id', $user->id)
                ->latest()
                ->take(1)
                ->with(['worker:id,name,phone', 'skill:id,name,icon'])
                ->get();
        } elseif ($user->hasRole('worker')) {
            $skillIds = $user->loadMissing('skills')->relevantSkillIds();

            if (!empty($skillIds)) {
                $jobs = ServiceJob::query()
                    ->where('status', 'open')
                    ->whereIn('skill_id', $skillIds)
                    ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                        $query->where(function ($builder) use ($searchTerm) {
                            $builder
                                ->where('title', 'like', '%' . $searchTerm . '%')
                                ->orWhere('description', 'like', '%' . $searchTerm . '%')
                                ->orWhere('location', 'like', '%' . $searchTerm . '%')
                                ->orWhereHas('skill', function ($skillQuery) use ($searchTerm) {
                                    $skillQuery->where('name', 'like', '%' . $searchTerm . '%');
                                });
                        });
                    })
                    ->latest()
                    ->take(5)
                    ->with(['client:id,name,phone', 'skill:id,name,icon'])
                    ->get();
            }

            $myJobs = ServiceJob::query()
                ->where('assigned_to', $user->id)
                ->latest()
                ->take(5)
                ->with(['client:id,name,phone', 'skill:id,name,icon'])
                ->get();
        }

        return view('web.app', compact(
            'skills',
            'jobs',
            'myJobs',
            'recentNotifications',
            'recentChats',
            'searchTerm'
        ));
    }
}
