<?php

namespace Tests\Support;

use Saucebase\Core\Settings\LocalizationSettings;

/**
 * A language added the way an app adds one: `translatable:export` writes its file, the admin
 * switches it on. For the E2E only; no language ships beyond English.
 */
class LocalizationSupport
{
    public static function addLanguage(string $code): void
    {
        file_put_contents(lang_path("{$code}.json"), '{}');

        $settings = app(LocalizationSettings::class);
        $settings->enabled_locales = array_values(array_unique([...$settings->enabled_locales, $code]));
        $settings->save();
    }

    public static function removeLanguage(string $code): void
    {
        @unlink(lang_path("{$code}.json"));

        $settings = app(LocalizationSettings::class);
        $settings->enabled_locales = array_values(array_diff($settings->enabled_locales, [$code]));
        $settings->save();
    }
}
