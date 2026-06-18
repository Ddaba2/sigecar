<?php

namespace App\Console\Commands;

use App\Services\MarketeurStockService;
use Illuminate\Console\Command;

class SyncMarketeurStocks extends Command
{
    protected $signature = 'marketeur-stocks:sync';

    protected $description = 'Recalcule les stocks opérateurs depuis les opérations et les cuves';

    public function handle(MarketeurStockService $service): int
    {
        $service->syncAll();

        $this->info('Stocks opérateurs synchronisés.');

        return self::SUCCESS;
    }
}
