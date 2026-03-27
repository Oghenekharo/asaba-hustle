<?php

namespace App\Models;

use App\Models\Concerns\AppliesAdminTextSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use AppliesAdminTextSearch, HasFactory;

    public const STATUS_AWAITING_CONFIRMATION = 'awaiting_confirmation';
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESSFUL = 'successful';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'job_id',
        'user_id',
        'amount',
        'payment_method',
        'reference',
        'status',
        'idempotency_key',
        'verified_at',
        'provider_payload',
    ];

    protected $casts = [
        'amount' => 'float',
        'verified_at' => 'datetime',
        'provider_payload' => 'array',
    ];

    public function job()
    {
        return $this->belongsTo(ServiceJob::class, 'job_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAdminFilter(Builder $query, array $filters): Builder
    {
        $status = trim((string) ($filters['status'] ?? ''));
        $paymentMethod = trim((string) ($filters['payment_method'] ?? ''));
        $term = trim((string) ($filters['q'] ?? ''));

        return $query
            ->when($status !== '', function (Builder $builder) use ($status) {
                $builder->where('status', $status);
            })
            ->when($paymentMethod !== '', function (Builder $builder) use ($paymentMethod) {
                $builder->where('payment_method', $paymentMethod);
            })
            ->when($term !== '', function (Builder $builder) use ($term) {
                $this->applyAdminTextSearch($builder, $term, [
                    'reference',
                ], [
                    'user' => ['name', 'phone'],
                    'job' => ['title'],
                ]);
            });
    }
}
