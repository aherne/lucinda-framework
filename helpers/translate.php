<?php

use Lucinda\Project\Translator;

/**
 * Translates text into preferred or default locale. Like gettext, ignores errors.
 *
 * @param  string   $key        Dictionary key by which translation can be accessed, relative to dictionary name (eg: furniture)
 * @param  string   $domain     Dictionary file name in which translation should be found (eg: house)
 * @param  string[] $parameters Varargs of custom values to replace in translation (placeholders %i, where i is custom value index)
 * @return string Value of translation (if found) or dictionary key (if not found)
 */
function translate(string $key, string $domain = "", ...$parameters): string
{
    try {
        $translation = Translator::get()->getTranslation($key, $domain);
        if (!empty($parameters)) {
            foreach ($parameters as $key => $value) {
                $translation = str_replace("%".$key, $value, $translation);
            }
        }
        return $translation;
    } catch (Exception $e) {
        return $key;
    }
}
