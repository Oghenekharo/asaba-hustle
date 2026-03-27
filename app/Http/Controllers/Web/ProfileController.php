<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\SendVerificationTokenRequest;
use App\Http\Requests\UpdateAvailabilityRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\VerifyContactRequest;
use App\Http\Resources\UserResource;
use App\Models\ChatMessage;
use App\Models\JobApplication;
use App\Models\ServiceJob;
use App\Models\Skill;
use App\Services\AuthSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ProfileController extends Controller
{
    public function __construct(
        protected AuthSecurityService $authSecurityService
    ) {}

    public function me(Request $request)
    {
        $profileUser = $request->user()->fresh(['skill', 'skills']);
        $user = new UserResource($profileUser);
        $skills = Skill::query()->orderBy('name')->get();
        $selectedSkillIds = $profileUser->skills->pluck('id')->all();
        $userId = $profileUser->id;

        $postedJobsQuery = ServiceJob::query()->where('user_id', $userId);
        $assignedJobsQuery = ServiceJob::query()->where('assigned_to', $userId);
        $applicationsQuery = JobApplication::query()->where('user_id', $userId);

        $profileCompletionFields = [
            $profileUser->name,
            $profileUser->phone,
            $profileUser->email,
            $profileUser->bio,
            $profileUser->primary_skill_id,
            $profileUser->availability_status,
            $profileUser->latitude,
            $profileUser->longitude,
            $profileUser->profile_photo,
            $profileUser->id_document,
        ];

        $completedProfileFields = collect($profileCompletionFields)
            ->filter(fn($value) => filled($value))
            ->count();

        $profileMetrics = [
            'profile_completion' => (int) round(($completedProfileFields / count($profileCompletionFields)) * 100),
            'unread_messages' => ChatMessage::query()
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->whereHas('conversation', function ($query) use ($userId) {
                    $query->where(function ($conversationQuery) use ($userId) {
                        $conversationQuery
                            ->where('client_id', $userId)
                            ->orWhere('worker_id', $userId);
                    });
                })
                ->count(),
            'unread_notifications' => $profileUser->notifications()->where('is_read', false)->count(),
            'average_rating' => (float) $profileUser->average_rating,
        ];

        if ($profileUser->hasRole('client')) {
            $profileMetrics['jobs_created'] = (clone $postedJobsQuery)->count();
            $profileMetrics['open_jobs'] = (clone $postedJobsQuery)->where('status', ServiceJob::STATUS_OPEN)->count();
            $profileMetrics['active_jobs'] = (clone $postedJobsQuery)->whereIn('status', [
                ServiceJob::STATUS_ASSIGNED,
                ServiceJob::STATUS_WORKER_ACCEPTED,
                ServiceJob::STATUS_IN_PROGRESS,
                ServiceJob::STATUS_PAYMENT_PENDING,
            ])->count();
            $profileMetrics['completed_jobs'] = (clone $postedJobsQuery)->whereIn('status', [
                ServiceJob::STATUS_COMPLETED,
                ServiceJob::STATUS_RATED,
            ])->count();
            $profileMetrics['applications_received'] = JobApplication::query()
                ->whereHas('job', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->count();
        } else {
            $profileMetrics['assigned_jobs'] = (clone $assignedJobsQuery)->count();
            $profileMetrics['active_jobs'] = (clone $assignedJobsQuery)->whereIn('status', [
                ServiceJob::STATUS_ASSIGNED,
                ServiceJob::STATUS_WORKER_ACCEPTED,
                ServiceJob::STATUS_IN_PROGRESS,
                ServiceJob::STATUS_PAYMENT_PENDING,
            ])->count();
            $profileMetrics['completed_jobs'] = (clone $assignedJobsQuery)->whereIn('status', [
                ServiceJob::STATUS_COMPLETED,
                ServiceJob::STATUS_RATED,
            ])->count();
            $profileMetrics['applications_sent'] = (clone $applicationsQuery)->count();
            $profileMetrics['skills_count'] = collect($profileUser->relevantSkillIds())->count();
        }

        return view('web.profile', compact('user', 'skills', 'selectedSkillIds', 'profileMetrics'));
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->safe()->except(['id_document', 'skill_ids']);
        $skillIds = collect($request->validated('skill_ids', []))
            ->map(fn($skillId) => (int) $skillId)
            ->filter()
            ->values();

        if ($request->hasFile('id_document')) {
            $data['id_document'] = $request->file('id_document')->store('kyc/documents', 'public');
        }

        $user->update($data);

        if ($user->hasRole('worker')) {
            $user->skills()->sync(
                $skillIds
                    ->reject(fn($skillId) => $skillId === (int) $user->primary_skill_id)
                    ->all()
            );
        }

        return $this->successResponse(
            new UserResource($user->fresh(['skill', 'skills'])),
            'Profile updated successfully.'
        );
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        if (!Hash::check($data['current_password'], $user->password)) {
            return $this->errorResponse('Current password is incorrect.', 400);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ])->save();

        return $this->successResponse(null, 'Password changed successfully.');
    }

    public function uploadPhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_photo' => 'required|file|mimes:jpg,jpeg,png,avif,webp,heic|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'The given data was invalid.',
                422,
                $validator->errors()->toArray()
            );
        }

        $path = $request->file('profile_photo')->store('users/photos', 'public');

        $request->user()->update([
            'profile_photo' => $path,
        ]);

        return $this->successResponse([
            'profile_photo' => Storage::url($path),
        ], 'Profile photo uploaded successfully.');
    }

    public function uploadId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'The given data was invalid.',
                422,
                $validator->errors()->toArray()
            );
        }

        $path = $request->file('id_document')->store('kyc/documents', 'public');

        $request->user()->update([
            'id_document' => $path,
        ]);

        return $this->successResponse([
            'path' => Storage::url($path),
        ], 'Document uploaded successfully.');
    }

    public function updateAvailability(UpdateAvailabilityRequest $request)
    {
        $user = $request->user();
        $user->update([
            'availability_status' => $request->validated('availability_status'),
        ]);

        return $this->successResponse(
            new UserResource($user->fresh('skill')),
            'Availability updated successfully.'
        );
    }

    public function sendVerificationToken(SendVerificationTokenRequest $request)
    {
        try {
            $channel = $request->validated('channel');

            if ($channel === 'email') {
                $this->authSecurityService->sendEmailVerificationLink($request->user());
            } else {
                $this->authSecurityService->issueVerificationToken($request->user(), 'phone');
            }
        } catch (RuntimeException $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(null, 'Verification token sent successfully.', 200);
    }

    public function verifyContact(VerifyContactRequest $request)
    {
        try {
            $this->authSecurityService->verifyContact(
                $request->user(),
                $request->validated('channel'),
                $request->validated('token')
            );
        } catch (RuntimeException $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(
            new UserResource($request->user()->fresh('skill')),
            'Phone verified successfully.'
        );
    }
}
