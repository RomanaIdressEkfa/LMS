<?php

namespace App\Support;

use App\Http\Controllers\Api\SiteContentController;

/**
 * Resolves a bilingual UI string ({en, bn}) for a dictionary key, applying any
 * admin text_overrides on top. Memoized per-request. Rendered via <x-t>.
 */
class Translations
{
    private static ?array $dict = null;
    private static ?array $overrides = null;

    public static function get(string $key): array
    {
        self::$dict ??= require base_path('lang/dict.php');
        self::$overrides ??= SiteContentController::textOverrides();

        $base = self::$dict[$key] ?? ['en' => $key, 'bn' => $key];
        $ov = self::$overrides[$key] ?? [];

        $en = $ov['en'] ?? $base['en'] ?? $key;
        $bn = $ov['bn'] ?? $base['bn'] ?? $en;

        return ['en' => $en, 'bn' => $bn];
    }
}
