<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyPhoneRequest;
use App\Http\Resources\UserResource;
use App\Models\Skill;
use App\Models\User;
use App\Services\AuthSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(
        protected AuthSecurityService $authSecurityService,
    ) {}

    public function showLogin()
    {
        return view('web.auth.login');
    }

    public function showRegister()
    {
        $skills = Skill::query()->orderBy('name')->get();

        return view('web.auth.register', compact('skills'));
    }

    public function showVerifyPhone(Request $request)
    {
        return view('web.auth.verify-phone', [
            'phone' => (string) $request->query('phone', ''),
        ]);
    }

    public function showForgotPassword()
    {
        return view('web.auth.forgot-password');
    }

    public function showResetPassword(Request $request)
    {
        return view('web.auth.reset-password', [
            'email' => (string) $request->query('email', ''),
            'phone' => (string) $request->query('phone', ''),
        ]);
    }

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

        // Auth::login($user, true);
        // $request->session()->regenerate();

        return $this->successResponse(
            [
                'user' => (new UserResource($user->load('skill')))->resolve(),
                'redirect' => $data['verification_method'] === 'phone'
                    ? route('web.verify.phone.page', ['phone' => $user->phone])
                    : route('login', ['verified' => 'email-pending']),
                'verification_method' => $data['verification_method'],
            ],
            'Registration successful. Complete verification to continue.',
            201
        );
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::query()->where('phone', $data['phone'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return $this->errorResponse('Invalid credentials.', 401);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return $this->successResponse(
            [
                'user' => (new UserResource($user->load('skill')))->resolve(),
                'redirect' => $user->hasRole('admin')
                    ? route('admin.dashboard')
                    : ($user->phone_verified_at
                        ? route('web.app')
                        : route('web.verify.phone.page', ['phone' => $user->phone])),
            ],
            'Login successful.'
        );
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // return $this->successResponse(
        //     ['redirect' => route('login')],
        //     'Logged out successfully.'
        // );

        return redirect()->route('login')->with('loggedOutStatus', 'Logged out successfully');
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $data = $request->validated();
        $user = $this->resolveUserForChannel($data['channel'], $data);

        if ($user) {
            $this->authSecurityService->issuePasswordResetToken($user, $data['channel']);

            return $this->successResponse(
                [
                    'redirect' => route('web.password.reset', [
                        'email' => $data['email'] ?? null,
                        'phone' => $data['phone'] ?? null,
                    ]),
                ],
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

        return $this->successResponse(
            ['redirect' => route('login')],
            'Password reset successfully.'
        );
    }

    public function verifyPhone(VerifyPhoneRequest $request)
    {
        $data = $request->validated();
        $user = User::query()->where('phone', $data['phone'])->first();

        if (!$user) {
            return $this->errorResponse('Invalid phone verification request.', 400);
        }

        try {
            $this->authSecurityService->verifyContact($user, 'phone', $data['token']);
        } catch (RuntimeException $exception) {
            return $this->errorResponse($exception->getMessage(), 400);
        }

        return $this->successResponse(
            [
                'user' => (new UserResource($user->load('skill')))->resolve(),
                'redirect' => route('login'),
            ],
            'Phone verified successfully.'
        );
    }

    protected function resolveUserForChannel(string $channel, array $data): ?User
    {
        return match ($channel) {
            'email' => User::query()->where('email', $data['email'] ?? null)->first(),
            'phone' => User::query()->where('phone', $data['phone'] ?? null)->first(),
            default => null,
        };
    }
}
