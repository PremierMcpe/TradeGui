<?php

namespace PremierMcpe\TradeGui\Tasks;

use pocketmine\scheduler\Task;
use PremierMcpe\TradeGui\Features\TradeInventory;

class TradeStartTask extends Task
{
    public function __construct(
        private readonly TradeInventory $inventory,
        private int                     $seconds = 5
    )
    {
    }

    public function onRun(): void
    {
        if ($this->seconds >= 0) {
            $this->inventory->setTimerSecond($this->seconds);
        }

        $this->seconds--;

        if ($this->seconds < -1) {
            $this->getHandler()->cancel();
            $this->inventory->endTrade();
        }
    }
}
