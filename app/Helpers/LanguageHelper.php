<?php

namespace App\Helpers;

class LanguageHelper
{
    public static function getSupportedLanguages(): array
    {
        return config('languages.supported', []);
    }

    public static function getCurrentLanguage(): array
    {
        $locale = app()->getLocale();
        $languages = self::getSupportedLanguages();

        return $languages[$locale] ?? $languages[config('languages.default', 'en')];
    }

    public static function isSupported(string $locale): bool
    {
        return array_key_exists($locale, self::getSupportedLanguages());
    }

    public static function getLanguageData(string $locale): ?array
    {
        $languages = self::getSupportedLanguages();

        return $languages[$locale] ?? null;
    }
}
