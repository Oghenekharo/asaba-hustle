<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\SendVerificationTokenRequest;
use App\Http\Requests\UpdateAvailabilityRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\VerifyContactRequest;
use App\Http\Requests\VerifyPhoneRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthSecurityService;
use App\Traits\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AuthController extends Controller
{
    use LogActivity;
    public function __construct(
        protected AuthSecurityService $authSecurityService,
    ) {
    }

    /**
     * Register new user
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'primary_skill_id' => $data['primary_skill_id'] ?? null,
        ]);

        $user->assignRole($data['role']);

        if ($data['verification_method'] === 'email') {
            $this->authSecurityService->sendEmailVerificationLink($user);
        } else {
            $this->authSecurityService->issueVerificationToken($user, 'phone');
        }

        return $this->successResponse(
            [
                'user' => (new UserResource($user))->resolve(),
                'verification_method' => $data['verification_method'],
            ],
            'Registration successful. Complete verification to continue.',
            201
        );
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('phone', $data['phone'])->first();

        if (
            !$user ||
            !Hash::check($data['password'], $user->password)
        ) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        $this->activityLog()->log(
            $user->id,
            'user_login',
            ['date' => now()],
            $request->ip()
        );

        return $this->successResponse([
            'token' => $token,
            'user' => (new UserResource($user))->resolve(),
        ], 'Login successful');
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        return $this->successResponse(
            new UserResource($request->user()->fresh('skill')),
            'Authenticated user retrieved successfully.'
        );
    }

    public function updateAvailability(UpdateAvailabilityRequest $request)
    {
        $user = $request->user();

        $user->update([
            'availability_status' => $request->availability_status
        ]);

        return $this->successResponse(
            new UserResource($user),
            'Availability updated successfully.'
        );
    }

    /**
     * Update user profile
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $user->update($data);

        $this->activityLog()->log(
            $user->id,
            'user_profile_update',
            ['date' => now()],
            $request->ip()
        );

        return $this->successResponse(
            new UserResource($user->fresh('skill')),
            'Profile updated successfully.'
        );
    }

    /**
     * Upload ID document
     */
    public function uploadId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'The given data was invalid.',
                422,
                $validator->errors()->toArray()
            );
        }

        $user = $request->user();

        $path = $request->file('id_document')
            ->store('kyc/documents', 'public');

        $user->update([
            'id_document' => $path
        ]);

        return $this->successResponse([
            'path' => Storage::url($path),
        ], 'Document uploaded successfully.');
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $data = $request->validated();
        $user = $this->resolveUserForChannel($data['channel'], $data);

        if ($user) {
            $this->authSecurityService->issuePasswordResetToken($user, $data['channel']);

            return $this->successResponse(
                null,
                'Password reset token sent successfully.',
                200
            );
        }

        return $this->successResponse(
            null,
            'If the account exists, a password reset token has been sent.'
        );
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();
        $user = $this->resolveUserForChannel($data['channel'], $data);

        if (!$user) {
            return $this->errorResponse('Invalid password reset request.', 400);
        }

        try {
            $this->authSecurityService->resetPassword(
                $user,
                $data['channel'],
                $data['token'],
                $data['password']
            );
        } catch (RuntimeException $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(null, 'Password reset successfully.');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return $this->errorResponse('Current password is incorrect.', 400);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ])->save();

        return $this->successResponse(null, 'Password changed successfully.');
    }

    public function sendVerificationToken(SendVerificationTokenRequest $request)
    {
        try {
            $channel = $request->validated('channel');

            if ($channel === 'email') {
                $this->authSecurityService->sendEmailVerificationLink($request->user());
            } else {
                $this->authSecurityService->issueVerificationToken(
                    $request->user(),
                    $channel
                );
            }
        } catch (RuntimeException $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(
            null,
            'Verification token sent successfully.',
            200
        );
    }

    public function verifyContact(VerifyContactRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        try {
            $this->authSecurityService->verifyContact(
                $user,
                $data['channel'],
                $data['token']
            );
        } catch (RuntimeException $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(
            new UserResource($user->fresh('skill')),
            'Phone verified successfully.'
        );
    }

    public function verifyPhone(VerifyPhoneRequest $request)
    {
        $data = $request->validated();
        $user = User::where('phone', $data['phone'])->first();

        if (!$user) {
            return $this->errorResponse('Invalid phone verification request.', 400);
        }

        try {
            $this->authSecurityService->verifyContact(
                $user,
                'phone',
                $data['token']
            );
        } catch (RuntimeException $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(
            new UserResource($user->fresh('skill')),
            'Phone verified successfully.'
        );
    }

    public function verifyEmail(Request $request, User $user, string $hash)
    {
        if (!$request->hasValidSignature()) {
            if (!$request->expectsJson()) {
                return redirect()->route('login', ['verified' => 'email-invalid']);
            }

            return $this->errorResponse('Invalid or expired email verification link.', 401);
        }

        try {
            $this->authSecurityService->verifyEmailLink($user, $hash);
        } catch (RuntimeException $exception) {
            if (!$request->expectsJson()) {
                return redirect()->route('login', ['verified' => 'email-failed']);
            }

            return $this->errorResponse($exception->getMessage(), 400);
        }

        if (!$request->expectsJson()) {
            return redirect()->route('login', ['verified' => 'email-success']);
        }

        return $this->successResponse(
            new UserResource($user->fresh('skill')),
            'Email verified successfully.'
        );
    }

    protected function resolveUserForChannel(string $channel, array $data): ?User
    {
        return match ($channel) {
            'email' => User::where('email', $data['email'] ?? null)->first(),
            'phone' => User::where('phone', $data['phone'] ?? null)->first(),
            default => null,
        };
    }
}
