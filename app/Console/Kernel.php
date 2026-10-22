<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\AutoSaveMonthlyReport::class,
        \App\Console\Commands\AutoSaveQuarterlyReport::class,
        \App\Console\Commands\SendVaccinationReminders::class,
        \App\Console\Commands\RunScheduledBackup::class,
        \App\Console\Commands\ProcessMissedVaccinations::class,
        \App\Console\Commands\ResetVaccinationDayStatus::class,
        \App\Console\Commands\BackfillPatientVaccineRecords::class,
        \App\Console\Commands\CreateRhuAdmin::class,
    ];

    /**
     * Define the application's command schedule.
     * 
     * NOTE: In Laravel 11, scheduling is defined in routes/console.php
     * This method is kept for backwards compatibility but scheduling
     * should be managed in routes/console.php
     */
    protected function schedule(Schedule $schedule)
    {
        // Scheduling is now defined in routes/console.php for Laravel 11
        // See: routes/console.php for all scheduled commands
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
