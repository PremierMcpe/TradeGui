<?php

namespace PremierMcpe\TradeGui\Enums;

use pocketmine\data\bedrock\block\BlockTypeNames as Ids;

enum TimerStateEnum: string
{
    case HOLD = 'hold';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';

    public function getBlockType(): string
    {
        return match ($this) {
            self::HOLD => Ids::LIGHT_GRAY_STAINED_GLASS_PANE,
            self::IN_PROGRESS => Ids::YELLOW_STAINED_GLASS_PANE,
            self::DONE => Ids::GREEN_STAINED_GLASS_PANE,
        };
    }
}
