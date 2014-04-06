<?php
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id: Route.php 107 2008-04-11 07:14:43Z magike.net $
 */

/**
 * Typecho Component base class
 *
 * @todo    Increase the buffer cache
 * @package Router
 */
class Typecho_Router
{
    /**
     * The current route name
     *
     * @access public
     * @var string
     */
    public static $current;

    /**
     * Has completed analytical routing table configuration
     *
     * @access private
     * @var mixed
     */
    private static $_routingTable = array();

    /**
     * Full path
     *
     * @access private
     * @var string
     */
    private static $_pathInfo = NULL;

    /**
     * Parse path
     *
     * @access public
     * @param string $pathInfo Full path
     * @param mixed $parameter Input parameters
     * @return mixed
     */
    public static function match($pathInfo, $parameter = NULL)
    {
        foreach (self::$_routingTable as $key => $route) {
            if (preg_match($route['regx'], $pathInfo, $matches)) {
                self::$current = $key;

                try {
                    /** Loading parameters */
                    $params = NULL;

                    if (!empty($route['params'])) {
                        unset($matches[0]);
                        $params = array_combine($route['params'], $matches);
                    }

                    $widget = Typecho_Widget::widget($route['widget'], $parameter, $params);

                    return $widget;

                } catch (Exception $e) {
                    if (404 == $e->getCode()) {
                        Typecho_Widget::destory($route['widget']);
                        continue;
                    }

                    throw $e;
                }
            }
        }

        return false;
    }

    /**
     * Set the full path
     *
     * @access public
     * @param string $pathInfo
     * @return void
     */
    public static function setPathInfo($pathInfo = '/')
    {
        self::$_pathInfo = $pathInfo;
    }

    /**
     * Get the full path
     *
     * @access public
     * @return string
     */
    public static function getPathInfo()
    {
        if (NULL === self::$_pathInfo) {
            self::setPathInfo();
        }

        return self::$_pathInfo;
    }

    /**
     * Route distribution function
     *
     * @param string $path The purpose of the directory where the file
     * @return void
     * @throws Typecho_Route_Exception
     */
    public static function dispatch()
    {
        /** Get PATHINFO */
        $pathInfo = self::getPathInfo();

        foreach (self::$_routingTable as $key => $route) {
            if (preg_match($route['regx'], $pathInfo, $matches)) {
                self::$current = $key;

                try {
                    /** Loading parameters */
                    $params = NULL;

                    if (!empty($route['params'])) {
                        unset($matches[0]);
                        $params = array_combine($route['params'], $matches);
                    }

                    $widget = Typecho_Widget::widget($route['widget'], NULL, $params);

                    if (isset($route['action'])) {
                        $widget->{$route['action']}();
                    }

                    return;

                } catch (Exception $e) {
                    if (404 == $e->getCode()) {
                        Typecho_Widget::destory($route['widget']);
                        continue;
                    }

                    throw $e;
                }
            }
        }

        /** Abnormal load routing support */
        throw new Typecho_Router_Exception("Path '{$pathInfo}' not found", 404);
    }

    /**
     * Routing anti-analytic functions
     *
     * @param string $name Routing configuration table name
     * @param string $value Routing padding
     * @param string $prefix Final synthesis path of Prefix
     * @return string
     */
    public static function url($name, array $value = NULL, $prefix = NULL)
    {
        $route = self::$_routingTable[$name];

        // An array of key exchange
        $pattern = array();
        foreach ($route['params'] as $row) {
            $pattern[$row] = isset($value[$row]) ? $value[$row] : '{' . $row . '}';
        }

        return Typecho_Common::url(vsprintf($route['format'], $pattern), $prefix);
    }

    /**
     * Set the default router configuration
     *
     * @access public
     * @param mixed $routes Configuration Information
     * @return void
     */
    public static function setRoutes($routes)
    {
        if (isset($routes[0])) {
            self::$_routingTable = $routes[0];
        } else {
            /** Resolve routing configuration */
            $parser = new Typecho_Router_Parser($routes);
            self::$_routingTable = $parser->parse();
        }
    }

    /**
     * Obtain routing information
     *
     * @param string $routeName Route Name
     * @static
     * @access public
     * @return void
     */
    public static function get($routeName)
    {
        return isset(self::$_routingTable[$routeName]) ? self::$_routingTable[$routeName] : NULL;
    }
}
