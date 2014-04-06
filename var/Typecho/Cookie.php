<?php
/**
 * Cookie support
 *
 * @category typecho
 * @package Cookie
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Cookie support class
 *
 * @author qining
 * @category typecho
 * @package Cookie
 */
class Typecho_Cookie
{
    /**
     * Prefix
     *
     * @var string
     * @access private
     */
    private static $_prefix = '';

    /**
     * path
     *
     * @var string
     * @access private
     */
    private static $_path = '/';

    /**
     * Set Prefix
     *
     * @param string $url
     * @access public
     * @return void
     */
    public static function setPrefix($url)
    {
        self::$_prefix = md5($url);
        $parsed = parse_url($url);

        /** Force add slash after the path */
        self::$_path = empty($parsed['path']) ? '/' : Typecho_Common::url(NULL, $parsed['path']);
    }

    /**
     * Get Prefix
     *
     * @access public
     * @return string
     */
    public static function getPrefix()
    {
        return self::$_prefix;
    }

    /**
     * Gets the specified COOKIE value
     *
     * @access public
     * @param string $key Specified parameters
     * @param string $default Default parameters
     * @return mixed
     */
    public static function get($key, $default = NULL)
    {
        $key = self::$_prefix . $key;
        $value = isset($_COOKIE[$key]) ? $_COOKIE[$key] : (isset($_POST[$key]) ? $_POST[$key] : $default);
        return is_array($value) ? $default : $value;
    }

    /**
     * Set specified COOKIE value
     *
     * @access public
     * @param string $key Specified parameters
     * @param mixed $value The value
     * @param integer $expire Expiration time, the default is 0
     * @return void
     */
    public static function set($key, $value, $expire = 0)
    {
        $key = self::$_prefix . $key;
        setrawcookie($key, rawurlencode($value), $expire, self::$_path);
        $_COOKIE[$key] = $value;
    }

    /**
     * Delete the specified COOKIE value
     *
     * @access public
     * @param string $key Specified parameters
     * @return void
     */
    public static function delete($key)
    {
        $key = self::$_prefix . $key;
        if (!isset($_COOKIE[$key])) {
            return;
        }

        setcookie($key, '', time() - 2592000, self::$_path);
        unset($_COOKIE[$key]);
    }
}

