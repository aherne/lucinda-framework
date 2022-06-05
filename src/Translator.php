<?php

namespace Lucinda\Project;

use Lucinda\Internationalization\Reader;

/**
 * Static wrapper of Lucinda\Internationalization\Reader required by translate function to operate
 */
class Translator
{
    private static Reader $reader;

    /**
     * Sets driver that will perform translation
     *
     * @param  Reader $reader
     * @return void
     */
    public static function set(Reader $reader): void
    {
        self::$reader = $reader;
    }

    /**
     * Gets driver that will perform translation
     *
     * @return Reader $reader
     */
    public static function get(): Reader
    {
        return self::$reader;
    }
}
