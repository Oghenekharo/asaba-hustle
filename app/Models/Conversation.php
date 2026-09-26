<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'job_id',
        'client_id',
        'worker_id',
        'is_closed',
        'closed_at',
        'reopened_at',
        'reopened_by',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Conversation $conversation) {
            if (blank($conversation->uuid)) {
                $conversation->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

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

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function close(): void
    {
        $this->forceFill([
            'is_closed' => true,
            'closed_at' => now(),
        ])->save();
    }

    public function reopenBy(User $admin): void
    {
        $this->forceFill([
            'is_closed' => false,
            'reopened_at' => now(),
            'reopened_by' => $admin->id,
        ])->save();
    }
}
