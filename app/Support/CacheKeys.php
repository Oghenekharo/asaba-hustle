<?php

namespace App\Support;

class CacheKeys
{
    public const ADMIN_DASHBOARD_METRICS = 'admin:dashboard:metrics';
    public const SKILLS_INDEX = 'skills:index';

    public static function skill(int $skillId): string
    {
        return "skills:{$skillId}";
    }

    public static function workerDiscovery(
        int $jobId,
        mixed $updatedAt,
        int $skillId,
        mixed $latitude,
        mixed $longitude
    ): string {
        return sprintf(
            'worker-discovery:%s:%s:%s:%s:%s',
            $jobId,
            $updatedAt ?? 'na',
            $skillId,
            $latitude,
            $longitude
        );
    }
}
