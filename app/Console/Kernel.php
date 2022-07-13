<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Jobs\TransCompany;
use App\Jobs\TransCompanyVendor;
use App\Jobs\TransCompanyBuyer;
use App\Jobs\TransBusinessunit;
use App\Jobs\TransBrand;
use App\Jobs\TransProduct;
use App\Jobs\TransProductItem;
use App\Jobs\TransStockshell;
use App\Jobs\TransStockshellRyutu;
use App\Jobs\TransStock;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new TransCompany());
        $schedule->job(new TransCompanyVendor());
        $schedule->job(new TransCompanyBuyer());
        $schedule->job(new TransBusinessunit());
        $schedule->job(new TransBrand());
        $schedule->job(new TransProduct());
        $schedule->job(new TransProductItem());
        $schedule->job(new TransStockshell());
        $schedule->job(new TransStockshellRyutu());
        $schedule->job(new TransStock());
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
