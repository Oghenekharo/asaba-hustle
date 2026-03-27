<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SkillResource;
use App\Models\Skill;
use Illuminate\Support\Facades\Cache;
use App\Support\CacheKeys;

class SkillController extends Controller
{
    /**
     * List all skills
     */
    public function index()
    {
        $skills = Cache::remember(CacheKeys::SKILLS_INDEX, now()->addHour(), function () {
            return Skill::orderBy('name')->get();
        });

        return $this->successResponse(
            SkillResource::collection($skills),
            'Skills retrieved successfully.'
        );
    }


    /**
     * Show single skill
     */
    public function show(Skill $skill)
    {
        $skill = Cache::remember(CacheKeys::skill($skill->id), now()->addHour(), function () use ($skill) {
            return Skill::findOrFail($skill->id);
        });

        return $this->successResponse(
            new SkillResource($skill),
            'Skill retrieved successfully.'
        );
    }
}
