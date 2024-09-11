<?php

namespace PremierMcpe\TradeGui\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use PremierMcpe\TradeGui\Events\TradeSendEvent;
use PremierMcpe\TradeGui\Features\TradeTranslations;
use PremierMcpe\TradeGui\Forms\TradeRequestForm;
use PremierMcpe\TradeGui\Main;
use PremierMcpe\TradeGui\TradeManager;

class TradeCommand extends Command implements PluginOwned
{
    public function __construct(public Main $main)
    {
        parent::__construct('trade');
        $this->setPermission('tradegui.command.trade');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            return false;
        }

        if (empty($args[0])) {
            $sender->sendMessage($this->main->getLocalization($sender->getLocale())->translate(TradeTranslations::USAGE_MESSAGE, [
                'command' => $commandLabel
            ]));
            return false;
        }

        $receiver = $sender->getServer()->getPlayerExact($args[0]);
        if (!$receiver instanceof Player) {
            $sender->sendMessage($this->main->getLocalization($sender->getLocale())->translate(TradeTranslations::PLAYER_NOT_FOUND_MESSAGE));
            return false;
        }

        $event = new TradeSendEvent($sender, $receiver);
        $event->call();

        if ($event->isCancelled()) {
            return false;
        }

        TradeManager::addRequest($sender->getName(), $receiver->getName());
        $receiver->sendForm(new TradeRequestForm($sender));

        return true;
    }

    public function getOwningPlugin(): Plugin
    {
        return Main::getInstance();
    }
}
