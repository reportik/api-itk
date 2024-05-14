<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use App\Models\RPT\RPT_ALERTA as ALE;

class RptAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $alertId;
    /**
     * Create a new job instance.
     */
    public function __construct($ALE_Id)
    {
        $this->alertId = $ALE_Id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $alerta = ALE::find($this->alertId);
        $alerta->process();
        //Log::info("Id: ". $this->alertId." run at " . \Carbon\Carbon::now());
    }
}
