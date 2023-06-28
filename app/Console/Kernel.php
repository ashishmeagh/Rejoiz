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
        //Commands\DeleteZip::class
        Commands\Cleanup\ProductImageCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->command('reminder:mail')->daily();
        //$schedule->command('delete:zip')->daily();
        $schedule->command('delete:zip')->everyTenMinutes();
        // $schedule->call('app/Common/Services/GeneralService@mail_after_duedate_over')->everyTenMinutes();

        $schedule->command('inventory:mail')->everyTenMinutes();
        $schedule->command('delete_temp_bag:zero')->daily();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
