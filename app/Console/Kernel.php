<?php

namespace App\Console;

use App\Console\Commands\SyncProductStatusCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    
    protected $commands = [
        SyncProductStatusCommand::class
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('Google:SyncProductStatus')->everyThreeHours();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
