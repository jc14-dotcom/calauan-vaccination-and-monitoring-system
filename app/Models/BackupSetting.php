<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BackupSetting extends Model
{
    /**
     * Philippine Timezone - all backup scheduling uses server-side Philippine time
     */
    const TIMEZONE = 'Asia/Manila';

    protected $fillable = [
        'schedule',
        'enabled',
        'last_backup_at',
        'next_backup_at',
        'retention_count',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'last_backup_at' => 'datetime',
        'next_backup_at' => 'datetime',
    ];

    /**
     * Get the singleton instance of backup settings.
     * Creates default settings if none exist.
     */
    public static function getInstance(): self
    {
        $settings = self::first();
        
        if (!$settings) {
            $settings = self::create([
                'schedule' => 'weekly',
                'enabled' => true,
                'retention_count' => 8,
            ]);
        }
        
        return $settings;
    }

    /**
     * Get current time in Philippine timezone (server-side, cannot be manipulated by client)
     */
    public static function getPhilippineTime(): Carbon
    {
        return Carbon::now(self::TIMEZONE);
    }

    /**
     * Get retention count based on schedule.
     */
    public static function getRetentionCount(string $schedule): int
    {
        return match ($schedule) {
            'daily' => 14,      // 2 weeks of daily backups
            'weekly' => 8,      // 2 months of weekly backups
            'monthly' => 4,     // 4 months of monthly backups
            'quarterly' => 4,   // 1 year of quarterly backups
            'yearly' => 3,      // 3 years of yearly backups
            default => 8,
        };
    }

    /**
     * Calculate next backup time based on schedule.
     * Uses Philippine timezone (Asia/Manila) - server-side only
     */
    public function calculateNextBackupTime(): Carbon
    {
        $now = self::getPhilippineTime();
        $backupTime = Carbon::today(self::TIMEZONE)->setTime(2, 0, 0); // 2:00 AM Philippine Time

        return match ($this->schedule) {
            'daily' => $backupTime->isPast() ? $backupTime->addDay() : $backupTime,
            'weekly' => $this->getNextSunday($backupTime),
            'monthly' => $this->getNextMonth($backupTime),
            'quarterly' => $this->getNextQuarter($backupTime),
            'yearly' => $this->getNextYear($backupTime),
            default => $backupTime->addDay(),
        };
    }

    /**
     * Get next Sunday at 2 AM Philippine Time.
     */
    private function getNextSunday(Carbon $baseTime): Carbon
    {
        $now = self::getPhilippineTime();
        $nextSunday = $baseTime->copy()->next(Carbon::SUNDAY)->setTime(2, 0, 0);
        
        // If today is Sunday and backup time hasn't passed
        if ($baseTime->isSunday() && !$baseTime->isPast()) {
            return $baseTime;
        }
        
        return $nextSunday;
    }

    /**
     * Get next month (1st day) at 2 AM Philippine Time.
     */
    private function getNextMonth(Carbon $baseTime): Carbon
    {
        $now = self::getPhilippineTime();
        $firstOfMonth = Carbon::create($now->year, $now->month, 1, 2, 0, 0, self::TIMEZONE);
        
        // If we're past this month's 1st at 2 AM, go to next month
        if ($firstOfMonth->isPast()) {
            return $firstOfMonth->addMonth();
        }
        
        return $firstOfMonth;
    }

    /**
     * Get next quarter (1st of Jan/Apr/Jul/Oct) at 2 AM Philippine Time.
     */
    private function getNextQuarter(Carbon $baseTime): Carbon
    {
        $now = self::getPhilippineTime();
        $currentMonth = $now->month;
        
        // Quarterly months: 1 (Jan), 4 (Apr), 7 (Jul), 10 (Oct)
        $quarterMonths = [1, 4, 7, 10];
        
        foreach ($quarterMonths as $month) {
            $quarterDate = Carbon::create($now->year, $month, 1, 2, 0, 0, self::TIMEZONE);
            if ($quarterDate->isFuture()) {
                return $quarterDate;
            }
        }
        
        // If all quarters this year have passed, return first quarter next year
        return Carbon::create($now->year + 1, 1, 1, 2, 0, 0, self::TIMEZONE);
    }

    /**
     * Get next year (Jan 1st) at 2 AM Philippine Time.
     */
    private function getNextYear(Carbon $baseTime): Carbon
    {
        $now = self::getPhilippineTime();
        $jan1 = Carbon::create($now->year, 1, 1, 2, 0, 0, self::TIMEZONE);
        
        if ($jan1->isFuture()) {
            return $jan1;
        }
        
        return Carbon::create($now->year + 1, 1, 1, 2, 0, 0, self::TIMEZONE);
    }

    /**
     * Get human-readable schedule description.
     */
    public function getScheduleDescription(): string
    {
        return match ($this->schedule) {
            'daily' => 'Every day at 2:00 AM (Philippine Time)',
            'weekly' => 'Every Sunday at 2:00 AM (Philippine Time)',
            'monthly' => 'Every 1st of the month at 2:00 AM (Philippine Time)',
            'quarterly' => 'Every quarter (Jan, Apr, Jul, Oct) at 2:00 AM (Philippine Time)',
            'yearly' => 'Every January 1st at 2:00 AM (Philippine Time)',
            default => 'Weekly',
        };
    }
}
