<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUniqueSlug
{
    public function assignUniqueSlug(string $source): string
    {
        $base = Str::slug($source) ?: Str::lower(class_basename($this));

        do {
            $slug = $base . '-' . Str::lower(Str::random(6));
        } while (static::query()->where('slug', $slug)->exists());

        return $slug;
    }
}
