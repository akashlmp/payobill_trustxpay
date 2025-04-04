<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('app:cms-trans-update')->everyFiveMinutes();
        //$schedule->command('bankIt:cron')->everyMinute();
        // $schedule->command('paSprint-transaction:cron')->twiceDaily();
        $schedule->command('iserveu-payout-status:cron')->everySixHours();
        $schedule->command('iserveu-mine-payout-status:cron')->everySixHours();
        // $schedule->command('iserveu-dmt-transfer-status:cron')->everyFourHours();

        // easebuzz status cron
        $schedule->command('easebuzz:dynamic-qr-status')->everyTenMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
