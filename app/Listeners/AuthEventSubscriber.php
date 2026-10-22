<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\PasswordReset;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Log;

class AuthEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        $guard = $event->guard;
        
        // Skip logging for parent accounts
        if ($guard === 'parents') {
            return;
        }
        
        $logName = $this->getLogName($guard);
        
        activity($logName)
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'guard' => $guard,
            ])
            ->event('login')
            ->log("{$this->getUserType($guard)} logged in");
    }

    /**
     * Handle user logout events.
     */
    public function handleLogout(Logout $event): void
    {
        $user = $event->user;
        $guard = $event->guard;
        
        if (!$user) {
            return;
        }
        
        // Skip logging for parent accounts
        if ($guard === 'parents') {
            return;
        }
        
        $logName = $this->getLogName($guard);
        
        activity($logName)
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip' => request()->ip(),
                'guard' => $guard,
            ])
            ->event('logout')
            ->log("{$this->getUserType($guard)} logged out");
    }

    /**
     * Handle failed login attempts.
     */
    public function handleFailed(Failed $event): void
    {
        $guard = $event->guard;
        
        // Skip logging for parent accounts
        if ($guard === 'parents') {
            return;
        }
        
        activity('auth')
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'guard' => $guard,
                'credentials' => [
                    'username' => $event->credentials['username'] ?? 'unknown',
                ],
            ])
            ->event('failed_login')
            ->log("Failed login attempt for {$this->getUserType($guard)}");
    }

    /**
     * Handle password reset events.
     */
    public function handlePasswordReset(PasswordReset $event): void
    {
        $user = $event->user;
        
        // Skip logging for parent accounts - check if user is from Parents model
        if ($user instanceof \App\Models\Parents) {
            return;
        }
        
        activity('auth')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip' => request()->ip(),
            ])
            ->event('password_reset')
            ->log("Password was reset");
    }

    /**
     * Get the log name based on guard.
     */
    private function getLogName(string $guard): string
    {
        return match($guard) {
            'parents' => 'parent',
            'health_worker' => 'health_worker',
            default => 'auth',
        };
    }

    /**
     * Get the user type label based on guard.
     */
    private function getUserType(string $guard): string
    {
        return match($guard) {
            'parents' => 'Parent',
            'health_worker' => 'Health worker',
            default => 'User',
        };
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
            PasswordReset::class => 'handlePasswordReset',
        ];
    }
}
