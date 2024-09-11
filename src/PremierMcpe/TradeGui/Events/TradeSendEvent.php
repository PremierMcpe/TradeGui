<?php

namespace PremierMcpe\TradeGui\Events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\player\Player;

class TradeSendEvent extends TradeEvent implements Cancellable
{
    use CancellableTrait;

    public function __construct(
        public readonly Player $sender,
        public readonly Player $receiver
    )
    {
    }

    public function getSender(): Player
    {
        return $this->sender;
    }

    public function getReceiver(): Player
    {
        return $this->receiver;
    }
}