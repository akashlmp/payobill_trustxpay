<?php

namespace App\Console\Commands;

use App\Models\Report;
use App\Jobs\EasebuzzDynamicQRStatusUpdate;
use Illuminate\Console\Command;

class EasebuzzDynamicQRStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'easebuzz:dynamic-qr-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Easebuzz dynamic QR status cron';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $transactions = Report::where('status_id', 3)
            ->where('provider_id', 592)
            ->where('provider_api_from', 4)
            ->orderBy('cron_order', 'asc')
            ->limit(20)
            ->pluck('reference_id')
            ->toArray();

        if (count($transactions) > 0) {
            foreach ($transactions as $reference_id) {
                Report::where('reference_id', $reference_id)
                    ->increment('cron_order', 1);
                EasebuzzDynamicQRStatusUpdate::dispatch($reference_id)
                    ->delay(now()->addMinutes(1));
            }
        }
    }
}
