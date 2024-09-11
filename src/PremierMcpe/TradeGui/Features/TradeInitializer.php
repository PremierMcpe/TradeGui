<?php

namespace PremierMcpe\TradeGui\Features;

use PremierMcpe\TradeGui\Commands\TradeCommand;
use PremierMcpe\TradeGui\Main;
use PremierMcpe\TradeGui\Utils\Localization;
use Symfony\Component\Filesystem\Path;

class TradeInitializer
{
    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function initialize(): void
    {
        $this->saveLanguageResources();
        Localization::setDataPath($this->plugin->getDataFolder());

        $languages = $this->getAvailableLanguages();
        foreach ($languages as $language) {
            $this->plugin->addLocalization($language, new Localization($language));
        }

        $this->plugin->getServer()->getCommandMap()->register('trade', new TradeCommand($this->plugin));
    }

    private function saveLanguageResources(): void
    {
        $languagePath = $this->plugin->getResourcePath("languages");
        $files = scandir($languagePath, SCANDIR_SORT_NONE);

        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_file($languagePath . '/' . $file)) {
                    $this->plugin->saveResource("languages/{$file}");
                }
            }
        }
    }

    /**
     * @return array<string>
     */
    private function getAvailableLanguages(): array
    {
        $languageDir = Path::join($this->plugin->getDataFolder(), "languages");
        $files = is_array(scandir($languageDir)) ? array_filter(scandir($languageDir), function (string $file): bool {
            return str_ends_with($file, ".ini");
        }) : [];

        return array_map(function (string $file): string {
            return pathinfo($file, PATHINFO_FILENAME); // Removes the ".ini" extension
        }, $files);
    }
}
