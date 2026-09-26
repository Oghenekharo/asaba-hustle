<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasUniqueSlug;

class Skill extends Model
{
    use HasFactory, HasUniqueSlug;

    protected static function booted(): void
    {
        static::creating(function (Skill $skill): void {
            $skill->slug ??= $skill->assignUniqueSlug($skill->name);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'name',
        'icon',
        'description'
    ];

    public function primaryUsers()
    {
        return $this->hasMany(User::class, 'primary_skill_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function jobs()
    {
        return $this->hasMany(ServiceJob::class);
    }
}
