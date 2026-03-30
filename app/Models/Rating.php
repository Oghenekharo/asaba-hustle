<?php

namespace App\Models;

use App\Models\Concerns\AppliesAdminTextSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use AppliesAdminTextSearch, HasFactory;

    protected $fillable = [
        'job_id',
        'client_id',
        'worker_id',
        'rated_by_user_id',
        'rated_user_id',
        'rated_by_role',
        'rated_role',
        'rating',
        'review'
    ];

    protected $casts = [
        'rating' => 'float'
    ];

    public function job()
    {
        return $this->belongsTo(ServiceJob::class, 'job_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rated_by_user_id');
    }

    public function ratee()
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }

    public function scopeAdminFilter(Builder $query, array $filters): Builder
    {
        $score = $filters['rating'] ?? null;
        $term = trim((string) ($filters['q'] ?? ''));

        return $query
            ->when($score !== null && $score !== '', function (Builder $builder) use ($score) {
                $builder->where('rating', (int) $score);
            })
            ->when($term !== '', function (Builder $builder) use ($term) {
                $this->applyAdminTextSearch($builder, $term, [
                    'review',
                ], [
                    'worker' => ['name', 'phone'],
                    'client' => ['name', 'phone'],
                    'job' => ['title'],
                ]);
            });
    }
}
