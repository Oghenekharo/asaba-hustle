<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertSkillRequest;
use App\Models\Skill;
use App\Support\CacheKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminSkillController extends Controller
{
    public function index(Request $request)
    {
        $term = trim((string) $request->string('q'));

        $skills = Skill::query()
            ->withCount(['jobs', 'primaryUsers', 'users'])
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('icon', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return view('admin.skills.index', compact('skills'));
    }

    public function edit(Skill $skill)
    {
        return view('admin.skills.edit', compact('skill'));
    }

    public function store(UpsertSkillRequest $request)
    {
        $skill = Skill::create($request->validated());

        $this->flushSkillCache($skill);

        return redirect()
            ->route('admin.skills.index')
            ->with('status', 'Skill created successfully.');
    }

    public function update(UpsertSkillRequest $request, Skill $skill)
    {
        $skill->update($request->validated());

        $this->flushSkillCache($skill);

        return redirect()
            ->route('admin.skills.edit', $skill)
            ->with('status', 'Skill updated successfully.');
    }

    public function destroy(Skill $skill)
    {
        $skill->loadCount(['jobs', 'primaryUsers', 'users']);

        if ($skill->jobs_count > 0 || $skill->primary_users_count > 0 || $skill->users_count > 0) {
            return redirect()
                ->route('admin.skills.index')
                ->with('status', 'This skill is already in use and cannot be deleted yet.');
        }

        $skillId = $skill->id;
        $skill->delete();

        Cache::forget(CacheKeys::SKILLS_INDEX);
        Cache::forget(CacheKeys::skill($skillId));

        return redirect()
            ->route('admin.skills.index')
            ->with('status', 'Skill deleted successfully.');
    }

    protected function flushSkillCache(Skill $skill): void
    {
        Cache::forget(CacheKeys::SKILLS_INDEX);
        Cache::forget(CacheKeys::skill($skill->id));
    }
}
