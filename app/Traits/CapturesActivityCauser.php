<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Contracts\Activity;

/**
 * Trait to capture the authenticated user as the causer for activity logs.
 * This handles multiple guards (health_worker, parent, web) properly.
 */
trait CapturesActivityCauser
{
    /**
     * Tap into activity logging to set the correct causer based on the authenticated guard.
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        // Check health_worker guard first (most common for admin actions)
        if (Auth::guard('health_worker')->check()) {
            $activity->causer()->associate(Auth::guard('health_worker')->user());
            return;
        }

        // Check parents guard (note: plural 'parents', not 'parent')
        if (Auth::guard('parents')->check()) {
            $activity->causer()->associate(Auth::guard('parents')->user());
            return;
        }

        // Check default web guard (for User model)
        if (Auth::guard('web')->check()) {
            $activity->causer()->associate(Auth::guard('web')->user());
            return;
        }

        // If no user is authenticated, the causer will be null (System)
    }
}
