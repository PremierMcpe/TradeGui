<?php

namespace PremierMcpe\TradeGui\Forms;

use dktapps\pmforms\ModalForm;
use pocketmine\player\Player;
use PremierMcpe\TradeGui\Features\TradeInventory;
use PremierMcpe\TradeGui\Features\TradeTranslations;
use PremierMcpe\TradeGui\Main;
use PremierMcpe\TradeGui\TradeManager;

class TradeRequestForm extends ModalForm
{
    final public function __construct(Player $sender)
    {
        $localization = Main::getInstance()->getLocalization($sender->getLocale());
        parent::__construct($localization->translate(TradeTranslations::REQUEST_FORM_TITLE), $localization->translate(TradeTranslations::REQUEST_FORM_DESCRIPTION, [
            'sender' => $sender->getName()
        ]), function (Player $player, bool $choice) use ($sender): void {
            if ($choice) {
                if (($tradeRequest = TradeManager::getRequest($sender->getName())) !== null) {
                    if ($tradeRequest === $player->getName()) {
                        $inventory = new TradeInventory($sender, $player);
                        $inventory->open();
                        TradeManager::removeRequest($sender);
                    }
                }
            }
        });
    }
}