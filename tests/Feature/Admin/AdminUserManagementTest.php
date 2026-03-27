<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_index_and_update_status(): void
    {
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $target = User::factory()->create([
            'account_status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee($target->name);

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/status", [
                'status' => 'suspended',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'account_status' => 'suspended',
        ]);
    }

    public function test_admin_can_only_verify_user_after_id_document_upload(): void
    {
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $target = User::factory()->create([
            'account_status' => 'active',
            'is_verified' => false,
            'id_document' => null,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/status", [
                'status' => 'active',
                'is_verified' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'is_verified' => false,
        ]);

        $target->update([
            'id_document' => 'kyc/documents/test-id.pdf',
        ]);

        $this->actingAs($admin)
            ->patch("/admin/users/{$target->id}/status", [
                'status' => 'active',
                'is_verified' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'is_verified' => true,
        ]);
    }
}
