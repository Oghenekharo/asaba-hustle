<?php

namespace App\Models;

use App\Models\Concerns\AppliesAdminTextSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use AppliesAdminTextSearch, HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'metadata',
        'ip_address'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAdminFilter(Builder $query, array $filters): Builder
    {
        $action = trim((string) ($filters['action'] ?? ''));
        $term = trim((string) ($filters['q'] ?? ''));

        return $query
            ->when($action !== '', function (Builder $builder) use ($action) {
                $builder->where('action', 'like', "%{$action}%");
            })
            ->when($term !== '', function (Builder $builder) use ($term) {
                $this->applyAdminTextSearch($builder, $term, [
                    'ip_address',
                ], [
                    'user' => ['name', 'phone'],
                ]);
            });
    }
}
