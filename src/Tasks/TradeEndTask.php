<?php

namespace PremierMcpe\TradeGui\Tasks;

use pocketmine\scheduler\Task;
use PremierMcpe\TradeGui\Features\TradeInventory;

class TradeEndTask extends Task
{
    public function __construct(
        private readonly TradeInventory $inventory,
    )
    {
    }

    public function onRun(): void
    {
        $this->inventory->completeTrade();
    }
}
