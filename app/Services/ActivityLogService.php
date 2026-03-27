<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public function log(?int $userId, string $action, ?array $metadata = null, ?string $ip = null)
    {
        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'metadata' => $metadata,
            'ip_address' => $ip,
        ]);
    }
}
