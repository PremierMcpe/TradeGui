<?php

namespace PremierMcpe\TradeGui\Utils;

use Symfony\Component\Filesystem\Path;

class Localization
{
    public const FALLBACK_LANGUAGE = "en_US";
    private static string $dataPath;
    private array $translations = [];

    public function __construct(string $locale)
    {
        $this->loadTranslations($locale);
    }

    private function loadTranslations(string $locale): void
    {
        $this->translations = $this->parseLanguageFile($locale) ?: $this->parseLanguageFile(self::FALLBACK_LANGUAGE);
    }

    private function parseLanguageFile(string $locale): ?array
    {
        $filePath = Path::join(self::$dataPath, "languages/{$locale}.ini");
        if (file_exists($filePath)) {
            return parse_ini_file($filePath, false, INI_SCANNER_RAW);
        }
        return null;
    }

    public static function setDataPath(string $path): void
    {
        self::$dataPath = $path;
    }

    public function translate(string $translateKey, array $params = []): string
    {
        $translation = $this->translations[$translateKey] ?? $translateKey;

        return $this->replaceParams($translation, $params);
    }

    private function replaceParams(string $translation, array $params): string
    {
        foreach ($params as $key => $value) {
            $translation = str_replace("{{$key}}", $value, $translation);
        }
        return $translation;
    }

    public function __toString(): string
    {
        return json_encode($this->translations);
    }
}
