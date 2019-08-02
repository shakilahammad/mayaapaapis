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
        'App\Console\Commands\UnSubscribeUser',
        'App\Console\Commands\SubscriptionAlert',
        'App\Console\Commands\PostLiteracyNotification',
        'App\Console\Commands\EmailNewsletter',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('unsubscribe:user')->everyThirtyMinutes();
        $schedule->command('subscription:alert')->dailyAt('10:00');
        $schedule->command('literacy:notification')->dailyAt('06:00');
        $schedule->command('push:search_view')->dailyAt('21:30');
//        $schedule->command('email:newsletter')->weekly()->thursdays()->at('21:59');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
