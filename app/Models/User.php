<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Concerns\AppliesAdminTextSearch;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use AppliesAdminTextSearch, HasApiTokens, Notifiable, HasRoles, HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'password',
        'primary_skill_id',
        'profile_photo',
        'id_document',
        'availability_status',
        'email',
        'bio',
        'rating',
        'is_verified',
        'phone_verified_at',
        'latitude',
        'longitude',
        'account_status',
        'bank_name',
        'account_name',
        'account_number'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
        'password_reset_token',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'rating' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'verification_token_expires_at' => 'datetime',
        'password_reset_token_expires_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function skill()
    {
        return $this->belongsTo(Skill::class, 'primary_skill_id');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class)->withTimestamps();
    }

    public function relevantSkillIds(): array
    {
        $skillIds = $this->relationLoaded('skills')
            ? $this->skills->pluck('id')->all()
            : $this->skills()->pluck('skills.id')->all();

        if ($this->primary_skill_id) {
            $skillIds[] = (int) $this->primary_skill_id;
        }

        return array_values(array_unique(array_filter($skillIds)));
    }

    public function postedJobs()
    {
        return $this->hasMany(ServiceJob::class, 'user_id');
    }

    public function assignedJobs()
    {
        return $this->hasMany(ServiceJob::class, 'assigned_to');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function ratingsReceived()
    {
        return $this->hasMany(Rating::class, 'worker_id');
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function averageRating()
    {
        return $this->ratingsReceived()->avg('rating');
    }

    public function negotiationsAsClient()
    {
        return $this->hasMany(JobNegotiation::class, 'client_id');
    }

    public function negotiationsAsWorker()
    {
        return $this->hasMany(JobNegotiation::class, 'worker_id');
    }

    public function syncAverageRating(): float
    {
        $average = round((float) ($this->ratingsReceived()->avg('rating') ?? 0), 2);

        $this->forceFill([
            'rating' => $average,
        ])->save();

        return $average;
    }

    public function getAverageRatingAttribute()
    {
        if (array_key_exists('ratings_received_avg_rating', $this->attributes)) {
            return round((float) ($this->attributes['ratings_received_avg_rating'] ?? 0), 2);
        }

        if (array_key_exists('rating', $this->attributes) && $this->attributes['rating'] !== null) {
            return round((float) $this->attributes['rating'], 2);
        }

        return round((float) ($this->ratingsReceived()->avg('rating') ?? 0), 2);
    }

    public function scopeAdminFilter(Builder $query, array $filters): Builder
    {
        $status = trim((string) ($filters['status'] ?? ''));
        $role = trim((string) ($filters['role'] ?? ''));
        $term = trim((string) ($filters['q'] ?? ''));

        return $query
            ->when($status !== '', function (Builder $builder) use ($status) {
                $builder->where('account_status', $status);
            })
            ->when($role !== '', function (Builder $builder) use ($role) {
                $builder->role($role);
            })
            ->when($term !== '', function (Builder $builder) use ($term) {
                $this->applyAdminTextSearch($builder, $term, [
                    'name',
                    'phone',
                    'email',
                ]);
            });
    }

    public function getRouteKeyName()
    {
        return 'id';
    }
}
