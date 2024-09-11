<?php

declare(strict_types=1);

namespace PremierMcpe\TradeGui;

use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use PremierMcpe\TradeGui\Features\TradeInitializer;
use PremierMcpe\TradeGui\Utils\Localization;

class Main extends PluginBase
{
    private static Main $instance;
    private array $localizations = [];

    public static function getInstance(): Main
    {
        return self::$instance;
    }

    public function getLocalization(string $locale): Localization
    {
        return $this->localizations[$locale] ?? $this->localizations[Localization::FALLBACK_LANGUAGE];
    }

    public function addLocalization(string $locale, Localization $localization): void
    {
        $this->localizations[$locale] = $localization;
    }

    protected function onEnable(): void
    {
        self::$instance = $this;

        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }

        $initializer = new TradeInitializer($this);
        $initializer->initialize();

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }
}
