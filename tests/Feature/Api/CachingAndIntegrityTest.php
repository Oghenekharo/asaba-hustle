<?php

namespace Tests\Feature\Api;

use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CachingAndIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_skills_index_uses_cache_between_requests(): void
    {
        Cache::flush();

        $skill = Skill::create([
            'name' => 'Carpenter',
        ]);

        $firstResponse = $this->getJson('/api/skills');

        $firstResponse
            ->assertOk()
            ->assertJsonPath('success', true);

        $skill->delete();

        $secondResponse = $this->getJson('/api/skills');

        $secondResponse
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment([
                'name' => 'Carpenter',
            ]);
    }
}
