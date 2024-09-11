<?php

namespace PremierMcpe\TradeGui\Enums;

use pocketmine\data\bedrock\item\ItemTypeNames as Ids;
use PremierMcpe\TradeGui\Features\TradeTranslations;

enum ActionEnum: string
{
    case APPROVE = 'approve';
    case APPROVED = 'approved';

    public function getItemName(): string
    {
        return match ($this) {
            self::APPROVE => TradeTranslations::ACTION_APPROVE,
            self::APPROVED => TradeTranslations::ACTION_APPROVED,
        };
    }

    public function getItemType(): string
    {
        return match ($this) {
            self::APPROVE => Ids::GRAY_DYE,
            self::APPROVED => Ids::LIME_DYE,
        };
    }
}
