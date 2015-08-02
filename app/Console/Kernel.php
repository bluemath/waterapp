<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Site;
use DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

		// Every 15 minutes, ping all the update URLs for site/variable
                 
        // Limit to Red Butte
		$siteCodeContains = ['RB_'];
		
		// Uncomment to update data for all sites
		// $siteCodeContains = ['RB_', 'PR_', 'LR_'];
		
		foreach($siteCodeContains as $piece) {
			$sites = Site::where('sitecode', 'LIKE', '%' . $piece . '%')->get();
			foreach ($sites as $site) {
				$series = DB::table('series')->select('variablecode')->where('sitecode', '=', $site->sitecode)->get();
				foreach ($series as $s) {
					$url = url('/data/sites/' . $site->sitecode. '/' . $s->variablecode . '/update');
					// Ping
					$schedule->call(function () { return; })->cron('*/15 * * * *')->pingBefore($url);
				}
			}
		}       
    }
}
