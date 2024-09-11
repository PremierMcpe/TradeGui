<?php

namespace PremierMcpe\TradeGui\Utils;

use PremierMcpe\TradeGui\Exceptions\InvalidDirectionException;

class InventoryTitle
{
    private const SENDER_LENGTH = 12;
    private const RECEIVER_LENGTH = 11;

    private const SEPARATOR = ' <|    |> ';

    /**
     * @throws InvalidDirectionException
     */
    public static function format(string $senderName, string $receiverName): string
    {
        return self::formatPlayerName($senderName, STR_PAD_RIGHT) . self::SEPARATOR . self::formatPlayerName($receiverName, STR_PAD_RIGHT);
    }

    /**
     * @throws InvalidDirectionException
     */
    private static function formatPlayerName(string $playerName, int $direction): string
    {
        $maxLength = self::getLengthByDirection($direction);

        if (strlen($playerName) > $maxLength) {
            return substr($playerName, 0, $maxLength) . '...';
        }

        return str_pad($playerName, $maxLength, ' ', $direction);
    }

    /**
     * @throws InvalidDirectionException
     */
    private static function getLengthByDirection(int $direction = STR_PAD_RIGHT): int
    {
        return match ($direction) {
            STR_PAD_RIGHT => self::SENDER_LENGTH,
            STR_PAD_LEFT => self::RECEIVER_LENGTH,
            default => throw new InvalidDirectionException
        };
    }
}

