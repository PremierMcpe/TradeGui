<?php

namespace PremierMcpe\TradeGui;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class EventListener implements Listener
{
    public function handlePlayerQuit(PlayerQuitEvent $event): void
    {
        if (TradeManager::hasRequest($event->getPlayer()->getName())) {
            TradeManager::removeRequest($event->getPlayer()->getName());
        }
    }
}