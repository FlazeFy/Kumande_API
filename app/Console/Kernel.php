<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Laravel\ScheduleMonitor\ScheduleHealth;

use App\Schedule\ConsumeSchedule;
use App\Schedule\PersonalAccessTokenSchedule;
use App\Schedule\UserSchedule;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // In staging
        // $schedule->call([new ConsumeSchedule, 'remind_consume_schedule'])->hourly();
        // $schedule->call([new PersonalAccessTokenSchedule, 'clean'])->dailyAt('01:00');
        // $schedule->call([new UserSchedule, 'remind_clean'])->dailyAt('04:00');
        // $schedule->call([new ConsumeSchedule, 'summary_day'])->dailyAt('02:00');
        // $schedule->call([new ConsumeSchedule, 'summary_weekly'])->dailyAt('05:00');

        // In development
        $schedule->command(ConsumeSchedule::remind_consume_schedule())->everyMinute();
        $schedule->command(PersonalAccessTokenSchedule::clean())->everyMinute();
        $schedule->command(UserSchedule::remind_clean())->everyMinute();
        $schedule->command(ConsumeSchedule::summary_day())->everyMinute();
        $schedule->command(ConsumeSchedule::summary_weekly())->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
