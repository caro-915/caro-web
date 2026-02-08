<?php

namespace App\Console\Commands;

use App\Services\BoostService;
use Illuminate\Console\Command;

class ExpireBoosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boosts:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire boosts that have passed their expiry date';

    /**
     * Execute the console command.
     */
    public function handle(BoostService $boostService): int
    {
        $count = $boostService->expireOldBoosts();
        $this->info("Expired $count boost(s).");
        return Command::SUCCESS;
    }
}
