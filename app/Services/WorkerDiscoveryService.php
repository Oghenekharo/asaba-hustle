<?php

namespace App\Services;

use App\Models\User;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

class WorkerDiscoveryService
{

    public function findWorkersForJob($job)
    {

        $lat = $job->latitude;
        $lng = $job->longitude;
        $skill = $job->skill_id;

        $workers = User::query()
            ->where(function ($query) use ($skill) {
                $query
                    ->where('primary_skill_id', $skill)
                    ->orWhereHas('skills', function ($skillQuery) use ($skill) {
                        $skillQuery->where('skills.id', $skill);
                    });
            })
            ->where('availability_status', 'available')

            ->select('*')

            ->selectRaw("
                (6371 *
                acos(
                    cos(radians(?)) *
                    cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(latitude))
                )
            ) AS distance", [$lat, $lng, $lat])

            ->withCount([
                'ratingsReceived as completed_jobs'
            ])

            ->withAvg('ratingsReceived', 'rating')

            ->orderBy('distance')
            ->limit(20)
            ->get();

        return $workers;
    }

    public function rankWorkers($workers)
    {
        return $workers->map(function ($worker) {

            $distanceScore = max(0, 10 - $worker->distance);

            $ratingScore = $worker->received_ratings_avg_rating ?? 0;

            $jobScore = min(5, $worker->completed_jobs / 10);

            $availabilityScore = $worker->availability_status === 'available' ? 1 : 0;

            $score =
                ($distanceScore * 0.4) +
                ($ratingScore * 0.3) +
                ($jobScore * 0.2) +
                ($availabilityScore * 0.1);

            $worker->ranking_score = round($score, 2);

            return $worker;
        })
            ->sortByDesc('ranking_score')
            ->values();
    }

    public function discover($job)
    {
        $cacheKey = CacheKeys::workerDiscovery(
            $job->id,
            $job->updated_at?->timestamp,
            $job->skill_id,
            $job->latitude,
            $job->longitude
        );
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($job) {
            $workers = $this->findWorkersForJob($job);

            return $this->rankWorkers($workers);
        });
    }
}
