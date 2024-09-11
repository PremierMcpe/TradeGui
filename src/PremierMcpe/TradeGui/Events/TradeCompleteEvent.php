<?php

namespace PremierMcpe\TradeGui\Events;

use pocketmine\player\Player;

class TradeCompleteEvent extends TradeEvent
{

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