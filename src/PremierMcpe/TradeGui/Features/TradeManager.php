<?php

namespace PremierMcpe\TradeGui;

class TradeManager
{
    /**
     * @var array<string,string>
     */
    private static array $requests = [];

    public static function addRequest(string $senderName, string $receiverName): bool
    {
        self::$requests[$senderName] = $receiverName;

        return self::hasRequest($senderName);
    }

    public static function hasRequest(string $senderName): bool
    {
        return isset(self::$requests[$senderName]);
    }

    public static function removeRequest(string $senderName): bool
    {
        unset(self::$requests[$senderName]);

        return self::hasRequest($senderName);
    }

    public static function getRequest(string $senderName): ?string
    {
        return self::hasRequest($senderName) ? self::$requests[$senderName] : null;
    }
}