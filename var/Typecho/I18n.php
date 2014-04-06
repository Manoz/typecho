<?php
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id: I18n.php 106 2008-04-11 02:23:54Z magike.net $
 */

/**
 * I18n function
 *
 * @param string $string Text to be translated
 * @return string
 */
function _t($string) {
    if (func_num_args() <= 1) {
        return Typecho_I18n::translate($string);
    } else {
        $args = func_get_args();
        array_shift($args);
        return vsprintf(Typecho_I18n::translate($string), $args);
    }
}

/**
 * I18n function, translate and echo
 *
 * @param string $string Outputs the translated text
 * @return void
 */
function _e() {
    $args = func_get_args();
    echo call_user_func_array('_t', $args);
}

/**
 * Translation function for the plural form
 *
 * @param string $single The single form of translation
 * @param string $plural The plural form of translation
 * @param integer $number Number
 * @return string
 */
function _n($single, $plural, $number) {
    return str_replace('%d', $number, Typecho_I18n::ngettext($single, $plural, $number));
}

/**
 * An international character translation
 *
 * @package I18n
 */
class Typecho_I18n
{
    /**
     * If flag has been loaded
     *
     * @access private
     * @var boolean
     */
    private static $_loaded = false;

    /**
     * Language files
     *
     * @access private
     * @var string
     */
    private static $_lang = NULL;

    /**
     * Initialization language files
     *
     * @access private
     */
    private static function init()
    {
        /** GetText Support */
        if (false === self::$_loaded && self::$_lang && file_exists(self::$_lang)) {
            self::$_loaded = new Typecho_I18n_GetTextMulti(self::$_lang);
        }
    }

    /**
     * Translate text
     *
     * @access public
     * @param string $string The text to be translated
     * @return string
     */
    public static function translate($string)
    {
        self::init();
        return self::$_lang ? self::$_loaded->translate($string) : $string;
    }

    /**
     * Translation function for the plural form
     *
     * @param string $single The singular form of translation
     * @param string $plural The plural form of translation
     * @param integer $number Number
     * @return string
     */
    public static function ngettext($single, $plural, $number)
    {
        self::init();
        return self::$_lang ? self::$_loaded->ngettext($single, $plural, $number) : ($number > 1 ? $plural : $single);
    }

    /**
     * Meaning of time
     *
     * @access public
     * @param string $from Start time
     * @param string $now End time
     * @return string
     */
    public static function dateWord($from, $now)
    {
        $between = $now - $from;

        /** If one day */
        if ($between >= 0 && $between < 86400 && date('d', $from) == date('d', $now)) {
            /** If one hour */
            if ($between < 3600) {
                /** If one minute */
                if ($between < 60) {
                    if (0 == $between) {
                        return _t('Just now');
                    } else {
                        return str_replace('%d', $between, _n('One second ago', '%d seconds ago', $between));
                    }
                }

                $min = floor($between / 60);
                return str_replace('%d', $min, _n('A minute ago', '%d minutes ago', $min));
            }

            $hour = floor($between / 3600);
            return str_replace('%d', $hour, _n('An hour ago', '%d hours ago', $hour));
        }

        /** If it was yesterday */
        if ($between > 0 && $between < 172800
        && (date('z', $from) + 1 == date('z', $now)                             // In the case of the same year
            || date('z', $from) + 1 == date('L') + 365 + date('z', $now))) {    // New Year's case
            return _t('Yesterday %s', date('H:i', $from));
        }

        /** If one week */
        if ($between > 0 && $between < 604800) {
            $day = floor($between / 86400);
            return str_replace('%d', $day, _n('One day ago', '%d days ago', $day));
        }

        /** If it is */
        if (date('Y', $from) == date('Y', $now)) {
            return date(_t('n Month j Day'), $from);
        }

        return date(_t('Y Year m Month d Day'), $from);
    }

    /**
     * Set language entry
     *
     * @access public
     * @param string $lang Configuration Information
     * @return void
     */
    public static function setLang($lang)
    {
        self::$_lang = $lang;
    }

    /**
     * Increase language items
     *
     * @access public
     * @param string $lang Language name
     * @return void
     */
    public static function addLang($lang)
    {
        self::$_loaded->addFile($lang);
    }

    /**
     * Get the language items
     *
     * @access public
     * @return void
     */
    public static function getLang()
    {
        return self::$_lang;
    }
}
