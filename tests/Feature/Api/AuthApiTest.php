<?php

namespace Tests\Feature\Api;

use App\Http\Middleware\EnsureTokenIsActive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_phone_verification_and_verify_with_token(): void
    {
        Role::create(['name' => 'client', 'guard_name' => 'web']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test Client',
            'phone' => '08012345678',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'client',
            'verification_method' => 'phone',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'phone'],
                    'verification_method',
                ],
                'meta' => ['debug_token'],
            ]);

        $this->assertDatabaseHas('users', [
            'phone' => '08012345678',
        ]);

        $this->postJson('/api/auth/verify-phone', [
            'phone' => '08012345678',
            'token' => $response->json('meta.debug_token'),
        ])->assertOk()->assertJsonPath('success', true);
    }

    public function test_user_can_request_and_reset_password_by_phone(): void
    {
        $user = User::factory()->create([
            'phone' => '08099999999',
            'password' => Hash::make('oldpassword'),
        ]);

        $forgot = $this->postJson('/api/auth/forgot-password', [
            'channel' => 'phone',
            'phone' => $user->phone,
        ]);

        $forgot
            ->assertOk()
            ->assertJsonPath('success', true);

        $token = $forgot->json('meta.debug_token');

        $this->postJson('/api/auth/reset-password', [
            'channel' => 'phone',
            'phone' => $user->phone,
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_authenticated_user_can_change_password_and_request_email_verification_link(): void
    {
        $user = User::factory()->create([
            'email' => 'tester@example.com',
            'password' => Hash::make('oldpassword'),
            'email_verified_at' => null,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/auth/change-password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk()->assertJsonPath('success', true);

        $tokenResponse = $this->postJson('/api/auth/send-verification-token', [
            'channel' => 'email',
        ]);

        $tokenResponse->assertOk()->assertJsonPath('success', true);

        $link = $tokenResponse->json('meta.debug_link');

        $this->getJson($link)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'tester@example.com');

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertFalse($user->fresh()->is_verified);
    }

    public function test_token_inactivity_middleware_rejects_expired_token(): void
    {
        $user = User::factory()->create();

        $issuedToken = $user->createToken('api_token');
        $accessToken = $issuedToken->accessToken;

        DB::table('personal_access_tokens')
            ->where('id', $accessToken->id)
            ->update([
                'last_used_at' => now()->subMinutes(31),
                'created_at' => now()->subMinutes(31),
            ]);

        $request = Request::create('/api/auth/me', 'GET', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $issuedToken->plainTextToken,
        ]);

        $response = app(EnsureTokenIsActive::class)->handle(
            $request,
            fn () => response()->json(['success' => true]),
        );

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Token expired due to inactivity.', $response->getData(true)['message']);

        $this->assertNull(PersonalAccessToken::find($accessToken->id));
    }
}
