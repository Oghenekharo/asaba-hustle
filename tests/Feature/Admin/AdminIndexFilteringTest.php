<?php

namespace Tests\Feature\Admin;

use App\Models\ServiceJob;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminIndexFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_jobs_index_by_status_and_query(): void
    {
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $skill = Skill::create([
            'name' => 'Plumber',
        ]);

        $client = User::factory()->create();
        $worker = User::factory()->create();

        ServiceJob::create([
            'user_id' => $client->id,
            'skill_id' => $skill->id,
            'title' => 'Kitchen leak repair',
            'description' => 'Fix pipe leak',
            'budget' => 20000,
            'location' => 'Asaba',
            'latitude' => 6.2000,
            'longitude' => 6.7333,
            'payment_method' => 'cash',
            'status' => 'assigned',
            'assigned_to' => $worker->id,
        ]);

        ServiceJob::create([
            'user_id' => $client->id,
            'skill_id' => $skill->id,
            'title' => 'Generator wiring',
            'description' => 'Electrical maintenance',
            'budget' => 30000,
            'location' => 'Okpanam',
            'latitude' => 6.1800,
            'longitude' => 6.7000,
            'payment_method' => 'cash',
            'status' => 'open',
        ]);

        $this->actingAs($admin)
            ->get('/admin/jobs?status=assigned&q=Kitchen')
            ->assertOk()
            ->assertSee('Kitchen leak repair')
            ->assertDontSee('Generator wiring');
    }
}
