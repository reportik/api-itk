<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\RPT\RPT_ALERTA as ALE;
use Log;
use App\Jobs\RptAlertJob as RAJ;
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
       $taskSchedules = ALE::where('ALE_activo', 1)
		->where('ALE_Eliminado', 0)->get();

		foreach ($taskSchedules as $key => $taskSchedule) {
			//$params = explode(",", $taskSchedule->ALE_parametros);
			$horario = explode("-", $taskSchedule->ALE_horario);
			$schedule->call(function () use ($taskSchedule) {
				/** Run your task here */
                //Log::info("{$taskSchedule->ALE_Id}");
                //$this->dispatch((new RAJ($taskSchedule->ALE_Id))->onQueue('RptAlertJob'));
                RAJ::dispatch($taskSchedule->ALE_Id)->onQueue('RptAlertJob');
			})
			->{$taskSchedule->ALE_frecuencia}()
			->{$taskSchedule->ALE_dia}()
            ->between($horario[0], $horario[1])
			->name($taskSchedule->ALE_nombre);
			//(...$params) 
		} 
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
