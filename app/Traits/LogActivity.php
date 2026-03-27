<?php

namespace App\Traits;

use App\Services\ActivityLogService;

trait LogActivity
{
    /**
     * Access the ActivityLog service anywhere in the controller
     */
    protected function activityLog(): ActivityLogService
    {
        return app(ActivityLogService::class);
    }
}
