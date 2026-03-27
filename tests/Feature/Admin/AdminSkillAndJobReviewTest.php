<?php

namespace Tests\Feature\Admin;

use App\Models\JobApplication;
use App\Models\ServiceJob;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminSkillAndJobReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(): User
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        return $admin;
    }

    public function test_admin_can_create_update_and_delete_skill(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post('/admin/skills', [
                'name' => 'Painter',
                'icon' => 'paintbrush',
                'description' => 'Surface finishing and wall painting.',
            ])
            ->assertRedirect('/admin/skills');

        $skill = Skill::where('name', 'Painter')->firstOrFail();

        $this->actingAs($admin)
            ->patch("/admin/skills/{$skill->id}", [
                'name' => 'House Painter',
                'icon' => 'paint-roller',
                'description' => 'Interior and exterior painting jobs.',
            ])
            ->assertRedirect("/admin/skills/{$skill->id}/edit");

        $this->assertDatabaseHas('skills', [
            'id' => $skill->id,
            'name' => 'House Painter',
        ]);

        $this->actingAs($admin)
            ->delete("/admin/skills/{$skill->id}")
            ->assertRedirect('/admin/skills');

        $this->assertDatabaseMissing('skills', [
            'id' => $skill->id,
        ]);
    }

    public function test_admin_job_review_page_shows_applications_and_timeline(): void
    {
        $admin = $this->createAdmin();
        $skill = Skill::factory()->create(['name' => 'Plumber']);
        $client = User::factory()->create();
        $worker = User::factory()->create([
            'primary_skill_id' => $skill->id,
            'rating' => 4.5,
        ]);

        $job = ServiceJob::factory()->assigned($worker)->create([
            'user_id' => $client->id,
            'skill_id' => $skill->id,
            'title' => 'Fix burst kitchen pipe',
        ]);

        JobApplication::factory()->create([
            'job_id' => $job->id,
            'user_id' => $worker->id,
            'message' => 'I can handle this today.',
            'status' => 'accepted',
        ]);

        $this->actingAs($admin)
            ->get("/admin/jobs/{$job->id}")
            ->assertOk()
            ->assertSee('Applications')
            ->assertSee('I can handle this today.')
            ->assertSee('Job Timeline')
            ->assertSee('Worker assigned');
    }
}
