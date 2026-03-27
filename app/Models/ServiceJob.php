<?php

namespace App\Models;

use App\Models\Concerns\AppliesAdminTextSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceJob extends Model
{
    use AppliesAdminTextSearch, HasFactory;

    const STATUS_OPEN = 'open'; // job posted
    const STATUS_ASSIGNED = 'assigned'; // worker hired
    const STATUS_WORKER_ACCEPTED = 'worker_accepted'; // worker accepted the job
    const STATUS_IN_PROGRESS = 'in_progress'; // work started
    const STATUS_PAYMENT_PENDING = 'payment_pending'; // worker completed work, awaiting payment closure
    const STATUS_COMPLETED = 'completed'; // worker confirms payment and closes job
    const STATUS_RATED = 'rated'; // client rated worker
    const STATUS_CANCELLED = 'cancelled'; // cancelled by admin
    protected $table = 'service_jobs';

    protected $fillable = [
        'user_id',
        'skill_id',
        'title',
        'description',
        'budget',
        'agreed_amount',
        'location',
        'latitude',
        'longitude',
        'payment_method',
        'status',
        'assigned_to',
        'paid_at'
    ];

    protected $casts = [
        'budget' => 'float',
        'agreed_amount' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'paid_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    public function negotiations()
    {
        return $this->hasMany(JobNegotiation::class, 'job_id');
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class, 'job_id');
    }

    public function messages()
    {
        return $this->hasManyThrough(
            ChatMessage::class,
            Conversation::class,
            'job_id',
            'conversation_id',
            'id',
            'id'
        );
    }

    public function rating()
    {
        return $this->hasOne(Rating::class, 'job_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'job_id');
    }

    public function scopeAdminFilter(Builder $query, array $filters): Builder
    {
        $status = trim((string) ($filters['status'] ?? ''));
        $skillId = $filters['skill_id'] ?? null;
        $term = trim((string) ($filters['q'] ?? ''));

        return $query
            ->when($status !== '', function (Builder $builder) use ($status) {
                $builder->where('status', $status);
            })
            ->when($skillId !== null && $skillId !== '', function (Builder $builder) use ($skillId) {
                $builder->where('skill_id', (int) $skillId);
            })
            ->when($term !== '', function (Builder $builder) use ($term) {
                $this->applyAdminTextSearch($builder, $term, [
                    'title',
                    'description',
                    'location',
                ], [
                    'client' => ['name', 'phone'],
                    'worker' => ['name', 'phone'],
                    'skill' => ['name'],
                ]);
            });
    }

    public static function adminCancellableStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_ASSIGNED,
            self::STATUS_WORKER_ACCEPTED,
            self::STATUS_IN_PROGRESS,
        ];
    }

    public static function adminRollbackTargets(string $currentStatus): array
    {
        return match ($currentStatus) {
            self::STATUS_IN_PROGRESS => [
                self::STATUS_WORKER_ACCEPTED,
            ],
            self::STATUS_PAYMENT_PENDING => [
                self::STATUS_WORKER_ACCEPTED,
                self::STATUS_IN_PROGRESS,
            ],
            self::STATUS_COMPLETED => [
                self::STATUS_WORKER_ACCEPTED,
                self::STATUS_IN_PROGRESS,
                self::STATUS_PAYMENT_PENDING,
            ],
            self::STATUS_RATED => [
                self::STATUS_WORKER_ACCEPTED,
                self::STATUS_IN_PROGRESS,
                self::STATUS_PAYMENT_PENDING,
                self::STATUS_COMPLETED,
            ],
            default => [],
        };
    }
}
